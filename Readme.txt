#########################################################################
#　　　　　     製品名：Gallery Board（Free,PHP)		        #
#		                バージョン：9.1	　　　   		#
#		 	　　　    製作者：詩晴	 　  			#
#			 再配布：無許可(許可必要)			#
#		 	　   完成日：09/04/13		   　		#
#			    最終更新日：19/06/24　　 			#　
#########################################################################


はじめに：
　この度は、Gallery Boardをダウンロードいただき、誠に有難うございます。　
　Gallery Boardは、画像を貼り付ける事の出来る画像掲示板です。

注意事項:
 ・許可無しにPHPの配布をする事を禁止しています。
 ・勝手に著作権表示を消去、書き換えをしないでください。
 ・Gallery Boardを使用しての損害、損失、画像の権利に関する問題などの
 　責任は一切取りませんので、必ず自己責任で御使用下さい。

アップロードについて：
　アップロード可能ファイルは、jpg,jpeg,png,gif画像となっています。
　設定により、管理者のみ画像の投稿ができる様にも出来ます。（レスは誰でも可能。)
  
7.6→8.0へのバージョン更新について：
	データの構造や、アップロードの保存先の方式が変わりました。
	データの引継ぎは出来ません。

動作について：
　簡易的な動作チェックしか行っておりません。
　何か異常やご要望がありましたら、ご報告くださいませ。　　

使用方法：

index.php、libファルダ、データファイルをアップロードしてください。
データファイルのパーミッションは666が推奨です。
また、画像ファイルをアップロードするフォルダは777が推奨です。
設置後、エラーが出る場合は、設定項目が"*";で囲まれているか確認してください。
(*は設定内容です。)

　・config.phpのファイル名を変えた場合、index.phpやpickup.php、rss.php内の
  　configの場所の記述を変更してください。
　
　・config.phpをテキストエディタ等で開いて設定を行ってください。
  ・$title['main']はメインのタイトル
  ・各種ヘッダー中の<title>タグ、テンプレートファイルの変数、$hdtitleに出力する値
  　$title['op'] 削除などの操作ページ
	$title['new'] 新規投稿ページ
	$title['edit'] 編集ページ
	$title['view'] 詳細表示ページ
	$title['page'] 2ページ目以降
　・各スクリプトの場所
    $php['main'] Index
　　$php['set'] Config 設定用
    $php['html'] HTML ページ出力
    $php['thumb'] Thunbnail サムネイル
	$php['embed'] Embed 動画の表示用
    $php['entry'] Entry 投稿、編集
    $php['count'] PageView Counter　ページビューカウンター
    $php['auth'] ImageAuth 画像認証用
    $php['files'] File Control データの読み込み、書き込み
    $php['ctrl'] Control
    $php['rss'] RSS表示用
    $php['pickup'] PickUP ピックアップ表示
    //Script Base URL
    $php['base_url'] index.phpのフォルダまでのアクセスURL
　　　http://～/

　・$home['pc']はPC用 TopページのURL
  ・$home['mb']はモバイル用 TopページのURL
  ・$admin['pass']は管理用のパスワードを設定してください。
   
  ・$admin['yt_api_key'] YoutubeAPI v3を利用して、Youtube動画のサムネイルを取得する場合のAPI Keyです。
  　*指定ない場合、動画情報を取得せずに、ベースとなっているURLに当てはめて、サムネイルURLとしています。
	 動画によっては、上手く取得できないかも知れません。
  　
	*API Keyの取得の仕方は、申し訳御座いませんが、各自お調べください。
   
  ・$data_file['template'] はPC表示用テンプレートファイルを指定してください。
　・$data_file['mb_template'] はモバイル用テンプレートファイルを指定
  ・$data_file['data'] はログデータファイル名を指定してください。
　・$data_file['no'] 掲示番号のデータファイル名を指定してください。

　・$lock['sw'] ファイルロック 1=ON,0=OFF
  ・$lock['file'] ファイルロック用のファイル
	
  ・$post_set['parent']['new'] は画像の貼り付け制限です。(0=誰でも,1=管理者のみ)
   　 1を指定した場合は、貼り付けは管理者だけになります。レスは誰でも可能です。
  ・$post_set['parent']['msg'] は親記事のコメント必須の設定です。(0=OFF 1=必須)
  　　1を設定した場合、新規投稿時、コメントを入力しなくても、ファイルのアップロードのみで投稿可能です。

　・$post_set['parent']['max_num'] 新規投稿の最大-投稿可能数 (0 = OFF 1以上 = ON)
  　　指定した投稿数を超えると、最終更新が古い記事から削除。
  ・$post_set['upload']['dir'] は画像ファイル、サムネイルを保存するフォルダを指定してください。相対パスで指定してください。
　・$post_set['upload']['thumbnail']['sw'] 詳細・一覧 表示に元画像の縮小版のサムネイルを使う(ON = 1,OFF=0)
    * createimageなどGDを使います。GDが利用できないサーバではご利用できません。

  ・$post_set['upload']['mode'] 貼り付け可能なもの (0=画像のみ,1=動画URLのみ,2=画像と動画URL)
  　動画は、
	- Youtube -
		・http://www.youtube.com/watch?v=********
		・http://youtu.be/********
	- ニコニコ動画 -
		http://***.nicovideo.jp/watch/********
	- Dailymotion -
		http://www.dailymotion.com/video/******
		http://dai.ly/******
	- ひまわり動画 -
		http://himado.in/******
	- Viemo -
		https://vimeo.com/******
  　を含むURLが貼り付け可能。
  
  ・$post_set['upload']['size'] は画像のファイルサイズの制限です。単位はバイトです。1KBの場合は、1000を設定して下さい。
  　KBは通常1024バイトですが、計算を行っていますので、1000と設定して頂ければ良いです。
  
  ・$post_set['upload']['movie_img'] 動画イメージの表示方法の設定です。
  　1の場合はダウンロードしてアップロードフォルダに保存します。0の場合は、動画サイトから直接参照します。

