<?php
include __DIR__ . '/../../../assets/includes/ini_set.inc';


$ch = curl_init();
#
$options = [
 CURLOPT_URL => 'https://crm.dmfs.ru/Service/ExportToSite.svc/AddressListFull/xml',
 CURLOPT_REFERER => 'https://wd.ingrad.ru',
 CURLOPT_RETURNTRANSFER => TRUE,
 CURLOPT_ENCODING => ''
];
curl_setopt_array($ch, $options);
$xml = curl_exec($ch); //die('<pre>' . print_r($xml, true) . '</pre>');
$aResponseInfo = curl_getinfo($ch); //die('<pre>' . print_r($aResponseInfo, true) . '</pre>');
#
if($aResponseInfo['http_code'] >= 300){
 $to = 'guztv@ingrad.com';
 $message = 'Ошибка при обращении к crm в скрипте: ' . __FILE__ . '<br><pre>' . print_r($aResponseInfo, true) . '</pre>';
 mail($to, 'Alarm wd.i/f2', $message, [
  'From' => $to,
  'Reply-To' => $to,
  'X-Mailer' => 'PHP/' . phpversion()
 ]);

 exit($message);
}
#
curl_close($ch);

$xml = str_replace('a:', '', $xml);
$xml = str_replace('b:', '', $xml);
$object = new SimpleXMLElement($xml);
$oRows = $object->XMLAddressListFullDataResult;
$aRows = json_decode( json_encode($oRows),true); //die('> <pre>' . print_r($aRows, true) . '</pre>');

$aTable = [
 0 => [],
 1 => []
];
foreach($aRows['Building'] as $value){
 #< filter
 if((string)$value['WithoutPrice'] == 'true') continue;
 #> filter

 #< data
 foreach((array)$value as $k => $v){
  $aTable[0][ (string)$value['BuildingID'] ][ucfirst($k)] = $v;
 }

 $aTable[1][ (string)$value['BuildingID'] ] = (string)$value['AddressBuild'];
 #> data
}



file_put_contents(__DIR__ . '/buildings.json', json_encode($aTable[0], JSON_UNESCAPED_UNICODE) );
file_put_contents(__DIR__ . '/buildings_.json', json_encode($aTable[1], JSON_UNESCAPED_UNICODE) );


echo '<p>' . count($aTable[0]) . ' корпусов: </p><pre>' . print_r($aTable, true) . '</pre>';