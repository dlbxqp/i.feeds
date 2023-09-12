<?php
function getCian($a){
 $content = '';
 foreach((array)$a as $v){
  #< data
   #< plot or cottage
  if($v['type'] == 'жилой дом и придомовой участок'){
   $cottageData = <<<HD
  <Category>cottageSale</Category>
  <TotalArea>{$v['building project']['area']['design']}</TotalArea>
  <FloorsCount>{$v['building project']['floors']}</FloorsCount>
  <RoomsCount>{$v['building project']['rooms count']}</RoomsCount>
  <BedroomsCount>{$v['building project']['bathrooms']}</BedroomsCount>
  <HasGarage>{$v['building project']['has garage']}</HasGarage>
  <HasTerrace>{$v['building project']['has terrace']}</HasTerrace>
  <HasElectricity>true</HasElectricity>
  <HasWater>true</HasWater>

HD;
  } else{
   $cottageData = "  <Category>landSale</Category>\r\n";
  }
   #> plot or cottage

   #< images
  $images = '';
  if(count($v['images']) > 0){
   foreach($v['images'] as $vv){
    $isDefault = (($images == '') ? 'true' : 'false');
    $images .= <<<HD
   <PhotoSchema>
    <FullUrl>{$vv}</FullUrl>
    <IsDefault>{$isDefault}</IsDefault>
   </PhotoSchema>

HD;
   }
  }
  $images = "  <Photos>{$images}  </Photos>\n";
   #> images
  #> data

  $content .= <<<HD
 <object>
  <Land>
   <Area>{$v['area']}</Area>
   <AreaUnitType>sotka</AreaUnitType>
  </Land>
  <BargainTerms>
   <Price>{$v['price']}</Price>
   <Currency>rur</Currency>
  </BargainTerms>
{$images}  <ExternalId>{$v['code']}</ExternalId>
  <Title>{$v['title']}</Title>
  <Description>{$v['description']}</Description>
  <Address>{$v['house']['address']}</Address>
  <SettlementName>{$v['project']['name']}</SettlementName>
  <Coordinates>
   <Lat>{$v['house']['latitude']}</Lat>
   <Lng>{$v['house']['longitude']}</Lng>
  </Coordinates>
  <Phones>
   <PhoneSchema>
    <CountryCode>+7</CountryCode>
    <Number>49{$v['project']['phone numbers']['cian']}</Number>
   </PhoneSchema>
  </Phones>
  <JKSchema>
   <Name>{$v['project']['name']}</Name>
  </JKSchema>
  <HasElectricity>true</HasElectricity>
{$cottageData}  <b>{$v['house']['number']}</b>
 </object>

HD;
 }
 #
 $content = <<<HD
<feed>
 <feed_version>2</feed_version>
{$content}</feed>

HD;

 $fileName = 'cian__suburban.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}