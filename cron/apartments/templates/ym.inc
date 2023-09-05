<?php
$GLOBALS['aYMObjectsTypes'] = [
 1 => [
  'title' => 'Бизнес',
  'projects' => [
   'c4a7fd91-6f8f-ea11-8128-005056ba18b6' => [
    'id' => 11,
    'title' => 'zk-foriver',
    'houses' => [null]
   ],
   '662ddc32-8af7-e711-80ea-005056ba18b6' => [
    'id' => 12,
    'title' => 'zk-riversky',
    'houses' => [null]
   ],
   'b3003808-8bf7-e711-80ea-005056ba18b6' => [
    'id' => 13,
    'title' => 'zk-tophills',
    'houses' => [null]
   ],
   '5e79c8bf-fb96-e711-80e5-005056ba18b6' => [
    'id' => 14,
    'title' => 'zk-kutuzovgrad',
    'houses' => [null]
   ],
   '18959ccf-06a6-ed11-8142-005056ba18b6' => [
    'id' => 15,
    'title' => 'injoy',
    'houses' => [null]
   ]
  ]
 ],
 2 => [
  'title' => 'Комфорт',
  'projects' => [
   '54a82767-85da-e711-80e8-005056ba18b6' => [
    'id' => 21,
    'title' => 'zk-filatov-lug',
    'houses' => [null]
   ],
   'dccb2f2e-e09b-e411-81e4-00155d110c01' => [
    'id' => 22,
    'title' => 'mkr-novoe-medvedkovo',
    'houses' => [null]
   ],
   'decb2f2e-e09b-e411-81e4-00155d110c01' => [
    'id' => 23,
    'title' => 'mkr-novoe-puskino',
    'houses' => [null]
   ],
   'f93d4a5b-844a-e711-80db-005056ba18b6' => [
    'id' => 24,
    'title' => 'zk-vesna',
    'houses' => [null]
   ],
   '9cd581b5-1b54-eb11-812f-005056ba18b6' => [
    'id' => 25,
    'title' => 'zk-milovidnoe',
    'houses' => [null]
   ],
   'f6437d15-f696-e711-80e5-005056ba18b6' => [
    'id' => 26,
    'title' => 'zk-odincovo-zapadnaya',
    'houses' => [null]
   ],
   '2130f38f-f5d3-e511-80d4-00155d110c02' => [
    'id' => 27,
    'title' => 'zk-gusarskaya-ballada',
    'houses' => [null]
   ],
   '5be8b96c-8af7-e711-80ea-005056ba18b6' => [
    'id' => 28,
    'title' => 'zk-odincovo-mbiryuzova',
    'houses' => [null]
   ]
  ]
 ],
 3 => [
  'title' => 'Загородная недвижимость',
  'projects' => [
   '3222e43f-0059-ed11-8141-005056ba18b6' => [ //4b4fd4d4-0159-ed11-8141-005056ba18b6
    'id' => 31,
    'title' => 'zk-inhaus-soloxovo',
    'houses' => [null]
   ],
   'cf84c934-a485-e811-80f3-005056ba18b6' => [ //c5de596a-a485-e811-80f3-005056ba18b6
    'id' => 32,
    'title' => 'solnecnyi-bereg',
    'houses' => [null]
   ]
  ]
 ]
];
function searchYMObjectsType($needle, $type = 0){
 foreach($GLOBALS['aYMObjectsTypes'] as $k => $v){
  foreach($v['projects'] as $kk => $vv){
   if($vv['title'] == $needle){
     if($type == 1){
      return $vv['id'];
     } else if($type == 2){
      return $k;
     } else{
      return $kk;
     }
   }

   foreach($vv['houses'] as $kkk => $vvv){
    if($vvv == $needle){
     return $kkk;
    }
   }
  }
 }

 return false;
}

$GLOBALS['aCategory'] = [
    'projects' => [],
    'houses' => []
];

#< suburban
$XMLFile = __DIR__ . '/../../../ym__suburban.xml';
$xml = simplexml_load_file($XMLFile);
$GLOBALS['aSuburbanItems'] = [];
foreach($xml->shop->offers->offer as $offer){
 $k = $offer->pt;
 $kk = searchYMObjectsType($k, 1);
 $offer->addChild('categoryId', $kk);
 $GLOBALS['aSuburbanItems'][(string)$k] .= $offer->asXML();
}
//print('> <pre>' . print_r($GLOBALS['aSuburbanItems'], true) . '</pre>');
//unlink($XMLFile);
#> suburban

#< commercial realty
$XMLFile = __DIR__ . '/../../../ym__commercial_realty.xml';
$xml = simplexml_load_file($XMLFile);
$GLOBALS['aCommercialRealtyItems'] = [];
foreach($xml->shop->offers->offer as $offer){
 $k = $offer->pt;
 $kk = searchYMObjectsType($k);
 $offer->addChild('categoryId', $kk);
 $GLOBALS['aCommercialRealtyItems'][(string)$k] .= $offer->asXML();
}
#> commercial realty


