<?php
$lib[] = "Gallery Board - Access Control Ver:1.1";
/*
- 更新ログ -
 v1.1 23/06/15 エラー修正。php8対応
 v1.0 19/11/26 

- コントロール -
 Access
  + Get_IP - IPアドレスの取得
  + Get_Caria - 機種判別 PC or Mobile
  + Referer_Check - リファラ規制
  + Proxy_Check - PROXY規制
  + Forced_Check - IP規制
  + Tor_Check - Tor規制
  + Country_Check - 国規制
  
 CC_Filter
  - Get_Update - IP割り当てリストの取得用
  +- Check - IP割り当てリストから国判別
*/
$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Access Control Script Error!</div></html>";exit;}

class Access{
/*
	private $Access_set = null;
	private $GB = null;

	function __construct(){
		global $GB;
		$this->GB = &$GB;
		$this->
		$this->GB->Html->Error;
	}
*/

	public static function Get_IP(){
		//$ip['host'] = HostName,$ip['addr'] = IpAddress
		//IP取得
		$ip = array();
		$ip['host'] = getenv('REMOTE_HOST');
		$ip['addr'] = getenv('REMOTE_ADDR');
		//代入チェック
		if (!$ip['host'] or $ip['host'] == $ip['addr']){$ip['host'] = gethostbyaddr($ip['addr']);}
		if (!$ip['addr'] or $ip['host'] == $ip['addr']){$ip['addr'] = gethostbyname($ip['host']);}
		if (!$ip['host']){$ip['host'] = $ip['addr'];}
		if (!$ip['addr']){$ip['addr'] = $ip['host'];}
		
		return array("host"=>$ip['host'],"addr"=>$ip['addr']);
	}
	
