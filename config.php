<?php

$code_set['system'] = "UTF-8";//文字コード
$code_set['list'] = array("UTF-8","EUC-JP","JIS","Shift_JIS");
setlocale(LC_ALL, 'ja_JP');
mb_internal_encoding($code_set['system']);
mb_http_output($code_set['system']);
ob_start('mb_output_handler');
mb_regex_encoding($code_set['system']);
date_default_timezone_set("Asia/Tokyo");
////////////////////////////////////////////
//  PHP名：Gallery Board Ver：9.1         //
$sys_info = 1;
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors',1);
//  製作者：詩晴                           //
//  メール：softs@tenskystar.net           //
//  再配布：無許可(許可必要)               //
//  作成日:09/04/13                        //
//  URL:https://www.tenskystar.net/         //
/////////////////////////////////////////////
$title['main'] = "Gallery Board";//タイトル

//各種ヘッダー中の<title>タグに出力する値
//""ではなく、''で囲ってください
$title['op'] = '$title - 操作';//操作ページ
$title['new'] = '$title - 新規貼り付け';//新規投稿
$title['edit'] = '$title - 編集';//編集
$title['view'] = '$entry';//詳細表示
$title['page'] = '$title P $page';//2ページ目～

//-- スクリプトの場所 --//
$php['main'] = "index.php";//Main
$php['set'] = "config.php";//Config
$php['html'] = "lib/html.php";//HTML
$php['thumb'] = "lib/thumbnail.php";//Thunbnail
$php['embed'] = "lib/embed.php";//Embed
$php['entry'] = "lib/entry.php";//Entry
$php['count'] = "lib/count.php";//PageView Counter
$php['auth'] = "lib/ima.php";//ImageAuth
$php['files'] = "lib/files.php";//File Control
$php['ctrl'] = "lib/ctrl.php";//Control
$php['rss'] = "rss.php";//RSS
$php['pickup'] = "pickup.php";//PickUP

$php['css'] = "lib/style.css";//スタイルシート
$php['javascript'] = "lib/script.js";//Javascript

//Script Base URL(index.phpを含まない、最終フォルダまでのURL。(http://~/~/)) *RSSやPickUpを使う場合
// * 自動取得しますが、上手く動かない場合は手動設定してください。
$php['base_url'] = (empty($_SERVER["HTTPS"])?"http://":"https://").$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME'])."/";

$home['pc'] = "http://www.com/";//ホーム
$home['mb'] ="";//モバイルのホーム。　無い場合は、""にする。（ある場合に限り、キャリア判別が行われます。)

$admin['pass'] = "12345";//管理パスワード

$admin['yt_api_key'] = "";//YoutubeAPI v3を利用してサムネイルを取得する場合のAPI Key

//-- データファイルの場所 --//
$data_file['template'] = "dat/tmp.dat";//PC用　テンプレートファイル
$data_file['mb_template'] = "dat/mb_tmp.dat";//モバイル用 テンプレートファイル
$data_file['data'] = "dat/dat.dat";//書き込みデータファイル
$data_file['no'] = "dat/no.dat";//掲示番号データ

//---- メインデータ ファイルロック ----//
$lock['sw'] = 1;//ON=1 , OFF=0
$lock['file'] = "lock.lock";//ファイルロック用のファイル名

//--- 投稿 設定 ---//
$post_set['parent']['new'] = 0;//新規投稿制限 0=誰でも 1=管理者のみ
$post_set['parent']['msg'] = 1;//親記事のコメント必須 0=OFF 1=ON
$post_set['parent']['max_num'] = 0;//親記事の最大-投稿数 (0 = OFF 1以上 = ON) 指定した投稿数を超えると、最終更新が古い記事から削除します

