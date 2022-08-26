<?php
#< Security
function f($a){ //die('<pre>' . print_r($a, TRUE) . '</pre>');
 foreach($a as $k => $v){
  $a[$k] = //htmlspecialchars(
   stripslashes(
    trim(
     rawurldecode($v)
    )
   );
  //);
 }

 return $a;
}

$_GET = f($_GET);
$_POST = f($_POST);
#> Security