　・$post_set['parent']['tag'] 親記事のTagキーワードの登録可能な数
　　0 = OFF 1以上 = 登録可能な個数。
　・$post_set['res']['tag'] レスのTagキーワードの登録可能な数
　　0 = OFF 1以上 = 登録可能な個数。

  ・$post_set['upload']['capacity'] 新規投稿時にアップロード先のフォルダ容量がこの指定容量を超えると最終更新が古い記事を削除します。（単位:バイト)1KB=1000 Bytes　(0=OFF , 1以上=ON)
  　削除は、新しくアップロードするファイルのサイズを含めて、新しい投稿順に親・レスにアップされているファイルのサイズを
	足して行き、この設定値を超えたら以降の投稿のデータ・画像ファイルを削除していきます。
	*サムネイル画像分も容量にカウントされます。

		現在、レス投稿時の容量チェックは行われません。
		また、荒らされた場合に「指定容量を超えて古い記事が削除されていき、最終的に正常な投稿のデータが残らない」
		と言った事も起こらなくは無いと思いますので。
		どちらかと言えば、利用しない方が良いのかも知れません。

　・$post_set['upload']['himado'] ひまわり動画のURLの投稿の可否 (1=可 0=不可)
　・$post_set['upload']['daily'] DailymotionのURLの投稿可否 (1=可 0=不可)
　・$post_set['upload']['viemo'] ViemoのURLの投稿の可否 (1=可 0=不可)

　・動画サムネイル用 画像
　　$post_set['backimg']['youtube'] Youtube
　　$post_set['backimg']['nico']　ニコニコ
　　$post_set['backimg']['daily'] DailyMotion
　　$post_set['backimg']['himado'] ひまわり動画
	$post_set['backimg']['viemo'] Viemo

　・$post_set['no_name']は投稿者名が未入力の場合でも、投稿可能にしたい場合は、代わりに代入する名前を設定してください。  
  　*空欄の場合、投稿者名は必須入力になります。

　・$post_set['trip_sw'] トリップ表示の機能です。 (ON=1 , OFF=0)
  	ON設定の時、名前の入力欄に 「名前#トリップ用英数字」として投稿すると、トリップ機能を利用することができます。
	
  ・$post_set['msg']['no_jp']日本語を含まないコメントの投稿を禁止します (0=OFF, 1=ON)
  ・$post_set['msg']['min_length'] コメントの最低限必要な文字数(0=OFF,1以上=ON)
  ・$post_set['hp_url']　HPのURL入力欄の利用と入力必須の設定(0=OFF, 1=ON(必須指定なし) , 2= 親のみ必須 , 3= 親および子が必須 , 4= 子(レス)のみ必須)
  
  ・$post_set['continue'] 連続投稿制限(cookie使用) 
    0=OFF 1以上= 添付後の次の投稿は指定秒数 禁止
    規制中は、親、レス（添付無しでも）投稿できません。
	規制中で無い場合、添付無しのレスをした後は規制中になりません。
  
　・$post_set['parent']['del_mode'] 親記事の編集制限　1=管理者のみ 0=各ユーザと管理者
  　親記事の編集を規制します。
	1を設定すると、管理者のパスワードのみで編集が行え、記事パスワードでの編集は行えません。
	0を指定すると、記事パスワード、管理者のパスワードで操作が行えます。

　・$post_set['res']['del_mode'] レスの削除制限　(0=親・レス者・管理者, 1=レス者・管理者, 2=親・管理者, 3=管理者のみ)
　　0を指定すると、管理者、親記事、レスのパスワードでレスの削除が行えます。
	1を指定すると、管理者、レスのパスワードでレスの削除が行えます。
	2を指定すると、管理者、親記事のパスワードでレスの削除が行えます。
	3を指定すると、管理者のパスワードのみでレスの削除が行えます。
	
  ・$post_set['daily_del']　親記事の投稿日から指定日数が経過している記事を削除します(0=OFF ,1~ON)
		1日1回。0:00以降の初めのアクセス時に記事をチェックします。
		その時点で、指定した日数を経過している記事は削除します。
		
		例:$daily_del = 1; 投稿日:3/6 20:10の場合
			3/7 00:00のチェック時は削除されず、3/8 00:00のチェック時に削除
		
  ・$post_set['res']['sw'] レス機能の設定です。 
  　(0=OFF , 1=画像アップロードOFF , 2=画像アップロードON , 3=アップロードON/コメント無し可)
  ・$post_set['res']['max_num']  1記事に投稿できるレス数の上限です。 (0 = OFF, 1以上 = ON)
  　レスが指定数を超えた場合は、その投稿へのレスができなくなります
  ・$post_set['res']['url'] Url(http://)を含むコメントの投稿を禁止します(0=OFF,1=ON)
  ・$post_set['res']['multiple'] 多重投稿対策 
  　0=OFF
	1=投稿先の記事にあるレスログの名前とコメント比較のみ
	2=sha1_fileによるファイルハッシュ比較のみ（投稿先の記事のレスに同じファイルの可能性がある場合、投稿不可にします)
	3=1と2の両方の方式

   ・$post_set['word_check'] 投稿時、禁止ワードチェック （0=OFF, 1 = 親のみ, 2 = 子のみ, 3 = 親と子)
　 ・$data_file['ng_word']　禁止ワード登録ファイル
	　ファイルの書き方は、1行に1ワード

　画像認証
   * createimageなどGDを使います。GDが利用できないサーバではご利用できません。
   
