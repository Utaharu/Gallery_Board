<?php
/*
 Gallery Board - Common Control
 v1.3 19/06/12 Tag検索などの処理
- コントロール -
 Access
  + Referer - リファラ規制
  + Proxy - PROXY規制
  + Forced - IP規制
  + Tor - Tor規制
  
 Ctrl
  + Get_Date - 現在の日時の取得
  + Get_IP - IPアドレスの取得
  + Get_Caria - 機種判別 PC or Mobile
  + Convert_Link - URLにリンクタグを付与
  + Get_ImgStyle - 画像の表示サイズの調整
  + Del_Dir - 記事フォルダの削除
  + Set_Cookie - クッキー
  + Get_Item - ログの取得
  + Daily_Check - 経過日数による自動削除
  + Tag_Adjust - タグ文字列を配列化
*/

Class Access{

//リファラ規制
	public static function Referer(){
		global $access_set;
		$referer = getenv('HTTP_REFERER');
			if(!getenv('HTTPS') and strtolower(getenv('HTTPS')) != 'off'){$script = "https";}
			else{$script = "http";}
			$script .= "://" . getenv('SERVER_NAME') . getenv('SCRIPT_NAME');
		if($access_set['referer']['not_sw'] and !$referer){Html::Error('取得エラー','リファラーが取得出来ませんでした。');}
		if(is_array($access_set['referer']['url'])){
			$referer_flag = False;
			foreach ($access_set['referer']['url'] as $one){
				if($one){
					//パターンマッチ検索（正規表現)
					$cnt = preg_match("/".$one."/",$referer);
					if(1 > $cnt && $referer != $access_set['referer']['url'] && $referer != $script && !$eflag){$referer_flag = 1;}
					elseif(1 <= $cnt){$referer_flag = 0; $eflag=1;}
				}
			}
			if($referer_flag){Html::Error('アクセス禁止',"管理者が指定したアドレス以外からのアクセスを禁止しています。\n");}
		}
		return;
	}
	
//PROXY制限
	public static function Proxy(){
		global $access_set,$ip;
		if($access_set['proxy']['sw']){
			$proxy_flag = False;
			
//			if(getenv('HTTP_X_FORWARDED_FOR')){$proxy_flag = 1;}
			if(getenv('HTTP_CLIENT_IP')){$proxy_flag = 2;}
			if(getenv('HTTP_SP_HOST')){$proxy_flag = 3;}
			if(getenv('HTTP_VIA')){$proxy_flag = 4;}
			if(getenv('HTTP_CACHE_INFO')){$proxy_flag = 5;}
			if(preg_match("/(prox|squid|cache|gate|firewall|webwarper|torproject|tor-exit|anonymizer)/",$ip['host'])){$proxy_flag = 6;}
			if(preg_match("/(via|proxy|gate)/",getenv('HTTP_USER_AGENT'))){$proxy_flag = 7;}
			if(preg_match("/no-cache/",getenv('HTTP_PRAGMA'))){$proxy_flag = 8;}
//			if(!getenv('HTTP_ACCEPT_ENCODING')){$proxy_flag = 9;}
			//if(preg_match("/close/",getenv('HTTP_CONNECTION'))){$proxy_flag = 10;}
	
			//強制許可
			if(is_array($access_set['proxy']['skip'])){
				foreach ($access_set['proxy']['skip'] as $_) {
					if($_){
						$hcnt = preg_match("/$_/",$ip['host']);
						$acnt = preg_match("/$_/",$ip['addr']);
						if ($ip['host'] == $_  or $ip['addr'] == $_ or $hcnt > 0 or $acnt > 0){$proxy_flag = 0;}
					}
				}
			}
			if ($proxy_flag){Html::Error("アクセス禁止",'PROXY経由でのアクセスは禁止されています。'.$proxy_flag );}
		}
		return;
	}
	
//強制制限
	public static function Forced(){
		global $access_set,$ip;
		$limit_flag = array('sw'=>1,'now_stamp'=>time(),'limit_stamp'=>0,'host'=>0,'addr'=>0);
		if(is_array($access_set['ip']['list'])){
			foreach ($access_set['ip']['list'] as $line) {
				$limit_flag['limit_stamp'] = $limit_flag['now_stamp'];
				if($line){
					if(isset($line['day'])){
						if(preg_match("/(?<year>[0-9]{4})[-\/](?<month>[0-9]{1,2})[-\/](?<day>[0-9]{1,2})/",$line['day'],$lim_match)){
							if(checkdate($lim_match['month'],$lim_match['day'],$lim_match['year'])){
								$limit_flag['limit_stamp'] = strtotime($lim_match['year'] . "/" . $lim_match['month'] . "/" . $lim_match['day']);
							}
						}
					}
					if(!isset($line['ip'])){$line = array('ip'=>$line);}
					$limit_flag['host'] = preg_match("/". $line['ip'] ."/",$ip['host']);
					$limit_flag['addr'] = preg_match("/". $line['ip'] ."/",$ip['addr']);

					if($access_set['ip']['sw']){
						if(($ip['host'] == $line['ip'] or $ip['addr'] == $line['ip'] or $limit_flag['addr'] > 0 or $limit_flag['host'] > 0) and $limit_flag['limit_stamp'] >= $limit_flag['now_stamp']){
							Html::Error('アクセス禁止',"ホストは管理者によってアクセスを禁止しております。");
						}
					}elseif(!$access_set['ip']['sw']){
							if(($ip['host'] == $line['ip'] or $ip['addr'] == $line['ip'] or $limit_flag['addr'] > 0 or $limit_flag['host'] > 0) and $limit_flag['limit_stamp'] >= $limit_flag['now_stamp']){$limit_flag['sw']=0;}
					}else{Html::Error("設定エラー","アクセス禁止方式の設定値が異常です。");}
				}
			}
		}
		if($limit_flag['sw'] and !$access_set['ip']['sw']){Html::Error("アクセス禁止","ホストは管理者によってアクセスを許可しておりません。");}
		return;
	}

//Tor - PROXY制限
	public static function Tor(){
		global $access_set,$ip;
		if($access_set['tor']['sw']){
			//現在の時刻(Sec)
			$now_time = intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s"));
			//今日の日付(timestamp)
			$to_date = time();
			//最終更新日(timestamp)
			$last_up_date = filemtime($access_set['tor']['data'][0]);
			if(filesize($access_set['tor']['data'][0]) == 0){$last_up_date = 0;}//if DataFile Size = 0
			
			//更新予定 時刻(sec)
			$up_time = $access_set['tor']['up_time'] * 60;
			//更新予定日(timestamp
			$next_up_date = $last_up_date + $up_time;
			
			if($next_up_date and $next_up_date <= $to_date and $last_up_date <= $next_up_date){
			//更新時間を超えている
				$fno=0;
				foreach ($access_set['tor']['ip_list'] as $tor_get_file){
					$file_sorce = file_get_contents($tor_get_file);
					$match_cnt = preg_match_all("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\n/",$file_sorce,$tor_match);
					if($match_cnt > 0){
						$fs = fopen($access_set['tor']['data'][$fno],"w+");
						$wflag = fwrite($fs,$file_sorce);//Write
						fclose($fs);
					}
					
					$fno++;
				}
			}
			
			//Check
			$tor_flag = 0;
			foreach ($access_set['tor']['data'] as $tor_file){
				if(!$tor_flag){
					$tor_data = file_get_contents($tor_file);
					
					if($tor_data){
						$addr_pt = preg_replace("/\./","\.",$ip['addr']);
						preg_match_all("/(".$addr_pt.")\n/",$tor_data,$tor_match);
						if(is_array($tor_match[1])){$tor_flag = count($tor_match[1]);}
						if($tor_flag > 0){$tor_flag = 1;}	
						else{$tor_flag = 0;}
					}
				}else{continue;}
			}
		
			if($tor_flag){Html::Error("アクセス禁止",'Tor-PROXY 経由でのアクセスは禁止されています。');}
		}
		return;
	}
}
class Ctrl{

