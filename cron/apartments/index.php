<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';
include __DIR__ . '/../../assets/includes/translit.inc';


$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
$aAdditionalData['phones'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/phones.json', true), true );
$aAdditionalData['api ids']['apartments'] = json_decode( file_get_contents(__DIR__ . '/../_data_from_api/apartments.json', true), true );

$aCompleted = json_decode( file_get_contents(__DIR__ . '/../_data_from_crm/completed.json', true), true);
//die('<pre>' . print_r($aCompleted, true) . '</pre>');
$a = $aTest = $aMinPrices = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
 #< filter
 if(
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
 ){
  continue;
 }
 #> filter

 #< data
 $a_addressNumber = explode('-', $building['AddressNumber']);
 (count($a_addressNumber) > 1) && ( $building['AddressNumber'] = trim($a_addressNumber[0]) );
 #
 $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");
 #
 $aCurrentProjectAdditionalData = $aAdditionalData['projects'][ $building['BuildingGroupID'] ]; //die('<pre>' . print_r($aCurrentProjectAdditionalData, true) . '</pre>');

 $aPlans = json_decode( file_get_contents(__DIR__ . '../../_data_from_crm/plans.json'), true );
 #> data

 foreach((array)$building['Sections']['Section'] as $section){ //die('=> <pre>' . print_r($section, true) . '</pre>');
  foreach((array)$section['Apartments'] as $apartment){
   $aPlans_ = [];
   foreach($aPlans as $k => $plan){
    if(stripos($plan, (string)$apartment['ArticleId']) !== false){
     $aPlans_[] = $plan;
    }
   }

   $title = "Квартира {$apartment['Code']} в {$apartment['BuildingGroup']}";
   $a[(string)$apartment['Code']] = [
    'project' => [
     'crm-id' => (string)$building['BuildingGroupID'],
     'title' => $aAdditionalData['projects'][$building['BuildingGroupID']]['title'],
     'name' => $building['BuildingGroupName'],
     'security' => (((int)$building['security'] > 0) ? 1 : 0)
    ],
    'house' => [
     'crm-id' => (string)$building['BuildingID'],
     'number' => (string)$building['AddressNumber'], //именно string
     'title' => (string)$building['Name'],
     'region' => (string)$building['AddressRegion'],
     'address' => (is_array($building['AddressBuild']) ? '' : (string)$building['AddressBuild']),
     'built' => [
      'year' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[1] : '',
      'quarter' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[0] : ''
     ]
    ],
    'section' => [
     'number' => (int)$section['SectionNumber']
    ],
    'api-id' => $aAdditionalData['api ids']['apartments'][(string)$apartment['ArticleId']],
    'article type' => (string)$apartment['ArticleType'],
    'code' => translit($apartment['Code']),
    'crm-membership' => (string)$apartment['AddressName'],
    'price' => (double)$apartment['Price'],
    'area' => (((int)$apartment['SpaceBti'] > 0) ? (double)$apartment['SpaceBti'] : (double)$apartment['SpaceDesign']),
    'title' => (string)$title,
    'description' => (string)$title . " по адресу {$building['AddressBuild']}; секция №{$section['SectionNumber']}",
    'floor number' => (int)$apartment['Floor'],
    'floors count' => abs($building['FloorsCount']),
    'phone number' => $aAdditionalData['phones']['Кладовые'],
    'plans' => $aPlans_,
    'rooms count' => $apartment['Rooms'],
    'rooms data' => $apartment['RoomsProject']['Room']
   ];

   #< test
   if(isset($aTest[$aAdditionalData['projects'][$building['BuildingGroupID']]['title']])) {
    $aTest[$aAdditionalData['projects'][$building['BuildingGroupID']]['title']]++;
   } else{
    $aTest[$aAdditionalData['projects'][$building['BuildingGroupID']]['title']] = 1;
   }
   #> test

   #< minPrices
   $price = number_format(($apartment['Price'] / 1000000), 1);
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
file_put_contents(__DIR__ . '/../../../minPrices/apartments.json', json_encode($aMinPrices) );
echo <<<HD
<a href="//wd.ingrad.ru/minPrices/apartments.json" target="_blank">//wd.ingrad.ru/minPrices/apartments.json</a>
<hr>

HD;
#> minPrices


require __DIR__ . '/templates/avito.inc'; echo getAvito($a);
require __DIR__ . '/templates/cian.inc'; echo getCian($a);
require __DIR__ . '/templates/yr.inc'; echo getYR($a);
require __DIR__ . '/templates/ym.inc'; echo getYM($a);

(count($aTest) > 0) && die('<pre>' . print_r($aTest, true) . '</pre>');