　・$post_set['auth']['type']
  　空欄 = 利用しない
	rand1 = ランダムな数字のみ (0-9の数字)
	rand2 = ランダムな大文字英数 (A-Z英字と0-9の数字)
	その他文字列 = 設定した固定文字をそのまま出力
	
　・$post_set['auth']['num'] ランダムの時の文字数
　・$post_set['auth']['caution'] 入力時の注意書き(タグ利用可)

  ・$print_set['msg']['color']は投稿フォームで選択できる文字色リスト。

    //各種 フォーム名 (テンプレートの$form_entry記述で出力される値)
　・$print_set['form']['new'] 新規投稿フォーム名
　・$print_set['form']['edit'] 編集フォーム名
　・$print_set['form']['res'] レスフォーム名

  ・$print_set['list']['no_img'] は投稿されてない空き掲示場所に表示する文字です。
  　*空欄にすると、枠も表示しません。

	//一覧表示の表示枠数
   ・$print_set['list']['pc_row'] PC表示用 縦
   ・$print_set['list']['pc_col'] PC表示用 横
   ・$print_set['list']['mb_row'] モバイル表示用　縦
   ・$print_set['list']['mb_col'] モバイル表示用 横

  ・$print_set['list']['img_h'] は一覧表示での画像の縦幅を指定してください。単位はpxです。
  ・$print_set['list']['img_w'] は一覧表示での画像の横幅を指定してください。単位はpxです。

  ・$print_set['new']['time'] Newと表示する時間を指定してください。
  ・$print_set['new']['mark'] Newの表示についての設定。
  	画像で表示したい場合は、<img>タグを書いてください。
  ・$print_set['new']['position'] 最新の投稿があったら一覧表示の時に、先頭表示にします。(ON = 1, OFF = 0)

　・$print_set['preview']['img_w'] 詳細表示にした時に表示するイメージの幅 px　
　・$print_set['preview']['img_h'] 詳細表示にした時に表示するイメージの高さ px
 　 両方を0にすると表示可能サイズまでで表示します。

　・$print_set['preview']['page'] 最新のレスページから表示する
  	(0 = OFF [1ページ目から] , 1 = ON [最新のレスがあるページから]

　・$print_set['res']['num'] レスの1ページあたりの表示数を設定してください。
　・$print_set['res']['sort'] レスの投稿位置を設定できます。 
  	最新のレスを先頭に表示していく場合は 1, 最新のレスを末尾に表示していく場合は 0
	 *データの出力時に並び替えを行う。
	
  ・$print_set['res']['q_navi'] レス引用-ナビゲート機能(ON = 1, OFF = 0)
  　レスのメッセージに「>>レス番号」がある場合に、指定レスにジャンプできるリンクを張ります。
	　*ただし、テンプレートデータに「レス引用 - ナビゲート」のタグが必要です。

　//-- 動画関連
　・$print_set['preview']['movie'] 動画の再生方法 
　　0 = 新規タブ or 新規ウィンドウでサイトを表示します。
　　1 = 各サイトの埋め込みタグを利用して、ページ内で再生します。 (*ただし、対応サイトのみ))

	//-- ページナビゲート
　・$print_set['navi'] 現在のページ数から、指定した範囲前後のページナビゲートを表示します。(ページが存在する番号のみ出力)
     #- 例 $print_set['navi'] = 2; 現在のページ6の場合
	 　出力 : 4 5 6 7 8

  ・$print_set['rule'] は注意書きを記述してください。
  ・$print_set['r_mark'] 入力必須の表示用マーク

  //-- ページビューカウンタ --//
　・$count_set['pvc']['sw'] 一覧画面から詳細画面に入った時、該当記事のページビューのカウントをする(0=off, 1=on)
  ・$count_set['ip_check'] 同一IPは1日1回のみカウント(0=off, 1=on)
  ・$data_file['pvc'] ビューカウント用のログファイル名
    *保存先は、アップロードフォルダの各記事番号フォルダ内

　・$count_set['lock'] ビューカウント用　ファイルロック ON = 1 , OFF = 0
  ・$count_set['lock_file'] ビューカウントのファイルロック用のファイル名

  ・$count_set['robot_sw']　サーチエンジン ロボットのIPをカウント？ 0=しない 1=する
  ・$count_set['robot']　$count_set['robot_sw']=0の場合の判別用。
  　ロボットのIPかHOST名を正規表現で

　・$count_set['not_count']　カウントしたくないIPかHostを正規表現で

  //-- Good カウンタ --//
　・$good_set['sw'] Good カウント ボタンを使う
  	0 = off, 1=親のみ, 2=親と添付有りのレス, 3=親と全レス
　・$good_set['ip_check'] 同一IPは1日1回のみカウント(0=off, 1=on)
  ・$data_file['good'] ログファイル名
　　* 指定したファイル名の前に、レス番号を付加して保存されます。
　　* ".dat"にすると、「レス番号.dat」で保存されます。
	*保存先は、アップロードフォルダの各記事番号フォルダ内
	
　・$good_set['lock'] Goodカウント用　ファイルロック ON = 1 , OFF = 0
　・$good_set['lock_file'] Goodカウントのファイルロック用のファイル名
　・$good_set['not_count'] Good カウントしない IP or Host 正規表現
  
  //--- Mail --- //
  ・$post_set['mail']['sw'] メールアドレス入力欄の出力と必須設定(0=OFF, 1=ON(必須指定なし) , 2= 親のみ必須 , 3= 親および子が必須 , 4= 子(レス)のみ必須)
　・$post_set['mail']['user_sw'] 親記事-投稿者にレス通知するオプション (0 = OFF, 1= ON)
    *$post_set['mail']['sw'] 必要
	*レス通知ON 時、掲示板が荒らされた場合などに、親記事-投稿者に無駄にメール送信が行われない様に注意。
	*ユーザ判別は無いので、自分が自分の記事にレスしても、通知されます。
  ・$post_set['mail']['ad_sw'] 親記事が新規投稿されたら管理者に通知(0 = OFF, 1=ON)
    *管理者アドレスに$mail['from']に送信
