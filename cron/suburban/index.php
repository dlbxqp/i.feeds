<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';


$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
$aAdditionalData['phones'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/phones.json', true), true );
$aBuildingProjects = json_decode( file_get_contents(__DIR__ . '/../_data_from_crm/building_projects/data.json', true), true );
//die('<pre>' . print_r($aBuildingProjects, true) . '</pre>');
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
  if( is_dir(__DIR__ . '/' . $dir) ){
   $dirContents = array_diff( scandir(__DIR__ . '/' . $dir), ['.', '..']);
   sort($dirContents);
   foreach($dirContents as $v){
    is_file(__DIR__ . '/' . $dir . '/' . $v) && ($a[] = $url . '/' . $dir . '/' . $v);
   }
  }
 }

 return /*(count($a) > 0) ?*/ $a /*: [print_r( array_diff( scandir(__DIR__ . '/' . $dir), ['.', '..']), true)]*/;
}
#> images

$aCompleted = json_decode( file_get_contents(__DIR__ . '/../_data_from_crm/completed.json', true), true);
//die('<pre>' . print_r($aCompleted, true) . '</pre>');

$mainCondition = (date('H') < 23 AND !isset($_GET['full']));
$a = $aTest = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 $a_addressNumber = explode('-', $building['AddressNumber']);
 (count($a_addressNumber) > 1) && ( $building['AddressNumber'] = trim($a_addressNumber[0]) );
 #
 $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");
 #
 $aCurrentProjectAdditionalData = $aAdditionalData['projects'][ $building['BuildingGroupID'] ]; //die('<pre>' . print_r($aCurrentProjectAdditionalData, true) . '</pre>');
 #
 /* 221128
 $aPlotsData = [];
 $pathOfPlotsData = __DIR__ . "/additional_data/{$aCurrentProjectAdditionalData['title']}/data.json";
 if( is_file($pathOfPlotsData) ){
  $aPlotsData = json_decode( file_get_contents($pathOfPlotsData,true), true);
 } //die($pathOfPlotsData . '> <pre>' . print_r($aPlotsData, true) . '</pre>');
 */
 #
 if( is_file(__DIR__ . '/additional_data/' . $aCurrentProjectAdditionalData['title'] . '/data.json') ){
  $aAdditionalData['descriptions'] = json_decode( file_get_contents(__DIR__ . '/additional_data/' . $aCurrentProjectAdditionalData['title'] . '/data.json', true), true );
  //die('<pre>' . print_r($aAdditionalData['descriptions'], true) . '</pre>');
 }

 foreach((array)$building['Sections']['Section'] as $section){ //die('<pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   #< filter
   if($mainCondition === true){
    if($apartment['StatusCode'] != 4) continue;
   }

   if(
    !isset($aCurrentProjectAdditionalData)
    OR (
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
    )
   ){ continue; }
   #> filter...

   #< data
   $area = 0;
   if(isset($apartment['SpaceBti']) && $apartment['SpaceBti'] !== '' && !is_array($apartment['SpaceDesign'])){
    $area = (double)$apartment['SpaceBti'];
   }
   if(isset($apartment['SpaceDesign']) && $apartment['SpaceDesign'] !== '' && !is_array($apartment['SpaceDesign'])){
    $area = $apartment['SpaceDesign'];
   }
   /* filter */ if($area == 0) continue;

   (is_array($building['AddressNumber']) && count($building['AddressNumber']) === 0) && ($building['AddressNumber'] = 0);
   //echo current($apartment['BuildingProjects']);

   $price = ((string)$building['BuildingGroupID'] == '3222e43f-0059-ed11-8141-005056ba18b6') ? (double)$apartment['Price'] : (double)$apartment['DiscountMax'];
   //isset($aBuildingProjects[ mb_strtolower( current($apartment['BuildingProjects']) ) ]) && die('!' . print_r($apartment['BuildingProjects']));
   isset($apartment['BuildingProjects']['string']) && ($apartment['BuildingProjects'] = (array)$apartment['BuildingProjects']['string']);
   if(
/*
    count$apartment['BuildingProjects']) > 0
    and in_array($apartment['BeforeBtiNumber'], $aCurrentProjectAdditionalData['filter']['cottages'])
*/
    count($apartment['BuildingProjects']) > 0
    AND isset($aBuildingProjects[ mb_strtolower( current($apartment['BuildingProjects']) ) ])
   ){ // Inhaus
    $currentBuildingProjectData = $aBuildingProjects[ mb_strtolower( current($apartment['BuildingProjects']) ) ];

    $title = "Коттедж по проекту {$currentBuildingProjectData['Name']} с участком ";
    $unit = 'м2';
    $buildingProjectData = [
     //'title' => $currentBuildingProject['Name'],
     'floors' => $currentBuildingProjectData['FloorsCount'],
     'rooms count' => $currentBuildingProjectData['Rooms'],
     'bathrooms' => $currentBuildingProjectData['WcCount'],
     'has garage' => $currentBuildingProjectData['Garage'],
     'has terrace' => $currentBuildingProjectData['Terrace'],
     'area' => [
      'design' => $currentBuildingProjectData['SpaceDesign'],
      'build' => $currentBuildingProjectData['SpaceBuild']
     ]
    ];
   } else{
    //$price = isset($aPlotsData[ (int)$apartment['BeforeBtiNumber'] ]['price']) ? (int)$aPlotsData[ (int)$apartment['BeforeBtiNumber'] ]['price'] : (double)$apartment['DiscountMax'];
    $title = 'Участок ';
    $unit = 'соток';
    $buildingProjectData = [];
   }
   /* filter */ if(!($price > 0)) continue;

   //$description = $title;
   $title .= "№{$apartment['BeforeBtiNumber']} площадью {$area} {$unit}."; //по цене {$price} рублей за
   //$description .= "№{$apartment['BeforeBtiNumber']}, {$area} {$unit}.";

   $number = 0;
   if(isset($apartment['BtiNumberTxt']) && $apartment['BtiNumberTxt'] !== ''){
    $number = $apartment['BtiNumberTxt'];
   }
   if(isset($apartment['BtiNumber']) && $apartment['BtiNumber'] !== ''){
    $number = $apartment['BtiNumber'];
   }
   if(isset($apartment['BeforeBtiNumberTxt']) && $apartment['BeforeBtiNumberTxt'] !== ''){
    $number = $apartment['BeforeBtiNumberTxt'];
   }
   if(isset($apartment['BeforeBtiNumber']) && $apartment['BeforeBtiNumber'] !== ''){
    $number = $apartment['BeforeBtiNumber'];
   }

   if( isset($aAdditionalData['descriptions'][ $number ]) ){
    $description = $aAdditionalData['descriptions'][ $number ];
   } else{
    $description = $aCurrentProjectAdditionalData['description'];
   }

   $address = (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['address'];
   //($address == '') && ($address = (string)$building['AddressGeocoder']['beautified address']); - говорят неверные адреса
   ($address == '') && ($address = (string)$building['AddressBuild']);

   ($building['AddressNumber'] == '') && ($building['AddressNumber'] = 0);
   $latitude =
    isset( $aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['latitude'] ) ?
     (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['latitude'] :
     (string)$building['AddressGeocoder']['latitude'];
   #
   $longitude =
    isset( $aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['longitude'] ) ?
     (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['longitude'] :
     (string)$building['AddressGeocoder']['longitude'];
   #> data

   $a[ (string)$apartment['Code'] ] = [
    'project' => [
     'crm-id' => (string)$building['BuildingGroupID'],
     'title' => $aCurrentProjectAdditionalData['title'],
     'name' => (string)$building['BuildingGroupName'],
     'region' => (string)$building['AddressRegion'],
     'built year' => ((count($a_deliveryPeriod) > 1) ? (int)$a_deliveryPeriod[1] : ''),
     'phone numbers' => $aCurrentProjectAdditionalData['phone numbers']
    ],
    'house' => [
     'number' => count((array)$building['AddressNumber']) == 0 ? 0 : (string)$building['AddressNumber'],
     'address' => $address,
     'latitude' => $latitude,
     'longitude' => $longitude
    ],
    'building project' => $buildingProjectData,
    'number' => $number,
    'code' => (string)$apartment['Code'],
    'price' => $price,
    'area' => $area,
    'type' => $apartment['ArticleSubType'],
    'title' => (string)$title,
    'description' => (string)$description,
    'images' => getAdditionalImages($aCurrentProjectAdditionalData['title'], $apartment['BeforeBtiNumber'])
   ];
   //( isset($aPlotsData[ (int)$apartment['BeforeBtiNumber'] ]) ) && $a[ (string)$apartment['Code'] ]['plots data'] = $aPlotsData[ (int)$apartment['BeforeBtiNumber'] ];

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
require __DIR__ . "/templates/ym.inc"; echo getYM($a);


(count($aTest) > 0) && die('<pre>' . print_r($aTest, true) . '</pre>');