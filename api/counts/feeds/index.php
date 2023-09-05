<?php
$path = '../../../';
require $path . 'assets/includes/ini_set.inc';
require $path . 'assets/includes/security.inc';

$_POST = json_decode( file_get_contents('php://input'), true );
//$_POST = $_REQUEST; $_POST['feed'] = 'avito__commercial_realty.xml';
//*/
if(!isset($_POST['feed']) or !is_file($path . $_POST['feed'])){
 http_response_code(400); die('$_POST[feed]: ' . $_POST['feed']);
}
//*/

$aResult = [];
#
$a_feed = explode('__', $_POST['feed']);
if(count($a_feed) == 2){
 $a__ = explode('.', $a_feed[1]);
 $a_feed[1] = $a__[0];
} //die('<pre>' . print_r($a_feed, true) . '</pre>');
#
$feedContent = file_get_contents($path . $_POST['feed']); //die('$feedContent: ' . $feedContent);
$xml = simplexml_load_string($feedContent) or die('Error: Cannot create object'); //5623
//echo "<pre>\r\n" . print_r($xml, TRUE) . '</pre>';

if($a_feed[0] == 'avito'){

 //preg_match_all('|<p>(.*)</p>|i', (string)$feedContent, $aMatches);
 foreach($xml as $v){
  $p = (string)$v->p;
  $b = (string)$v->b;
  if($p == '' or $b == '') continue;
  $aResult[$p][$b][ $a_feed[1] ] = !isset($aResult[$p][$b][ $a_feed[1] ]) ? 1 : ($aResult[$p][$b][ $a_feed[1] ] + 1);
 }

} elseif($a_feed[0] == 'cian'){

 foreach($xml as $v){
  $p = (string)$v->JKSchema->Name;
  $b = (string)$v->b;
  if($p == '' or $b == '') continue;
  $aResult[$p][$b][ $a_feed[1] ] = !isset($aResult[$p][$b][ $a_feed[1] ]) ? 1 : ($aResult[$p][$b][ $a_feed[1] ] + 1);
 }

} elseif($a_feed[0] == 'yr'){

 foreach($xml as $v){
  $p = isset($v->{'building-name'}) ? (string)$v->{'building-name'} : (string)$v->{'village-name'};
  $b = (string)$v->{'building-section'};
  if($p == '' or $b == '') continue;
  $aResult[$p][$b][ $a_feed[1] ] = !isset($aResult[$p][$b][ $a_feed[1] ]) ? 1 : ($aResult[$p][$b][ $a_feed[1] ] + 1);
 }

} elseif($a_feed[0] == 'ym'){ //die('$xml = ' . print_r($xml, true));

 foreach($xml->shop->offers->offer as $v){
  $p = (string)$v->p;
  $b = (string)$v->b; //die($p . ' / ' . $b);
  if($p == '' or $b == '') continue;
  $aResult[$p][$b][ $a_feed[1] ] = !isset($aResult[$p][$b][ $a_feed[1] ]) ? 1 : ($aResult[$p][$b][ $a_feed[1] ] + 1);
 }

} elseif($a_feed[0] == 'dc'){ //die('$xml = ' . print_r($xml, true));

 foreach($xml->complex as $complex){
  $p = (string)$complex->name; //die('$p = ' . $p);
  foreach($complex->buildings->building as $building){
   $b = (string)$building->b; //die('$b = ' . $b);
/*
   foreach((array)$building->flats as $flat){
    $aResult[ $p ][ $b ][ $a_feed[1] ] = !isset($aResult[ $p ][ $b ][ $a_feed[1] ]) ? 1 : ($aResult[ $p ][ $b ][ $a_feed[1] ] + 1);
   }
//*/
   $aResult[ $p ][ $b ][ $a_feed[1] ] = count($building->flats);
  }
 }

}
//die('<pre>' . print_r($aResult, true) . '</pre>');

header('Content-Type: application/json; charset=utf-8');
exit( json_encode($aResult, JSON_UNESCAPED_UNICODE) );