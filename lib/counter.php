<?php
$lib[] = "Gallery Board - Counter Ver:1.3";
/*
- 更新ログ -
 v1.3 22/02/03 php8に対応。
 v1.2 18/10/05 最初のPVカウントやGoodカウント時のWarningエラーを修正。
  
- カウンタ -
 PV_Counter
  + PV_Up - PVカウント用
  
 Good_Counter
  + Good_Up - Goodカウント用

 Counter
  -+ Count_Up - カウント処理

 Counter_Files
  -+ Load_Count - カウントデータ読み込み
  -+ Save_Count - カウントデータ保存
*/
$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 PV Counter Control Script Error!</div></html>";exit;}

class PV_Counter extends Counter{
//Preview Count
	public static function PV_Up($Entry_No,$Now_Count = 0){
		global $post_set,$data_file,$count_set;
		//Count Data File
		$pvc_file['dir'] = getcwd().$post_set['upload']['dir']."/".$Entry_No;
		$pvc_file['path'] = $pvc_file['dir']."/".$data_file['pvc'];
		//Counter Setting
		$counter_set = $count_set;
		$counter_set['data_file'] = $pvc_file['path'];
		//Count
		$cnt_data = Counter::Count_Up("pv",$counter_set,$Now_Count);
		if($cnt_data === -1){Error_Page::Main("エラー","ページビュー カウント用のデータファイルが作成できません。");}
		return $cnt_data['info']['sum'];
	}
}

class Good_Counter extends Counter{
//Good Count
	public static function Good_Up($Item,$No){
		global $F,$lock,$good_set,$post_set,$count_set,$data_file;
		
		list($parent_no,$child_no) = explode("-",$No);
		if(!$good_set['sw']){Error_Page::Main("エラー","Good機能は有効ではありません。");}
		elseif($good_set['sw'] == 1 and $child_no !== "0"){Error_Page::Main("エラー","レスにGoodする機能は有効ではありません。");}
		elseif(!$Item['img']['file']){Error_Page::Main("エラー","この記事にはGood出来ません。");}
		elseif(!is_numeric($F['prevno']) or !preg_match("/^[0-9]+-[0-9]+$/",$No)){Error_Page::Main("エラー","Goodリクエストが不正です。");}
		elseif(!$Item){Error_Page::Main("エラー","Goodする記事が特定できません。");}
		elseif($F['prevno'] != $parent_no or $F['prevno'] != $Item['ent_no'] or $Item['ent_no'] != $parent_no){Error_Page::Main("エラー","Goodする記事が一致していません。");}
		else{
			if($child_no !== "0"){
				$child_flag = false;
				foreach($Item['res'] as $res_item){
					if($res_item['ent_no'] === $child_no){
						if($good_set['sw'] == 2 and !$res_item['img']['file']){Error_Page::Main("エラー","このレスにはGood出来ません。");}
						else{
							$child_flag = true; break;
						}
					}
				}
				if(!$child_flag){Error_Page::Main("エラー","Goodするレスが見つかりません。");}
			}

			//Count Data File
			$good_file['dir'] = getcwd().$post_set['upload']['dir']."/".$parent_no;
			$good_file['path'] = $good_file['dir']."/".$child_no.$data_file['good'];
			//Counter Setting
			$counter_set = $good_set;
			$counter_set['robot'] = "";
			$counter_set['robot_sw'] = 0;
			$counter_set['data_file'] = $good_file['path'];
			//Count
			$cnt_data = Counter::Count_Up("good",$counter_set);
			if($cnt_data === -1){Error_Page::Main("エラー","Goodカウント用のデータファイルが作成できません。");}
			return $cnt_data['info']['sum'];
		}
		return 0;
	}
}

