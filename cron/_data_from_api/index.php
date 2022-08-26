<?php
$url = 'https://api.ingrad.ru/public';

$ch = curl_init();
$options = [
CURLOPT_REFERER => 'https://wd.ingrad.ru',
CURLOPT_RETURNTRANSFER => TRUE,
CURLOPT_ENCODING => ''
];
curl_setopt_array($ch, $options);

curl_setopt($ch, CURLOPT_URL, "{$url}/apartments?status[]=4&status[]=8");

$result = json_decode(curl_exec($ch), true);
//die('<pre>' . print_r($result, true) . '</pre>');

$aResult = [];
foreach((array)$result["data"] as $v){
 $aResult[(string)$v['crm_article_id']] = (int)$v['id'];
}

file_put_contents(__DIR__ . "/apartments.json", json_encode($aResult, JSON_FORCE_OBJECT));

curl_close($ch);