	public static function Get_Date(){
		//時間取得   # 日本時間
		$_ENV{'TZ'} = "JST-9";
		$times = time();
		list($sec,$min,$hour,$mday,$mon,$year,$wday)=localtime($times);
		$week = array('日','月','火','水','木','金','土');
		
		// 日時のフォーマット
		$date = sprintf("%04d/%02d/%02d(%s) %02d:%02d",
		       $year+1900,$mon+1,$mday,$week[$wday],$hour,$min);
		return $date;
	}
	
	public static function Get_IP(){
		//$ip['host'] = HostName,$ip['addr'] = IpAddress
		//IP取得
		$ip['host'] = getenv('REMOTE_HOST');
		$ip['addr'] = getenv('REMOTE_ADDR');
		$ip['agent'] = getenv('HTTP_USER_AGENT');
		//代入チェック
		if (!$ip['host'] or $ip['host'] == $ip['addr']){$ip['host'] = gethostbyaddr($ip['addr']);}
		if (!$ip['addr'] or $ip['host'] == $ip['addr']){$ip['addr'] = gethostbyname($ip['host']);}
		if (!$ip['host']){$ip['host'] = $ip['addr'];}
		if (!$ip['addr']){$ip['addr'] = $ip['host'];}
		return $ip;
	}
	
