<?php
function wrapYR($content){
 $creationData = date('Y-m-d\TH:i:s');

 return <<<HD
<?xml version="1.0" encoding="utf-8"?>
<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">
<generation-date>{$creationData}+03:00</generation-date>
{$content}</realty-feed>

HD;
}

function getYR($a){
 $a_ = $GLOBALS['aAdditionalData'];

 /* 230324
 #< action
 $promotionalCodes = file_get_contents(__DIR__ . '/../additional_data/promotional_apartments_for_yr.csv');
 $a_promotionalPrices = explode("\r\n", $promotionalCodes);
 $a_promotionalPrices_ = [];
 foreach($a_promotionalPrices as $promotionalPrice){
  $a_promotionalPrice = explode("\t", $promotionalPrice);
  $a_promotionalPrices_[ translit($a_promotionalPrice[0]) ] = $a_promotionalPrice[1];
 }
 //die('<pre>' . print_r($a_promotionalCodes_, true) .  '</pre>');
 #> action
 */

 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00
 $content = $content_novostroyM = $SB = '';
 $aSB = [];
 foreach((array)$a as $v){
  #< filter
  $projectId = (int)$a_['projects'][ $v['project']['crm-id'] ]['project ids']['yr'];
  $houseId = (int)$a_['projects'][ $v['project']['crm-id'] ]['buildings'][ $v['house']['number'] ]['building ids']['yr'];
  if($projectId == 0 or $houseId == 0){ continue; }
  #> filter

  #< data
  $internalId = ($v['api-id'] > 0) ? $v['api-id'] : $v['code']['translite'];
  #
  isset($v['url']) && ($url = "   <url>{$v['url']}</url>\r\n");

  $renovation = '';
  if(
   $v['finishing'] != 'без отделки'
   and $v['finishing'] != ''
  ){
   $renovation = "<renovation>черновая отделка</renovation>\r\n";
  }

  $buildingState = 'built';
  if(
   $v['house']['built']['year'] < date('Y')
   or (
    $v['house']['built']['year'] == date('Y')
    and $v['house']['built']['quarter'] <= ceil(date('m') / 3)
   )
  ){
   $buildingState = 'hand-over';
  }

  foreach($v['project']['images'] as $vv){
   $v['images'][] = $vv;
  }
  $aImages = $v['images'];
  $images = '';
  foreach($aImages as $k => $image){
   $attribute = '';
   if($image != null){
    if($k == 0){
     $attribute = 'plan';
    } else if($k == 1){
     $attribute = 'floor-plan';
    }

    ($attribute != '') && ($attribute = ' tag="' . $attribute . '"');
    $images .= "    <image{$attribute}>$image</image>\r\n";
   }
  }

  ($v['house']['region'] == 'Москва') && ($localityName = "   <locality-name>{$v['house']['region']}</locality-name>\r\n");

  $dealStatus = $v['переуступка'] ? 'переуступка' : 'продажа от застройщика';

  if( isset($a_promotionalPrices_[ $v['code']['translite'] ]) ){
   $price = ceil( str_replace(',', '.', $a_promotionalPrices_[ $v['code']['translite'] ] ) );
   $description = 'Цена указана с учетом скидки для пользователей Яндекс Недвижимости. Скидка будет работать только при 100% оплате либо стандартной ипотеке. ' . $v['description'];
  } else{
   $price = $v['price'];
   $description = $v['description'];
  }
  #> data

  //Порядок тегов согласно ТТ https://yandex.ru/support/realty/requirements/requirements-sale-new.html#concept4
  $SB = <<<HD
 <offer internal-id="{$internalId}">
  <type>продажа</type>
  <property-type>жилая</property-type>
  <category>квартира</category>
{$url}  <creation-date>{$creationData}+03:00</creation-date>
  <location>
   <country>Россия</country>
   <address>{$v['house']['address']}</address>
{$localityName}   <latitude>{$v['house']['latitude']}</latitude>
   <longitude>{$v['house']['longitude']}</longitude>
  </location>
  <sales-agent>
   <phone>+749{$v['project']['phone numbers']['yr']}</phone>
   <category>застройщик</category>
   <organization>ГК «Инград»</organization>
   <url>https://ingrad.ru/</url>
   <email>{$v['project']['e-mail']}</email>
  </sales-agent>
  <deal-status>{$dealStatus}</deal-status>
  <price>
   <value>{$price}</value>
   <currency>RUR</currency>
  </price>
{$images}  <is-image-order-change-allowed>да</is-image-order-change-allowed>
  <area>
   <value>{$v['area']['total']}</value>
   <unit>кв. м</unit>
  </area>
  <living-space>
   <value>{$v['area']['living']}</value>
   <unit>кв. м</unit>
  </living-space>
{$renovation}  <description>{$description}</description>
  <new-flat>да</new-flat>
  <floor>{$v['floor number']}</floor>
  <rooms>{$v['rooms count']}</rooms>
  <floors-total>{$v['house']['floors count']}</floors-total>
  <building-name>{$v['project']['name']}</building-name>
  <yandex-building-id>{$projectId}</yandex-building-id>
  <yandex-house-id>{$houseId}</yandex-house-id>
  <building-state>{$buildingState}</building-state>
  <built-year>{$v['house']['built']['year']}</built-year>
  <ready-quarter>{$v['house']['built']['quarter']}</ready-quarter>  
  <building-section>{$v['house']['number']}</building-section>  
 </offer>

HD;

  $content .= $SB;
  #
  $content_novostroyM_ = str_replace('</location', "<novostroy-section>{$v['section']['number']}</novostroy-section>\r\n  </location", $SB);
  $content_novostroyM_ = str_replace('</location', "<apartment>{$v['number']}</apartment>\r\n  </location", $content_novostroyM_);
  $content_novostroyM .= $content_novostroyM_;
  #
  $aSB[ (string)$v['project']['title'] ] .= $SB;
 }
 #
 $content = wrapYR($content);
 $content_novostroyM = wrapYR($content_novostroyM);

 #< main
 $fileName = 'yr__apartments.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 $result = <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
 #> main

 #< novostroyM
 $fileName_novostroyM = 'yr__apartments__novostroyM.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName_novostroyM}", $content_novostroyM);
 #
 $result .= <<<HD
<p>
 <a href="/f2/{$fileName_novostroyM}" target="_blank">//wd.ingrad.ru/f2/{$fileName_novostroyM}</a>
</p>

HD;
 #> novostroyM

 #< SB
 $result_SB = '';
 foreach($aSB as $k => $v){
  $fileName_SB = 'yr__apartments__sb__' . (string)$k . '.xml';
  file_put_contents(__DIR__ . "/../../../{$fileName_SB}", wrapYR((string)$v));
  #
  $result_SB .= <<<HD
 <a href="/f2/{$fileName_SB}" target="_blank">//wd.ingrad.ru/f2/{$fileName_SB}</a><br>
 
HD;
 }
 $result .= "<p>{$result_SB}</p>";
 #> SB

 #< real estate agencies (для Дмитрия Ломакина)
 $fileName_rea = 'yr__apartments__rea.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName_rea}", $content);
 //copy("../../{$fileName}", "../../{$fileName_rea}");
 #
 $result .= <<<HD
<p>
 <a href="/f2/{$fileName_rea}" target="_blank">//wd.ingrad.ru/f2/{$fileName_rea}</a>
</p>

HD;
 #> real estate agencies

 return $result;
}