$post_set['upload']['dir'] = "/img";//アップロードの保存先　フォルダ名　相対パスで指定
$post_set['upload']['thumbnail']['sw'] = 1;//詳細・一覧 表示にサムネイルを使う(ON = 1,OFF=0) * createimageなどGDを使います。GDが利用できないサーバではご利用できません。
$post_set['upload']['mode'] = 2;//貼り付け可能 (0=画像のみ,1=動画URLのみ,2=画像と動画URL)
$post_set['upload']['size'] = 150000;//ファイルサイズ制限 （単位:バイト)1KB=1000 Bytes
$post_set['upload']['movie_img'] = 1;//動画イメージ (1 = DL $upload['dir']に保存, 0 = Preview 動画サイトから参照 して表示する)

$post_set['parent']['tag'] = 0;//親記事のTagキーワードの登録可能な数 0 = OFF 1以上 = 登録可能な個数。
$post_set['res']['tag'] = 0;//レスのTagキーワードの登録可能な数 0 = OFF 1以上 = 登録可能な個数。

//-- アップロードフォルダ 容量制限 （単位:バイト)1KB=1000 Bytes
//-- 新規投稿時にアップロード先のフォルダ容量がこの指定容量を超えると古い記事を削除。 
//-- 0 = OFF 1以上 = ON
//-- *サムネイルを利用する場合、サムネイル画像分もカウントされます。
$post_set['upload']['capacity'] = 0;

//投稿可能な動画サイト(0=不可 1許可)
//Youtube、ニコニコ動画は不可にできません。
$post_set['upload']['himado'] = 1;//ひまわり動画
$post_set['upload']['daily'] = 1;//Dailymotion
$post_set['upload']['viemo'] = 1;//Viemo

// 動画サムネイル用 画像
$post_set['backimg']['youtube'] = "./img/youtube.png";
$post_set['backimg']['nico'] = "./img/nicovideo.png";
$post_set['backimg']['daily'] = "./img/daily.png";
$post_set['backimg']['himado'] = "./img/himado.png";
$post_set['backimg']['viemo'] = "./img/viemo.png";

//投稿者 名設定
//投稿フォームで名前が未入力でも投稿出来るようにする場合は、代わりに入力する名前を設定してください。
// *名前を必須入力にするには、空欄にしてください。
$post_set['no_name'] = "";
$post_set['trip_sw'] = 1;//トリップ機能 (ON=1 , OFF=0) 名前の入力欄に 「名前#トリップ用英数字」
$post_set['msg']['no_jp'] = 1;//日本語を含まないコメントの投稿を禁止 (0=OFF,1=ON)
$post_set['msg']['min_length'] = 0;//コメントの最低文字数制限(0=OFF,1以上=ON)

//HPのURL入力欄の利用と入力必須設定(0=OFF, 1=ON(必須指定なし) , 2= 親のみ必須 , 3= 親および子が必須 , 4= 子(レス)のみ必須)
$post_set['hp_url'] = 1;

$post_set['continue']  = 10; //連続投稿制限(cookie使用) 0=OFF 1以上= 添付後の次の投稿は指定秒数 禁止

//編集モードの権限
$post_set['parent']['del_mode'] = 0;//親記事の編集制限 (1=管理者のみ 0=記事投稿者と管理者)
$post_set['res']['del_mode'] = 0;//レスの削除制限(0=親・レス者・管理者, 1=レス者・管理者, 2=親・管理者, 3=管理者のみ)

$post_set['daily_del'] = 0;//親記事の投稿日から指定日数が経過している記事を削除します(0=OFF ,1~ON)


$post_set['res']['sw'] = 2;//レス機能 (0=OFF,1=アップロードOFF,2=アップロードON/コメント必須,3=アップロードON/コメント無し可,4=管理者のみ/アップロードON/コメント無し可)
$post_set['res']['max_num'] = 0;//投稿できるレス数の上限 (0 = OFF, 1~ = ON) レスが指定数を超える場合は、レスの投稿できなくなります
$post_set['res']['url'] = 1;//Url(http://)を含むコメントの投稿を禁止(0=OFF,1=ON)
$post_set['res']['multiple'] = 3;//多重投稿対策 (0=OFF 1=レスログの名前とコメント比較のみ 2=sha1_fileによるファイルハッシュ比較のみ 3=1と2の両方の方式)

