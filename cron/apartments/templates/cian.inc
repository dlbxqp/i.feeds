<?php
function getCian($a){
 $a_ = $GLOBALS['aAdditionalData']; //die('<pre>' . print_r($a_, true) . '</pre>');

 #< action
 $promotionalCodes = file_get_contents(__DIR__ . '/../additional_data/promotional_apartments_for_cyan.csv');
 $a_promotionalCodes = explode("\r\n", $promotionalCodes);
 $a_promotionalCodes_ = [];
 foreach($a_promotionalCodes as $promotionalCode){
  $a_promotionalCode = explode("\t", $promotionalCode);
  $a_promotionalCodes_[ translit($a_promotionalCode[0]) ] = $a_promotionalCode[1];
 }
 //die('<pre>' . print_r($a_promotionalCodes_, true) .  '</pre>');
 #> action

 $content = $content_unique = '';
 foreach((array)$a as $v){
  $projectId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['cian'];
  $houseId = (int)$a_['projects'][$v['project']['crm-id']]['buildings'][$v['house']['number']]['building ids']['cian'];

  #< filter
  if($projectId == 0 or $houseId == 0) continue;
  #> filter

  #< data
  $externalId = /*($v['api-id'] > 0) ? $v['api-id'] :*/ $v['code']['translite'];
  #
  isset($v['url']) && ($url = "   <url>{$v['url']}</url>\r\n");

  $decoration = 'fine';
  if(
   $v['finishing'] == ''
   or $v['finishing'] == 'без отделки'
  ){
   $decoration = 'without';
  } else if($v['finishing'] == 'white box'){
   $decoration = 'preFine';
  }

  #< layout
  $layout = <<<HD
  <LayoutPhoto>
   <FullUrl>{$v['images'][0]}</FullUrl>
   <IsDefault>true</IsDefault>
  </LayoutPhoto>

HD;
  unset($v['images'][0]);
  #> layout

  #< photos
  $aImages = array_merge($v['images'], $v['project']['images']);
  $photos = '';
  foreach($aImages as $image){
   $isDefault = !isset($isDefault) ? true : false;
   $photos .= <<<HD
  <PhotoSchema>
   <FullUrl>{$image}</FullUrl>
   <IsDefault>{$isDefault}</IsDefault>
  </PhotoSchema>
  
HD;
  }
  $photos = <<<HD
  <Photos>
{$photos}  </Photos>
  
HD;
  #> photos

  $contractType = $v['переуступка'] ? 'leaseAssignment' : 'sale';

  $actionId = '';
  if( isset($a_promotionalCodes_[ $v['code']['translite'] ]) ){
   $actionId = "   <ActionId>{$a_promotionalCodes_[ $v['code']['translite'] ]}</ActionId>\r\n";
   unset($a_promotionalCodes_[ $v['code']['translite'] ]);
  }
  //die('> ' . $v['code']['translite']);

  $flatRoomsCount = $v['rooms count'];
  if($flatRoomsCount > 5){
   $flatRoomsCount = 6;
  }
  #> data

  $row = <<<HD
  <object>
   <ExternalId>{$externalId}</ExternalId>
   <Description>{$v['description']}</Description>
   <Address>{$v['house']['address']}</Address>
   <Phones>
    <PhoneSchema>
     <CountryCode>+7</CountryCode>
     <Number>49{$v['project']['phone numbers']['cian']}</Number>
    </PhoneSchema>
   </Phones>
   <Category>newBuildingFlatSale</Category>
   <FlatRoomsCount>{$flatRoomsCount}</FlatRoomsCount>
   <TotalArea>{$v['area']['total']}</TotalArea>
   <FloorNumber>{$v['floor number']}</FloorNumber>
   <JKSchema>
    <Id>{$projectId}</Id>
    <Name>{$v['project']['name']}</Name>
    <House>
     <Id>{$houseId}</Id>
     <Name>{$v['house']['number']}</Name>
     <Flat>
      <FlatNumber>{$v['number']}</FlatNumber>
      <FlatsCountOnFloor>{$v['count on floor']}</FlatsCountOnFloor>
      <FlatNumberOnFloor>{$v['number on floor']}</FlatNumberOnFloor>
      <SectionNumber>{$v['section']['number']}</SectionNumber>
     </Flat>
    </House>
   </JKSchema>
   <Decoration>{$decoration}</Decoration>
   <LivingArea>{$v['area']['living']}</LivingArea>
   <KitchenArea>{$v['area']['kitchen']}</KitchenArea>
   <Building>
    <FloorsCount>{$v['floor number']}</FloorsCount>
    <MaterialType>monolith</MaterialType>
   </Building>
   <CplModeration>
    <PersonType>legal</PersonType>
    <Inn>7702336269</Inn>
   </CplModeration>
   <BargainTerms>
    <Price>{$v['price']}</Price>
    <SaleType>fz214</SaleType>
    <ContractType>{$contractType}</ContractType>
   </BargainTerms>
{$layout}{$photos}{$url}{$actionId}   <b>{$v['house']['number']}</b>
  </object>

HD;

  $content .= $row;
  $v['unique'] && $content_unique .= $row;
 }
 #
 $content = <<<HD
<feed>
 <feed_version>2</feed_version>
{$content}</feed>

HD;
 #
 $content_unique = <<<HD
<feed>
 <feed_version>2</feed_version>
{$content_unique}</feed>

HD;

 #< main
 $result = '';
 $fileName = 'cian__apartments.xml';
 if( file_put_contents(__DIR__ . "/../../../{$fileName}", $content) ){
  $result = <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
 } else{
  $result .= "<p>Не создан //wd.ingrad.ru/f2/{$fileName}</p>\r\n";
 }
 #> main

 #< m2
 $fileName_m2 = 'cian__apartments__m2.xml';
 if( file_put_contents(__DIR__ . "/../../../{$fileName_m2}", $content) ){
  $result .= <<<HD
<p>
 <a href="/f2/{$fileName_m2}" target="_blank">//wd.ingrad.ru/f2/{$fileName_m2}</a>
</p>

HD;
 } else{
  $result .= "<p>Не создан //wd.ingrad.ru/f2/{$fileName_m2}</p>\r\n";
 }
 #> m2

 #< real estate agencies (для Дмитрия Ломакина)
 $fileName_rea = 'cian__apartments__rea.xml';
 if( file_put_contents(__DIR__ . "/../../../{$fileName_rea}", $content) ){
  $result .= <<<HD
<p>
 <a href="/f2/{$fileName_rea}" target="_blank">//wd.ingrad.ru/f2/{$fileName_rea}</a>
</p>

HD;
 } else{
  $result .= "<p>Не создан //wd.ingrad.ru/f2/{$fileName_rea}</p>\r\n";
 }
 #> real estate agencies

 #< unique (для Дмитрия Ломакина)
 $fileName_unique = 'cian__apartments__unique.xml';
 if( file_put_contents(__DIR__ . "/../../../{$fileName_unique}", $content_unique) ){
  $result .= <<<HD
<p>
 <a href="/f2/{$fileName_unique}" target="_blank">//wd.ingrad.ru/f2/{$fileName_unique}</a>
</p>

HD;
 } else{
  $result .= "<p>Не создан //wd.ingrad.ru/f2/{$fileName_unique}</p>\r\n";
 }
 #> unique

 //$result .= '<pre>' . print_r($a_promotionalCodes_, true) . '</pre>';

 return $result;
}