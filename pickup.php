<?php
$lib[] = "Gallery Board - PickUp View v1.0";
/*
- 更新ログ -
 v1.0 17/01/28
  
- ピックアップ出力 -

*/
require_once("config.php");//configの場所

//- 以下 PHP知る者以外は触るべからず -//
//Load
if($F['mode'] != "Pickup"){
	require_once($php['lib']['files']);
	require_once($php['lib']['html']);
	require_once($php['lib']['common']);
}

if($post_set['upload']['thumbnail']['sw']){require_once($php['lib']['thumb']);}

//Set ctrl
$print_set['list']['img_h'] = $pickup_set['img_h'];
$print_set['list']['img_w'] = $pickup_set['img_w'];

//Main call
require_once($php['lib']['pickup']);

$pickup = new Pickup();
$pickup->View();

exit;

//Gallery Board - www.tenskystar.net
?>