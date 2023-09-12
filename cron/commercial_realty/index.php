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

$a = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 #< filter
 if(stripos($building['Name'], 'нежилые') === false){
  continue;
 }
 #> filter

 $aCurrentProjectAdditionalData = $aAdditionalData['projects'][ $building['BuildingGroupID'] ];
 #
 $a_addressNumber = explode('-', $building['AddressNumber']);
 (count($a_addressNumber) > 1) && ( $building['AddressNumber'] = trim($a_addressNumber[0]) );
 #
 $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");

 foreach((array)$building['Sections']['Section'] as $section){ //die('=> <pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   #< ...filter
   if($apartment['StatusCode'] != 4) continue;
   #> filter

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

   $area = $apartment['Quantity'];
   if(isset($apartment['SpaceDesign']) && $apartment['SpaceDesign'] !== ''){
    $area = $apartment['SpaceDesign'];
   }
   if(isset($apartment['SpaceBti']) && (count((array)$apartment['SpaceBti']) > 0)){
    $area = $apartment['SpaceBti'];
   }

   $address = (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['address'];
   //($address == '') && ($address = (string)$building['AddressGeocoder']['beautified address']); - говорят неверные адреса
   ($address == '') && ($address = (string)$building['AddressBuild']);

   $latitude =
    isset( $aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['latitude'] ) ?
     (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['latitude'] :
     (string)$building['AddressGeocoder']['latitude'];

   $longitude =
    isset( $aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['longitude'] ) ?
     (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['longitude'] :
     (string)$building['AddressGeocoder']['longitude'];
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
     'latitude' => $latitude,
     'longitude' => $longitude,
     'built year' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[1] : ''
    ],
    'section' => [
     'number' => (int)$section['SectionNumber']
    ],
    'code' => (string)$apartment['Code'],
    'crm-membership' => (string)$apartment['AddressName'],
    'price' => (double)$apartment['DiscountMax'], //Price
    'area' => $area,
    'floor number' => (int)$apartment['Floor'],
    'floors count' => abs($building['FloorsCount']),
    'phone numbers' => $aAdditionalData['phones']['Коммерческая'],
    'images' => getAdditionalImages($apartment['Code']),
    'title' => $title,
    'description' => htmlspecialchars($description, ENT_XHTML)
   ];
  }
 }
}
//die('<pre>' . print_r($a,true) . '</pre>');

require __DIR__ . '/templates/avito.inc'; echo getAvito($a);
require __DIR__ . '/templates/cian.inc'; echo getCian($a);
require __DIR__ . '/templates/yr.inc'; echo getYR($a);
require __DIR__ . "/templates/ym.inc"; echo getYM($a);
