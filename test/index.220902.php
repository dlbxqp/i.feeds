<?php //phpinfo(INFO_ALL);
ini_set('error_reporting', E_ERROR);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$aCompleted = json_decode( file_get_contents('../cron/_data_from_crm/completed.json', true), true);
//die('<pre>' . print_r($aCompleted, true) . '</pre>');



$a = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 foreach((array)$building['Sections']['Section'] as $section){ //die('=> <pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   $a['FloorsCount'][] = $building['FloorsCount'];
  }
 }
}
$a['FloorsCount'] = array_unique($a['FloorsCount']); sort($a['FloorsCount']); reset($a['FloorsCount']);



die('<pre>' . print_r($a['FloorsCount'], true) . '</pre>');