//NGワード
$post_set['word_check'] = 2;//投稿時、禁止ワードチェック （0=OFF, 1 = 親のみ, 2 = 子のみ, 3 = 親と子)
$data_file['ng_word'] = "dat/ng_word.dat";//禁止ワード登録ファイル

//---- 投稿時 画像認証 ----/
//空欄 = 利用しない , rand1 = ランダムな数字のみ , rand2 = ランダムな大文字英数 (A-Z,0-9) ,その他文字列 = 固定文字
// * createimageなどGDを使います。GDが利用できないサーバではご利用できません。
$post_set['auth']['type'] = "";
$post_set['auth']['num'] = 5;//ランダムの時の文字数
$post_set['auth']['caution'] = "<div style=\"color:red; padding-left:10px; font-size:10px; clear:both;\">*大文字英数字で入力してください</div>";//入力時の注意書き

//--- 表示 設定 ---//
$print_set['msg']['color'] = Array("black","brown","red","purple","fuchsia","pink","yellow","olive","orange","orangered","blue","aqua","green","lime","gold","silver");//投稿時の文字色

//各種 フォーム名 (テンプレートの$form_entry記述で出力される値)
$print_set['form']['new'] = "新規貼り付け";//新規
$print_set['form']['edit'] = "編集";//編集
$print_set['form']['res'] = "レス書き込み";//レス

//- 一覧表示時
$print_set['list']['no_img'] = "NoImage";//画像が登録されてない空間に表示するメッセージ
//表示 枠数
$print_set['list']['pc_row'] = 4;//縦
$print_set['list']['pc_col'] = 5;//横
$print_set['list']['mb_row'] = 5;//モバイルページ　縦
$print_set['list']['mb_col'] = 1;//モバイルページ 横
$print_set['list']['img_h'] = 150;//一覧イメージの高さ px
$print_set['list']['img_w'] = 140;//一覧イメージの幅 px
//-- 最新の投稿
$print_set['new']['time'] = 1; //Newと表示する時間
$print_set['new']['mark'] = "new";//New表示。　画像で表示したい場合は、<img>タグを書いてください。
$print_set['new']['position'] = 1;//最新の投稿(レス含む)があったら一覧表示の時に、先頭表示にする。(ON = 1, OFF = 0)

//-詳細表示時

//表示サイズ  両方を 0 = 表示可能範囲で表示
$print_set['preview']['img_w'] = 180;//イメージの幅 px
$print_set['preview']['img_h'] = 200;//イメージの高さ px
$print_set['preview']['page'] = 1; //レスページアクセス時に表示するページ(0 = OFF [1ページ目から] , 1 = ON [最後のページから]

//--子(レス)関連
$print_set['res']['num'] = 20;//レスの1ページあたりの表示数
$print_set['res']['sort'] = 0;//レスの投稿位置 (最新の投稿が先頭 = 1, 最新の投稿が末尾 = 0)
$print_set['res']['q_navi'] = 1;//レス引用-ナビゲート機能(ON = 1, OFF = 0) レスのメッセージに「>>レス番号」がある場合に、リンクを張ります。

//-- 動画関連
$print_set['preview']['movie'] = 0;//再生方法 (0 = 新規タブ or 新規ウィンドウ　,　 1=ページ内埋め込み(*ただし、対応サイトのみ)) 

//-- ページナビゲート
//現在のページ数から、指定した範囲前後のページナビゲートを表示します。(ページが存在する番号のみ出力)
$print_set['navi'] = 2;

//注意書き
$print_set['rule']= "ルールを守った投稿をお願いします。";

//必須マーク
$print_set['r_mark']  = "<span style=\"font-size:10px; color:red;\">*必須</span>";

