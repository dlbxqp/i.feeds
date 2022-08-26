<?php
include __DIR__ . '/../../../assets/includes/ini_set.inc';


$url = 'https://crm.dmfs.ru/Service/ExportToSite.svc/AddressListFull/xml';

$ch = curl_init();
$options = [
 CURLOPT_REFERER => 'https://wd.ingrad.ru',
 CURLOPT_RETURNTRANSFER => TRUE,
 CURLOPT_ENCODING => ''
];
curl_setopt_array($ch, $options);
curl_setopt($ch, CURLOPT_URL, $url);
$xml = curl_exec($ch); //die('<pre>' . print_r($xml) . '</pre>');
$xml = str_replace('a:', '', $xml);
$xml = str_replace('b:', '', $xml);

$object = new SimpleXMLElement($xml);
$oRows = $object->XMLAddressListFullDataResult;
$aRows = json_decode( json_encode($oRows),true); //die('> <pre>' . print_r($aRows, true) . '</pre>');
//$aProjectsIds = [];
$aBuildingsIds = [];
$aTable = [];
foreach($aRows['Building'] as $value){
 if((string)$value['WithoutPrice'] == 'true'){
  continue;
 }
 $key = (string)$value['BuildingID'];

 $aBuildingsIds[] = $key;

 $aTable[$key]['AddressRegion'] = ( mb_stripos((string)$value['AddressBuild'], 'Москва') === false) ? 'МО' : 'Москва';

 foreach((array)$value as $k => $v){
  $aTable[$key][ucfirst($k)] = $v;
 }

 unset($key);
}


//file_put_contents(__DIR__ . '/projectsIds.json', json_encode( array_unique($aProjectsIds) ) );
file_put_contents(__DIR__ . '/buildingsIds.json', json_encode($aBuildingsIds) );
file_put_contents(__DIR__ . '/buildings.json', json_encode($aTable, JSON_UNESCAPED_UNICODE) );


echo '<p>' . count($aTable) . ' корпусов: </p><pre>' . print_r($aTable, true) . '</pre>';