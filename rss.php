<?php
$lib[] = "Gallery Board - RSS　View v1.0";
/*
- 更新ログ -
 v1.0

- RSS出力 -

*/
require_once("config.php");//configの場所

//- 以下 PHP知る者以外は触るべからず -//
//Load
if($F['mode'] != "Rss"){
	require_once($php['lib']['files']);
	require_once($php['lib']['html']);
	require_once($php['lib']['common']);
}

if($post_set['upload']['thumbnail']['sw']){require_once($php['lib']['thumb']);}

//Main Call
require_once($php['lib']['rss']);

$rss = New Rss;
$rss->Index();

exit;
//Gallery Board - www.tenskystar.net
?>