//--- Page View Counter 設定 ---/
//ページビュー　カウント 一覧から詳細画面に入った時、ビューカウントする(0=off, 1=on)
$count_set['pvc']['sw'] = 1;
$count_set['ip_check'] = 1;// 同一IPは1日1回のみカウント(0=off, 1=on)

//ログファイル名	
$data_file['pvc'] = "pv_count.dat";

//-- ビューカウンタ　ファイルロック --//
$count_set['lock'] = 1;//ON = 1 , OFF = 0
$count_set['lock_file'] = "pvc_lock.lock";//ファイルロック用のファイル名

//サーチエンジン ロボット
$count_set['robot_sw'] = 0;//カウント？ 0=しない 1=する
//ロボットのIP or Host 正規表現
$count_set['robot'] = array(
	'search\.msn\.com',
	'googlebot\.com',
	'crawl',
	'choopa\.net'
);

//ビュー カウントしない IP or Host 正規表現
$count_set['not_count'] = array(
	"182\.1(1[2-9]|2[0-7])\.[0-9]*\.[0-9]*",
	"101\.2(2[4-9]|3[0-1])\.[0-9]*\.[0-9]*"
);

//--- Image Good Counter ---/
//Good カウント ボタンを使う(0 = off, 1=親のみ, 2=親と添付有りのレス, 3=親と全レス)
$good_set['sw'] = 3;
$good_set['ip_check'] = 1;// 同一IPは1日1回のみカウント(0=off, 1=on)

//ログファイル名
//* 指定したファイル名の前に、レス番号を付加して保存されます。
//* ".dat"にすると、「レス番号.dat」で保存されます。
$data_file['good'] = "_good.dat";

//-- Good カウンタ　ファイルロック --//
$good_set['lock'] = 1;//ON = 1 , OFF = 0
$good_set['lock_file'] = "good_lock.lock";//ファイルロック用のファイル名

//Good カウントしない IP or Host 正規表現
$good_set['not_count'] = array(
	"182\.1(1[2-9]|2[0-7])\.[0-9]*\.[0-9]*",
	"101\.2(2[4-9]|3[0-1])\.[0-9]*\.[0-9]*"
);

//--- Mail --- //
//メール関連 設定
$post_set['mail']['sw'] = 1;//メールアドレス入力欄(0=OFF, 1=ON(必須指定なし) , 2= 親のみ必須 , 3= 親および子が必須 , 4= 子(レス)のみ必須)

//親記事-投稿者にレス通知するオプション (0 = OFF, 1= ON) *$post_set['mail']['sw'] 必要
//*レス通知ON 時、掲示板が荒らされた場合などに、親記事-投稿者に無駄にメール送信が行われない様に注意。
//*ユーザ判別は無いので、自分が自分の記事にレスしても、通知されます。
$post_set['mail']['user_sw'] = 1;

$post_set['mail']['ad_sw'] = 0;//親記事-新規投稿されたら管理者に通知(0 = OFF, 1=ON) *管理者アドレスに$mail['from']に送信

$post_set['mail']['title'] = "投稿通知";//メールタイトル
$post_set['mail']['name'] = "管理者";//通知メールにおける送信者名
$post_set['mail']['from'] = "admin@admin.com";//管理者(送信元）アドレス
//メール用テンプレート
//テンプレ内　使用可変数 : $log_no = 記事番号、$name = 投稿者名、$date = 投稿日、$msg = 投稿メッセージ
$data_file['mail_temp'] ="dat/mail.dat";

//---- RSS ----//
$title['rss'] = 'RSS';//タイトル
$rss_set['desc'] = '最新の投稿';//説明
$rss_set['num'] = 10;//表示する記事数
$rss_set['sort'] = 0;//ソート順 0=記事No順,1=レスを含む新着(更新日)順

