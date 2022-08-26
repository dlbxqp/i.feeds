<?php
function getCian($a){
 $content = '';
 foreach((array)$a as $v){
  #< data
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

   #< additional plots data
  if($v['plots data']){
   $plotsData = <<<HD
  <Category>cottageSale</Category>
  <TotalArea>{$v['plots data']['house area']}</TotalArea>
  <FloorsCount>{$v['plots data']['floors']}</FloorsCount>

HD;
  } else{
   $plotsData = <<<HD
  <Category>landSale</Category>

HD;
  }
   #> additional plots data
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
  <Address>{$v['project']['address']}</Address>
  <SettlementName>{$v['project']['name']}</SettlementName>
  <Coordinates>
   <Lat>{$v['project']['latitude']}</Lat>
   <Lng>{$v['project']['longitude']}</Lng>
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
{$plotsData}  <b>{$v['house']['number']}</b>
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