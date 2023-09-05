<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';
include __DIR__ . '/../../assets/includes/translit.inc';

$aAdditionalData = ['projects', 'phones', 'api ids'];
$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
//$aAdditionalData['api ids']['apartments'] = json_decode( file_get_contents(__DIR__ . '/../_data_from_api_old/apartments.json', true), true );
#
$aAPIData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_data_from_api/projects.json', true), true );
$aAPIData['apartments'] = json_decode( file_get_contents(__DIR__ . '/../_data_from_api/apartments.json', true), true );
#
#< images
function getAdditionalProjectsImages($projectCode){ //die('> ' . $projectCode);
 $a = [];

 $dir = "additional_data/projects/{$projectCode}/images";
 $url = 'https://wd.ingrad.ru/f2/cron/apartments/' . $dir;
 if( is_dir(__DIR__ . '/' . $dir) ){
  $dirContents = array_diff(scandir(__DIR__ . '/' . $dir), ['.', '..']);
  foreach($dirContents as $v){
   is_file(__DIR__ . "/{$dir}/{$v}") && ($a[] = "{$url}/{$v}");
  }
 }

 return $a;
}

function getAdditionalApartmentsImages($apartmentCode){ //die('> ' . $projectCode);
 $a = [];

 $dir = "additional_data/apartments/{$apartmentCode}";
 $url = 'https://wd.ingrad.ru/f2/cron/apartments/' . $dir;
 if( is_dir(__DIR__ . '/' . $dir) ){
  $dirContents = array_diff(scandir(__DIR__ . '/' . $dir), ['.', '..']);
  foreach($dirContents as $v){
   is_file(__DIR__ . "/{$dir}/{$v}") && ($a[] = "{$url}/{$v}");
  }
 }

 //die('> ' . print_r($a, true));

 return $a;
}
#> images

$aCompleted = json_decode( file_get_contents(__DIR__ . '/../_data_from_crm/completed.json', true), true);
//die('<pre>' . print_r($aCompleted, true) . '</pre>');

