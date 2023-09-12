<?php
function getAvito($a){
 $content = '';
 foreach((array)$a as $v){
  ($v['floor number'] < 0) && ($v['floor number'] = 'Подвальный');

  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $images .= '   <Image url="' . $vv . '" />' . "\n";
   }
   $images = "  <Images>\n{$images}  </Images>\n";
  }

  $coordinates = '';
  if($v['house']['latitude'] > 0 and $v['house']['longitude'] > 0){
   $coordinates = <<<HD
  <Latitude>{$v['house']['latitude']}</Latitude>
  <Longitude>{$v['house']['longitude']}</Longitude>

HD;
  }

  //<Title>{$v['title']}</Title>
  $content .= <<<HD
 <Ad>
  <Id>{$v['code']}</Id>
  <Description>{$v['description']}</Description>
  <Country>Россия</Country>
  <Address>{$v['house']['address']}</Address>
{$coordinates}  <BuildingType>Жилой дом</BuildingType>
  <Category>Коммерческая недвижимость</Category>
  <ContactPhone>+749{$v['phone numbers']['avito']}</ContactPhone>
  <Decoration>Чистовая</Decoration>
  <Floor>{$v['floor number']}</Floor>
  <Floors>{$v['floors count']}</Floors>
  <ObjectType>Помещение свободного назначения</ObjectType>
  <OperationType>Продам</OperationType>
  <ParkingType>В здании</ParkingType>
  <Price>{$v['price']}</Price>
  <PropertyRights>Собственник</PropertyRights>
  <Square>{$v['area']}</Square>
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

 $fileName = 'avito__commercial_realty.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}