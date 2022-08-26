<?php
function getYR($a){
 $a_ = $GLOBALS['aAdditionalData'];

 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00
 foreach((array)$a as $v){
  $projectId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['yr'];
  $houseId = (int)$a_['projects'][$v['project']['crm-id']]['buildings'][$v['house']['number']]['building ids']['yr'];
  if($projectId == 0 or $houseId == 0){ continue; }

  #< Подготовка данных
  $coordinates = '';
  if($v['house']['latitude'] > 0 and $v['house']['longitude'] > 0){
   $coordinates = <<<HD
   <latitude>{$v['house']['latitude']}</latitude>
   <longitude>{$v['house']['longitude']}</longitude>

HD;
  }

   #< images
  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $images .= "  <image>{$vv}</image>\n";
   }
  } else{
   $images = "  <image>https://api.ingrad.ru/photos/content_blocks/c830b640-fb0c-4d36-a3d1-20b204354c9d.jpeg</image>\n";
  }
   #> images
  #> Подготовка данных

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
{$coordinates}  </location>
  <sales-agent>
   <phone>+749{$v['phone number']['yr']}</phone>
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
  <floor>{$v['floor number']}</floor>
  <url>https://ingrad.ru/projects/{$v['project']['title']}/select/commercial/all</url>
  <description>{$v['description']}</description>
{$images} </offer>

HD;
 }
 #
 $content = <<<HD
<?xml version="1.0" encoding="UTF-8"?>
<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">
<generation-date>{$creationData}+03:00</generation-date>
{$content}</realty-feed>

HD;

 $fileName = 'yr__commercial_realty.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}