	public static function Get_Caria($Addr = "", $Host = "" , $Agent = ""){
		global $access_set;

		if(!$Agent){$Agent = getenv('HTTP_USER_AGENT');}
		if(!$Addr or !$Host){
			$ip = Access::Get_IP();
			$Addr = $ip['addr'];
			$Host = $ip['host'];
		}
		
		//機種:キャリア 判別
		$caria = "pc";
		foreach ($access_set['caria']['host'] as $caria_set) {
			//IP
			list($caria_ip,$caria_type) = explode(":",$caria_set);
			if($Host and 0 < preg_match("/" . $caria_ip . "/",$Host)){$caria = $caria_type; break;}
			if($Addr and 0 < preg_match("/" . $caria_ip . "/",$Addr)){$caria = $caria_type; break;}
		}
		if($caria == "pc"){
			//Agent
			foreach($access_set['caria']['agent'] as $caria_type){
				list($caria_agent,$caria_type) = explode(":",$caria_type);
				if(preg_match("/".$caria_agent."/",$Agent)){$caria = $caria_type; break;}
			}
		}
		return $caria;
	}
	
//リファラ規制
	public static function Referer_Check($Referer = ""){
		global $access_set;
		if(!$Referer){$Referer = getenv('HTTP_REFERER');}
		
		if(!getenv('HTTPS') and strtolower(getenv('HTTPS')) != 'off'){$script = "https";}
		else{$script = "http";}
		
		$script .= "://" . getenv('SERVER_NAME') . getenv('SCRIPT_NAME');
		if($access_set['referer']['not_sw'] and !$Referer){Error_Page::Main('取得エラー','リファラーが取得出来ませんでした。');}
		if(is_array($access_set['referer']['url'])){
			$referer_flag = False;
			$eflag = False;
			foreach ($access_set['referer']['url'] as $one){
				if($one){
					//パターンマッチ検索（正規表現)
					$cnt = preg_match("/".$one."/",$Referer);
					if(1 > $cnt && $Referer != $access_set['referer']['url'] && $Referer != $script && !$eflag){$referer_flag = 1;}
					elseif(1 <= $cnt){$referer_flag = 0; $eflag = 1;}
				}
			}
			if($referer_flag){Error_Page::Main('アクセス禁止',"管理者が指定したアドレス以外からのアクセスを禁止しています。\n");}
		}
		return;
	}
	
//PROXY制限
	public static function Proxy_Check($Addr,$Host){
		global $access_set;
		if($access_set['proxy']['sw']){
			$proxy_flag = False;
			
//			if(getenv('HTTP_X_FORWARDED_FOR')){$proxy_flag = 1;}
			if(getenv('HTTP_CLIENT_IP')){$proxy_flag = 2;}
			if(getenv('HTTP_SP_HOST')){$proxy_flag = 3;}
			if(getenv('HTTP_VIA')){$proxy_flag = 4;}
			if(getenv('HTTP_CACHE_INFO')){$proxy_flag = 5;}
			if(preg_match("/(prox|squid|cache|gate|firewall|webwarper|torproject|tor-exit|anonymizer)/",$Host)){$proxy_flag = 6;}
			if(preg_match("/(via|proxy|gate)/",getenv('HTTP_USER_AGENT'))){$proxy_flag = 7;}
			if(preg_match("/no-cache/",getenv('HTTP_PRAGMA'))){$proxy_flag = 8;}
//			if(!getenv('HTTP_ACCEPT_ENCODING')){$proxy_flag = 9;}
			//if(preg_match("/close/",getenv('HTTP_CONNECTION'))){$proxy_flag = 10;}
	
			//強制許可
			if(is_array($access_set['proxy']['skip'])){
				foreach ($access_set['proxy']['skip'] as $line) {
					if($line){
						$host_match = preg_match("/" . $line . "/",$Host);
						$addr_match = preg_match("/" . $line . "/",$Addr);
						if ($Host == $line  or $Addr == $line or $host_match > 0 or $addr_match > 0){$proxy_flag = 0;}
					}
				}
			}
			if ($proxy_flag){Error_Page::Main("アクセス禁止",'PROXY経由でのアクセスは禁止されています。'.$proxy_flag );}
		}
		return;
	}
	
//強制制限
	public static function Forced_Check($Addr,$Host){
		global $access_set;
		$limit_flag = array('sw'=>1,'now_stamp'=>time(),'limit_stamp'=>0,'host'=>0,'addr'=>0);
		if(is_array($access_set['ip']['list'])){
			foreach ($access_set['ip']['list'] as $line){
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
					$limit_flag['host'] = preg_match("/". $line['ip'] ."/",$Host);
					$limit_flag['addr'] = preg_match("/". $line['ip'] ."/",$Addr);

					if($access_set['ip']['sw']){
						if(($Host == $line['ip'] or $Addr == $line['ip'] or $limit_flag['addr'] > 0 or $limit_flag['host'] > 0) and $limit_flag['limit_stamp'] >= $limit_flag['now_stamp']){
							Error_Page::Main('アクセス禁止',"ホストは管理者によってアクセスを禁止しております。");
						}
					}elseif(!$access_set['ip']['sw']){
						if(($Host == $line['ip'] or $Addr == $line['ip'] or $limit_flag['addr'] > 0 or $limit_flag['host'] > 0) and $limit_flag['limit_stamp'] >= $limit_flag['now_stamp']){$limit_flag['sw'] = 0;}
					}else{Error_Page::Main("設定エラー","アクセス禁止方式の設定値が異常です。");}
				}
			}
		}
		if($limit_flag['sw'] and !$access_set['ip']['sw']){Error_Page::Main("アクセス禁止","ホストは管理者によってアクセスを許可しておりません。");}
		return;
	}

