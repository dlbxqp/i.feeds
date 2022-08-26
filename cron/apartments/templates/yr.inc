<?php
function getYR($a){
 $a_ = $GLOBALS['aAdditionalData'];

 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00
 $content = '';
 foreach((array)$a as $v){
  $projectId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['yr'];
  $houseId = (int)$a_['projects'][$v['project']['crm-id']]['buildings'][$v['house']['number']]['building ids']['yr'];
  if($projectId == 0 or $houseId == 0){ continue; }

  #< data
  $buildingState = 'built';
  if(
   $v['house']['built']['year'] < date('Y')
   or (
    $v['house']['built']['year'] == date('Y')
    and $v['house']['built']['quarter'] <= ceil(date('m') / 4)
   )
  ){
   $buildingState = 'hand-over';
  }
  #> data

  //Порядок тегов согласно ТТ https://yandex.ru/support/realty/requirements/requirements-sale-new.html#concept4
  $content .= <<<HD
 <offer internal-id="{$v['code']}">
  <type>продажа</type>
  <property-type>жилая</property-type>
  <category>квартира</category>
  <url></url>
  <creation-date>{$creationData}+03:00</creation-date>
  <location>
   <country>Россия</country>
   <address>{$v['house']['address']}</address>
   <latitude></latitude>
   <longitude></longitude>
  </location>
  <sales-agent>
   <phone>+749{$v['phone number']['yr']}</phone>
   <category>застройщик</category>
   <organization>ГК «Инград»</organization>
   <url>https://ingrad.ru/</url>
   <email></email>
  </sales-agent>
  <deal-status>продажа от застройщика</deal-status>
  <price>
   <value>{$v['price']}</value>
   <currency>RUR</currency>
  </price>
  <image></image>
  <is-image-order-change-allowed>да</is-image-order-change-allowed>
  <area>
   <value>{$v['area']}</value>
   <unit>кв. м</unit>
  </area>
  <living-space>
   <value>{$v['area']}</value>
   <unit>кв. м</unit>
  </living-space>
  <description>{$v['description']}</description>
  <new-flat>да</new-flat>
  <floor>{$v['floor number']}</floor>
  <rooms>{$v['rooms count']}</rooms>
  <floors-total>{$v['floor count']}</floors-total>
  <building-name>{$v['project']['name']}</building-name>
  <yandex-building-id>{$projectId}</yandex-building-id>
  <yandex-house-id>{$houseId}</yandex-house-id>
  <building-state>{$buildingState}</building-state>
  <built-year>{$v['house']['built']['year']}</built-year>
  <ready-quarter>{$v['house']['built']['quarter']}</ready-quarter>  
  <building-section>{$v['house']['number']}</building-section>  
 </offer>

HD;
 }
 #
 $content = <<<HD
<?xml version="1.0" encoding="utf-8"?>
<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">
<generation-date>{$creationData}+03:00</generation-date>
{$content}</realty-feed>

HD;

 $fileName = 'yr__apartments.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}