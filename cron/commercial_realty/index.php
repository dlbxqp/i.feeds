<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';


$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
$aAdditionalData['phones'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/phones.json', true), true );
#
#< images
function getAdditionalImages($id){
 $dir = "additional_data/{$id}/images";
 $url = 'https://wd.ingrad.ru/f2/cron/commercial_realty/' . $dir;
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
 if(stripos($building['Name'], 'нежилые') === false){
  continue;
 }
 #> filter

 $a_addressNumber = explode('-', $building['AddressNumber']);
 (count($a_addressNumber) > 1) && ( $building['AddressNumber'] = trim($a_addressNumber[0]) );
 #
 $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");

 foreach((array)$building['Sections']['Section'] as $section){ //die('=> <pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   #< data
   $title = "Коммерческое помещение {$apartment['Code']} площадью {$apartment['Quantity']} квадратных метров в {$apartment['BuildingGroup']}";
   $description = $title . " по адресу {$building['AddressBuild']}; секция №{$section['SectionNumber']}";
   # or additional:
   $titleAndDescriptionFile = __DIR__ . "/additional_data/{$apartment['Code']}/data.csv";
   if( is_file($titleAndDescriptionFile) ){
    $titleAndDescription = file_get_contents($titleAndDescriptionFile);
    $a_ = explode('|', $titleAndDescription);
    $title = trim($a_[0]);
    $description = trim($a_[1]);
    unset($a_);
   }

   $address = (string)$aAdditionalData['projects'][ $building['BuildingGroupID'] ]['buildings'][ $building['AddressNumber'] ]['address'];
   if($address == ''){
    $address = (is_array($building['AddressPost']) ? (string)$building['AddressBuild'] : (string)$building['AddressPost']);
   }
   #> data

   $a[(string)$apartment['Code']] = [
    'project' => [
     'crm-id' => (string)$building['BuildingGroupID'],
     'title' => $aAdditionalData['projects'][$building['BuildingGroupID']]['title'],
     'name' => $building['BuildingGroupName']
    ],
    'house' => [
     'crm-id' => (string)$building['BuildingID'],
     'number' => (string)$building['AddressNumber'], //именно string
     'title' => (string)$building['Name'],
     'region' => (string)$building['AddressRegion'],
     'address' => $address,
     'latitude' => (float)$aAdditionalData['projects'][ $building['BuildingGroupID'] ]['buildings'][ $building['AddressNumber'] ]['latitude'],
     'longitude' => (float)$aAdditionalData['projects'][ $building['BuildingGroupID'] ]['buildings'][ $building['AddressNumber'] ]['longitude'],
     'built year' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[1] : ''
    ],
    'section' => [
     'number' => (int)$section['SectionNumber']
    ],
    'code' => (string)$apartment['Code'],
    'crm-membership' => (string)$apartment['AddressName'],
    'price' => (double)$apartment['Price'],
    'area' => (((int)$apartment['SpaceBti'] > 0) ? (double)$apartment['SpaceBti'] : (double)$apartment['SpaceDesign']),
    'floor number' => (int)$apartment['Floor'],
    'floors count' => abs($building['FloorsCount']),
    'phone number' => $aAdditionalData['phones']['Коммерческая'],
    'images' => getAdditionalImages($apartment['Code']),
    'title' => $title,
    'description' => htmlspecialchars($description, ENT_XHTML)
   ];

   #< test
   if(isset($aTest[$aAdditionalData['projects'][$building['BuildingGroupID']]['title']])) {
    $aTest[$aAdditionalData['projects'][$building['BuildingGroupID']]['title']]++;
   } else{
    $aTest[$aAdditionalData['projects'][$building['BuildingGroupID']]['title']] = 1;
   }
   #> test
  }
 }
}
//die('<pre>' . print_r($a,true) . '</pre>');

require __DIR__ . '/templates/avito.inc'; echo getAvito($a);
require __DIR__ . '/templates/cian.inc'; echo getCian($a);
require __DIR__ . '/templates/yr.inc'; echo getYR($a);


(count($aTest) > 0) && die('<pre>' . print_r($aTest, true) . '</pre>');