//アイテム表示用テンプレート(Item部出力の変数が利用可能)
$rss_set['item'] = <<< 'EOM'
<item>
	<title>$log_entry</title>
	<link>$img_url</link>
	<description>$log_msg</description>
	<content:encoded>
		<![CDATA[
			<p><a href="$img_url"><img src="$img_file" alt="$log_entry" title="$log_entry" /></a></p>
		]]>
	</content:encoded>
	<pubDate>$log_day</pubDate>
</item>

EOM;

//---- PickUP ----//
$data_file['pickup_tmp'] ="dat/pickup_tmp.dat";//出力テンプレ(Item部出力の変数が利用可能)
$pickup_set['num'] = 5;//出力数
$pickup_set['img_w'] = 140;//イメージの幅 px
$pickup_set['img_h'] = 150;//イメージの高さ px

//出力確率指定
//$pickup_set['list'] = array('per'=>確率(%),'day'=>指定日数);
//投稿日からの経過日数が、[指定日数]以下の場合、設定した[確率]で優先出力。
//設定値が"";で投稿No順, array();で確率無しランダム
$pickup_set['list'] = array('per'=>90,'day'=>4);

//--- Access ---//

//- リファラ規制
//リファラが代入されて無い場合にアクセスを規制する(ON=1,OFF=0)
$access_set['referer']['not_sw'] = 0;

//以下のURL以外からのアクセスを拒否
// 設定しない場合は空欄。複数設定可
//http://不要。正規表現で指定。 ''で囲み、,で区切って下さい。
$access_set['referer']['url'] = array('',);

//- IP 規制
//IP アクセス規制方式 0=指定したIPからのアクセスのみを許可 1=指定したIPからのアクセスを拒否
$access_set['ip']['sw'] = 1;

// アクセス規制IP設定 IPアクセス規制で禁止or許可するIP,HOSTを正規表現で指定してください。
//array('ip'=>IP又は、HOST,'day'=>制限期間(年/月/日))
//  いくつでも追加可。host名一部でも可能。
//制限期間は、指定日の前日までが規制対象。また、何も指定しなかった場合は、ずっと規制します。
//一つ一つ区切ってください区切る場合は,で区切ってください。
$access_set['ip']['list'] = array(
	array('ip'=>'cache*.*.interlog\.com','day'=>'2222/8/8'),
	array('ip'=>'anonymizer'),
);

//- PROXY 規制
//PROXY規制をする1=ON
$access_set['proxy']['sw'] = 0;

//PROXY制限使用時にPROXYを使用していないのにかかってしまった場合に
//通過を可能にするIPを設定。''で囲み,で絶対区切ってください。　正規表現で指定してください。
$access_set['proxy']['skip'] = array(
	'',
);

//- Tor 規制
//Torをチェックする(0=OFF , 1=ON);
$access_set['tor']['sw'] = 0;

//Tor IP List
$access_set['tor']['ip_list'][0] = "http://torstatus.blutmagie.de/ip_list_all.php/Tor_ip_list_ALL.csv";//取得先
$access_set['tor']['data'][0] = "./dat/tor_data.dat";//保存場所

$access_set['tor']['up_time'] = 360;//Tor IP List  再取得-時間 (分) 60 => 1時間毎


//---- 以下 PHP知る者以外は触るべからず ----//
/*
 Movie Set
 regexp = Movie Url Pattern
 embed = Movie Enbed Tag ($movie_id = Movie Id)
*/
$movie_set['youtube']['regexp'] = "(?:^https?:\/\/(?:[\w\+\-]*\.)?youtube\.com\/watch.*v=|^https?:\/\/(?:[\w\+\-@]*\.)?youtu\.be\/)([\w&\+\-]+)";
$movie_set['youtube']['embed'] = '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/$movie_id?autoplay=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><div><a href="https://www.youtube.com/watch?v=$movie_id" target="_blank">https://www.youtube.com/watch?v=$movie_id</a></div>';

