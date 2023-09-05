<?php
function getYR($a){
 $a_ = $GLOBALS['aAdditionalData'];

 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00
 $content = '';
 foreach((array)$a as $v){
  #< images
  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $images .= "  <image>{$vv}</image>\n";
   }
  }
  #> images

  $projectId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['yr'];
  $houseId = (int)$a_['projects'][$v['project']['crm-id']]['buildings'][$v['house']['number']]['building ids']['yr'];
  if($projectId == 0 or $houseId == 0){ continue; }

  $content .= <<<HD
 <offer internal-id="{$v['code']}">
  <yandex-building-id>{$projectId}</yandex-building-id>
  <yandex-house-id>{$houseId}</yandex-house-id>
  <building-name>{$v['project']['name']}</building-name>
  <building-section>{$v['house']['number']}</building-section>
  <type>продажа</type>
  <category>Коммерческая недвижимость</category>
  <commercial-type>free purpose</commercial-type>
  <creation-date>{$creationData}+03:00</creation-date>
  <location>
   <country>Россия</country>
   <address>{$v['house']['address']}</address>
  </location>
  <sales-agent>
   <phone>+749{$v['phone numbers']['yr']}</phone>
   <category>застройщик</category>
   <organization>ГК «Инград»</organization>
  </sales-agent>
  <price>
   <value>{$v['price']}</value>
   <currency>RUR</currency>
  </price>
  <deal-status>продажа от застройщика</deal-status>
  <area>
   <value>{$v['area']}</value>
   <unit>кв. м</unit>
  </area>
{$images}  <floor>{$v['floor number']}</floor>
  <url>https://ingrad.ru/projects/{$v['project']['title']}/select/keller/all</url>
  <description>{$v['description']}</description>
 </offer>

HD;
 }
 #
 $content = <<<HD
<?xml version="1.0" encoding="UTF-8"?>
<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">
<generation-date>{$creationData}+03:00</generation-date>
{$content}</realty-feed>

HD;

 $fileName = 'yr__storage_rooms.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}