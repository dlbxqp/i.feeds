<?php
function getCian($a){
 $a_ = $GLOBALS['aAdditionalData']; //die('<pre>' . print_r($a_, true) . '</pre>');

 $content = '';
 foreach((array)$a as $v){
  $projectId = (int)$a_['projects'][$v['project']['crm-id']]['project ids']['cian'];
  $houseId = (int)$a_['projects'][$v['project']['crm-id']]['buildings'][$v['house']['number']]['building ids']['cian'];
  if($projectId == 0 or $houseId == 0){ continue; }

  $content .= <<<HD
  <object>
   <ExternalId>{$v['code']}</ExternalId>
   <Description>{$v['description']}</Description>
   <Address>{$v['house']['address']}</Address>
   <Phones>
    <PhoneSchema>
     <CountryCode>+7</CountryCode>
     <Number>49{$v['phone number']['cian']}</Number>
    </PhoneSchema>
   </Phones>
   <JKSchema>
    <Id>{$projectId}</Id>
    <Name>{$v['project']['name']}</Name>
    <House>
     <Id>{$houseId}</Id>
    </House>
   </JKSchema>
   <BargainTerms>
    <Price>{$v['price']}</Price>
    <VatType>included</VatType>
   </BargainTerms>
   <Category>freeAppointmentObjectSale</Category>
   <TotalArea>{$v['area']}</TotalArea>
   <FloorNumber>{$v['floor number']}</FloorNumber>
   <Specialty>
    <Types>
     <String>warehouse</String>
    </Types>
   </Specialty>
   <Building>
    <FloorsCount>{$v['floors count']}</FloorsCount>
   </Building>
   <b>{$v['house']['number']}</b>
  </object>

HD;
 }
 #
 $content = <<<HD
<feed>
 <feed_version>2</feed_version>
{$content}</feed>

HD;

 $fileName = 'cian__apartments.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}