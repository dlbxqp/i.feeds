<?php
include __DIR__ . '/../../../assets/includes/ini_set.inc';


$url = 'https://crm.dmfs.ru/Service/ExportToSite.svc/ApartmentList/xml/';

$startHRTime = microtime(true);
$startTime = date('ymdHis');
$fileLogName = __DIR__ . '/log.json';
is_file($fileLogName) && file_put_contents($fileLogName, '');

$ch = curl_init();
$options = [
 CURLOPT_REFERER => 'https://wd.ingrad.ru',
 CURLOPT_RETURNTRANSFER => TRUE,
 CURLOPT_ENCODING => ''
];
curl_setopt_array($ch, $options);
//$aTest['StatusCodes'] = [];
$aBuildingsIds = json_decode( file_get_contents(__DIR__ . '/../buildings/buildingsIds.json') ); //die('> <pre>' . print_r($aBuildingsIds, true) . '</pre>');
foreach($aBuildingsIds as $buildingId){
 curl_setopt($ch, CURLOPT_URL, $url . $buildingId);
 $xml = curl_exec($ch); //die('> ' . print_r($xml));
 $xml = str_replace('a:', '', $xml);
 $xml = str_replace('b:', '', $xml);

 $aTable = [];
 #
 $object = new SimpleXMLElement($xml);
 $oRows = $object->XMLApartmentListDataResult;
 $aRows = json_decode( json_encode($oRows),true ); //die('> <pre>' . print_r($aRows, true) . '</pre>');
 foreach($aRows['Apartment'] as $value){
  if(
   (int)$value['StatusCode'] != 4 and
   (int)$value['StatusCode'] != 8
  ){ continue; }
  //$aTest['StatusCodes'][(int)$value['StatusCode']] = $value['StatusCodeName'];

  $buildingGroupId = $value['BuildingGroupId'];

  $key = $value['ArticleId'];
  foreach($value as $k => $v){
   $aTable[$key][$k] = $v;
  }
 }
 #
 if(count($aTable) > 0){
  $path = date('ymd');
  !is_dir(__DIR__ . '/' . $path) && mkdir(__DIR__ . '/' . $path);
  $path .= '/' . $buildingGroupId;
  !is_dir(__DIR__ . '/' . $path) && mkdir(__DIR__ . '/' . $path);
  $path .= "/{$buildingId}.json";
  file_put_contents(__DIR__ . '/' . $path, json_encode($aTable, JSON_UNESCAPED_UNICODE));

  echo <<<HD
<p>Создан <a href="{$path}" target="_blank">{$path}</a></p>

HD;
 }
}


$aLog = json_decode( file_get_contents($fileLogName), true );
$aLog[$startTime] = abs( ceil($startHRTime - microtime(true))); //Затраченное время в секундах
file_put_contents($fileLogName, json_encode($aLog));


//die('$aTable =><br><pre>' . print_r($aTable, true) . '</pre>');
//file_put_contents('./test__status_codes.json', json_encode($aTest['StatusCodes']));