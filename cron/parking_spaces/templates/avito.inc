<?php
function getAvito($a){
 $content = '';

 foreach($a as $v){
  #< images
  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $images .= '   <Image url="' . $vv . '" />' . "\n";
   }
  }
  $images = "  <Images>{$images}  </Images>\n";
  #> images

  ($v['floor number'] < 0) && ($v['floor number'] = 'Подвальный');
  $objectSubtype = (($v['article type'] == 'наземный паркинг') ? 'Многоуровневый паркинг' : 'Подземный паркинг');
  $secured = ($v['security'] > 0 ? 'Да' : 'Нет');

  $content .= <<<HD
 <Ad>
  <Id>{$v['code']}</Id>
  <Country>Россия</Country>
  <Address>{$v['house']['address']}</Address>
  <Category>Гаражи и машиноместа</Category>
  <ContactPhone>+749{$v['phone number']['avito']}</ContactPhone>
  <Decoration>Чистовая</Decoration>
  <Description>{$v['description']}</Description>
  <Floor>{$v['floor number']}</Floor>
  <Floors>{$v['floors count']}</Floors>
  <ObjectType>Машиноместо</ObjectType>
  <ObjectSubtype>{$objectSubtype}</ObjectSubtype>
  <OperationType>Продам</OperationType>
  <ParkingType>Парковочное место</ParkingType>
  <Price>{$v['price']}</Price>
  <PropertyRights>Собственник</PropertyRights>  
  <Secured>{$secured}</Secured>
{$images}  <p>{$v['project']['name']}</p><b>{$v['house']['number']}</b>
 </Ad>

HD;
 }
 #
 $content = <<<HD
<Ads formatVersion="3" target="Avito.ru">
{$content}</Ads>

HD;

 $fileName = 'avito__parking_spaces.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}