<?php
function getDC($a){
 $a_ = $GLOBALS['aAdditionalData'];

 $aDC = [];
 foreach((array)$a as $v){ //die('<pre>' . print_r($v, true) . '</pre>');
  #< filter
  if(
   $a_['projects'][ $v['project']['crm-id'] ]['developer ids']['dc'] == 0
   or $a_['projects'][ $v['project']['crm-id'] ]['project ids']['dc'] == 0
   or $a_['projects'][ $v['project']['crm-id'] ]['buildings'][ $v['house']['number'] ]['building ids']['dc'] == 0
  ){
   continue;
  }
  #> filter

  #< переборка массива под формат DC
  !isset($aDC[ $v['project']['crm-id'] ]) && $aDC[ $v['project']['crm-id'] ] = [];
  foreach($v['project'] as $kk => $vv){
   $aDC[ $v['project']['crm-id'] ][ $kk ] = $vv;
  }

  !isset($aDC[ $v['project']['crm-id'] ]['buildings'][ $v['house']['crm-id'] ]) && $aDC[ $v['project']['crm-id'] ]['buildings'][ $v['house']['crm-id'] ] = [];
  foreach($v['house'] as $kk => $vv){
   $aDC[ $v['project']['crm-id'] ]['buildings'][ $v['house']['crm-id'] ][ $kk ] = $vv;
  }

  !isset($aDC[ $v['project']['crm-id'] ]['buildings'][ $v['house']['crm-id'] ]['sections'][ $v['section']['crm-id'] ]) && $aDC[ $v['project']['crm-id'] ]['buildings'][ $v['house']['crm-id'] ]['sections'][ $v['section']['crm-id'] ] = [];
  foreach($v['house'] as $kk => $vv){
   $aDC[ $v['project']['crm-id'] ]['buildings'][ $v['house']['crm-id'] ]['sections'][ $v['section']['crm-id'] ][ $kk ] = $vv;
  } //die('<pre>' . print_r($aDC, true) . '</pre>');

  foreach($v as $kk => $vv){
   if( in_array($kk, ['project', 'house', 'section']) ) continue;

   $aDC[ $v['project']['crm-id'] ]['buildings'][ $v['house']['crm-id'] ]['sections'][ $v['section']['crm-id'] ]['apartments'][ $v['api-id'] ][ $kk ] = $vv;
  }
 } //die('<pre>' . print_r($aDC, true) . '</pre>');
 #> переборка массива под формат DC


 foreach($aDC as $project){
  $developerId = (int)$a_['projects'][ $project['crm-id'] ]['developer ids']['dc'];
  $projectId = (int)$a_['projects'][ $project['crm-id'] ]['project ids']['dc'];

  $buildings = '';
  foreach($project['buildings'] as $building){
   $houseId = (int)$a_['projects'][ $project['crm-id'] ]['buildings'][ $building['number'] ]['building ids']['dc'];

   $sections = '';
   foreach($building['sections'] as $section){
    $apartments = '';
    foreach($section['apartments'] as $apartment){ //die('<pre>' . print_r($apartment, true) . '</pre>');
     #< data
     foreach($apartment['images'] as $image){
      !isset($plan) && $plan = $image;
     }

     if( in_array($apartment['finishing'], ['бизнес', 'премиум']) ){
      $renovation = 'дизайнерский ремонт';
     } /*else if( in_array($apartment['finishing'], ['white box']) ){
      $renovation = 'косметический ремонт';
     }*/ else if( in_array($apartment['finishing'], ['без отделки']) ){
      $renovation = 'нет';
     } else{
      $renovation = 'да';
     }
     #> data

     $apartments .= <<<HD
     <flat>
      <flat_id>{$apartment['api-id']}</flat_id>
      <apartment>{$apartment['title']}</apartment>
      <floor>{$apartment['floor number']}</floor>
      <room>{$apartment['rooms count']}</room>
      <plan>{$plan}</plan>
      <renovation>{$renovation}</renovation>
      <price>{$apartment['price']}</price>
      <area>{$apartment['area']['total']}</area>
      <kitchen_area>{$apartment['area']['kitchen']}</kitchen_area>
      <living_area>{$apartment['area']['living']}</living_area>
      <window_view>во двор и на улицу</window_view>
      <bathroom>раздельный</bathroom>
      <balcony>лоджия</balcony>
     </flat>

HD;
    }
    $sections .= <<<HD
    <flats>
{$apartments}    </flats>

HD;
   }
   #< data
   $buildingState = 'ready';
   if(
    $building['built']['year'] < date('Y')
    or (
     $building['built']['year'] == date('Y')
     and $building['built']['quarter'] <= ceil(date('m') / 3)
    )
   ){
    $buildingState = 'hand_over';
   }
   #> data

   $buildings .= <<<HD
   <building>
    <id>{$houseId}</id>
    <fz_214>true</fz_214>
    <name>Корпус {$building['number']}</name>
    <floors>{$building['floors count']}</floors>
    <building_state>{$buildingState}</building_state>
    <built_year>{$building['built']['year']}</built_year>
    <ready_quarter>{$building['built']['quarter']}</ready_quarter>
    <building_type>монолитный</building_type>
{$sections}    <b>{$building['number']}</b>   
   </building>

HD;
  }
  #< data
  $images = '';
  foreach($project['images'] as $image){
   $images .= "   <image>{$image}</image>\r\n";
  }
  #> data

  $projects .= <<<HD
 <complex>
  <id>{$projectId}</id>
  <name>{$project['name']}</name>
  <latitude>{$building['latitude']}</latitude>
  <longitude>{$building['longitude']}</longitude>
  <address>{$building['address']}</address>
  <images>
{$images}  </images>
  <description_main>
   <title>{$apartment['title']}</title>
   <text>{$apartment['description']}</text>
  </description_main>
  <buildings>
{$buildings}  </buildings>
  <developer>
   <id>{$developerId}</id>
   <logo>https://wd.ingrad.ru/_images/logotype/ingrad.jpg</logo>
   <name>ГК Инград</name>
   <phone>+74955000004</phone>
   <site>https://www.ingrad.ru/</site>
  </developer>
  <sales_info>
   <sales_phone>+74955000004</sales_phone>
   <address>ул. Краснопролетарская, д.2, к.4</address>
   <sales_latitude>55.773739</sales_latitude>
   <sales_longitude>37.609048</sales_longitude>
   <timezone>+3</timezone>
   <work_days>
    <work_day>
     <day>пн</day>
     <open_at>09:00</open_at>
     <close_at>21:00</close_at>
    </work_day>
    <work_day>
     <day>вт</day>
     <open_at>09:00</open_at>
     <close_at>21:00</close_at>
    </work_day>
    <work_day>
     <day>ср</day>
     <open_at>09:00</open_at>
     <close_at>21:00</close_at>
    </work_day>
    <work_day>
     <day>чт</day>
     <open_at>09:00</open_at>
     <close_at>21:00</close_at>
    </work_day>
    <work_day>
     <day>пт</day>
     <open_at>09:00</open_at>
     <close_at>21:00</close_at>
    </work_day>
    <work_day>
     <day>сб</day>
     <open_at>09:00</open_at>
     <close_at>21:00</close_at>
    </work_day>
    <work_day>
     <day>вс</day>
     <open_at>09:00</open_at>
     <close_at>21:00</close_at>
    </work_day>
   </work_days>
  </sales_info>
 </complex>

HD;
 }
 $content = <<<HD
<complexes>
{$projects}  </complexes>
HD;

 $fileName = 'dc__apartments.xml';
 file_put_contents(__DIR__ . "/../../../{$fileName}", $content);
 #
 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</p>
</a>

HD;
}