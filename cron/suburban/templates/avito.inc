<?php
function getAvito($a){
 $creationData = date('Y-m-d\TH:i:s'); //YYYY-MM-DDTHH:mm:ss+00:00

 $content = '';
 foreach((array)$a as $v){
  #< data
   #< images
  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $images .= '   <Image url="' . $vv . '" />' . "\n";
   }
  }
  $images = "  <Images>{$images}  </Images>\n";
   #> images

   #< additional plots data
  if($v['plots data']){
   $plotsData = <<<HD
  <Category>Дома, дачи, коттеджи</Category>
  <ObjectType>{$v['plots data']['object type']}</ObjectType>
  <TotalArea>{$v['plots data']['house area']}</TotalArea>
  <FloorsCount>{$v['plots data']['floors']}</FloorsCount>
  <Rooms>{$v['plots data']['rooms']}</Rooms>
  <WallsType>{$v['plots data']['walls type']}</WallsType>
  <Renovation>{$v['plots data']['renovation']}</Renovation>

HD;
  } else{
   $plotsData = <<<HD
  <Category>Земельные участки</Category>
  <ObjectType>Сельхозназначения (СНТ, ДНП)</ObjectType>

HD;
  }
   #> additional plots data
  #> data

  $content .= <<<HD
 <Ad>
  <Id>{$v['code']}</Id>
  <DateBegin>{$creationData}</DateBegin>
  <ContactPhone>+749{$v['project']['phone numbers']['avito']}</ContactPhone>
  <Address>{$v['project']['address']}</Address>
  <Latitude>{$v['project']['latitude']}</Latitude>
  <Longitude>{$v['project']['longitude']}</Longitude>
  <Description><![CDATA[{$v['description']}]]></Description>
  <Category>Земельные участки</Category>
  <OperationType>Продам</OperationType>    
  <Price>{$v['price']}</Price>
  <LandArea>{$v['area']}</LandArea>
  <PropertyRights>Собственник</PropertyRights>
{$images}  <p>{$v['project']['name']}</p>
{$plotsData}  <b>{$v['house']['number']}</b>
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