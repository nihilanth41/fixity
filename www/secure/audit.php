<?php

require('setup.php');

$smarty = new Smarty_Fixity();
$smarty->configLoad('fixity.cfg');
$configVars = $smarty->getConfigVars();

# Get audit page number (apn) from query string otherwise assume first page
if( isset($_GET['apn']) && ($_GET['apn'] > 0) )
{
	$apn = $_GET['apn'];
}
else {
	$apn = 1;
}

$darklib_prefix = $configVars['darklib_prefix'];
$darklib_dirs = explode(',', $configVars['darklib_dirs']);
array_walk($darklib_dirs, 'trim_value');
$darklib_postfix = $configVars['darklib_postfix'];
$resultsPerPage = $configVars['resultsPerPage'];
$navLinksPerPage = $configVars['navLinksPerPage'];

$dpn = $_GET['dpn'];
$uuid = $_GET['uuid'];

$qlimit = $resultsPerPage;
$qoffset = ($apn-1)*$resultsPerPage;

$dbdir = $_GET['dbdir']; 

$shib_whitelist = explode(',', $configVars['shibboleth_whitelist']);
array_walk($shib_whitelist, 'trim_value');

if( (isset($_SERVER['samaccountname'])) )
{
	$pawprint = $_SERVER['samaccountname'];
}


