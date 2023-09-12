<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';

//$aAPIData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_data_from_api/projects.json', true), true );
//$aAPIData['apartments'] = json_decode( file_get_contents(__DIR__ . '/../_data_from_api/apartments.json', true), true );
//die('><pre>' . print_r($aAddresses, true) .  '</pre>');



function getDirectoryContents($dir){
 return array_diff( scandir($dir), ['..', '.']);
}

function convertEmptyArraysToString($array){
 foreach($array as $v => $k){
  if(is_array($v)){
   if(count($v) > 0){
    $array[$k] = convertEmptyArraysToString( (array)$v );
   } else{
    $array[$k] = '';
   }
  } else{
   $array[$k] = (string)$v;
  }
 }

 return $array;
}



$aBuildings = json_decode( file_get_contents(__DIR__ . '/buildings/buildings.json'), true );
$buildingsCount = count($aBuildings);
$aBuildingsPrintR = ''; //print_r($aBuildings, true);
echo <<<HD
<details>
 <summary>Количество корпусов: {$buildingsCount}</summary>
 <pre>{$aBuildingsPrintR}</pre>
</details>

HD;

#< apartments
$today = date('ymd');
$apartmentsDir = __DIR__ . '/apartments';
if(is_dir("{$apartmentsDir}/{$today}")){
 $apartmentsDir .= "/{$today}";
} else{
 //$apartmentsDir .= '/' . date('ymd', time() - 86400);
 $a = getDirectoryContents($apartmentsDir);
 $a_ = [];
 foreach($a as $v){
  (is_dir("{$apartmentsDir}/{$v}") and (int)$v > 211201) && ($a_[] = $v);
 }
 $apartmentsDir .= '/' . max($a_);

 unset($a, $a_);
} //die('$apartmentsDir > ' . $apartmentsDir);

$aApartments = [];
$a = getDirectoryContents($apartmentsDir); //die('> <pre>' . print_r($a) . '</pre>');
foreach($a as $v){
 if(is_dir("{$apartmentsDir}/{$v}")){
  $a_ = glob("{$apartmentsDir}/{$v}/*.json"); //die('> <pre>' . print_r($a_) . '</pre>');
  foreach($a_ as $v_){ //die('$v_ > ' . $v_);
   $aApartments += json_decode( file_get_contents($v_), true );
  }
 }
}
$apartmentsCount = count($aApartments);
$aApartmentsPrintR = ''; //print_r($aApartments, true);
echo <<<HD
<details>
 <summary>Количество помещений: {$apartmentsCount}</summary>
 <pre>{$aApartmentsPrintR}</pre>
</details>

HD;
#> apartments

#< sections
$aSections = [];
foreach($aApartments as $apartment){
 $aSections[$apartment['SectionId']][] = $apartment;
}
$sectionsCount = count($aSections);
$aSectionsPrintR = ''; //print_r($aSections, true);
echo <<<HD
<details>
 <summary>Количество секций: {$sectionsCount}</summary>
 <pre>{$aSectionsPrintR}</pre>
</details>

HD;
/*
$tempI = 0;
foreach($aSections as $section){
 $tempI += count($section);
}
die('$tempI = ' . $tempI);
*/
#< sections

#< completed
//$tempI = 0;
$aCompleted = [];
$aAddresses = json_decode( file_get_contents(__DIR__ . '/../geocoder/addresses.json', true), true);
foreach($aBuildings as $k => $building){ //die('> <pre>' . print_r($building, true) . '</pre>');
 #< data
 $aBuildings[$k] += [
  'AddressRegion' => ((mb_stripos((string)$building['AddressBuild'], 'Москва') === false) ? 'МО' : 'Москва'),
  'AddressGeocoder' => $aAddresses[$k]
 ];
 #> data

 if(array_key_exists('SectionID', $building['Sections']['Section'])){
  $aBuildings[$k]['Sections']['Section'] = [
   0 => $building['Sections']['Section']
  ];
 } //die('<pre>' . print_r($building['Sections']['Section'], true) .  '</pre>');

 foreach($aBuildings[$k]['Sections']['Section'] as $kk => $section){
  //die('<div>' . count($section) . '</div><div>' . $section['SectionID'] . '</div><pre>' . print_r($section, true) . '</pre>');
  //die('> ' . $aSections[(string)$section['SectionID']]);
  //die('> ' . (array_key_exists('SectionID', $section)) ? 'true' : 'false')

  if(isset($aSections[$section['SectionID']])){
   //$tempI += count($aSections[$section['SectionID']]);
   $aBuildings[$k]['Sections']['Section'][$kk]['Apartments'] += $aSections[$section['SectionID']];
  }
 }
} //die('<pre>' . print_r($tempI, true) . '</pre>');


$aCompleted = convertEmptyArraysToString($aBuildings);
file_put_contents(__DIR__ . '/completed.json', json_encode($aCompleted, JSON_UNESCAPED_UNICODE));
//die('<pre>' . print_r($aCompleted, true) . '</pre>');
#> completed