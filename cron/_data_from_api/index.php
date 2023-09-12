<?php
$url = 'https://new-api.ingrad.ru/api';

$ch = curl_init();
$options = [
 CURLOPT_REFERER => 'https://feeds.ingrad.ru',
 CURLOPT_RETURNTRANSFER => TRUE,
 CURLOPT_ENCODING => ''
];
curl_setopt_array($ch, $options);

#< projects
$aResult = ['in', 'out'];
curl_setopt($ch, CURLOPT_URL, "{$url}/estates?isFlatsInfo=true");
$aResult['in'] = json_decode( curl_exec($ch), true);
#
$aResult['out'] = [];
foreach($aResult['in']['list'] as $v){
 $aResult['out'][ $v['externalId'] ] = [
  'api-code' => $v['code'],
  'prices' => [
   'max' => $v['maxPrice'],
   'min' => $v['minPrice']
  ],
  'panorama' => $v['panorama'],
  'plan' => $v['imagePlan']['url'],
  'tour' => $v['tourLink']
 ];
}
//die('<pre>' . print_r($aResult, true) . '</pre>');
$fileName = 'projects.json';
file_put_contents(__DIR__.'/'.$fileName, json_encode($aResult['out'], JSON_FORCE_OBJECT));
#
$updateDate = date("Y-m-d H:i:s", filectime("./{$fileName}"));
echo <<<HD
<p>
 <a href="./{$fileName}" target="_blank">{$fileName}</a>
 <span style="opacity: .8; font-size: .8em">({$updateDate})</span>
</p>

HD;
#> projects

#< apartments
$aResult = ['in', 'out'];
curl_setopt($ch, CURLOPT_URL, "{$url}/flats?types=flat");
$aResult['in'] = json_decode( curl_exec($ch), true);
#
$aResult['out'] = [];
foreach($aResult['in']['list'] as $v){
 $aResult['out'][ $v['externalId'] ] = [
  'api-id' => $v['id'],
  'link' => $v['link'],
  'images' => [
   'main' => $v['imageMain'],
   'top' => $v['imageTop'],
   'floor' => $v['imageFloor'],
   'window' => $v['imageWindow'],
   'plain' => $v['planning']['image'],
   'plain 3d' => $v['planning']['image3d'],
   'plain furniture' => $v['planning']['imageFurniture']
  ],
  'tour' => $v['tourLink']
 ];
}
//die('<pre>' . print_r($aResult, true) . '</pre>');
$fileName = 'apartments.json';
file_put_contents(__DIR__.'/'.$fileName, json_encode($aResult['out'], JSON_FORCE_OBJECT));
#
$updateDate = date("Y-m-d H:i:s", filectime("./{$fileName}"));
echo <<<HD
<p>
 <a href="./{$fileName}" target="_blank">{$fileName}</a>
 <span style="opacity: .8; font-size: .8em">({$updateDate})</span>
</p>

HD;
#> apartments

curl_close($ch);