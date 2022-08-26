<?php
function getAvito($a){
 $content = '';
 foreach((array)$a as $v){
  ($v['floor number'] < 0) && ($v['floor number'] = 'Подвальный');

  $content .= <<<HD
 <Ad>
  <Id>{$v['code']}</Id>
  <Country>Россия</Country>
  <Address>{$v['house']['address']}</Address>
  <BuildingType>Жилой дом</BuildingType>
  <Category>Коммерческая недвижимость</Category>
  <ContactPhone>+749{$v['phone number']['avito']}</ContactPhone>
  <Description>{$v['description']}</Description>
  <Floor>{$v['floor number']}</Floor>
  <Floors>{$v['floors count']}</Floors>
  <ObjectType>Складское помещение</ObjectType>
  <OperationType>Продам</OperationType>
  <Price>{$v['price']}</Price>
  <PropertyRights>Собственник</PropertyRights>
  <Square>{$v['area']}</Square>
  <p>{$v['project']['name']}</p>
  <b>{$v['house']['number']}</b>
 </Ad>

HD;
 }
 #
 $content = <<<HD
<Ads formatVersion="3" target="Avito.ru">
{$content}</Ads>

HD;

 $fileName = 'avito__apartments.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}