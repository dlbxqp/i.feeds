<?php
function getAvito($a){
 $a_ = $GLOBALS['aAdditionalData'];

 $content = '';
 foreach((array)$a as $v){
  $projectId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['avito'];
  $houseId = (int)$a_['projects'][ $v['project']['crm-id'] ]['buildings'][ $v['house']['number'] ]['building ids']['avito'];
  #< filter
  if($projectId == 0 or $houseId == 0){ continue; }
  #> filter

  #< data
  $id = ($v['api-id'] > 0) ? $v['api-id'] : $v['code']['translite'];

  $newDevelopmentId = ($houseId == 0) ? $projectId : $houseId;

  if( in_array($v['finishing'], ['white box']) ){
   $decoration = 'Предчистовая';
  } else if( in_array($v['finishing'], ['без отделки', '']) ){
   $decoration = 'Без отделки';
  } else{
   $decoration = 'Чистовая';
  }

  $aImages = array_merge($v['images'], $v['project']['images']);
  $images = '  <ImageUrls>' . implode('|', $aImages) . "</ImageUrls>\r\n";
  #> data

  $content .= <<<HD
 <Ad>
  <Id>{$id}</Id>
  <Description>{$v['description']}</Description>
  <Category>Квартиры</Category>
  <Price>{$v['price']}</Price>
  <OperationType>Продам</OperationType>
  <MarketType>Новостройка</MarketType>
  <HouseType>Монолитный</HouseType>
  <Floor>{$v['floor number']}</Floor>
  <Floors>{$v['house']['floors count']}</Floors>
  <Rooms>{$v['rooms count']}</Rooms>
  <Square>{$v['area']['total']}</Square>
  <KitchenSpace>{$v['area']['kitchen']}</KitchenSpace>
  <Status>Квартира</Status>
  <RoomType>
   <Option>Смежные</Option>
   <Option>Изолированные</Option>
  </RoomType>
  <NewDevelopmentId>{$newDevelopmentId}</NewDevelopmentId>
  <PropertyRights>Застройщик</PropertyRights>
  <Decoration>{$decoration}</Decoration>
  <ContactPhone>+749{$v['project']['phone numbers']['avito']}</ContactPhone>
{$images}  <p>{$v['project']['name']}</p>
  <b>{$v['house']['number']}</b>
 </Ad>

HD;
 }
 #
 $content = <<<HD
<Ads formatVersion="3" target="Avito.ru">
{$content}</Ads>

HD;

 #< main
 $fileName = 'avito__apartments.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 $result = <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
 #> main

 #< real estate agencies (для Дмитрия Ломакина)
 $fileName_rea = 'avito__apartments__rea.xml';
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