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
   //if($building['BuildingGroup'] == 'ЖК «КутузовGRAD»'){
    $a['finishing'][] = $apartment['FinishTypeId'];
    $a['material'][] = $building['HouseMaterial'];
    $a['houseSerie'][] = $building['HouseSeries'];
   //}
  }
 }
}

$a['finishing'] = array_unique($a['finishing']); sort($a['finishing']); reset($a['finishing']);
echo '<pre>' . print_r($a['finishing'], true) . '</pre>';

$a['material'] = array_unique($a['material']); sort($a['material']); reset($a['material']);
echo '<pre>' . print_r($a['material'], true) . '</pre>';

$a['houseSerie'] = array_unique($a['houseSerie']); sort($a['houseSerie']); reset($a['houseSerie']);
echo '<pre>' . print_r($a['houseSerie'], true) . '</pre>';