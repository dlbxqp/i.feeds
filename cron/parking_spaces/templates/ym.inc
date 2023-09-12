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

        $typePrefix = 'Парковочное место';
        $model = $typePrefix . " {$v['area']['total']} м2";
        #> data

        $content .= <<<HD
   <offer id="{$v['code']}" available="true">
    <vendor>Инград</vendor>
    <price>{$v['price']}</price>
    <currencyId>RUR</currencyId>
    <typePrefix>{$typePrefix}</typePrefix>
    <model>{$model}</model>
    <description>{$v['description']}</description>
{$pictures}    <param name="Есть отделка?">нет</param>
    <param name="Застройка">{$v['project']['name']}</param>
    <param name="Площадь по проекту общая (расчетная)">{$v['area']['total']}</param>
    <param name="Тип объекта">{$typePrefix}</param>
    <param name="Корпус">{$v['house']['number']}</param>
    <param name="Плановая дата ввода в эксплуатацию (РВЭ)">{$v['built']['quarter']} квартал {$v['built']['year']}</param>
    <categoryId>4</categoryId>
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
   <category id="6" parentId="0">Парковочные места</category>
  </categories>
  <offers>
{$content}  </offers>
 </shop>
</yml_catalog>

HD;

    $fileName = 'ym__parking_spaces.xml';
    file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
    #
    return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}
