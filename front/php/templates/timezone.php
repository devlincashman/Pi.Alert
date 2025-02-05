<?php

// ###################################
// ## TimeZone processing start
// ###################################

$configFolderPath = "/home/pi/pialert/config/";
$config_file = "pialert.conf";
$logFolderPath = "/home/pi/pialert/front/log/";
$log_file = "pialert_front.log";


$fullConfPath = $configFolderPath.$config_file;

$config_file_lines = file($fullConfPath);
$config_file_lines_timezone = array_values(preg_grep('/^TIMEZONE\s.*/', $config_file_lines));

$timeZone = "";

foreach ($config_file_lines as $line)
{    
  if( preg_match('/TIMEZONE(.*?)/', $line, $match) == 1 )
  {        
      if (preg_match('/\'(.*?)\'/', $line, $match) == 1) {          
        $timeZone = $match[1];
      }
  }
}

if($timeZone == "")
{
  $timeZone = "Europe/Berlin";
}

date_default_timezone_set($timeZone);

$date = new DateTime("now", new DateTimeZone($timeZone) );
$timestamp = $date->format('Y-m-d_H-i-s');

// ###################################
// ## TimeZone processing end
// ###################################

?>