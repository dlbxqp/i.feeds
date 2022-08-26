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

  $yandexVillageId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['yr'];
  //if($yandexVillageId == 0){ continue; }

  $content .= <<<HD
 <offer internal-id="{$v['code']}">
  <yandex-village-id>{$yandexVillageId}</yandex-village-id>
  <village-name>{$v['project']['name']}</village-name>
  <type>продажа</type>
  <property-type>жилая</property-type>
  <category>участок</category>
  <creation-date>{$creationData}</creation-date>
  <location>
   <country>Россия</country>
   <district>{$v['project']['region']}</district>
   <locality-name>кп «{$v['project']['name']}»</locality-name>
   <address>{$v['project']['address']}</address>
   <latitude>{$v['project']['latitude']}</latitude>
   <longitude>{$v['project']['longitude']}</longitude>
  </location>
  <sales-agent>
   <phone>+749{$v['project']['phone numbers']['yr']}</phone>
   <category>застройщик</category>
  </sales-agent>
  <price>
   <value>{$v['price']}</value>
   <currency>RUR</currency>
  </price>
  <lot-area>
   <value>{$v['area']}</value>
   <unit>сотка</unit>
  </lot-area>
{$images}  <lot-number>{$v['number']}</lot-number>
  <deal-status>продажа от застройщика</deal-status>
  <description>{$v['description']}</description>
  <built-year>{$v['built year']}</built-year>
  <mortgage>1</mortgage>
  <building-section>{$v['house']['number']}</building-section>
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

 $fileName = 'yr__suburban.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}