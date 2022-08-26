<?php
require '../../../assets/includes/ini_set.inc';
require '../../../assets/includes/security.inc';

$aCounts = [];
$aCompleted = json_decode( file_get_contents('../../../cron/_data_from_crm/completed.json'), true);
foreach($aCompleted as $v){
 foreach($v['Sections']['Section'] as $vv){
  $c = count($vv['Apartments']);

  if($c == 0) continue;

  if(stripos($v['Name'], 'нежилые') !== false){

   $aCounts[ $v['BuildingGroupName'] ][ $v['AddressNumber'] ]['commercial realty'] += $c;

  } elseif(
   stripos($v['Name'], 'паркинг') !== false or
   stripos($v['Name'], 'машиноместа') !== false or
   stripos($v['Name'], 'МХМТС') !== false or
   stripos($v['Name'], 'мото-места') !== false
  ){

   $aCounts[ $v['BuildingGroupName'] ][ $v['AddressNumber'] ]['parking spaces'] += $c;

  } elseif(stripos($v['Name'], 'кладовые') !== false){

   $aCounts[ $v['BuildingGroupName'] ][ $v['AddressNumber'] ]['storage rooms'] += $c;

  } elseif(
   stripos($v['BuildingGroupName'], 'Экодолье Шолохово') !== false or
   stripos($v['BuildingGroupName'], 'Солнечный Берег') !== false or
   stripos($v['BuildingGroupName'], 'Мартемьяново') !== false
  ){

   $aCounts[ $v['BuildingGroupName'] ][ (double)$v['AddressNumber'] ]['suburban'] += $c;

  } else{

   $aCounts[ $v['BuildingGroupName'] ][ $v['AddressNumber'] ]['apartments'] += $c;

  }

  unset($c);
 }
}
//die('<pre>' . print_r($aCounts, true) . '</pre>');

ksort($aCounts);
header('Content-Type: application/json; charset=utf-8');
exit( json_encode($aCounts, JSON_UNESCAPED_UNICODE) );