	public static function Get_Caria($Agent,$Host){
		global $home;
		//携帯判別用
		$mtype = array(
		'docomo.\ne\.jp:Docomo','ezweb\.ne\.jp:Au',#docomo # au
		'jp-d\.ne\.jp:SoftBank','jp-h\.ne\.jp:SoftBank','jp-t\.ne\.jp:SoftBank','jp-c\.ne\.jp:SoftBank','jp-k\.ne\.jp:SoftBank','jp-r\.ne\.jp:SoftBank','jp-n\.ne\.jp:SoftBank','jp-s\.ne\.jp:SoftBank','jp-q\.ne\.jp:SoftBank',#softbank
		);
		//SmartPhone(Agent)
		$agtype = array(
		'Googlebot-Mobile:GMobile','iPhone:iPhone','iPod:iPod','(?!(Android.*SC-01C))(Android.*Mobile):Android','IEMobile:IEMobile','BlackBerry:BlackBerry',
		'Y!J-SRD\/1\.0:Ymobile','Y!J-MBS\/1\.0:Ymobile'
		);
	   
		//機種 判別
		$caria = "pc";
		if($home['mb']){
			foreach ($mtype as $caria_set) {
				//IP
				list($caria_ip,$caria_type) = explode(":",$caria_set);
				if(0 < preg_match("/".$caria_ip."/",$Host)){$caria = $caria_type; break;}
			}
		 	if($caria == "pc"){
				//Agent
				foreach($agtype as $caria_type){
					list($caria_agent,$caria_type) = explode(":",$caria_type);
					if(preg_match("/".$caria_agent."/",$Agent)){$caria = $caria_type; break;}
				}
			}
		}
		return $caria;
	}

