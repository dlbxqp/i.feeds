<?php
function getAvito($a){
 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00

 $content = '';
 foreach((array)$a as $v){
  #< data
   #< plot or cottage

  if($v['type'] == 'жилой дом и придомовой участок'){
   $cottageData = <<<HD
  <Category>Дома, дачи, коттеджи</Category>
  <ObjectType>коттедж</ObjectType>
  <WallsType>Газоблоки</WallsType>
  <Renovation>Требуется</Renovation>
  <TotalArea>{$v['building project']['area']['design']}</TotalArea>
  <FloorsCount>{$v['building project']['floors']}</FloorsCount>
  <Rooms>{$v['building project']['rooms count']}</Rooms>

HD;
  } else{
   $cottageData = <<<HD
  <Category>Земельные участки</Category>
  <ObjectType>Сельхозназначения (СНТ, ДНП)</ObjectType>

HD;
  }
   #> plot or cottage

   #< images
  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $images .= '   <Image url="' . $vv . '" />' . "\n";
   }
  }
  $images = "  <Images>{$images}  </Images>\n";
   #> images
  #> data

  $content .= <<<HD
 <Ad>
  <Id>{$v['code']}</Id>
  <DateBegin>{$creationData}</DateBegin>
  <ContactPhone>+749{$v['project']['phone numbers']['avito']}</ContactPhone>
  <Address>{$v['house']['address']}</Address>
  <Latitude>{$v['house']['latitude']}</Latitude>
  <Longitude>{$v['house']['longitude']}</Longitude>
  <Description><![CDATA[{$v['description']}]]></Description>
  <Category>Земельные участки</Category>
  <OperationType>Продам</OperationType>    
  <Price>{$v['price']}</Price>
  <LandArea>{$v['area']}</LandArea>
  <LandStatus>Индивидуальное жилищное строительство (ИЖС)</LandStatus>
  <PropertyRights>Собственник</PropertyRights>
{$images}  <p>{$v['project']['name']}</p>
{$cottageData}  <b>{$v['house']['number']}</b>
 </Ad>

HD;
 }
 #
 $content = <<<HD
<Ads formatVersion="3" target="Avito.ru">
{$content}</Ads>

HD;

 $fileName = 'avito__suburban.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}