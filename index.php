<?php
$start_time = microtime(true);
////////////////////////////////////////////
//  PHP名：Gallery Board Ver：9.1         //
//  製作者：詩晴                           //
//  メール：softs@tenskystar.net           //
//  再配布：無許可(許可必要)               //
//  作成日:09/04/13                        //
//  URL:https://www.tenskystar.net/         //
/////////////////////////////////////////////

require('config.php');//set.phpの場所

//- 以下 PHP知る者以外は触るべからず -//
//Load
require($php['ctrl']);
require($php['files']);
require($php['html']);
require_once($php['thumb']);
if($F['mode'] == "Embed" and preg_match("/^https?:\/\//",$F['url'])){require($php['embed']); new Embed();exit;}
if($F['mode'] == "Rss" and $php['rss']){require($php['rss']);exit;}
if($F['mode'] == "Pickup" and $php['pickup']){require($php['pickup']);exit;}
if($F['mode'] == "auth"){require_once($php['auth']);}
if($F['mode'] == "parent" or $F['mode'] == "child" or $F['mode'] == "remove" or $F['mode'] == "rewrite" or $F['mode'] == "edit"){
	require_once($php['entry']);
}

//Get Host
$date = Ctrl::Get_Date();
$ip = Ctrl::Get_IP();
$caria = Ctrl::Get_Caria($ip['agent'],$ip['host']);

//Check
if($lock['sw'] and !file_exists($lock['file'])){touch($lock['file']);}
if(!file_exists($data_file['data']) or !file_exists($data_file['no'])){Html::Error("エラー","データファイルが存在しません。");}
if($post_set['upload']['thumbnail']['sw'] and !function_exists("imagecreate")){Html::Error("設定エラー","GDが利用できない為、サムネイル機能は使えません。");}
if($post_set['auth']['type'] and !function_exists("imagecreate")){Html::Error("設定エラー","GDのimagecreateが利用できないため、画像認証は使えません。");}

//Access Check
Access::Referer();
Access::Proxy();
Access::Forced();
Access::Tor();

if(!$F['page']){$F['page'] = 1;}
if($F['msg']){$F['msg'] = preg_replace("/\r\n|\r|\n/","<br />",$F['msg']);}//改行を<BR>
if($no_getpost and $_GET and ($F['mode'] == "child" or $F['mode'] == "parent" or $F['mode'] == "remove" or $F['mode'] == "rewrite")){Html::Error("エラー","正しくない通信手段です。");}

//Mode Call
if($F['mode'] == "auth"){
	Auth::Get_Image();
	exit;
}
if($F['mode'] == "parent" or $F['mode'] == "child" or $F['mode'] == "remove" or $F['mode'] == "rewrite"){
	if($F['mode'] == "child"){list($F['mode'],$F['page']) = Entry::Res_Post($F['prevno']);}
	if($F['mode'] == "remove" or $F['mode'] == "rewrite"){$F['mode'] = Entry::Operation($F['mode'],$F['prevno'],$F['pass']);}
	if($F['mode'] == "parent" or $F['mode'] == "rewrite"){$F['mode'] = Entry::Parent_Post($F['mode']);}
}
if($F['mode'] == "enter" or $F['mode'] == "view"){require_once($php['count']);}
if($F['mode'] != "parent" and $F['mode'] != "child" and $F['mode'] != "remove"){Html::Index($F['page'],$caria);}


$end_time = microtime(true);
if($sys_info){
	$fms = memory_get_peak_usage();
	$unit_text = "B";
	$unit_list = array('KB','MB','GB','TB');
	foreach($unit_list as $unit){
		if($fms >= 1000){$fms = $fms / 1024; $unit_text = $unit;}
		else{continue;}
	}
	
	print "Memory Max Use:".round($fms,2) ." ".$unit_text;
	print "<BR>Sec:" .($end_time-$start_time)."<BR>";
}
//19/06/24 Ver9.1 Tagキーワードの追加。動画の埋め込み再生（対応可能サイトのみ）機能の追加。
//19/02/22 Ver9.0 編集モードの調整。 レスの削除の権限の設定を追加。
// 19/02/17 Ver8.9 編集時、ファイルの変更しない場合に、エラーが発生する可能性が有ったのを修正。　エラー時のファイルの削除処理を調整。
exit;
?>