	public static function Convert_Link($Val){
    	//自動リンク
    	$Val = preg_replace("/(https?|ftp|news)"."(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/i","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $Val);
    	$Val = mb_eregi_replace("([0-9A-Za-z\.\/\~\-\+\:\;\?\=\&\%\#\_]+@[-0-9A-Za-z\.\/\~\-\+\:\;\?\=\&\%\#\_]+)","[<a href=\"mailto:\\1\">\\1</a>]", $Val);
		return $Val;
	}

//表示サイズ
	public static function Get_ImgStyle($img_file,$Type = False){
		global $F,$print_set;
		$img_style = false;
		$img_center = false; 
		if(!$img_file){return;}
		elseif(file_exists($img_file)){
			//X=幅 Y=高さ
			$imw = 0; $imh = 0;
			$re_imgw = $print_set['preview']['img_w'];$re_imgh = $print_set['preview']['img_h'];//詳細表示 サイズを代入
			list($imw,$imh) = getimagesize($img_file);//サイズ取得
			if($imh > 0 and $imw > 0){
				$imx = ceil($imw/$imh); 
				$imy = ceil($imh/$imw);//比の算出
		
				if($F['mode'] != "enter" and $F['mode'] != "view" and $F['mode'] != "rewrite" and !$Type){$re_imgh = $print_set['list']['img_h']; $re_imgw = $print_set['list']['img_w'];}//サイズ 代入値を一覧表示のサイズに
		
				//比に基づいた画像表示サイズの算出
				if($imy > $imx){$imy = $re_imgh; $imx = round($imw * ($re_imgh/$imh));}//縦長 表示サイズ 範囲内
				elseif($imy < $imx ){$imy = round($imh * ($re_imgw/$imw)); $imx = $re_imgw;}//横長 表示サイズ 範囲内
				else{//正方形
					if($imh > $re_imgh or $imw > $re_imgw){//画像サイズ > 表示サイズ
						if($re_imgh > $re_imgw){$imy = round($imh * ($re_imgw/$imw)); $imx = round($imh * ($re_imgw/$imw));}//表示幅 < 表示高さの場合、狭い表示幅にあわせる。
						elseif($re_imgh < $re_imgw){$imy = round($imw * ($re_imgh/$imh)); $imx = round($imw * ($re_imgh/$imh));}//表示幅 > 表示高さの場合、狭い表示高さに合わせる。
						else{$imy = round($imh * ($re_imgw/$imw)); $imx = round($imw * ($re_imgh/$imh));}//表示幅 == 表示高さ
					}else{$imy = $imh; $imx = $imw;}
				}
				//画像枠内の表示位置を調整(センタリング)
				$imgum = ceil(($re_imgh-$imy)/2); 
				$imgrm = ceil(($re_imgw-$imx)/2);
				
				if(!$Type){
					if($imw > $imx or $imh > $imy){$img_size = " height:".$imy."px; width:".$imx."px;";}
					$img_center = "padding: ".$imgum."px ".$imgrm."px;";
				}else{$img_size = $imx; $img_center = $imy;}
		
				$img_style[0] = $img_size;
				$img_style[1] = $img_center;
			}
		}
		return $img_style;
  	}

//Directory Delete
	public static function Del_Dir($Dir){
		$del_flag = False;
		if(file_exists(getcwd().$Dir)){
	  		$dirs = dir(getcwd().$Dir);
			$f_cnt = 0; $ng_cnt = 0;
			$base_dir = dirname($Dir);
			if($base_dir != "/" and $base_dir != "\\"){
				while(($ent = $dirs->read()) !== FALSE){
					if($ent != ".." and $ent != "."){
						if(!is_dir(getcwd().$Dir."/".$ent)){
							$f_cnt++;
							$d_flag = False;
							$d_flag = unlink(getcwd().$Dir."/".$ent);
						}else{
							Crl::Del_Dir($Dir."/".$ent);
						}
					}
				}
				$del_flag =  rmdir(getcwd().$Dir);
			}
		}
		return $del_flag;
	}
	
//お菓子をプレゼント
	public static function Set_Cookie($Type){
		global $F,$post_set;
			$times = time();
			if($Type == "form" ){
				$c_time = $times + (24 * 60 * 60) * 30;
				setcookie("name",$F['name'],$c_time);
				setcookie("color",$F['color'],$c_time);
				setcookie("url",$F['hp_url'],$c_time);
				setcookie("kword",$F['pass'],$c_time);
			}elseif($Type == "limit"){
				$c_time = $times + $post_set['continue'];
				setcookie("plim",$times,$c_time);
			}
		return;
	}

//投稿取得
	public static function Get_Item($Keyword = False){
		global $php,$data_file,$F,$print_set,$count_set;
		list($data,$ent_no_list,$up_date_list) = Files::Load($data_file['data'],"line");
		$item_list=array();
		if($data){
			if($F['mode'] == "view" or $F['mode'] == "enter" or $F['mode'] == "edit"){
				$key = False;
				if(is_numeric($Keyword)){
					if($ent_no_list and is_numeric($Keyword)){$key = array_search($Keyword,$ent_no_list);}
					if($key !== False){
						$item_list[] = $data[$key];
						$ent_no_list = array($data[$key]['ent_no']);
					}
				}
				if($key === False){Html::Error("エラー","投稿が見つかりませんでした。");}
			}elseif($F['search']){
				//KeyWord Search
				mb_regex_encoding("UTF-8");
				$Word_List = mb_ereg_replace("　"," ",$Keyword);
				$Word_List = explode(" ",$Word_List);
				$ent_no_list = array();
				$up_date_list = array();
				
				if(is_array($data) and is_array($Word_List)){
					foreach($data as $item){
						$Word_Flag = array_fill(0,count($Word_List),False);//ワード毎のフラグ 初期化
						if($item){
							//Parent Search
							foreach($Word_List as $word_key=>$Word){
								if($Word){
									if(mb_ereg("^[#＃](.+)",$Word,$match_tag)){
										//Tag Search
										if(preg_grep("/^" . $match_tag[1] . "$/",$item['tag'])){$Word_Flag[$word_key] = True;}
									}else{
										if((mb_strpos($item['name'],$Word) !== False) or (mb_strpos($item['entry'],$Word) !== False) or (mb_strpos($item['msg'],$Word) !== False) or (mb_strpos($item['img']['url'],$Word) !== False) or (mb_strpos($item['img']['file'],$Word) !== False) or preg_grep("/" . $Word . "/",$item['tag'])){$Word_Flag[$word_key] = True;}
									}
								}
							}
							//Res Search
							if(is_array($item['res'])){
								foreach($item['res'] as $res){
									if(in_array(False,$Word_Flag,True) === False){break;}//各ワードTrueの場合、以降を省く
									if($res['name']){
										foreach($Word_List as $word_key=>$Word){
											if($Word){
												if(mb_ereg("^[#＃](.+)",$Word,$match_tag)){
													//Tag Search
													if(preg_grep("/^" . $match_tag[1] . "$/",$res['tag'])){$Word_Flag[$word_key] = True;}
												}elseif((mb_strpos($res['name'],$Word) !== False) or (mb_strpos($res['msg'],$Word) !== False) or (mb_strpos($res['img']['url'],$Word) !== False) or (mb_strpos($res['img']['file'],$Word) !== False) or preg_grep("/" . $Word . "/",$res['tag'])){$Word_Flag[$word_key] = True;}
											}
										}
									}
								}
							}
							if(in_array(False,$Word_Flag,True) === False){//False is Not Found -> All Word Found
								$item_list[] = $item;
								$ent_no_list[] = $item['ent_no'];
								$up_date_list[] = $item['up_date'];
							}
						}
					}
				}
				if(count($item_list) <= 0){Html::Error("エラー","投稿が見つかりませんでした。");}
			}else{$item_list = $data;}
		}
		$return = array($item_list,count($item_list),$ent_no_list,$up_date_list);
		return $return;
	}

//親記事-経過日数
	public static function Daily_Check(){
		global $post_set,$data_file;
		$del_no_list = array();
		list($Item,) = Ctrl::Get_Item();
		
		if(is_array($Item)){
			foreach ($Item as $no=>$line){
				if($line){
					//文字列の日付をタイムスタンプに
					$day = date_parse_from_format("Y/m/d(w) H:i",$Item[$no]['date']); 
					$day = mktime($day['hour'],$day['minute'],0,$day['month'],$day['day'],$day['year']);
					
					$flag = ($day + 24 * 3600 * $post_set['daily_del']) - time();
					if($flag  < 0){
						$del_no_list[] = $Item[$no]['ent_no'];
						unset($Item[$no]);//削除対象ログの削除
						unset($Ent_no_list[$no]);
						unset($Up_date_list[$no]);
					}
				}
			}
		}
		if(count($del_no_list) > 0){
			Files::Save($data_file['data'],$Item,"log");
			//不要な画像の削除
			foreach ($del_no_list as $parent_no){Ctrl::Del_Dir($post_set['upload']['dir']."/".$parent_no);}//Delete Directory
		}

		return;
	}

//Tag Split
	public static function Tag_Adjust($Tag){
		$tag_list = array();
		if(!is_array($Tag)){
			$Tag = mb_ereg_replace("　"," ",$Tag);
			$Tag = explode(" ",$Tag);
		}
		if(is_array($Tag)){
			foreach($Tag as $line){
				if($line){
					$tag_list[] = $line;
				}
			}
		}
		return $tag_list;
	}

}
//Gallery Board - www.tenskystar.net
?>