$mainCondition = (date('H') < 23 AND !isset($_GET['full']));
$a = $aTest = $aMinPrices = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 #< filter
 if(
  (
   !isset($aAdditionalData['projects'][ $building['BuildingGroupID'] ])
   //OR !isset($aAPIData['projects'][ $building['BuildingGroupID'] ])
  )
  OR (
   (
    stripos($building['BuildingGroupName'], 'Экодолье Шолохово') !== false or
    stripos($building['BuildingGroupName'], 'Солнечный Берег') !== false or
    stripos($building['BuildingGroupName'], 'Мартемьяново') !== false
   ) or (
    stripos($building['Name'], 'нежилые') !== false or
    stripos($building['Name'], 'паркинг') !== false or
    stripos($building['Name'], 'машиноместа') !== false or
    stripos($building['Name'], 'кладовые') !== false or
    stripos($building['Name'], 'МХМТС') !== false or
    stripos($building['Name'], 'мото-места') !== false
   )
  )
 ){
  continue;
 }
 #> filter

 #< data
 $a_addressNumber = explode('-', $building['AddressNumber']);
 (count($a_addressNumber) > 1) && ( $building['AddressNumber'] = trim($a_addressNumber[0]) );

 $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");

 $aCurrentProjectAdditionalData = array_merge( $aAdditionalData['projects'][ $building['BuildingGroupID'] ], $aAPIData['projects'][ $building['BuildingGroupID'] ]); //die('<pre>' . print_r($aCurrentProjectAdditionalData, true) . '</pre>');

 $address = (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['address'];
 //($address == '') && ($address = (string)$building['AddressGeocoder']['beautified address']); - говорят неверные адреса
 ($address == '') && ($address = (string)$building['AddressBuild']);
 #> data...


 foreach((array)$building['Sections']['Section'] as $section){ //die('=> <pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   #< ...filter
   if($mainCondition === true){
    if($apartment['StatusCode'] != 4) continue;
   }
   #> filter

   #< ...data
   $aImages = ['apartment' => [ 0 => null, 1 => null ]];
   foreach( $aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['images'] as $k => $v){
    if($v == '') continue;

    if($k == 'plain'){
     $aImages['apartment'][0] = $v;
    } else if($k == 'floor'){
     $aImages['apartment'][1] = $v;
    } else{
     $aImages['apartment'][] = $v;
    }
   }
   #
   if(
    in_array( (int)$aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['api-id'], [6519, 6520] )
   ){ //die('> ' . (int)$aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['api-id']);
    $aAdditionalApartmentsImages = getAdditionalApartmentsImages( (int)$aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['api-id'] );
    foreach($aAdditionalApartmentsImages as $additionalApartmentsImage){
     $aImages['apartment'][] = $additionalApartmentsImage;
    }
   }
   #
   //ksort($aImages['apartment']);

   $aImages['project'] = getAdditionalProjectsImages( $aCurrentProjectAdditionalData['api-code'] );

   $livingArea = $kitchenArea = 0;
   //die('> ' . print_r($apartment['RoomsProject']['Room'], true));
   foreach((array)$apartment['RoomsProject']['Room'] as $room){ //die('> ' . print_r($room, true));
    if(mb_stripos($room['TypeName'], 'комната') !== false){ //$room['TypeName'] == 'Жилая комната' or $room['TypeName'] == 'Комната'
     $livingArea += $room['AreaProject'] * 1;
    } else if(mb_stripos($room['TypeName'], 'кухня') !== false){ //$room['TypeName'] == 'Кухня'
     //die('> ' . $room['AreaProject']);
     $kitchenArea += $room['AreaProject'] * 1;
    }
   }

   $latitude =
    isset( $aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['latitude'] ) ?
     (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['latitude'] :
     (string)$building['AddressGeocoder']['latitude'];

   $longitude =
    isset( $aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['longitude'] ) ?
     (string)$aCurrentProjectAdditionalData['buildings'][ $building['AddressNumber'] ]['longitude'] :
     (string)$building['AddressGeocoder']['longitude'];

   $totalArea = $apartment['Quantity'];
   if(isset($apartment['SpaceBti']) && $apartment['SpaceBti'] !== ''){
    $totalArea = $apartment['SpaceBti'];
   }
   if(isset($apartment['SpaceDesign']) && $apartment['SpaceDesign'] !== ''){
    $totalArea = $apartment['SpaceDesign'];
   }

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

   if($apartment['Rooms'] === 0){
    $title = 'студия';
   } else if($apartment['Rooms'] === 1){
    $title = 'однокомнатная квартира';
   } else if($apartment['Rooms'] === 2){
    $title = 'двухкомнатная квартира';
   } else if($apartment['Rooms'] === 3){
    $title = 'трёхкомнатная квартира';
   } else if($apartment['Rooms'] === 4){
    $title = 'четырёхкомнатная квартира';
   } else if($apartment['Rooms'] === 5){
    $title = 'пятикомнатная квартира';
   } else{
    $title = 'квартира';
   }
   $title = "Продается {$title} (№{$apartment['BeforeBtiNumber']}) в новостройке {$apartment['BuildingGroup']} по адресу {$building['AddressBuild']}."; //"Квартира {$apartment['Code']} в {$apartment['BuildingGroup']}"
   #
   $description = <<<HD
Программа TRADE-IN - живите в старой квартире до получения новой!
Не упустите выгоду, звоните сейчас, чтобы узнать подробности и сделать предварительные расчеты.
{$title}
{$aCurrentProjectAdditionalData['description']}
Общая площадь квартиры - {$totalArea} кв.м. Тип дома – монолитный.

HD;
   #> data

/*
   if($apartment['Code'] == 'МК3-01-09-09-072'){
    //die('> <pre>' . print_r($aCurrentProjectAdditionalData, true) . '</pre>');
    echo mb_strtolower($apartment['FinishTypeId']) . '<br>';
   }
//*/

   $a[(string)$apartment['Code']] = [
    'project' => [
     'crm-id' => (string)$building['BuildingGroupID'],
     'title' => $aCurrentProjectAdditionalData['api-code'], //$aCurrentProjectAdditionalData['title'],
     'name' => $building['BuildingGroupName'],
     'security' => (((int)$building['security'] > 0) ? 1 : 0),
     'images' => (array)$aImages['project'],
     'phone numbers' => (array)$aCurrentProjectAdditionalData['phone numbers'],
     'e-mail' => (stripos($address, 'Москва')) ? 'ozmsk@ingrad.com' : 'ozmo@ingrad.com'
    ],
    'house' => [
     'crm-id' => (string)$building['BuildingID'],
     'number' => (string)$building['AddressNumber'], //именно string
     'title' => (string)$building['Name'],
     'floors count' => abs($building['FloorsCount']),
     'region' => (string)$building['AddressRegion'],
     'address' => $address,
     'latitude' => $latitude,
     'longitude' => $longitude,
     'built' => [
      'year' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[1] : '',
      'quarter' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[0] : ''
     ]
    ],
    'section' => [
     'crm-id' => (string)$section['SectionID'],
     'number' => (int)$section['SectionNumber']
    ],
    'crm-id' => (string)$apartment['ArticleId'],
    'api-id' => (int)$aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['api-id'],
    'article type' => (string)$apartment['ArticleType'],
    'code' => [
     'ru' => $apartment['Code'],
     'translite' => translit($apartment['Code'])
    ],
    'crm-membership' => (string)$apartment['AddressName'],
    'price' => (double)$apartment['DiscountMax'],
    'oldprice' => (double)$apartment['Price'],
    'area' => [
     'living' => (double)$livingArea,
     'kitchen' => (double)$kitchenArea,
     'total' => $totalArea
    ],
    'status' => [
     'code' => $apartment['StatusCode'],
     'title' => $apartment['StatusCodeName']
    ],
    'переуступка' => ((string)$apartment['isDupt'] == 'true') ? true : false,
    'count on floor' => (int)$section['ObjectsCount'],
    'description' => (string)$description,
    'finishing' => trim( mb_strtolower( is_array($apartment['FinishTypeId']) ? '' : $apartment['FinishTypeId'] ) ),
    'floor number' => (int)$apartment['Floor'],
    'images' => (array)$aImages['apartment'],
    'number' => $number,
    'number on floor' => (int)$apartment['PlatformNumber'],
    'rooms count' => (int)$apartment['Rooms'],
    'rooms data' => (array)$apartment['RoomsProject']['Room'],
    'title' => "<![CDATA[{$title}]]>",
    'townhouse' => (boolean)$apartment['TownHouse'],
    'url' => "https://www.ingrad.ru{$aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['link']}",
    'unique' => ((string)$apartment['isUnique'] == 'true') ? true : false
   ];

   #< test
   if(isset($aTest[$aCurrentProjectAdditionalData['title']])) {
    $aTest[$aCurrentProjectAdditionalData['title']]++;
   } else{
    $aTest[$aCurrentProjectAdditionalData['title']] = 1;
   }
   #> test

   #< minPrices
/*
   $price = number_format(((integer)$apartment['DiscountMax'] / 1000000), 1); //Price
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
   if($aMinPrices[ $aCurrentProjectAdditionalData['title'] ] * 1 == 0){
    unset( $aMinPrices[ $aCurrentProjectAdditionalData['title'] ] );
   }
*/
   #> minPrices...
  }
 }
}


if($mainCondition === true){
#<...minPrices
 file_put_contents(__DIR__ . '/../../../minPrices/apartments.json', json_encode($aMinPrices) );
 echo <<<HD
<a href="//wd.ingrad.ru/minPrices/apartments.json" target="_blank">//wd.ingrad.ru/minPrices/apartments.json</a>
<hr>

HD;
#> minPrices

 require __DIR__ . '/templates/dc.inc'; echo getDc($a);
 require __DIR__ . '/templates/avito.inc'; echo getAvito($a);
 require __DIR__ . '/templates/cian.inc'; echo getCian($a);
 require __DIR__ . '/templates/yr.inc'; echo getYR($a);
}
require __DIR__ . '/templates/ym.inc'; echo getYM($a);

(count($aTest) > 0) && die('<pre>' . print_r($aTest, true) . '</pre>');
