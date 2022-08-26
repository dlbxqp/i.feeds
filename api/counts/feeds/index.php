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
preg_match_all("/__(.*)\./", $_POST['feed'], $aMatches);
$feedType = $aMatches[1][0]; //die('<pre>' . print_r($feedType, true) . '</pre>');
#
$feedContent = file_get_contents($path . $_POST['feed']); //die('$feedContent: ' . $feedContent);
$xml = simplexml_load_string($feedContent) or die('Error: Cannot create object'); //5623
//echo "<pre>\r\n" . print_r($xml, TRUE) . '</pre>';
if(stripos($_POST['feed'], 'avito_') !== false){

 //preg_match_all('|<p>(.*)</p>|i', (string)$feedContent, $aMatches);
 foreach($xml AS $v){
  $p = (string)$v->p;
  $b = (string)$v->b;
  if($p == '' or $b == '') continue;
  $aResult[$p][$b][$feedType] = !isset($aResult[$p][$b][$feedType]) ? 1 : ($aResult[$p][$b][$feedType] + 1);
 }

} elseif(stripos($_POST['feed'], 'cian_') !== false){

 foreach($xml AS $v){
  $p = (string)$v->JKSchema->Name;
  $b = (string)$v->b;
  if($p == '' or $b == '') continue;
  $aResult[$p][$b][$feedType] = !isset($aResult[$p][$b][$feedType]) ? 1 : ($aResult[$p][$b][$feedType] + 1);
 }

} elseif(stripos($_POST['feed'], 'yr_') !== false){

 foreach($xml AS $v){
  $p = isset($v->{'building-name'}) ? (string)$v->{'building-name'} : (string)$v->{'village-name'};
  $b = (string)$v->{'building-section'};
  if($p == '' or $b == '') continue;
  $aResult[$p][$b][$feedType] = !isset($aResult[$p][$b][$feedType]) ? 1 : ($aResult[$p][$b][$feedType] + 1);
 }

}
//die('<pre>' . print_r($aResult, true) . '</pre>');

header('Content-Type: application/json; charset=utf-8');
exit( json_encode($aResult, JSON_UNESCAPED_UNICODE) );