function getYM($a){
 $aOffers = [];
 foreach((array)$a as $v){
  #< data
  $aImages = array_merge($v['images'], $v['project']['images']);
  $pictures = '';
  foreach($aImages as $image){
   $pictures .= "    <picture>$image</picture>\r\n";
  }

  $finishing = false;
  if(
   $v['finishing'] != 'без отделки'
   and $v['finishing'] != ''
  ){
   $finishing = true;
  }

   #< по просьбе Мышак
  $projectName = $v['project']['name'];
  if($v['project']['title'] == 'mkr-novoe-puskino'){
   $projectName = "ЖК «Новое Пушкино»";
  } else if($v['project']['title'] == 'mkr-novoe-medvedkovo'){
   $projectName = "ЖК «Новое Медведково»";
  } else if($v['project']['title'] == 'zk-odincovo-mbiryuzova'){
   $projectName = "ЖК «Одинград. Лесной и Центральный»";
  } else if($v['project']['title'] == 'zk-odincovo-zapadnaya'){
   $projectName = "ЖК «Одинград. Семейный»";
  }
   #> по просьбе Мышак

   #< categories
  $projectId = searchYMObjectsType($v['project']['title'], 1);
  $objectTypeId = searchYMObjectsType($v['project']['title'], 2);
  if($projectId > 0 and $objectTypeId > 0){
      if(is_array($projectName)){
          $projectName = print_r($projectName);
      }

      $item = <<<HD
   <category id="{$projectId}" parentId="{$objectTypeId}">{$projectName}</category>

HD;
      !in_array($item, $GLOBALS['aCategory']['projects']) && ($GLOBALS['aCategory']['projects'][] = $item);
  }
  #
  $houseId = searchYMObjectsType($v['house']['number']);
  if($houseId === false){
   $GLOBALS['aYMObjectsTypes'][$objectTypeId]['projects'][$projectId]['houses'][] = $v['house']['number'];
   $houseId = array_key_last($GLOBALS['aYMObjectsTypes'][$objectTypeId]['projects'][$projectId]['houses']);
  }
  $houseId = searchYMObjectsType($v['project']['title'], 1) . $houseId;
  if($houseId > 0 and $projectId > 0){
      $item = <<<HD
   <category id="{$houseId}" parentId="{$projectId}">Корпус {$v['house']['number']}</category>

HD;
      !in_array($item, $GLOBALS['aCategory']['houses']) && ($GLOBALS['aCategory']['houses'][] = $item);
  }
  #
  unset($item);
  #
  $categoryId = "    <categoryId>{$houseId}</categoryId>\r\n";
   #> categories

   #< status
  if(
   (int)$v['status']['code'] == 4
   //or (int)$v['status']['code'] == 8
  ){
   $aStatus[0] = 'true';
  } else{
   $aStatus[0] = 'false';
  }
  $aStatus[1] = <<<HD
    <param name="Статус объекта">{$v['status']['title']}</param>

HD;
   #> status

   #<
  $builtDate = '';
  if($v['house']['built']['quarter'] != '' && $v['house']['built']['year'] != ''){
   $builtDate = <<<HD
    <param name="Плановая дата ввода в эксплуатацию (РВЭ)">{$v['house']['built']['quarter']} квартал {$v['house']['built']['year']}</param>

HD;
  }
   #>
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

  $aOffers['mindbox'] .= <<<HD
   <offer id="{$v['crm-id']}" available="{$aStatus[0]}">
    <vendor>Инград</vendor>
    <price>{$v['price']}</price>
    <oldprise>{$v['price']}</oldprise>
    <currencyId>RUR</currencyId>
    <typePrefix>квартира</typePrefix>
    <model>{$v['rooms count']}-комн. квартира, {$v['area']['total']} м2</model>
    <description>{$v['description']}</description>
    <url>{$v['url']}</url>
{$pictures}    <param name="Есть отделка?">{$finishing}</param>
    <param name="Застройка">{$projectName}</param>
    <param name="Площадь по проекту общая (расчетная)">{$v['area']['total']}</param>
    <param name="Количество комнат">{$v['rooms count']}</param>
    <param name="Тип объекта">квартира</param>
    <param name="Корпус">{$v['house']['number']}</param>
{$builtDate}{$aStatus[1]}{$categoryId}   </offer>

HD;
 }

 $categories = implode('', array_merge($GLOBALS['aCategory']['projects'], $GLOBALS['aCategory']['houses']));
 $result = '';
 foreach($aOffers as $k => $v){
  $date = date("Y-m-d H:i");

  if($k == 'mindbox'){
   $feedType = 'yml_catalog';
   $v = $v . implode('', $GLOBALS['aSuburbanItems']);
   $v = $v . implode('', $GLOBALS['aCommercialRealtyItems']);
  } else{
   $feedType = 'torg_price';
  }

  $content = <<<HD
<?xml version="1.0" encoding="utf-8"?>
<{$feedType} date="{$date}">
 <shop>
  <name>Интернет-магазин</name>
  <company>ПАО "Инград"</company>
  <url>https://www.ingrad.ru/</url>
  <currencies>
   <currency id="RUR"/>
  </currencies>
  <categories>
   <category id="1">Бизнес</category>
   <category id="2">Комфорт</category>
   <category id="3">Загородная недвижимость</category>
   <category id="4">Коммерческая недвижимость</category>
{$categories}  </categories>
  <offers>
{$v}  </offers>
 </shop>
</{$feedType}>

HD;

  $mainCondition = (date('H') < 23 AND !isset($_GET['full']));
  if(
   !$mainCondition
   AND $k == 'mindbox'
  ){
   $k .= '__full';
  }
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
