<?php
function getYM($a){
 $aOffers = [];
 foreach((array)$a as $v){
  #< data
   #< images
  $aImages = array_merge($v['images'], $v['project']['images']);
  $pictures = '';
  foreach($aImages as $image){
   $pictures .= "    <picture>$image</picture>\r\n";
  }
   #> images
  #> data

  if($v['project']['crm-id'] == '54a82767-85da-e711-80e8-005056ba18b6'){
   $aOffers[ $v['project']['title'] ] .= <<<HD
   <offer id="{$v['code']['translite']}" available="true">
    <vendor>Инград</vendor>
    <price>{$v['price']}</price>
    <currencyId>RUR</currencyId>
    <typePrefix>квартира</typePrefix>
    <model>{$v['rooms count']}-комн. квартира, {$v['area']['total']} м2</model>
    <description>{$v['description']}</description>
    <url>{$v['url']}</url>
{$pictures}    <p>{$v['project']['name']}</p>
    <b>{$v['house']['number']}</b>
   </offer>

HD;
  }
 }


 $result = '';
 foreach($aOffers as $k => $v){
  $date = date("Y-m-d H:i");
  $content = <<<HD
<?xml version="1.0" encoding="utf-8"?>
<torg_price date="{$date}">
 <shop>
  <name>Интернет-магазин</name>
  <company>ПАО "Инград"</company>
  <url>https://www.ingrad.ru/</url>
  <currencies>
   <currency id="RUR"/>
  </currencies>
  <offers>
{$v}  </offers>
 </shop>
</torg_price>

HD;

  $fileName = "ym__apartments__{$k}.xml";
  file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
  #
  $result .= <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
 }

 return $result;
}
