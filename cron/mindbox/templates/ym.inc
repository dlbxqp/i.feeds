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
                } else if($type == 3){
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

function getYM($a){
 $aOffers = [];
 foreach((array)$a as $v){
 #< general data
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

  #< images
     $pictures = '';
     $aImages = array_merge($v['images'], $v['project']['images']);
     foreach($aImages as $image){
         if($image != ''){
             $pictures .= "    <picture>$image</picture>\r\n";
         }
     }
  #> images

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

  #< ввод в эксплуатацию
     $builtDate = '';
     if($v['house']['built']['quarter'] != '' && $v['house']['built']['year'] != ''){
         $builtDate = <<<HD
    <param name="Плановая дата ввода в эксплуатацию (РВЭ)">{$v['house']['built']['quarter']} квартал {$v['house']['built']['year']}</param>

HD;
     }
  #> ввод в эксплуатацию

  #< отделка
     $finishing = 'Нет';
     if(
         $v['finishing'] != 'без отделки'
         and $v['finishing'] != ''
     ){
         $finishing = 'Есть';
     }
  #> отделка
 #> general data

 #< unique data by offer type data+xml
     if(
         $v['type'] === 'земельный участок'
         or $v['type'] === 'жилой дом и придомовой участок'
     ){
      #< suburban
         if(
             $v['building project'] !== ''
         ){
             #< inhaus
             $typePrefix = 'Дом с земельным участком';
             $model = "{$v['building project']['floors']}-этажный коттедж" . ($v['area']['total'] != '') && ", {$v['area']['total']} м2";
             $description = $typePrefix . '. ' . $model;
             #> inhaus
         } else{
             #< ecodolie
             $typePrefix = 'Земельный участок';
             $model = $typePrefix . (($v['area']['total'] != '') && " {$v['area']['total']} соток");
             $description = $model;
             #> ecodolie
         }

         //die('> ' . $v['crm-id'] . ' > '. $v['project']['title']);

         $offerData = <<<HD
   <offer id="{$v['crm-id']}" available="{$aStatus[0]}">
    <vendor>Инград</vendor>
    <price>{$v['price']['new']}</price>
    <currencyId>RUR</currencyId>
    <typePrefix>{$typePrefix}</typePrefix>
    <model>{$model}</model>
    <description>{$description}</description>
{$pictures}    <categoryId>3</categoryId>
    <pt>{$v['project']['title']}</pt>
   </offer>

HD;
      #> suburban
     } else if(stripos($v['house']['title'], 'нежилые') !== false){
      #< commercial realty
         #< data
         $typePrefix = 'Помещение коммерческого назначения';
         $model = $typePrefix . " {$v['area']['total']} м2";
         $description = $model;
         #> data

         $offerData = <<<HD
   <offer id="{$v['crm-id']}" available="{$aStatus[0]}">
    <vendor>Инград</vendor>
    <price>{$v['price']['new']}</price>
    <currencyId>RUR</currencyId>
    <typePrefix>{$typePrefix}</typePrefix>
    <model>{$model}</model>
    <description>{$description}</description>
{$pictures}    <param name="Есть отделка?">{$finishing}</param>
    <param name="Застройка">{$v['project']['name']}</param>
    <param name="Площадь по проекту общая (расчетная)">{$v['area']['total']}</param>
    <param name="Количество комнат">{$v['rooms count']}</param>
    <param name="Тип объекта">помещение коммерческого назначения</param>
    <param name="Корпус">{$v['house']['number']}</param>
    <param name="Плановая дата ввода в эксплуатацию (РВЭ)">{$v['built']['quarter']} квартал {$v['built']['year']}</param>
    <categoryId>4</categoryId>
    <pt>{$v['project']['title']}</pt>
   </offer>

HD;
      #> commercial realty
     } else if(stripos($v['house']['title'], 'кладовые') !== false){
      #< storage rooms
         #< data
         $typePrefix = 'Кладовая комната';
         $model = $typePrefix . " {$v['area']['total']} м2";
         $description = $model;
         #> data

         $offerData = <<<HD
   <offer id="{$v['crm-id']}" available="{$aStatus[0]}">
    <vendor>Инград</vendor>
    <price>{$v['price']['new']}</price>
    <currencyId>RUR</currencyId>
    <typePrefix>{$typePrefix}</typePrefix>
    <model>{$model}</model>
    <description>{$description}</description>
{$pictures}    <param name="Есть отделка?">{$finishing}</param>
    <param name="Застройка">{$v['project']['name']}</param>
    <param name="Площадь по проекту общая (расчетная)">{$v['area']['total']}</param>
    <param name="Тип объекта">{$typePrefix}</param>
    <param name="Корпус">{$v['house']['number']}</param>
    <param name="Плановая дата ввода в эксплуатацию (РВЭ)">{$v['built']['quarter']} квартал {$v['built']['year']}</param>
    <categoryId>6</categoryId>
    <pt>{$v['project']['title']}</pt>
   </offer>

HD;
      #> storage rooms
     } else if(
         stripos($v['house']['title'], 'паркинг') !== false
         or stripos($v['house']['title'], 'машиноместа') !== false
         or stripos($v['house']['title'], 'МХМТС') !== false
         or stripos($v['house']['title'], 'мото-места') !== false
     ){
      #< parking spaces
         $typePrefix = 'Парковочное место';
         $model = $typePrefix . " {$v['area']['total']} м2";
         $description = $model;
         #> data

         $offerData = <<<HD
   <offer id="{$v['crm-id']}" available="{$aStatus[0]}">
    <vendor>Инград</vendor>
    <price>{$v['price']['new']}</price>
    <currencyId>RUR</currencyId>
    <typePrefix>{$typePrefix}</typePrefix>
    <model>{$model}</model>
    <description>{$description}</description>
{$pictures}    <param name="Есть отделка?">{$finishing}</param>
    <param name="Застройка">{$v['project']['name']}</param>
    <param name="Площадь по проекту общая (расчетная)">{$v['area']['total']}</param>
    <param name="Тип объекта">{$typePrefix}</param>
    <param name="Корпус">{$v['house']['number']}</param>
    <param name="Плановая дата ввода в эксплуатацию (РВЭ)">{$v['built']['quarter']} квартал {$v['built']['year']}</param>
    <categoryId>5</categoryId>
    <pt>{$v['project']['title']}</pt>
   </offer>

HD;
      #> parking spaces
     } else{
      #< apartments
        if($v['rooms count'] === 0){
            $title = 'студия';
        } else if($v['rooms count'] === 1){
            $title = 'однокомнатная квартира';
        } else if($v['rooms count'] === 2){
            $title = 'двухкомнатная квартира';
        } else if($v['rooms count'] === 3){
            $title = 'трёхкомнатная квартира';
        } else if($v['rooms count'] === 4){
            $title = 'четырёхкомнатная квартира';
        } else if($v['rooms count'] === 5){
            $title = 'пятикомнатная квартира';
        } else{
            $title = $v['rooms count'] . '-комнатная квартира';
        }
        #
        $description = <<<HD
Программа TRADE-IN - живите в старой квартире до получения новой!
Не упустите выгоду, звоните сейчас, чтобы узнать подробности и сделать предварительные расчеты.
Продается {$title} (№{$v['number']}) в новостройке {$v['project']['name']} по адресу {$v['house']['address']}.
Общая площадь квартиры - {$v['area']['total']} кв.м. Тип дома – монолитный.

HD;

         $offerData = <<<HD
   <offer id="{$v['crm-id']}" available="{$aStatus[0]}">
    <vendor>Инград</vendor>
    <price>{$v['price']['new']}</price>
    <oldprise>{$v['price']['old']}</oldprise>
    <currencyId>RUR</currencyId>
    <typePrefix>квартира</typePrefix>
    <model>{$title}, {$v['area']['total']} м2</model>
    <description>{$description}</description>
    <url>{$v['url']}</url>
{$pictures}    <param name="Есть отделка?">{$finishing}</param>
    <param name="Застройка">{$projectName}</param>
    <param name="Площадь по проекту общая (расчетная)">{$v['area']['total']}</param>
    <param name="Количество комнат">{$v['rooms count']}</param>
    <param name="Тип объекта">квартира</param>
    <param name="Корпус">{$v['house']['number']}</param>
{$builtDate}{$aStatus[1]}{$categoryId}    <pt>{$v['project']['title']}</pt>
   </offer>

HD;
    }
     #> apartments

    $aOffers['mindbox_full'] .= $offerData;
    if($v['status']['code'] == 4){
        $aOffers['mindbox_simple'] .= $offerData;
    }
 }
 #> unique data by offer type data+xml

 $categories = implode('', array_merge($GLOBALS['aCategory']['projects'], $GLOBALS['aCategory']['houses']));
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
  <categories>
   <category id="1">Бизнес</category>
   <category id="2">Комфорт</category>
   <category id="3">Загородная недвижимость</category>
   <category id="4">Коммерческая недвижимость</category>
   <category id="5">Машиноместа</category>
   <category id="6">Кладовые</category>
{$categories}  </categories>
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
