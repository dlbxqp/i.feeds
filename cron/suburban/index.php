<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';


$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
$aAdditionalData['phones'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/phones.json', true), true );
#
#< images
function getAdditionalImages($village, $plot){
 $a = [];

 $url = 'https://wd.ingrad.ru/f2/cron/suburban';
 $dir = "additional_data/{$village}";
 if( is_dir(__DIR__ . '/' . $dir) ){
  $dir .= '/images';
  $plan = "{$dir}/plots/{$plot}.jpg";
  if( is_file(__DIR__ . '/' . $plan) ){
   $a[] = $url . '/' . $plan;
  }

  $dir .= '/village';
  if( is_dir($dir) ){
   $dirContents = array_diff(scandir(__DIR__ . '/' . $dir), ['.', '..']);
   sort($dirContents);
   foreach($dirContents as $v){
    is_file(__DIR__ . "/{$dir}/{$v}") && ($a[] = $url . '/' . $dir . '/' . $v);
   }
  }
 }

 return $a;
}
#> images

$aCompleted = json_decode( file_get_contents(__DIR__ . '/../_data_from_crm/completed.json', true), true);
//die('<pre>' . print_r($aCompleted, true) . '</pre>');
$a = $aTest = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 $a_addressNumber = explode('-', $building['AddressNumber']);
 (count($a_addressNumber) > 1) && ( $building['AddressNumber'] = trim($a_addressNumber[0]) );
 #
 $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");
 #
 $aCurrentProjectAdditionalData = $aAdditionalData['projects'][ $building['BuildingGroupID'] ]; //die('<pre>' . print_r($aCurrentProjectAdditionalData, true) . '</pre>');
 #
 $aPlotsData = [];
 $pathOfPlotsData = __DIR__ . "/additional_data/{$aCurrentProjectAdditionalData['title']}/data.json";
 if( is_file($pathOfPlotsData) ){
  $aPlotsData = json_decode( file_get_contents($pathOfPlotsData,true), true);
 } //die($pathOfPlotsData . '> <pre>' . print_r($aPlotsData, true) . '</pre>');


 foreach((array)$building['Sections']['Section'] as $section){ //die('<pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   #< filter
   if(
    (
     $apartment['ArticleSubType'] !== 'земельный участок'
     AND $apartment['ArticleSubType'] !== 'жилой дом и придомовой участок'
    )
    OR (
     count($aCurrentProjectAdditionalData['filter']['included']) > 0
     AND !in_array($apartment['BeforeBtiNumber'], $aCurrentProjectAdditionalData['filter']['included'])
    )
    OR (
     count($aCurrentProjectAdditionalData['filter']['excluded']) > 0
     AND in_array($apartment['BeforeBtiNumber'], $aCurrentProjectAdditionalData['filter']['excluded'])
    )
   ){ continue; }
   #> filter

   #< data
   $price = isset($aPlotsData[ (int)$apartment['BeforeBtiNumber'] ]['price']) ? (int)$aPlotsData[ (int)$apartment['BeforeBtiNumber'] ]['price'] : (double)$apartment['Price'];

   if(
    count($aCurrentProjectAdditionalData['filter']['cottages']) > 0
    and in_array($apartment['BeforeBtiNumber'], $aCurrentProjectAdditionalData['filter']['cottages'])
   ){
    $title = 'Коттедж ';
    $unit = 'м2';
   } else{
    $title = 'Участок ';
    $unit = 'соток';
   }
   $title .= "№{$apartment['BeforeBtiNumber']} по цене {$price} рублей за {$apartment['SpaceDesign']} {$unit}.";

   $address = (string)$aAdditionalData['projects'][ $building['BuildingGroupID'] ]['buildings'][ $building['AddressNumber'] ]['address'];
   if($address == ''){
    $address = (is_array($building['AddressPost']) ? (string)$building['AddressBuild'] : (string)$building['AddressPost']);
   }
   #> data

   $a[ (string)$apartment['Code'] ] = [
    'project' => [
     'crm-id' => (string)$building['BuildingGroupID'],
     'name' => (string)$building['BuildingGroupName'],
     'region' => (string)$building['AddressRegion'],
     'address' => $address,
     'latitude' => (float)$aCurrentProjectAdditionalData['sales department']['latitude'],
     'longitude' => (float)$aCurrentProjectAdditionalData['sales department']['longitude'],
     'built year' => ((count($a_deliveryPeriod) > 1) ? (int)$a_deliveryPeriod[1] : ''),
     'phone numbers' => $aCurrentProjectAdditionalData['sales department']['phone numbers']
    ],
    'house' => [
     'number' => count($building['AddressNumber']) == 0 ? 0 : (string)$building['AddressNumber']
    ],
    'number' => (int)$apartment['BeforeBtiNumber'],
    'code' => (string)$apartment['Code'],
    'price' => $price,
    'area' => (((int)$apartment['SpaceBti'] > 0) ? (double)$apartment['SpaceBti'] : (double)$apartment['SpaceDesign']),
    'title' => (string)$title,
    'description' => (string)$title . ' ' . $aCurrentProjectAdditionalData['description']['all'],
    'images' => getAdditionalImages($aCurrentProjectAdditionalData['title'], $apartment['BeforeBtiNumber'])
   ];
   ( isset($aPlotsData[ (int)$apartment['BeforeBtiNumber'] ]) ) && $a[ (string)$apartment['Code'] ]['plots data'] = $aPlotsData[ (int)$apartment['BeforeBtiNumber'] ];

   #< test
   if(isset($aTest[$aCurrentProjectAdditionalData['title']])) {
    $aTest[$aCurrentProjectAdditionalData['title']]++;
   } else{
    $aTest[$aCurrentProjectAdditionalData['title']] = 1;
   }
   #> test

   #< minPrices
   $price = number_format(($price / 1000000), 1);
   #
   if(
    !isset( $aMinPrices[ $aCurrentProjectAdditionalData['title'] ] ) or
    (
     $price > 0 and
     $aMinPrices[ $aCurrentProjectAdditionalData['title'] ] > $price
    )
   ){
    $aMinPrices[ $aCurrentProjectAdditionalData['title'] ] = $price;
   }
   #
   if($aMinPrices[ $aCurrentProjectAdditionalData['title'] ] * 1 == 0) {
    unset( $aMinPrices[ $aCurrentProjectAdditionalData['title'] ] );
   }
   #> minPrices...
  }
 }
}
#<...minPrices
file_put_contents(__DIR__ . '/../../../minPrices/suburban.json', json_encode($aMinPrices) );
echo <<<HD
<a href="//wd.ingrad.ru/minPrices/suburban.json" target="_blank">//wd.ingrad.ru/minPrices/suburban.json</a>
<hr>

HD;
#> minPrices


require __DIR__ . "/templates/avito.inc"; echo getAvito($a);
require __DIR__ . "/templates/cian.inc"; echo getCian($a);
require __DIR__ . "/templates/yr.inc"; echo getYR($a);


(count($aTest) > 0) && die('<pre>' . print_r($aTest, true) . '</pre>');