$movie_set['nico']['regexp'] = "^https?:\/\/(?:[\w\+\-]*\.)?nicovideo\.jp\/watch\/(.+)";
$movie_set['nico']['embed'] = '<iframe width="560" height="315" src="http://embed.nicovideo.jp/watch/$movie_id?jsapi=1&playerId=1" id="nicovideoPlayer-1" frameborder="0" allowfullscreen></iframe><div><a href="http://nicovideo.jp/watch/$movie_id" target="_blank">http://nicovideo.jp/watch/$movie_id</a></div>';

$movie_set['himado']['regexp'] = "^https?:\/\/(?:[\w\+\-]*\.)?himado\.in\/([0-9]+)";
$movie_set['himado']['embed'] = '';

$movie_set['daily']['regexp'] = "(?:^https?:\/\/(?:[\w\+\-]*\.)?dailymotion\.com\/video\/|https?\:\/\/(?:[\w\+\-]*\.)?dai\.ly\/)([\w\+\-]+)";
$movie_set['daily']['embed'] = '<iframe width="560" height="315" frameborder="0" src="https://www.dailymotion.com/embed/video/$movie_id?autoPlay=1" allowfullscreen allow="autoplay"></iframe><div><a href="https://www.dailymotion.com/video/$movie_id" target="_blank">https://www.dailymotion.com/video/$movie_id</a></div>';

$movie_set['viemo']['regexp'] = "^https?:\/\/(?:[\w\+\-]*\.)?vimeo\.com\/([0-9]+)";
$movie_set['viemo']['embed'] = '<iframe width="560" height="315" src="https://player.vimeo.com/video/$movie_id?autoplay=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe><div><a href="https://vimeo.com/$movie_id" target="_blank">https://vimeo.com/$movie_id</a></div>';

//Movie Url Help
$help['movie'] = "- Youtube -\n
 http://www.youtube.com/watch?v=********\n
 http://youtu.be/********\n
- ニコニコ動画 -\n
 http://***.nicovideo.jp/watch/********\n";
 
if($post_set['upload']['daily']){
$help['movie'] .= "
- Dailymotion -\n
 http://www.dailymotion.com/video/******\n
 http://dai.ly/******\n";
}

if($post_set['upload']['himado']){
$help['movie'] .= "
- ひまわり動画 -\n
 http://himado.in/******\n";
}

if($post_set['upload']['viemo']){
$help['movie'] .= "
- Viemo -\n
 https://vimeo.com/******";
}


//Operation Help
$help['op']="
親記事を削除したい場合は\r\n
親番号のみで\r\n
レスを削除したい場合は\r\n
親番号-レス番号
";

//HomePage Url Input Pattern
//URL入力欄($hp_url_sw)におけるチェック用正規表現(「&」などのエスケープが必要な文字は、エスケープ後の文字で記述）
$hp_url_type='/^(https?|ftp|news)(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/i';

//未実装
/*
$post_set['parent']['category'] = array(
"アニメ","オリジナル",
);
*/

//check
$include_list = get_included_files();
$include_flag =  False;

if($php['main'] and is_array($include_list)){$include_flag = preg_grep("/".$php['main']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Script Call Error!</div></html>";exit;}

// -- Decode Start
$no_getpost = 1;//Get Method Error
	if($_GET){$queries=$_GET;}
	if($_POST){
		if(!is_array($queries)){$queries=$_POST;}
		else{$queries = array_merge($queries,$_POST);}
	}
//	if(is_array($queries)){mb_convert_variables($code_set['system'],$code_set['list'],$queries);}
	if($queries){$F = Decode($queries);}

	function Decode($array = array()) {
		global $code_set;
    	$return = array();
    	foreach ($array as $key => $value) {
    	    if (is_array($value)) {
    	        $value = Decode($value);
    	    } else {
				$value = mb_convert_encoding($value,$code_set['system'],$code_set['list']);
	   	        $value = htmlspecialchars($value,ENT_XHTML and ENT_QUOTES,$code_set['system']);
			}
			$return[$key] = $value;
    	}
		
		return $return;
	}
// -- Decode End

?>