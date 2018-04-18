<?php 

require('/usr/local/lib/php/Smarty/Smarty.class.php');

class Smarty_Fixity extends Smarty { 

	function __construct() 
	{
		parent::__construct();
		
		# Change this on new install
		$prefix = '../../smarty';

		$this->setTemplateDir("$prefix/templates");
		$this->setCompileDir("$prefix/templates_c");
		$this->setConfigDir("$prefix/configs");
		$this->setCacheDir("$prefix/cache");

		#$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		$this->assign('app_name', 'Fixity');


	}

}

# Strips whitespace
function trim_value(&$value) {
	$value = trim($value);
}


# From http://php.net/manual/en/function.filesize.php
/** 
* Converts bytes into human readable file size. 
* 
* @param string $bytes 
* @return string human readable file size (2,87 Мб)
* @author Mogilev Arseny 
*/ 
function FileSizeConvert($bytes)
{
	$bytes = floatval($bytes);
	
	if($bytes == 0)
	{
		return "0 B";
	}

        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "." , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

# Convert unixtime (as string) to a human readable time/date in local time.
function utc_to_localtime($unixtime) {
	date_default_timezone_set('America/Chicago');
	$unixtime = '@' . $unixtime;
	$datetime = new DateTime($unixtime);
	$datetime->setTimezone(new DateTimeZone('America/Chicago'));
	return $datetime->format('M d Y, h:i A');
}
?>