if(in_array($pawprint, $shib_whitelist))
{
	if(in_array($dbdir, $darklib_dirs))
	{
		$db_path = "$darklib_prefix" . "/$dbdir/" . "$darklib_postfix"; 

		$dbh = new PDO("sqlite:/$db_path");
		$query = "SELECT MIN(rowid),MAX(rowid) FROM audit WHERE (uuid = :uuid AND attr = :attr)";
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $dbh->prepare($query);
		$stmt->setFetchMode(PDO::FETCH_NUM);
		
		$attr = 'A';
		$stmt->execute(array(':uuid' => $uuid, ':attr' => $attr));
		$arrTemp = $stmt->fetch();	
		if(!isset($arrTemp[0]) || !isset($arrTemp[1]))
			{ $aCount = 0; }
		else
			{ $aCount = $arrTemp[1] - ($arrTemp[0] - 1); }
		
		$attr = 'D';
		$stmt->execute(array(':uuid' => $uuid, ':attr' => $attr));
		$arrTemp = $stmt->fetch();
		if(!isset($arrTemp[0]) || !isset($arrTemp[1])) 
			{ $dCount = 0; }
		else 
			{ $dCount = $arrTemp[1] - ($arrTemp[0] - 1); }

		$attr = 'M';
		$stmt->execute(array(':uuid' => $uuid, ':attr' => $attr));
		$arrTemp = $stmt->fetch();
		if(!isset($arrTemp[0]) || !isset($arrTemp[1]))
			{ $mCount = 0; }
		else 
			{ $mCount = $arrTemp[1] - ($arrTemp[0] - 1); }

		$arrCounts = array($aCount, $mCount, $dCount);
		$maxCount = max($arrCounts);
		$totCount = $aCount + $mCount + $dCount;

		$query = "SELECT relpath,size,mtime,sha256 FROM audit WHERE (uuid = :uuid AND attr = :attr ) LIMIT :qlimit OFFSET :qoffset"; 
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(":qlimit", $qlimit);
		$stmt->bindParam(":qoffset", $qoffset);
		$stmt->bindParam(":uuid", $uuid);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		// Add 
		$attr = 'A';
		$stmt->bindParam(":attr", $attr);
		$stmt->execute();
		$ares = $stmt->fetchAll();
		// Delete
		$attr = 'D';
		$stmt->bindParam(":attr", $attr);
		$stmt->execute();
		$dres = $stmt->fetchAll();
		// Modify
		$attr = 'M';
		$stmt->bindParam(":attr", $attr);
		$stmt->execute();
		$mres = $stmt->fetchAll();

		$stmt = null;
		$dbh = null;

		for($i=0; $i<count($ares); $i++)
		{
			if(isset($ares[$i]['mtime']))
			{
				$ares[$i]['mtime'] = utc_to_localtime($ares[$i]['mtime']);
			}
			if(isset($ares[$i]['size']))
			{
				$ares[$i]['size'] = FileSizeConvert($ares[$i]['size']);
			}
		}
		for($i=0; $i<count($dres); $i++)
		{
			if(isset($dres[$i]['mtime']))
			{
				$dres[$i]['mtime'] = utc_to_localtime($dres[$i]['mtime']);
			}
			if(isset($dres[$i]['size']))
			{
				$dres[$i]['size'] = FileSizeConvert($dres[$i]['size']);
			}	
		}
		for($i=0; $i<count($mres); $i++) 
		{
			if(isset($mres[$i]['mtime']))
			{
				$mres[$i]['mtime'] = utc_to_localtime($mres[$i]['mtime']);
			}
			if(isset($mres[$i]['size']))
			{
				$mres[$i]['size'] = FileSizeConvert($mres[$i]['size']);
			}
		}


		$numPages = ceil($maxCount / $resultsPerPage);
		if($apn > $numPages) { $apn = $numPages; }
		elseif($apn < 1) { $apn = 1; }
		if($apn == 1) {
			$result_lower = 1;
			$prevpage = 1;
		}
		else {
			$result_lower = ($resultsPerPage * $apn)-($resultsPerPage-1);
			$prevpage = $apn-1;
		}
		if($apn == $numPages) {
			$nextpage = $apn;
			$result_upper = $maxCount;
		}
		else {
			$nextpage = $apn+1;
			$result_upper = $result_lower+$resultsPerPage-1;
		}

		$first_hl = "audit.php?dbdir=".$dbdir."&dpn=".$dpn."&uuid=".$uuid."&apn=1";
		$prev_hl = "audit.php?dbdir=".$dbdir."&dpn=".$dpn."&uuid=".$uuid."&apn=".$prevpage;
		$next_hl = "audit.php?dbdir=".$dbdir."&dpn=".$dpn."&uuid=".$uuid."&apn=".$nextpage;
		$last_hl = "audit.php?dbdir=".$dbdir."&dpn=".$dpn."&uuid=".$uuid."&apn=".$numPages;

		$navArray = array();
		# Generate array of hyperlinks based on current page
		if($apn < $navLinksPerPage)
		{
			for($i=1; $i<=$navLinksPerPage; $i++)
			{
				if($i > $numPages)
				{
					break;
				}
				if($i == $apn)
				{
					$hl = $apn;
				}
				else
				{
					$hl = '<a href="'."audit.php?dbdir=".$dbdir."&dpn=".$dpn."&uuid=".$uuid."&apn=".$i.'">'.$i."</a>";
				}
				array_push($navArray, $hl);
			}
		}
		elseif($apn >= $numPages-floor($navLinksPerPage/2))
		{
			for($i=($numPages-($navLinksPerPage)+1); $i<=$numPages; $i++)
			{
				if($i == $apn)
				{
					$hl = $apn;
				}
				else
				{
					$hl = '<a href="'."audit.php?dbdir=".$dbdir."&dpn=".$dpn."&uuid=".$uuid."&apn=".$i.'">'.$i."</a>";
				}
				array_push($navArray, $hl);
			}
		}
		else
		{
			$j= -(floor($navLinksPerPage/2));
			do {
				$i = $apn + $j;
				if($i > $numPages)
				{
					break;
				}
				if($i == $apn)
				{
					$hl = $apn;
				}
				else
				{
					$hl = '<a href="'."audit.php?dbdir=".$dbdir."&dpn=".$dpn."&uuid=".$uuid."&apn=".$i.'">'.$i."</a>";
				}
				array_push($navArray, $hl);
				$j++;
			}while($j < ceil($navLinksPerPage/2));
		}

		$smarty->assign('result_lower', $result_lower);
		$smarty->assign('result_upper', $result_upper);
		$smarty->assign('result_total', $maxCount);

		$smarty->assign('db_path', $db_path);
		$smarty->assign('dbdir', $dbdir);
		$smarty->assign('uuid', $uuid);
		
		$smarty->assign('ares', $ares);
		$smarty->assign('mres', $mres);
		$smarty->assign('dres', $dres);

		$smarty->assign('dpn', $dpn);
		$smarty->assign('pageno', $apn);
		$smarty->assign('numpages', $numPages);
		$smarty->assign('prevpage', $prevpage);
		$smarty->assign('nextpage', $nextpage);
		$smarty->assign('firstpage', 1);
		$smarty->assign('lastpage', $numPages);
		$smarty->assign('first_hl', $first_hl);
		$smarty->assign('prev_hl', $prev_hl);
		$smarty->assign('next_hl', $next_hl);
		$smarty->assign('last_hl', $last_hl);
		
		$smarty->assign('navArray', $navArray);
			
		$smarty->display('audit.tpl');
	}
	else {
		echo "Error: directory $dbdir not valid\n";
	}
}
else
{
	$smarty->assign('pawprint', $pawprint);
	$smarty->assign('webmaster_contact', $configVars['webmaster_contact']);
	$smarty->display('error.tpl');
}

?>
