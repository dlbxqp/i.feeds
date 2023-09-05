<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';


$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
$aAdditionalData['phones'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/phones.json', true), true );
#
#< images
function getAdditionalImages($dir){
 $url = 'https://wd.ingrad.ru/f2/cron/parking_spaces/' . $dir;
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
$a = $aTest = $aDependence = $aDependence_ = $aStorageRooms = $aMotoPlaces = $aParkingSpaces = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 #< filter
 $isParkingSpaces = ((stripos($building['Name'], 'паркинг') !== false) or (stripos($building['Name'], 'машиноместа') !== false)) ? true : false;
 $isMotoPlace = ((stripos($building['Name'], 'МХМТС') !== false) or (stripos($building['Name'], 'мото-места') !== false)) ? true : false;
 $isStorageRoom = (stripos($building['Name'], 'кладовые') !== false) ? true : false;
 if(
  !$isParkingSpaces
  and !$isMotoPlace
  and !$isStorageRoom
 ){
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
   #< ...filter
   if($apartment['StatusCode'] != 4) continue;

   if($isStorageRoom){
    $aStorageRooms[] = (string)$apartment['ArticleId'];

    continue;
   } else if($isMotoPlace){
    $aMotoPlaces[] = (string)$apartment['ArticleId'];
    $title = 'Мото-место';
   } else{
    $aParkingSpaces[] = (string)$apartment['ArticleId'];
    $title = 'Машиноместо';
   }
   #> filter

   #< data
   $title .= " {$apartment['Code']} в {$apartment['BuildingGroup']}";

   $area = $apartment['Quantity'];
   if(isset($apartment['SpaceBti']) && $apartment['SpaceBti'] !== ''){
    $area = $apartment['SpaceBti'];
   }
   if(isset($apartment['SpaceDesign']) && $apartment['SpaceDesign'] !== ''){
    $area = $apartment['SpaceDesign'];
   }

    #< зависимые
   if(/*$apartment['ParkingType'] == 'Зависимый' and */is_string($apartment['DependentId'])){
    $aDependence[ (string)$apartment['DependentId'] ][] = (string)$apartment['Code'];
   }
    #> зависимые...

   $address = (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['address'];
   //($address == '') && ($address = (string)$building['AddressGeocoder']['beautified address']); - говорят неверные адреса
   ($address == '') && ($address = (string)$building['AddressBuild']);
   #> data

   $a[ (string)$apartment['Code'] ] = [
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
     'address' => $address,
     'built year' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[1] : ''
    ],
    'section' => [
     'number' => (int)$section['SectionNumber']
    ],
    'article id' => (string)$apartment['ArticleId'],
    'article type' => (string)$apartment['ArticleType'],
    'code' => (string)$apartment['Code'],
    'crm-membership' => (string)$apartment['AddressName'],
    'price' => (double)$apartment['DiscountMax'], //Price
    'area' => $area,
    'title' => (string)$title,
    'description' => (string)$title . " по адресу {$building['AddressBuild']}; секция №{$section['SectionNumber']}",
    'floor number' => (int)$apartment['Floor'],
    'floors count' => abs($building['FloorsCount']),
    'phone numbers' => $aAdditionalData['phones']['Машиноместа'],
    'images' => array_merge(
     getAdditionalImages('additional_data/images'),
     getAdditionalImages("additional_data/{$aCurrentProjectAdditionalData['title']}/images")
    )
   ];

   #< test
   if(isset($aTest[$aCurrentProjectAdditionalData['title']])){
    $aTest[$aCurrentProjectAdditionalData['title']]++;
   } else {
    $aTest[$aCurrentProjectAdditionalData['title']] = 1;
   }
   #> test
  }
 }
}

#< ...зависимые
foreach($a as $k => $v){
 if( !isset($aDependence[ $v['article id'] ]) ) continue;

 $aDependence_[$k] = $aDependence[ $v['article id'] ];
} //die('<pre>' . print_r($aDependence_, true) . '</pre>');

$A = 'Продаётся вместе с ';
foreach($aDependence_ as $k => $v){
 foreach($v as $vv){
  $price = $a[$k]['price'] + $a[$vv]['price'];
  $area = $a[$k]['area'] + $a[$vv]['area'];

  $description = ' ';
  if( strstr($a[$k]['description'], $A) === false ){ //($k == 'МРС4п-01-(-2)-03-2080' && $vv == 'МРС4м-01-(-2)-53-020') && die('> ' . stristr($a[$k]['description'], $A) );
   $description .= $A;
  } else{
   $description .= 'и ';
  }

  if( in_array($a[$vv]['article id'], $aStorageRooms) ){ //die('> ' . $a[$vv]['article id']);
   $description .= " кладовкой ";
  } else if( in_array($a[$vv]['article id'], $aMotoPlaces) ){ //die('> ' . $a[$vv]['article id']);
   $description .= " мото-местом ";
  } else{
   $description .= " машиноместом ";
  }

  $a[$k]['price'] = $price;
  $a[$k]['area'] = $area;
  $a[$k]['description'] .= $description . $a[$vv]['code'];

/*
  $a[$vv]['price'] = $price;
  $a[$vv]['area'] = $area;
  $a[$vv]['description'] .= $description . $a[$k]['code'];
*/
  unset($a[$vv]);
 }
}
#> зависимые
//echo '<pre>' . print_r($aDependence_, true) . '</pre>';


require __DIR__ . "/templates/avito.inc"; echo getAvito($a);
require __DIR__ . "/templates/cian.inc"; echo getCian($a);
require __DIR__ . "/templates/yr.inc"; echo getYR($a);


(count($aTest) > 0) && die('<pre>' . print_r($aTest, true) . '</pre>');