//Tor - PROXY制限
	public static function Tor_Check($Addr){
		global $access_set;
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
				$fno = 0;
				foreach ($access_set['tor']['ip_list'] as $tor_get_file){
					$file_sorce = file_get_contents($tor_get_file);
					$match_cnt = preg_match_all("/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/",$file_sorce,$tor_match,PREG_PATTERN_ORDER);
					if($match_cnt > 0){
						$tor_match = join("\n",$tor_match[0]);
						$fs = fopen($access_set['tor']['data'][$fno],"w+");
						$wflag = fwrite($fs,$tor_match);//Write
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
						$addr_pt = preg_replace("/\./","\.",$Addr);
						preg_match_all("/(".$addr_pt.")\n/",$tor_data,$tor_match);
						if(is_array($tor_match[1])){$tor_flag = count($tor_match[1]);}
						if($tor_flag > 0){$tor_flag = 1;}	
						else{$tor_flag = 0;}
					}
				}else{continue;}
			}
		
			if($tor_flag){Error_Page::Main("アクセス禁止",'Tor-PROXY 経由でのアクセスは禁止されています。');}
		}
		return;
	}

//国規制
	public static function Country_Check($Addr){
		global $ip_filter,$access_set;
			CC_Filter::Check($Addr);
		return;
	}
}

