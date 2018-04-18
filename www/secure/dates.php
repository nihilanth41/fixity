<?php

require('setup.php');
$smarty = new Smarty_Fixity();
$smarty->configLoad('fixity.cfg');
$configVars = $smarty->getConfigVars();

$darklib_dirs = explode(',', $configVars['darklib_dirs']);	
array_walk($darklib_dirs, 'trim_value');
$darklib_prefix = $configVars['darklib_prefix'];		
$darklib_postfix = $configVars['darklib_postfix'];		
$resultsPerPage = $configVars['resultsPerPage'];		
$navLinksPerPage = $configVars['navLinksPerPage']; 

# Get dates page number (dpn) from query string otherwise assume first page
if( (isset($_GET['dpn'])) && ($_GET['dpn'] > 0) )
{
	$dpn = $_GET['dpn'];
}
else {
	$dpn = 1; 
}

$qlimit = $resultsPerPage;
$qoffset = ($dpn-1)*$resultsPerPage;
$dbdir = $_GET['dbdir'];

$shib_whitelist = explode(',', $configVars['shibboleth_whitelist']);
array_walk($shib_whitelist, 'trim_value');

if( (isset($_SERVER['samaccountname'])) )
{
	$pawprint = $_SERVER['samaccountname'];
}

if(in_array($pawprint, $shib_whitelist))
{
	# Validate $dbdir against list of valid db dirs
	if(in_array($dbdir, $darklib_dirs))
	{
		# $dbdir is valid:
		$db_path = "$darklib_prefix" . "/$dbdir/" . "$darklib_postfix"; 
		
		$dbh = new PDO("sqlite:/$db_path");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$rowCount_tot = $dbh->query('SELECT COUNT(*) FROM dates')->fetchColumn(); 
		$query = "SELECT uuid,utime,finished FROM dates ORDER BY utime DESC LIMIT $qlimit OFFSET $qoffset";
		$stmt = $dbh->query($query);
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$length = count($res);
		
		# Prepare second query	
		#$query = "SELECT COUNT(*) FROM audit WHERE (uuid = :uuid)";
		$query = "SELECT MIN(rowid),MAX(rowid) FROM audit WHERE (uuid = :uuid)";
		$stmt = $dbh->prepare($query);
		$stmt->setFetchMode(PDO::FETCH_NUM);

		# Get row count for each uuid
		for($i=0; $i<$length; $i++)
		{
			$stmt->execute(array(':uuid' => $res[$i]['uuid']));
			$arrTemp = $stmt->fetch();	
			if(!isset($arrTemp[0]) || !isset($arrTemp[1]))
				{ $fileCount = 0; }
			else
				{ $fileCount = $arrTemp[1] - ($arrTemp[0] - 1); }
			$res[$i]['count'] = $fileCount;
		}	

		$stmt = null;	
		$dbh = null;
		
		for($i=0; $i<$length; $i++)
		{
			if(isset($res[$i]['utime']))
			{
				$res[$i]['utime'] = utc_to_localtime($res[$i]['utime']);
				$res[$i]['csv'] = "csv.php?dbdir=$dbdir" . "&uuid=" . $res[$i]['uuid'];
			}
			if(isset($res[$i]['finished']))
			{
				$res[$i]['finished'] = $res[$i]['finished'] ? 'Yes' : 'No';
			}
		}

		# Calculate number of pages needed. 
		$numPages = ceil($rowCount_tot / $resultsPerPage);
		if($dpn > $numPages) { $dpn = $numPages; }
		elseif($dpn < 1) { $dpn = 1; }
		if($dpn == 1) {
			$result_lower = 1;
			$prevpage = 1;
		}
		else {
			# Use the current page number to determine which rows are displayed
			# e.g. ($dpn == 2) displays rows 11-20 (when $resultsPerPage == 10)	
			$result_lower = ($resultsPerPage * $dpn)-($resultsPerPage-1);
			$prevpage = $dpn-1;
		}
		if($dpn == $numPages) {
			$nextpage = $dpn;
			$result_upper = $rowCount_tot;
		}
		else {
			$nextpage = $dpn+1;
			$result_upper = $result_lower+$resultsPerPage-1;
		}

		$first_hl = "dates.php?dbdir=".$dbdir."&dpn=1";
		$prev_hl = "dates.php?dbdir=".$dbdir."&dpn=".$prevpage;
		$next_hl = "dates.php?dbdir=".$dbdir."&dpn=".$nextpage;
		$last_hl = "dates.php?dbdir=".$dbdir."&dpn=".$numPages;
		
		$navArray = array();
		# Generate array of hyperlinks based on current page
		if($dpn < $navLinksPerPage)
		{
			for($i=1; $i<=$navLinksPerPage; $i++)
			{
				if($i > $numPages)
				{
					break;
				}
				if($i == $dpn)
				{
					$hl = $dpn;
				}
				else
				{
					$hl = '<a href="'."dates.php?dbdir=".$dbdir."&dpn=".$i.'">'.$i."</a>";
				}
				array_push($navArray, $hl);
			}
		}
		elseif($dpn >= $numPages-floor($navLinksPerPage/2))
		{
			for($i=($numPages-($navLinksPerPage)+1); $i<=$numPages; $i++)
			{
				if($i == $dpn)
				{
					$hl = $dpn;
				}
				else
				{
					$hl = '<a href="'."dates.php?dbdir=".$dbdir."&dpn=".$i.'">'.$i."</a>";
				}
				array_push($navArray, $hl);
			}
		}
		else
		{
			$j= -(floor($navLinksPerPage/2));
			do {
				$i = $dpn + $j;
				if($i > $numPages)
				{
					break;
				}
				if($i == $dpn)
				{
					$hl = $dpn;
				}
				else
				{
					$hl = '<a href="'."dates.php?dbdir=".$dbdir."&dpn=".$i.'">'.$i."</a>";
				}
				array_push($navArray, $hl);
				$j++;
			}while($j < ceil($navLinksPerPage/2));
		}
		
		# result_* vars are for displaying where we are in the table 
		# e.g. displaying results: m through n of p 
		$smarty->assign('result_lower', $result_lower);
		$smarty->assign('result_upper', $result_upper);
		$smarty->assign('result_total', $rowCount_tot);
		
		$smarty->assign('db_path', $db_path);	# full path to db file (for subheading)
		$smarty->assign('dbdir', $dbdir);	# name of target directory (query string)

		$smarty->assign('res', $res);		# array containing the (sliced) results
		
		$smarty->assign('pageno', $dpn);	# current page number 
		$smarty->assign('numpages', $numPages);	# total number of pages
		$smarty->assign('prevpage', $prevpage);	# previous page number
		$smarty->assign('nextpage', $nextpage);	# next page number
		$smarty->assign('firstpage', 1);	# first page number
		$smarty->assign('lastpage', $numPages);	# last page number
		$smarty->assign('first_hl', $first_hl);
		$smarty->assign('prev_hl', $prev_hl);
		$smarty->assign('next_hl', $next_hl);
		$smarty->assign('last_hl', $last_hl);

		$smarty->assign('navArray', $navArray);
		
		$smarty->display('dates.tpl');
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
