<?php
require '../../assets/includes/ini_set.inc';
require '../../assets/includes/security.inc';



$aFiles = [
 'geocoder' => json_decode( file_get_contents('addresses.json'), true),
 'buildings' => json_decode( file_get_contents(__DIR__ . '/../_data_from_crm/buildings/buildings_.json'), true)
]; //die('<pre>' . print_r($aFiles, true) . '</pre>');

$aGeocoder = [];

$ch = curl_init();
#
$options = [
 CURLOPT_URL => 'https://cleaner.dadata.ru/api/v1/clean/address',
 CURLOPT_HTTPHEADER => [
  "Content-Type: application/json",
  "Accept: application/json",
  "Authorization: Token 18d6bacfa570f8a522e6bb330749f8ca10211ec9",
  "X-Secret: 372af69863094e6752854cff0d0e00e7b337a460"
 ],
 CURLOPT_POST => true,
 CURLOPT_RETURNTRANSFER => true,
 CURLOPT_VERBOSE => true
];
#
foreach((array)$aFiles['buildings'] as $k => $v){
 if( array_key_exists($k, $aFiles['geocoder']) ){
  $aGeocoder[$k] = [
   'beautified address' => $aFiles['geocoder'][$k]['beautified address'],
   'latitude' => $aFiles['geocoder'][$k]['latitude'],
   'longitude' => $aFiles['geocoder'][$k]['longitude']
  ];
 } else{
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([$v], JSON_UNESCAPED_UNICODE));
  curl_setopt_array($ch, $options);
  #
  $aResult = json_decode(curl_exec($ch), true); //die('> <pre>' . print_r($aResult, true) . '</pre>');

  $aGeocoder[$k] = [
   //'crm address' => (string)$v,
   'beautified address' => (string)$aResult[0]['result'],
   //'postal index' => (int)$aResult[0]['postal_code'],
   //'region' => (string)$aResult[0]['region'],
   //'city' => (string)$aResult[0]['city'],
   //'district' => (string)$aResult[0]['city_district_with_type'],
   'latitude' => (string)$aResult[0]['geo_lat'],
   'longitude' => (string)$aResult[0]['geo_lon'],
   //'metro' => (array)$aResult[0]['metro']
  ];
 }
}
#
curl_close($ch);

file_put_contents('addresses.json', json_encode($aGeocoder, JSON_UNESCAPED_UNICODE));

die('<pre>' . print_r($aGeocoder, true) . '</pre>');