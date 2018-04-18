<?php

require('setup.php');
$smarty = new Smarty_Fixity();
$smarty->configLoad('fixity.cfg');
$configVars = $smarty->getConfigVars();

$darklib_dirs = explode(',', $configVars['darklib_dirs']);
array_walk($darklib_dirs, 'trim_value');

$shib_whitelist = explode(',', $configVars['shibboleth_whitelist']);
array_walk($shib_whitelist, 'trim_value');

$darklib_prefix = $configVars['darklib_prefix'];
$darklib_postfix = $configVars['darklib_postfix'];

$hl_list = array();

if( (isset($_SERVER['samaccountname'])) )
{
	$pawprint = $_SERVER['samaccountname'];
}

if(in_array($pawprint, $shib_whitelist))
{
	foreach($darklib_dirs as $dbdir)
	{
		$db_path = "$darklib_prefix" . "/$dbdir/" . "$darklib_postfix"; 
		$hasRows = 0;
		$dbh = new PDO("sqlite:/$db_path");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		# Check if at least 1 table row exists without actually fetching it. 
		$query = "SELECT 1 FROM dates LIMIT 1";
		$hasRows = $dbh->query($query)->fetchColumn(); 
		
		$dbh = null;
		
		if($hasRows != 0) 
		{
			$hl = '<a href="dates.php?dbdir='.$dbdir.'&dpn=1">'. $dbdir .'</a>';
		}
		else
		{
			$hl = $dbdir;
		}
		array_push($hl_list, $hl);
	}

$smarty->assign('dirs', $hl_list);
$smarty->display('index.tpl');
}
else
{
	$smarty->assign('pawprint', $pawprint);
	$smarty->assign('webmaster_contact', $configVars['webmaster_contact']);
	$smarty->display('error.tpl');
}

?>
