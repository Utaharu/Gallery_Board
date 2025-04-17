<?php
$start_time = microtime(true);
//////////////////////////////////////////
//  - PHP名 -     						//
$lib[] = "Gallery Board Ver:9.7";		//
//  製作者：詩晴                           //
//  メール：softs@tenskystar.net           //
//  再配布：無許可(許可必要)					//
//  作成日:09/04/13               		//
//  URL:https://www.tenskystar.net/     //
//////////////////////////////////////////

// - GalleryBoard mode振り分け - //
require('config.php');//set.phpの場所

//- 以下 PHP知る者以外は触るべからず -//

// - Script Load - //
//-- Default
require($php['lib']['access']);
require($php['lib']['common']);
require($php['lib']['files']);
require($php['lib']['html']);
require_once($php['lib']['thumb']);
require($php['lib']['maintain']);

//-- Mode Load
if($F['mode'] == "Embed" and preg_match("/^https?:\/\//",$F['url'])){require($php['lib']['embed']); new Embed();exit;}
if($F['mode'] == "Rss" and $php['rss']){require($php['rss']);exit;}
if($F['mode'] == "Pickup" and $php['pickup']){require($php['pickup']);exit;}
if($F['mode'] == "auth"){require_once($php['lib']['auth']);}
if($F['mode'] == "parent" or $F['mode'] == "child" or $F['mode'] == "remove" or $F['mode'] == "rewrite" or $F['mode'] == "edit"){
	require_once($php['lib']['entry']);
}

// - System Check - //
if($lock['sw'] and !file_exists($lock['file'])){touch($lock['file']);}
if(!file_exists($data_file['data']) or !file_exists($data_file['no'])){Error_Page::Main("エラー","データファイルが存在しません。");}
if($post_set['upload']['thumbnail']['sw'] and !function_exists("imagecreate")){Error_Page::Main("設定エラー","GDが利用できない為、サムネイル機能は使えません。");}
if($post_set['auth']['type'] and !function_exists("imagecreate")){Error_Page::Main("設定エラー","GDのimagecreateが利用できないため、画像認証は使えません。");}
if($no_getpost and $_GET and ($F['mode'] == "child" or $F['mode'] == "parent" or $F['mode'] == "remove" or $F['mode'] == "rewrite")){Error_Page::Main("エラー","正しくない通信手段です。");}



$date = Common::Get_Date();


// -  Access - //

//-- Get Ip , Host
$ip = Access::Get_IP();

//-- Get Caria
$caria = Access::Get_Caria($ip['host'],"");
if(!$home['mb']){$caria = "pc";}

//-- Access Check
Access::Country_Check($ip['addr']);
Access::Referer_Check();
Access::Proxy_Check($ip['addr'],$ip['host']);
Access::Forced_Check($ip['addr'],$ip['host']);
Access::Tor_Check($ip['addr']);

// - Variable　Adjustment -//
if(!$F['page']){$F['page'] = 1;}
if($F['msg']){$F['msg'] = preg_replace("/\r\n|\r|\n/","<br />",$F['msg']);}//改行を<BR>

/* -- Mode Call -- Auth */
if($F['mode'] == "auth"){
	Auth::Get_Image();
	exit;
}

/* -- Main Mode - Call & Mode _ Adjustment -- */
if($F['mode'] == "parent" or $F['mode'] == "child" or $F['mode'] == "remove" or $F['mode'] == "rewrite"){
	// Entry - Res Post
	if($F['mode'] == "child"){
		list($F['mode'],$F['page']) = Entry::Res_Post($F,$F['prevno']);
	}
	
	// Entry - remove/rewrite _ Process Mode 
	if($F['mode'] == "remove" or $F['mode'] == "rewrite"){
		$F['mode'] = Entry::Operation($F['mode'],$F['prevno'],$F['pass']);
	}

	// Entry - Parent Post
	if($F['mode'] == "parent" or $F['mode'] == "rewrite"){
		$F['mode'] = Entry::Parent_Post($F,$F['mode']);
	}
}

/* -- Counter Require -- */
if($F['mode'] == "enter" or $F['mode'] == "view"){require_once($php['lib']['counter']);}

//if($F['mode'] == "version"){version_info();exit;}

/* -- Index Html Print -- */
if($F['mode'] != "parent" and $F['mode'] != "child" and $F['mode'] != "remove"){Html::Index($F['page'],$caria);}


/*
function version_info(){
	global $php;
	foreach($php['lib'] as $key=>$file){
		if($key != "css" and $key != "javascript"){require_once($file);}
	}
	
	if(is_array($lib)){
		foreach($lib as $key=>$line){
			if($key != "css" and $key != "javascript"){print $line."<BR>";}
		}
	}

}
*/

// System Proc Infomation Print　(config.php - $sys_info = 1; の場合、参考程度に出力してみる)
$end_time = microtime(true);	if($sys_info){
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


exit;
//Gallery Board - www.tenskystar.net
?>