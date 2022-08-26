<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';


$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
$aAdditionalData['phones'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/phones.json', true), true );
#
#< images
function getAdditionalImages($projectCode){
 $dir = "additional_data/{$projectCode}/images";
 $url = 'https://wd.ingrad.ru/f2/cron/storage_rooms/' . $dir;
 $a = [];

 if( is_dir(__DIR__ . '/' . $dir) ){
  $dirContents = array_diff(scandir(__DIR__ . '/' . $dir), ['.', '..']);
  foreach($dirContents as $v){
   is_file(__DIR__ . "/{$dir}/{$v}") && ($a[] = "{$url}/{$v}");
  }
 }

 return $a;
}
#> images

$aCompleted = json_decode( file_get_contents(__DIR__ . '/../_data_from_crm/completed.json', true), true);
//die('<pre>' . print_r($aCompleted, true) . '</pre>');
$a = $aTest = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 #< filter
 if(stripos($building['Name'], 'кладовые') === false){
  continue;
 }
 #> filter

 $a_addressNumber = explode('-', $building['AddressNumber']);
 (count($a_addressNumber) > 1) && ( $building['AddressNumber'] = trim($a_addressNumber[0]) );
 #
 $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");
 #
 $aCurrentProjectAdditionalData = $aAdditionalData['projects'][ $building['BuildingGroupID'] ]; //die('<pre>' . print_r($aCurrentProjectAdditionalData, true) . '</pre>');



 foreach((array)$building['Sections']['Section'] as $section){ //die('=> <pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   $title = "Кладовая {$apartment['Code']} в {$apartment['BuildingGroup']}";
   $a[(string)$apartment['Code']] = [
    'project' => [
     'crm-id' => (string)$building['BuildingGroupID'],
     'title' => $aCurrentProjectAdditionalData['title'],
     'name' => $building['BuildingGroupName'],
     'security' => (((int)$building['security'] > 0) ? 1 : 0)
    ],
    'house' => [
     'crm-id' => (string)$building['BuildingID'],
     'number' => (string)$building['AddressNumber'], //именно string
     'title' => (string)$building['Name'],
     'region' => (string)$building['AddressRegion'],
     'address' => (is_array($building['AddressBuild']) ? '' : (string)$building['AddressBuild']),
     'built year' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[1] : ''
    ],
    'section' => [
     'number' => (int)$section['SectionNumber']
    ],
    'article type' => (string)$apartment['ArticleType'],
    'code' => (string)$apartment['Code'],
    'crm-membership' => (string)$apartment['AddressName'],
    'price' => (double)$apartment['Price'],
    'area' => (((int)$apartment['SpaceBti'] > 0) ? (double)$apartment['SpaceBti'] : (double)$apartment['SpaceDesign']),
    'title' => (string)$title,
    'description' => (string)$title . " по адресу {$building['AddressBuild']}; секция №{$section['SectionNumber']}",
    'floor number' => (int)$apartment['Floor'],
    'floors count' => abs($building['FloorsCount']),
    'phone number' => $aAdditionalData['phones']['Кладовые'],
    'images' => getAdditionalImages($aCurrentProjectAdditionalData['title'])
   ];

   #< test
   if(isset($aTest[ $aCurrentProjectAdditionalData['title']] )) {
    $aTest[ $aCurrentProjectAdditionalData['title'] ]++;
   } else{
    $aTest[ $aCurrentProjectAdditionalData['title'] ] = 1;
   }
   #> test
  }
 }
}


require __DIR__ . "/templates/avito.inc"; echo getAvito($a);
require __DIR__ . "/templates/cian.inc"; echo getCian($a);
require __DIR__ . "/templates/yr.inc"; echo getYR($a);


(count($aTest) > 0) && die('<pre>' . print_r($aTest, true) . '</pre>');