class Counter{
//Count UP
	protected static function Count_Up($Type,$Counter_Set,$Now_Count = 0){
		//Counter_Set Key Memo ('ip_cehck','robot_sw','robot','not_count','lock','lock_file','data_file')
		//True Return Value  $cnt_data = array(0=>array('info'=>array('sum','to','yes','ip','stamp'),'user'=>array(array('ip','cnt','date','robot'))),1=>array(ip_list))
		//False Return Value  -1 = File Make Error,
		global $ip,$agent,$caria,$data_file;
		if(!$Counter_Set['data_file']){return -1;}
		if(!is_numeric($Now_Count)){$Now_Count = 0;}

		date_default_timezone_set('Asia/Tokyo');//Time Zon Set
		if($Counter_Set['ip_check'] and !$ip['addr']){return;}
		
		//Search Robot
		$robot_flag = False;
		if($Counter_Set['robot']){
			$robot_pm = join("|",$Counter_Set['robot']);
			if(preg_match("/(".$robot_pm.")/",$ip['host']) or preg_match("/(".$robot_pm.")/",$ip['addr'])){$robot_flag = True;};
		}
		//Not Count IP 
		$no_count_flag = False;
		if($Counter_Set['not_count']){
			$not_pm = join("|",$Counter_Set['not_count']);
			if(preg_match("/(".$not_pm.")/",$ip['host']) or preg_match("/(".$not_pm.")/",$ip['addr'])){$no_count_flag = True;};
		}

		if(!file_exists(dirname($Counter_Set['data_file']))){return -1;}//Not Directory
		//CountFile Not Found => Make
		if(!file_exists($Counter_Set['data_file'])){
			if(touch($Counter_Set['data_file'])){
				umask(0);
				chmod($Counter_Set['data_file'],0666);
			}else{return -1;}
		}

		//Load
		if($Counter_Set['lock']){
			$lock_flag = False;
			$lock_flag = Files::Lock(dirname($Counter_Set['data_file'])."/".$Counter_Set['lock_file'],$lock_flag,"EX");
		}
		list($cnt_data,$ip_list) = Files::Load($Counter_Set['data_file'],$Type);

		if(!isset($cnt_data['info']['to']) or !is_numeric($cnt_data['info']['to'])){$cnt_data['info']['to'] = 0;}
		if(!isset($cnt_data['info']['yes']) or !is_numeric($cnt_data['info']['yes'])){$cnt_data['info']['yes'] = 0;}
		if(!isset($cnt_data['info']['sum']) or !is_numeric($cnt_data['info']['sum'])){$cnt_data['info']['sum'] = $Now_Count;}
		if(!isset($cnt_data['info']['stamp']) or !is_numeric($cnt_data['info']['stamp'])){$cnt_data['info']['stamp'] = time();}

		//IP - Log Check
		$ip_flag = false;
		if($Counter_Set['ip_check']){
			$last_up = date("d",$cnt_data['info']['stamp']);// stamp - Day
			$now_day = date("d",time());//Now - Day
		
			//Today? And ListCount > 0?	
			if($now_day == $last_up and count($ip_list) > 0){
				$ip_flag = array_search($ip['addr'],$ip_list);
				if($ip_flag !== False){
					$cnt_data['user'][$ip_flag]['cnt']++;
					$cnt_data['user'][$ip_flag]['date'] = date("Y-n-j G:i:s");
				}
			}
			
			if($ip_flag === False){$cnt_data['user'][] = array('ip'=>$ip['addr'],'cnt'=>1,'date'=>date("Y-n-j G:i:s"),'robot'=>$robot_flag,'caria'=>$caria);}
		}
	
		//Count Data Write Flag Change
		if(!$Counter_Set['robot_sw'] and $robot_flag){$ip_flag = True;}
		if($no_count_flag){$ip_flag = True;}

		$count_flag = 0;
		//データ書き換え
		$Counter_Set['count'] = 1;
		if($ip_flag === False){
			if(! ($Counter_Set['count'] && ($ip['addr'] == $cnt_data['info']['ip']) )){
				$t_y_flag = 0;//日付が変わった? Yesterday Image Refresh Flag
	
				$cnt_data['info']['sum']++;
				if(date("d",time()) == date("d", $cnt_data['info']['stamp'])){ $cnt_data['info']['to']++;}//当日
				else{//次の日
					$cnt_data['info']['yes'] = $cnt_data['info']['to'];
					$cnt_data['info']['to'] = 1;
					$t_y_flag = 1;
					$cnt_data['info']['stamp'] = mktime(0,0,0,date("n",time()),date("j",time()),date("Y",time()));
					$cnt_data['user'] = array($cnt_data['user'][(count($cnt_data['user'])-1)]);
				}
				$count_flag = 1;
			}
		}

		Files::Save($Counter_Set['data_file'],$cnt_data,$Type);//Save

		if($Counter_Set['lock']){$lock_flag = Files::Lock(dirname($Counter_Set['data_file'])."/".$Counter_Set['lock_file'],$lock_flag,"UN");}
		
		return $cnt_data;

	}
}

class Counter_Files extends Files{
//読込
	protected static function Load_Count ($Data){
		$ip_list = array();
		if(is_array($Data)){
			$user_list = array();
		
			$cnt_data = array_shift($Data);
			if(!$cnt_data){$cnt_data = "0,0,0,0.0.0.0,0000000000";}
			$cnt_data = preg_replace("/[\r\n]/","",$cnt_data);
			$count = array('info'=>array(),'user'=>array());
			list($count['info']['sum'],$count['info']['to'],$count['info']['yes'],$count['info']['ip'],$count['info']['stamp']) = explode(",",$cnt_data);
			if(is_array($Data)){
				foreach($Data as $key=>$line){
					$line = preg_replace("/[\r\n]/","",$line);
					if($line){
						$user = array();
						list($user['ip'],$user['cnt'],$user['date'],$user['robot']) = explode("<>",$line);
						$count['user'][$key] = $user; 
						$ip_list[$key] = $user['ip'];
					}else{unset($Data[$key]);}
				}
			}
		}
		return array($count,$ip_list);
	}
 
//書込
	protected static function Save_Count($Data){
		global $count_set;
		if(is_array($Data['info'])){
			$Data['info'] = array($Data['info']['sum'],$Data['info']['to'],$Data['info']['yes'],$Data['info']['ip'],$Data['info']['stamp']);
			$Data['info'] = join(",",$Data['info']);
			if(is_array($Data['user'])){
				foreach($Data['user'] as $key=>$user){
					if($user['ip']){
						$user = array($user['ip'],$user['cnt'],$user['date'],$user['robot']);
						$user = join("<>",$user);
						$Data['user'][$key] = $user;
					}else{unset($Data['user'][$key]);}
				}
				if(is_array($Data['user'])){$Data['user'] = join("\r\n",$Data['user']);}
			}
		}else{unset($Data);}
		if(is_array($Data)){$Data = join("\r\n",$Data);}
		
		return $Data;
	}
}

//Gallery Board - www.tenskystar.net
?>
