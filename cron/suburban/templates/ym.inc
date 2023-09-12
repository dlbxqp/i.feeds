<?php
function getYM($a){
 $content = '';
 foreach((array)$a as $v){
  #< data
  $aImages = array_merge($v['images'], $v['project']['images']);
  $pictures = '';
  foreach($aImages as $image){
   $pictures .= "    <picture>$image</picture>\r\n";
  }

  if( isset($v['building project']['floors']) ){
   $typePrefix = 'Дом с земельным участком';
   $model = "{$v['building project']['floors']}-этажный коттедж, {$v['area']['total']} м2";
  } else{
   $typePrefix = 'Земельный участок';
   $model = $typePrefix . " {$v['area']['total']} м2";
  }
  #> data

  $content .= <<<HD
   <offer id="{$v['code']}" available="true">
    <vendor>Инград</vendor>
    <price>{$v['price']}</price>
    <currencyId>RUR</currencyId>
    <typePrefix>{$typePrefix}</typePrefix>
    <model>{$model}</model>
    <description>{$v['description']}</description>
{$pictures}    <categoryId>3</categoryId>
    <pt>{$v['project']['title']}</pt>
   </offer>

HD;
 }
 #
 $date = date("Y-m-d H:i");
 $content = <<<HD
<?xml version="1.0" encoding="utf-8"?>
<yml_catalog date="{$date}">
 <shop>
  <name>Интернет-магазин</name>
  <company>ПАО "Инград"</company>
  <url>https://www.ingrad.ru/</url>
  <currencies>
   <currency id="RUR"/>
  </currencies>
  <categories>
   <category id="3" parentId="0">Загородная недвижимость</category>
  </categories>
  <offers>
{$content}  </offers>
 </shop>
</yml_catalog>

HD;

 $fileName = 'ym__suburban.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}