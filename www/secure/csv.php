<?php 

include('setup.php');

$smarty = new Smarty_Fixity();
$smarty->configLoad('fixity.cfg');
$configVars = $smarty->getConfigVars();

$darklib_prefix = $configVars['darklib_prefix'];
$darklib_dirs = explode(',', $configVars['darklib_dirs']);
array_walk($darklib_dirs, 'trim_value');
$darklib_postfix = $configVars['darklib_postfix'];

$uuid = $_GET['uuid'];
$dbdir = $_GET['dbdir'];

if(in_array($dbdir, $darklib_dirs))
{
	# Initial LSO csv is ~190 MB
	ini_set('memory_limit', '1024M');
	
	$db_path = "$darklib_prefix" . "/$dbdir/" . "$darklib_postfix";
	exec("csv_export.sh $db_path $uuid 2>&1", $retArr, $retVal);
	
	$fileName = $dbdir . "_$uuid" . "_csv.txt";
	$tmpName = tempnam(sys_get_temp_dir(), 'csv');
	$fh = fopen($tmpName, 'w');
	
	foreach($retArr as $line) 
	{
		$line = $line . "\r\n";
		$ret = fwrite($fh, $line);
		if($ret == 0)
		{
			echo "Error writing file.";
			break; 
		}
	}

	fclose($fh); 

	header('Content-Description: File Transfer');
	header('Content-Type: text/csv');
	header("Content-Disposition: attachment; filename=$fileName");
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($tmpName));
	
	ob_clean();
	flush();
	readfile($tmpName);

	unlink($tmpName);
}




