<?php
function getYR($a){
 $a_ = $GLOBALS['aAdditionalData'];

 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00
 $content = '';
 foreach((array)$a as $v){
  #< data
   #< plot or cottage
  if($v['type'] == 'жилой дом и придомовой участок'){
   $cottageData = <<<HD
  <area>
   <value>{$v['building project']['area']['design']}</value>
   <unit>кв. м</unit> 
  </area>
  <rooms>{$v['building project']['rooms count']}</rooms>
  <floors-total>{$v['building project']['floors']}</floors-total>
  <bathroom-unit>{$v['building project']['bathrooms']}</bathroom-unit>

HD;
   $category = 'дом с участком';
  } else{
   $cottageData = '';
   $category = 'участок';
  }
   #> plot or cottage

   #< images
  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $images .= "  <image>{$vv}</image>\n";
   }
  }
   #> images

  $yandexVillageId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['yr'];
  #> data

  #< filter
  //if($yandexVillageId == 0){ continue; }
  #> filter

  $content .= <<<HD
 <offer internal-id="{$v['code']}">
  <yandex-village-id>{$yandexVillageId}</yandex-village-id>
  <village-name>{$v['project']['name']}</village-name>
  <type>продажа</type>
  <property-type>жилая</property-type>
  <category>{$category}</category>
  <creation-date>{$creationData}</creation-date>
  <location>
   <country>Россия</country>
   <district>{$v['project']['region']}</district>
   <locality-name>кп «{$v['project']['name']}»</locality-name>
   <address>{$v['house']['address']}</address>
   <latitude>{$v['house']['latitude']}</latitude>
   <longitude>{$v['house']['longitude']}</longitude>
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
{$cottageData}  <building-section>{$v['house']['number']}</building-section>
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