　・$post_set['mail']['title'] 通知メールタイトル
　・$post_set['mail']['name'] 通知メールにおける送信者名
　・$post_set['mail']['from'] 管理者(送信元）アドレス
  ・$data_file['mail_temp'] メール内容テンプレート
  　*テンプレ内　使用可変数 : $log_no = 記事番号、$name = 投稿者名、$date = 投稿日、$msg = 投稿メッセージ

  //---- RSS ----//
  ・$title['rss'] RSSタイトル
  ・$rss_set['desc'] RSSの説明
　・$rss_set['num'] 表示するアイテム数
  ・$rss_set['sort'] ソート順、表示順の設定。
   　 0=記事No順,1=レスを含む新着(更新日)順

　・$rss_set['item'] アイテム表示用テンプレート(Item部出力の変数が利用可能)
   　'EOM'～EOM;の間に記述

　//---- PickUP ----//
　・$data_file['pickup_tmp']　ピックアップ出力用テンプレートのファイル(Item部出力の変数が利用可能)
　・$pickup_set['num'] 記事出力数
　・$pickup_set['img_w'] 画像の表示の幅 px
　・$pickup_set['img_h'] 画像の表示の高さ px
　・$pickup_set['list'] 出力確率指定
  　* $pickup_set['list'] = array('per'=>確率(%),'day'=>指定日数);
    投稿日からの経過日数が、[指定日数]以下の場合、設定した[確率]で出力。
	* 設定値が"";で投稿No順で表示, array();で確率無しでランダム表示。

 //--- Access規制 ----// 
  ・$access_set['referer']['not_sw'] リファラー情報を保持してないユーザからのアクセスを拒否します。1=ON 0=Off
　・$access_set['referer']['url']は特定のリンク元のアドレスからしか来させない様にする場合にアドレス設定してください。
　　正規表現で指定。複数設定が可能です。

　・$access_set['ip']['sw'] アクセス規制方式　$access_set['ip']['list']で指定したIPを 0=指定したIPからのアクセスのみを許可 1=指定したIPからのアクセスを拒否
　・$access_set['ip']['list'] $access_set['ip']['sw']するIP,HOSTを正規表現で設定してください。
	* array('ip'=>IP又は、HOST,'day'=>制限期間(年/月/日))
	いくつでも追加可。host名一部でも可能。あるプロバイダ使用者を全部禁止する場合は、固定部分を入力してください。
	制限期間は、指定日の前日までが規制対象。また、何も指定しなかった場合は、ずっと規制します。
	
　・access_set['proxy']['sw']はPROXY経由でのアクセスを禁止する場合は1、禁止しない場合は0を入力してください。
  ・$access_set['proxy']['skip']はPROXYを付けてない方がチェックにかかった場合にアドレスを指定してください。
　　指定する事により通過できるようにします。
　　注意：固定部分を指定するとそのプロバイダ使用者全員が通過できてしまうのでお気をつけください。

  //---- Tor 規制 ----//
　・$access_set['tor']['sw'] Tor-Proxyをチェックする(0=OFF , 1=ON);
　・$access_set['tor']['ip_list'] TorのIPリストの取得URL
　・$access_set['tor']['data'] 取得したリストの保存場所
　・$access_set['tor']['up_time'] 再取得-時間 (分) 60 => 1時間毎


レスの削除について：
　・レスの削除は、親記事の作成者や管理者が編集画面に入って削除する場合は
　　該当記事の編集画面で削除したいレスのチェックを有効にし、「掲載」ボタンを押してください。
　・掲示番号-レス番号を入力してパスワードを入力する事で削除することも出来ます。

レスの引用について：
　・レスメッセージに「>>レス番号」を記述した場合、指定のレスがあればリンクします。
  	ただし、テンプレートデータに「レス引用 - ナビゲート」のタグが必要です。

画像認証についての注意：
　・JavaScriptを利用して画像（認証コード）を更新しています。JavaScriptが利用できないブラウザでは動作しません。
  ・画像更新時にCookieを発布してから認証コードのチェックを行っています。Cookieが利用できないブラウザでは動作しません。
  

テンプレートデータについて：
	* <form>タグで使うmethodはPOSTを利用してください。
	下記の記述の<!-- * --> ～ <!-- * -->や<*> ～ </*>は、「～」の部分にHtmlタグやそれぞれ使用可能な変数を記述してください。
	
	・<!-- EditForm --> ～ <!-- EditForm -->	投稿フォームの出力部

		・利用できる記述
			<!-- SiteUrlArea --> ～ <!-- SiteUrlArea -->	サイトURL入力エリア
			<!-- TitleArea --> ～ <!-- TitleArea -->		題名入力エリア
			<!-- FileUpArea --> ～ <!-- FileUpArea -->		アップロードファイル選択エリア
			<!-- MovieUpArea --> ～ <!-- MovieUpArea -->	動画URL入力エリア
			<!-- AuthArea --> ～ <!-- AuthArea -->			画像認証入力エリア
			<!-- ColorList:文字色出力用タグ -->				文字色出力用			
			 *例 <!-- ColorList:<option style="background:$color;" value="$color">$color</option> -->
			<!-- TagArea --> ～ <!-- TagArea -->	Tag入力エリア

		・利用できる変数
			$form_entry		フォームタイトル
			$rule			注意書き
			$up_size		アップロード可能サイズ表示
			$ftyu			編集時の注意書き
			$movie_help		貼り付け可能な動画URLの説明
			$ia_tyu			画像認証時の注意書き

			$host			親の編集時のみ、投稿者のhostを表示
			
			必須マーク用変数
			$h_mark[name]	名前
			$h_mark[pass]	パスワード
			$h_mark[mail]	メールアドレス
			$h_mark[hp_url]	サイトURL
			$h_mark[title]	題名
			$h_mark[msg]	コメント
			$h_mark[auth]	画像認証
			
		・<!-- EditForm -->部で必要な部品
			<form enctype="multipart/form-data" method="post" action="$php"> ～ </form>
			
			<input type="text" name="name" value="$name" />		名前入力欄
			
			サイトURL入力欄
				<!-- SiteUrlArea --> 
					<input type="text" name="hp_url" value="$hp_url />
				<!-- SiteUrlArea --> 
			題名入力欄
				<!-- TitleArea -->
					<input type="text" name="entry" value="$entry" />
				<!-- TitleArea -->
			
			<textarea name="msg">$msg</textarea>				コメント入力欄
			<input type="password" name="pass" value="$pass" />	パスワード入力欄

			メールアドレス入力欄
				<!-- MailArea --> 
					<input type="text" name="mail" style="width:80%;" value="$mail" />
					レス通知選択 ラジオボタン
					<!-- NewsArea -->
						<input type="radio" name="news" value="0" />不要 <input type="radio" name="news" value="1" />必要
					<!-- NewsArea -->
				<!-- MailArea -->

			文字色選択用
				<select name="color"> ～ </select>
				 *name属性がcolorならば、selectタグじゃなくても可。
				 *このselectタグ無しで、radioボタンで出力したい場合は出力したい位置に
				 	<!-- ColorList:<input type="radio" value="$color" /><font style="color:$color;">■</font> -->
				 と記述し、出力することも出来ます?
				 
			Tag入力欄
				<!-- TagArea -->
					<input type="text" name="tag" style="width:80%;" value="$tag" />
				<!-- TagArea -->

			アップロードファイル選択欄
				<!-- FileUpArea -->
					<input type="hidden" name="MAX_FILE_SIZE" value="$max_file_size" />
					<input type="radio" name="utp" id="file" value="file" />
					<input type="file" name="up_file" onchange="valchange('file');get_filesize();" title="jpg、jpeg、gif、pngのファイルが対象" accept=".jpg,.jpeg,.png,.gif" />			
					<span id="UpFile_Size"></span>
				<!-- FileUpArea -->
			
				*valchange('file')は、utpのラジオボタンを自動選択。
				*get_filesize();は、選択されたファイルのサイズを　id="UpFile_Size"　に表示します。
			
			動画URL入力欄
				<!-- MovieUpArea -->
					<input type="radio" name="utp" id="movie" value="movie" />
					<input type="text" name="up_movie" value="$file_url" onchange="valchange('movie');" />
				<!-- MovieUpArea -->
				
			必要なhidden
				<input type="hidden" name="mode" value="$type" />
				<input type="hidden" name="prevno" value="$prevno" />

			画像認証
				<!-- AuthArea -->
					<input type="text" name="auth" />	入力欄
					<img src="./image_auth.png" id="auth" />　出力画像
					<button type="button" onclick="auth_ref();" title="画像認証の更新">更新</button>
				<!-- AuthArea -->
	
				*画像認証利用時は、必ず以下の記述が必要です。これが無い場合、画像が更新されません。
				　<body onload="auth_ref();" onpageshow="auth_ref();">
				  <script language="JavaScript">
					<!--
					function auth_ref(){
						if(document.getElementById('auth')){
							dd = new Date();
							document.getElementById('auth').src = 'pb.php?mode=auth&stamp=' + dd.getTime();
						}
					}
					
					-->
				  </script>
			
			
	・<!-- OpForm --> ～ <!-- OpForm -->	操作フォーム用の出力部

		・<!-- OpForm -->部で利用できる変数
			$op_help 操作モードのヘルプ
			
		・<!-- OpForm -->部で必要な部品
			<input type="radio" name="mode" value="edit" />編集 
			<input type="radio" name="mode" value="remove" />削除
			掲示番号:<input type="text" name="prevno" />
			パスワード:<input type="password" name="pass" />

	<!-- ImagePreview --> ～ <!-- ImagePreview -->	詳細表示用の記述部

		・利用できる記述
			・ <!-- Item --> ～ <!-- Item -->	ログを出力部
				1つだけ記述してください。
		
			・<!-- Item -->部で使える変数	
				$log_no		投稿番号
				$log_user	投稿者名
				$user_trip	トリップ
				$res_num	レス数
				$img_file	画像のURL-表示用
				$img_url	画像のURL-リンク用
				$log_entry	題名
				$log_msg	メッセージ
				$log_color	文字色
				$log_day	投稿日
				$log_host	Host/IP
				$hp_url		投稿者のサイトURL（以下に記載の「<!-- Item -->部で使える記述」も確認してください）
				$pv_cnt		ページビュー数
				$good_cnt	Good数
				$tag			Tag一覧
					１個づつ表示する場合は、$tag1 $tag2 $tag3　等
					$tag + 数字で、個別に表示することもできます。

				・以下は補助用の変数です
					$img_center	<img>タグのstyle属性用。
						phpに指定した画像の表示サイズ内で画像をセンタリングする為に、paddingを出力します

							#- <img src="a.jpg" style="$img_style">の場合
								<img src="a.jpg" style="padding:10px 15px;">
							と出力します。(数値部分は、画像により変動します)
					$img_size	<img>タグのstyle属性用
						画像の表示サイズを調整する為のhightとwidthを出力します。
			
				・<!-- Item -->部で使える記述
					・<!-- SiteUrl:[<a href="$hp_url">URL</a>] -->	投稿者のサイトURL出力用
						SiteUrl:の後に出力したいタグを設定してください。
						*$hp_urlの部分に、サイトURLが出力されます。
						*サイトURLの入力がない投稿には、何も出力されません。
						
					・<!-- PV_Counter:<span class="pv">PV:$pv_cnt</span> -->	ページ ビューカウント数 表示制御用
						PV_Counter:の後に出力したいタグを設定してください。
						*$count_set['pvc']['sw']での表示制御用です。
						 $count_set['pvc']['sw'] = 1の時、PV_Counter:の後に指定したタグが出力され、0の時は何も出力されません。
						*$pv_cntの部分に、カウント数が出力されます。
						*表示制御が不要な場合は、$pv_cnt変数のみを使用してください。
					
					・<!-- Good_Counter --> ～ <!-- Good_Counter --> Goodカウント 表示制御用
						*$good_set['sw']での表示制御用です。
						 $good_set['sw'] = 0の時は、この範囲は出力されません。
						 1の時は、親記事の部分のみ出力されます。
						 2の時は、親記事と、アップロードのあるレスの部分は出力されます。
						 3の時は、親記事と、全レスに出力されます。
						*表示制御が不要な場合は、この範囲指定を利用しなくても$good_cnt変数は利用できます。
					
					・<!-- Tag --> ～ <!-- Tag --> Tagキーワード表示制御用
						$post_set['parent']['tag'] = 0の時に、範囲を表示しなくする。
			
			・<!-- ResItem --> ～ <!-- ResItem -->	レスの出力部
				1つだけ記述してください。
				記述した形式で、レス数分の出力を行います。
		
			・<!-- ResItem -->部で使える変数
				$res_no		レス番号
				$res_user	投稿者名
				$user_trip	トリップ
				$res_msg	メッセージ
				$res_color	文字色
				$res_day	投稿日
				$res_host	Host/IP
				$img_url	画像のURL-リンク用(aタグ)
				$img_file	画像のURL-表示用(imgタグ)
				$hp_url		投稿者のサイトURL(以下に記載の「<!-- ResItem -->部で使える記述」も確認してください）
				$good_cnt	Good数
				$tag			Tag一覧
					１個づつ表示する場合は、$tag1 $tag2 $tag3　等
					$tag + 数字で、個別に表示することもできます。
					
			・<!-- ResItem -->部で使える記述
				・<!-- SiteUrl:[<a href="$hp_url">URL</a>] -->	投稿者のサイトURL出力用
					SiteUrl:の後に出力したいタグを設定してください。
					*$hp_urlの部分に、サイトURLが出力されます。
					*サイトURLの入力がない投稿には、何も出力されません。

					・<!-- Good_Counter --> ～ <!-- Good_Counter --> Goodカウント 表示制御用
						*$good_set['sw']での表示制御用です。
						*$good_set['sw']での表示制御用です。
						 $good_set['sw'] = 0の時は、この範囲は出力されません。
						 1の時は、親記事の部分のみ出力されます。
						 2の時は、親記事と、アップロードのあるレスの部分は出力されます。
						 3の時は、親記事と、全レスに出力されます。
						*表示制御が不要な場合は、この範囲指定を利用しなくても$good_cnt変数は利用できます。
						
					・<!-- Tag --> ～ <!-- Tag --> Tagキーワード表示制御用
						$post_set['res']['tag'] = 0の時に、範囲を表示しなくする。
					
			・その他変数
				$back		次のレスページへのナビゲートURL
					#- <a href="$back">次のページ</a>

				$next		前のレスページへのナビゲートURL
					#- <a href="$next">前のページ</a>

				$p_navi		レスページでのページナビゲート

				
			・以下は補助用の為、直接テンプレート側でサイズ設定していただいてもかまいません。
				$prevw		画像の表示サイズ-幅
				$prevh		画像の表示サイズ-高さ
					
					*タグのstyle属性で利用する場合は、「$prevwpxや$prevhpx」としてください。
					
						#- $prevw =10, $prevh = 15;の場合
							$prevwpx　は　「10px」
							$prevhpx  は　「15px」
						の出力になります。

				$img_center	<img>タグのstyle属性用。
					phpに指定した画像の表示サイズ内で画像をセンタリングする為に、paddingを出力します

						#- <img src="a.jpg" style="$img_style">の場合
							<img src="a.jpg" style="padding:10px 15px;">
						と出力します。(数値部分は、画像により変動します)
				$img_size	<img>タグのstyle属性用
					画像の表示サイズを調整する為のhightとwidthを出力します。
			
			・レス引用 - ナビゲート用
				レスの出力部内を<a name="Res$res_no">～</a>で囲んで置くとレスメッセージ内に「>>8」など書かれている場合に
				指定のレス位置までナビゲートできます。

		・Goodボタン用に必要な部品
			<form action="" method="post"> ～ </form>
			 *actionは空欄、method="post"は固定必須です。 
			
			親記事用ボタン <button type="submit" name="good" value="$log_no-0">Good</button>
			レス用ボタン <button type="submit" name="good" value="$log_no-$res_no">Good</button>

	<!-- ImageList --> ～ <!-- ImageList -->	一覧表示用の記述部
		・利用できる記述
			・<!-- ListBox --> ～ <!-- ListBox --> 1行 判別用
			
			・<!-- ItemBox --> ～ <!-- ItemBox --> 1列 判別用
				<!-- ListBox -->の範囲内に記述。
			
			・<!-- Item --> ～ <!-- Item -->	ログを出力部
				<!-- ItemBox -->の範囲内に記述。
				1つだけ記述して$list_col,$list_rowで出力個数を設定してください。
				
				*基本的に、<!-- ListBox -->範囲内に<!-- ItemBox -->を記述し、
				<!-- ItemBox -->範囲内に、<!-- Item -->を記述する形になります。
		
			・<!-- Item -->部で使える変数
				<!-- ImagePreview -->の<!-- Item -->部で使える変数
	
			・<!-- Item -->部で使える記述
				<!-- ImagePreview -->の<!-- Item -->部で使える記述に同じ

			・以下は補助用の変数です。
				$imgw	一覧表示での画像の幅 (phpに指定した値をそのまま出力)
				$imgh	一覧表示での画像の高さ (phpに指定した値をそのまま出力)
					*上2つは、タグのstyle属性で使う場合、それぞれ「$imgwpx , $imghpx」としてください
					
	・範囲に関係なく使える変数
		$back		次のページへのナビゲートURL
			#- <a href="$back">次のページ</a>

		$next		前のページへのナビゲートURL
			#- <a href="$next">前のページ</a>
			
		$p_navi		ページナビゲート
		
		$pnum		総ページ数
		$page		現在のページ数
		
		・以下は補助用の変数です。
			$php		phpスクリプトの名前
			$home		HomeのURL
			$hdtitle	<title>タグ用のタイトル
			$title		タイトル

	・操作ナビゲートのリンク先
		<a href="$home">Home</a>
		<a href="?">閲覧</a>
		<a href="?mode=new">貼り付け</a>
		<a href="?mode=op">編集</a>

	・RSS用テンプレート
		<!-- ImagePreview -->の<!-- Item -->部で使える記述に同じ
	
	・PickUP用テンプレート
		<!-- ImageList --> ～ <!-- ImageList -->	一覧表示用の記述方式で指定できます。

お問い合わせ：
　・ご意見、ご感想、質問、エラー報告等は、メールまたは、雑談板で！！
    メールはホームページのお問い合わせページからお願いします。
　・HPは「天空の彼方」https://www.tenskystar.net/
　　雑談板などに気楽に投稿してください。
　　

更新情報
19/06/24 Ver9.1
Tagキーワードの登録、#検索の機能の追加。
動画をページ内で埋め込み再生も出来るように。（可能なサイトのみ）
画像UP時、選択したファイルの容量を表示するJavaScripコードの追加。
一部の設定変数名の変更。
PC向けテンプレートファイルをhtml5記述ベース化。
JavaScriptを外部ファイル化。
CSSを外部ファイル化。

19/02/22 Ver9.0
編集モードの調整。
レスの削除の権限の設定を追加。

19/02/17 Ver8.9
編集時、ファイルの変更しない場合に、エラーが発生する可能性が有ったのを修正。
エラー時のファイルの削除処理を調整。

19/02/11 Ver8.8
IPによるアクセス制限に、期間設定できる様に変更。

18/12/18 Ver8.7
編集時、サイトURLやメールアドレスなどの値が書き換えられないのを修正。
SiteURLの置換方法の調整。

18/11/24 Ver.8.6
動画サイトViemoに対応。

18/10/10 Ver.8.5
動画のサムネイル情報を取得する時、xmlオブジェクトのままだと
判定ミスが起きていたのを修正。

18/10/05 Ver.8.4
PHPのバージョンが7.2の場合に
記事編集時、colorに関するWarningエラー
最初のPVカウントや、Goodカウント時にcount()に関するWarningエラーが表示されるのを修正。

18/04/01 ver 8.3
html.php , entry.phpのセキュリティ調整。
投稿時や、出力時のエラーの追加。
アップロードされた画像情報を取得失敗した時のエラー処理を念のために追加。

18/03/16 ver 8.2.1
rss.phpにて RSSの表示ソート。　$rss_set['sort'] = 1が効かないミス修正。

17/02/21 Ver8.2
Goodカウント機能を正式追加。 ($c_count -> $good_cnt)
設定変数$good_set、表示制御 <!-- Good_Counter --> ～ <!-- Good_Counter -->です。
$count_set['pvc']['sw']によるページビュー数の表示制御用の記述<!-- PV_Counter:* -->を追加。

17/01/25 Ver8.1
レスに管理者のみが投稿可能モードを追加。
通知機能の修正。
データ保存時に整列処理。


16/12/28 Ver8.0.1
レス引用の記述を行った場合、コメントが表示されないミスを修正。

16/12/26 Ver8.0
検索の調整。個別版のRSS出力、PickUp出力の標準化。
IP_CheckerのTorCheckを追加。

16/12/15 Ver7.9
ページビューのカウンター調整。設定変数の変更。
画像保存フォルダを記事番号別に変更。

16/11/25 Ver7.8
検索の追加、投稿の調整。画像認証用の画像呼び出しタグを変更し、[戻る]時にも画像が更新されるように。

16/09/15 Ver7.7
スクリプト分割、ページ出力の調整。

15/12/27 Ver7.6
文字コードをUTF8。
親記事の投稿時に、アップする種別を選ばなかった場合に不正なデータが発生する不具合を修正。

15/12/13 Ver7.5
画像の種類を拡張子判断からMimeType判別に。

15/05/18 Ver7.4
ページビューカウントをON時、詳細表示の画像サイズ指定が効かなかったを修正。

15/05/08 Ver7.3
Youtubeのサムネイル取得APIが変わったため、V3に変更。V3を利用する方法と、基本的なURLに当てはめる方法に。

15/02/28 Ver7.2
必須マーク

15/02/19 Ver7.1
Cookieが保存されなくなっていたのを修正。

15/02/02 Ver7.0
キャリア判別の追加。モバイルの場合、モバイル用テンプレートを読み込む様に。

15/01/06 Ver6.9
ページビューカウント機能を追加。(詳細表示に入った時、該当記事のページビュー数をカウントアップ)

14/12/20 Ver6.8
コメント中にURLを含む場合に自動でリンクを貼る様に。
投稿者のサイトURL欄の機能を追加。（設定によって、入力必須指定も可)

14/11/22 Ver6.7
$res_adswがどちらの値であっても、投稿後のページ表示が最後のレスページだったのを修正。

14/08/13 Ver6.6
親記事の編集時に、レスが消えてしまう,
最初からアップ種別のラジオボタンにチェックが入った状態になっている,
元が動画貼付だった場合にURL入力欄が画像URLになっていた。を修正。

14/08/07 Ver6.5
レスが無い時のレスの並び替えでPHPエラー発生を修正。親記事の投稿後の経過日数による削除機能を追加。
レス機能の種別($res_sw)、アップロード機能の種別($upsw)によるテンプレ表示制御が正しくなかったのを修正。
名無し投稿($no_name)、新レス投稿時の記事並び替え($new_mov)、トリップ機能、容量,親数制限、レスデータ保存、コメント無し投稿($res_req)が正しく動作しなくなっていたのを修正。
管理パスで編集,削除が出来なかったのを修正、ひまわり動画にて動画によってはサムネイル情報が取得できなかったのを修正。

14/07/25 Ver6.4
画像認証の追加。 ログにhost名では無く、基本的にはIPアドレスを保存するように変更。

14/07/24 Ver6.3
gif,pngの透過画像のサムネイル化に対応。（サムネイル化時に背景が黒になるのを修正）

14/07/14 Ver6.2
ファイルロックの機能を追加。

14/06/03 Ver6.1
Dailymotion、ひまわり動画、Youtubeの短縮URL形式に対応。

14/05/26 Ver6.0
テンプレートの置換方法と記述の一部を変更（最適化) ページ番号によるページナビゲートを追加。

13/7/12 Ver5.0
コメント最低文字数=0でレス時にコメント等入力無しでも投稿できたのを修正。
レス時、コメント無しでも添付があれば投稿できる設定の追加。

13/6/11 Ver4.9
トリップを利用していた場合、記事編集時にトリップ値が変わってしまう場合があったのを修正。

13/6/10 Ver4.8
画像の大きさによっては、サムネイルが生成されず、投稿できないのを修正。
サムネイルの生成・表示時のサイズ計算を修正。

13/6/02 Ver4.7
拡張子が大文字の場合に登録不可、レスが有る場合に親の画像差し替えが出来ないのを修正。

13/5/27 Ver4.6
レス多重投稿のチェックにファイルハッシュ方式を追加、チェック方式の選択設定を追加。 レスの新古 表示並び替えが動作していなかったのを修正。

13/5/26 Ver4.5
レスの多重投稿時の調整。コメント最低文字数の制限設定を追加。

13/5/22 Ver4.4
親、レスでアップロード投稿後に指定秒数が経過しないと規制する機能を追加。

13/5/20 Ver4.3
親を削除した時、レスに動画の貼り付けがあった場合にその動画イメージが削除されないミスを修正。
親の編集画面で、レスの貼り付け画像が表示されないミスを修正。
セキュリティでクエリ処理の調整。
最新レスを先頭に表示するか、末尾に表示するか。ログ上ではなく、表示する時に並び替える様に。

13/5/15 Ver4.2
画像差し替え時に前画像が削除されず、差し替え出来なかったのを修正。

12/11/22 Ver4.1
サムネイル表示 元画像の縮小版をサーバに保存して、一覧・詳細　表示に利用する。

12/07/24 Ver4.0
セキュリティの脆弱性を発見したため、修正いたしました。

12/07/24 Ver3.9
編集の規制を追加。

12/07/12 Ver3.8
レス削除時の置換ミスを修正。

12/07/03 Ver3.7
画像差し替え時のエラーを修正。

12/07/01 Ver3.6
レス投稿者によるレス削除の機能を追加。

12/06/28 Ver3.5
一覧表示のナビゲートリンクを修正

12/06/18 Ver3.4
親、レスの投稿数の制限、動画のイメージの表示方法の追加、New表示の修正。

12/06/13 Ver3.3
動画のイメージurl取得を変更。

12/06/11 Ver3.2
レス削除、親記事修正時の問題を修正。

12/06/10 Ver3.1
レス引用のナビゲート機能を追加。

12/06/09 Ver3.0
記事が更新された場合などにNewを表示、トリップ機能、レスの表示数の調整機能を追加。

12/05/19 Ver2.9
セキュリティ上の調整

12/04/12 Ver2.8
編集時の画像サイズ調整、削除完了後に何も表示されないのを修正。

12/04/09 Ver2.7
デザインのテンプレート化。

12/04/07 Ver2.6
レス時にアップロードが必須になっていたのを修正し、コメントのみでの投稿を可能に。

12/04/03 Ver2.5
一覧表示の画像サイズ・表示位置の計算方法を変更。

12/03/31 Ver2.4
ニコニコ、Youtubeの動画のURLも貼れるように。

12/03/26 Ver2.3
レスにも画像アップロード可能に。

11/12/15 Ver2.2
レス機能のON/OFF設定、レスの削除、http://を含むコメントの禁止を追加

11/02/20 Ver2.1
荒らし対策 - 日本語を含まないコメントの投稿を禁止出来るように。

10/04/21 Ver2.0
要望により、投稿後の編集、画像差替えを可能に。

10/04/19 Ver1.9
削除が行えなかったのを修正。
貼り付け時に拡張子が大文字の場合、アップロードできなかったのを修正。

10/04/03 Ver1.8
一覧ページの画像表示を調整。
配布開始。

10/03/19 Ver1.7 
通信セキュリティの強化。

09/11/14 Ver1.6
 一覧ページの全体サイズを計算で算出する様に。

09/10/09 Ver1.5
 強制許可、強制制限をIP,HOSTどちらでも指定可能に修正。

09/09/21 Ver1.4 
 画像表示の調整。
 
09/09/18 Ver1.3 
 レス表示の調整。

09/07/20 Ver1.2
 データ書き込み時のエラーを修正

09/04/14 Ver1.1
 アップロード容量制限

09/04/13 Ver1.0
 一先ず完成。