class CC_Filter extends Access{

//IP割り当てリストを取得
	private static function Get_Update(){
		global $ip_filter,$access_set;
		$lock_file = "filter.lock";
		$ipfilter_fr = Files::Lock($lock_file,$ipfilter_fr, "EX");
		//データの更新
			foreach ($access_set['cc']['up_time'] as $up_time){
				//現在の時刻(Sec)
					$now_time = intval(date("H")) * 3600 + intval(date("i")) * 60 + intval(date("s"));
				//今日の日付(timestamp)
					$to_date = time();
					$to_day = getdate($to_date);
					$to_day = mktime(0,0,0,$to_day['mon'],$to_day['mday'],$to_day['year']);

				foreach($ip_filter['ipinfo'] as $ip_type){
					//最終更新日(timestamp)
						$last_up_date = 0;
						if(file_exists($ip_type['save_file'])){
							$last_up_date = filemtime($ip_type['save_file']);
							if(filesize($ip_type['save_file']) == 0){$last_up_date = 0;}
						}
						$last_up_day = getdate($last_up_date);
						$last_up_day = mktime(0,0,0,$last_up_day['mon'],$last_up_day['mday'],$last_up_day['year']);

					//更新予定 時刻(sec)
						$set_time = explode(":",$up_time);
						$set_time = intval($set_time[0]) * 3600 + intval($set_time[1]) * 60 + intval($set_time[2]);

					//更新予定日(timestamp 
						$next_up_day = $last_up_day + ($access_set['cc']['up_day'] * (24 * 3600));
						
						$next_up_date = $next_up_day + $set_time;
						if($next_up_day <= $to_day){$next_up_date = $to_day + $set_time;}//更新日過ぎている -> 今日を更新日時に
						else{$next_up_date = $next_up_day + $set_time;}
						
					//更新日時確認
					if($next_up_date and $next_up_date <= $to_date and $last_up_date < $next_up_date){
					
						$ipr_file_ext = pathinfo($ip_type['base_file'],PATHINFO_EXTENSION);//URLの拡張子取得
						$ip_range_data = false;
						
						if($ipr_file_ext == "gz" and function_exists('gzdecode') == False){$file_data = $ip_type['base_file'];}//gzdecodeが使えない場合はgzfileを解凍処理時点で行う
						elseif($access_set['cc']['curl_sw'] and function_exists('curl_init')){
							$curl = curl_init();
							curl_setopt($curl, CURLOPT_URL,$ip_type['base_file']);
							curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
							curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
							curl_setopt($curl,CURLOPT_TIMEOUT,60);
							$file_data = curl_exec($curl); 
							curl_close($curl);
						}else{$file_data = file_get_contents($ip_type['base_file']);}
						
						//解凍
						switch($ipr_file_ext){
							case "gz":
								if(function_exists('gzdecode') == True){$ip_range_data  = gzdecode($file_data);}
								else{$ip_range_data = gzfile($file_data);}//gz - load
								break;
							case "bz2":
								$ip_range_data = bzdecompress($file_data);
								break;
							default:
								$ip_range_data = $file_data;
						}
						if(!is_array($ip_range_data)){$ip_range_data = explode("\n",$ip_range_data);}
						
						//コンバート データを生成
						$c_code = "JP";
						if($ip_range_data){
							$ip_range_data = preg_grep("/^" . $c_code .".+?/",$ip_range_data);//指定国のリストを生成

							$conv_ip = array();
							foreach($ip_range_data as $line){
								$line = preg_replace("/[\r\n]/","",$line);
								if(preg_match("/([" . $ip_type['digit_pm'] . $ip_type['split'] ."]+)\/([0-9]+)$/",$line,$match_cap)){//IP部とCidr部を分解取得
									$ip_part = $match_cap[1];//IP部
									$cidr = $match_cap[2];//Cidr部
									
									//IPの各桁値を分解取得 $glp[1][]=各桁の数値のみ
									preg_match_all("/[" . preg_replace("/\./","\.",$ip_type['split']) . "]?([" . $ip_type['digit_pm'] . "]+)/",$ip_part,$match_cap,PREG_PATTERN_ORDER);
									$ip_digit = array_pad($match_cap[1],$ip_type['unit'],0);//省略分を埋める為に、桁合せ
									//v6用 16進数 -> 10進数 変換
									if($ip_type == $ip_filter['ipinfo']['v6'] and count($ip_digit)){
										foreach($ip_digit as $key=>$one){$ip_digit[$key] = hexdec($one);}
									}
									//範囲計算
									$ip_cnt = ($ip_type['unit']-1);
									$ip_ts = str_pad($ip_ts,$ip_type['bit'] - $cidr,"1");//Not SubnetMask Bit
									$ts_len = strlen($ip_ts);//Not SubnetMask Bit - Length
									$unit_bit = round($ip_type['bit'] / $ip_type['unit']);
									
									do{
										if($ts_len >= $unit_bit){
											$ip_digit[$ip_cnt] .= "-". (intval($ip_digit[$ip_cnt]) + bindec(substr($ip_ts,$ts_len - $unit_bit)));
											$ip_ts = substr($ip_ts,0,$ts_len - $unit_bit);
										}else{
											$ip_digit[$ip_cnt] .= "-". (intval($ip_digit[$ip_cnt]) + bindec(substr($ip_ts,0)));
											$ip_ts = "";
										}
										$ts_len = strlen($ip_ts);
										$ip_cnt--;
									}while($ts_len > 0);
									$conv_ip[] = join($ip_type['split'],$ip_digit);
								}
							}
							if(count($conv_ip) > 0){$conv_ip = join("\r\n",$conv_ip);}

							//保存 必不 確認
							if(file_exists($ip_type['save_file'])){$now_data = file_get_contents($ip_type['save_file']);}//now_data - load
							if($now_data !== false and $conv_ip and $now_data !== $conv_ip){
								if($now_data != $conv_ip){
									$fp = fopen($ip_type['save_file'],"w");
									if($fp){
										$flag = fputs($fp,$conv_ip);//new_data - save
										fclose($fp);
										clearstatcache();//ファイル情報 キャッシュの削除
										chmod($ip_type['save_file'],0666);
									}						
								}
							}else{touch($ip_type['save_file']);}
						}
					}
				}
			}
		Files::Lock($lock_file,$ipfilter_fr, "UN");
		if(file_exists($lock_file)){unlink($lock_file);}
	
	 return;
	}

//国規制 - IPから国を判別して規制
	//option = $ip_filterがglobal呼び出しできない場合にcheck($addr,$ip_filter)で呼び出す。
	// require中のファイルからのip-fil呼び出しの場合など。
	protected static function Check($addr,$option = ""){
		global $ip_filter,$access_set;

		if($access_set['cc']['sw']){
			CC_Filter::Get_Update();
			
			if(!$ip_filter and $option != ""){$ip_filter = $option;}
			if(!$ip_filter){$erv = "(呼び出しパラメータ不足)";}
			
			//IP取得
			if(!$host){$host = getenv('REMOTE_HOST');}
			if(!$addr){$addr = getenv('REMOTE_ADDR');}
			if (!$host or $host == $addr){$host = gethostbyaddr($addr);}
			if (!$addr or $host == $addr){$addr = gethostbyname($host);}
			if (!$host){$host = $addr;}
			if (!$addr){$addr = $host;}

			//Ip - Version
			$ip_ver = "";
			if(filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) and $ip_filter['ipinfo']['v4']['base_file']){$ip_type = $ip_filter['ipinfo']['v4'];}
			elseif(filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) and $ip_filter['ipinfo']['v6']['base_file']){$ip_type = $ip_filter['ipinfo']['v6'];}
			else{Error_Page::Main('501 Not Implemented',"<div>Not Implemented</div> IPのバージョンが判別出来ませんでした。(Ip:$addr)</div>\n<div>管理者へ連絡してください。$erv</div>\n");}

