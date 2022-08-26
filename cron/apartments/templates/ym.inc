<?php
function getYM($a){
 $a_ = $GLOBALS['aAdditionalData'];

 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00
 $content = '';
 foreach((array)$a as $v){
  #< filter
  //$projectId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['yr'];
  //$houseId = (int)$a_['projects'][$v['project']['crm-id']]['buildings'][$v['house']['number']]['building ids']['yr'];
  if(
   (
    $v['project']['crm-id'] != '54a82767-85da-e711-80e8-005056ba18b6' //filatovlug
   )
   //or $houseId == 0
  ){ continue; }
  #> filter

  #< data
  $pictures = '';
  foreach($v['plans'] as $plan){
   $pictures .= "    <picture>$plan</picture>\r\n";
  }

  $model = "{$v['rooms count']}-комн. квартира, {$v['area']} м2";
  #> data

  $content .= <<<HD
   <offer id="{$v['code']}" available="true">
    <vendor>Инград</vendor>
    <price>{$v['price']}</price>
    <currencyId>RUR</currencyId>
    <categoryId>1</categoryId>
    <typePrefix>квартира</typePrefix>
    <model>{$model}</model>
    <description>{$v['description']}</description>
    <url>https://www.ingrad.ru/{$v['project']['title']}/apartment/{$v['api-id']}/</url>
{$pictures}   </offer>

HD;
/*
    <android_url>{диплинк для Android устройств}</android_url>
    <ios_url>{диплинк для iOS устройств}</ios_url>
    <bundle_id>{идентификатор приложения в Google Play}</bundle_id>
    <ios_app_store_id>{идентификатор приложения в App Store}</ios_app_store_id> </ios_app_store_id>
    <android_tracking_url>{трекинг ссылка для Android} </android_tracking_url>
    <ios_tracking_url>{трекинг ссылка для iOS}</ios_tracking_url>
*/
 }
 #
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
  <categories>
   <category id="1" parentId="0">Квартиры</category>
  </categories>
  <offers>
{$content}  </offers>
 </shop>
</torg_price>

HD;

 $fileName = 'ym__apartments.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}