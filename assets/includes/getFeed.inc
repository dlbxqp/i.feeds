<?php
function getFeed($fileName){
 if(stripos($_POST['feed'], 'avito_') !== false){
  $content = getAvito($GLOBALS['a']);
 } elseif(stripos($_POST['feed'], 'cian_') !== false){
  $content = getCian($GLOBALS['a']);
 } elseif(stripos($_POST['feed'], 'yr_') !== false){
  $content = getYR($GLOBALS['a']);
 } else{
  return false;
 }

 file_put_contents("../../{$fileName}", $content);

 return <<<HD
<p>
 <a href="/f2/{$fileName}" target="_blank">//wd.ingrad.ru/f2/{$fileName}</a>
</p>

HD;
}