			//Range List File - Load
			$list = file_get_contents($ip_type['save_file']);
			if($list === False){print "<html><head><title>エラー</title></head><div style=\"color:red;\">データの読み込みに失敗しました。 by.".basename(__FILE__)."</div>"; exit;}
			$list = explode("\r\n",$list);

			//IP Unit Split
			$ips = explode($ip_type['split'],$addr);
			$ips = array_pad($ips,$ip_type['unit'],0);
			//ipv6 ip - 16->10
			if($ip_type == $ip_filter['ipinfo']['v6'] and count($ips)){
				foreach($ips as $key => $one){$ips[$key] = hexdec($one);}
			}
			
			for($cnt=0; $cnt < $ip_type['unit']; $cnt++){
				$lim_one_list = array();
				
				//範囲指定の無いデータで一致するアイテムを取得
				$search = preg_grep("/^(?:.+" . preg_replace("/\./","\.",$ip_type['split']) ."){" . $cnt ."}".$ips[$cnt]."(?:" . preg_replace("/\./","\.",$ip_type['split']) . ".+){" . ($ip_type['unit'] - ($cnt+1)) . "}/",$list);
				$lim_one_list = $search;
				
				//範囲指定の有るデータで一致するアイテムを取得
				$pm = "/^(?:.+" . preg_replace("/\./","\.",$ip_type['split']) ."){" . $cnt ."}"."([0-9]+)-([0-9]+)(?:" . preg_replace("/\./","\.",$ip_type['split']) . ".+){" . ($ip_type['unit'] - ($cnt+1)) . "}/";
				$search = preg_grep($pm,$list);

				foreach ($search as $one){
					if(preg_match($pm,$one,$glp)){
						if($glp[1] <= $ips[$cnt] and $glp[2] >= $ips[$cnt]){array_push($lim_one_list,$one);}
					}
				}
				$list = $lim_one_list;
				if(count($list) == 0){break;}
			}
			
			// False = 許可 (指定国から）True = 拒否（指定国以外）
			$flag = False;
			if(count($list)){$flag = False;}
			else{$flag = True;}

			//手動 制限
			if($access_set['cc']['limit'] and is_array($access_set['cc']['limit'])){
				foreach($access_set['cc']['limit'] as $one){
					if($one){
						if($host and preg_match("/".$one."/",$host)){$flag = True;}
						elseif(preg_match("/".$one."/",$addr)){$flag = True;}
					}
				}
			}
			//制限 除外
			if($access_set['cc']['skip'] and is_array($access_set['cc']['skip'])){
				foreach($access_set['cc']['skip'] as $one){
					if($one){
						if($host and preg_match("/".$one."/",$host)){$flag = False;}
						elseif(preg_match("/".$one."/",$addr)){$flag = False;}
					}
				}
			}
			if($flag){Error_Page::Main("アクセス禁止","日本以外からのアクセスは禁止しております。");}
		}
		return;
	}
}
//Gallery Board - www.tenskystar.net
?>