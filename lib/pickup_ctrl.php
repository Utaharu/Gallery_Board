<?php
$lib[] = "Gallery Board - PickUp Control Ver:1.2";
/*
- 更新ログ -
 v1.2 22/02/07 php8対応
 v1.1 17/01/28 ソート調整,No順、up_date順。
  
- コントロール -
 PickUP
  - Per - 確率用
  + View - PickUP出力
*/
$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 RSS Control Script Error!</div></html>";exit;}

class PickUP{
	
	//確率から、キーを返す
	private static function Per($per_log_list){
		global $pickup_set;
		
		$rnd = mt_rand(1,100);
		$min = 0;
		$max = 0;
		if(is_array($pickup_set['list'])){
			foreach($pickup_set['list'] as $key => $ritu){
				if(isset($ritu['per'])){
					
					$min =  $max;
					$max += $ritu['per'];
					if($rnd > $min and $rnd <= $max){
						if(count($per_log_list[$key])){return $key;}
						else{
							for($cnt = ($key+1); $cnt < count($per_log_list); $cnt++){
								if(count($per_log_list[$cnt])){
									if($pickup_set['list'][$cnt]['per']){
										$rnd = mt_rand(1,100);
										if($rnd <= $pickup_set['list'][$cnt]['per']){return $cnt;}
									}
								}
							}
						}
						break;
					}
				}
			}
			 return count($pickup_set['list']);
		}
		return 0;
	}

	public static function View(){
		global $F,$php,$data_file,$pickup_set,$post_set,$code_set,$count_set,$lock,$lock_fr;
			
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"SH");
			if($F['category'] and $post_set['category']['sw']){$get_type = "Search";$get_key=$F['category'];}
			else{$get_type = "All";$get_key = "";}
			list($data,,$ent_no_list,$up_date_list) = Common::Get_Item($get_type,$get_key);
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		
		$tmp = Files::Load($data_file['pickup_tmp'],"all");

		//データを確率ごとの配列に格納
		mb_regex_encoding($code_set['system']);
		$per_log_list = array();
		if(is_array($data)){
			if(count($data) > 0 and is_array($pickup_set['list'])){
				array_multisort($up_date_list,SORT_DESC,SORT_NUMERIC,$data);//Sort
				foreach($data as $item){
					mb_ereg("([0-9]+)/([0-9]{2})/([0-9]{2}).*?([0-9]+?):([0-9]+?)",$item['date'],$match);//文字列型の日付から数値を抽出
					
					$day_flag = (time() - mktime($match[4],$match[5],0,$match[2],$match[3],$match[1])) / (24 * 60 * 60); //投稿日からの経過日数
					$add_flag = false;
					//データを確率毎の配列に振り分ける
						foreach($pickup_set['list'] as $key => $ritu){
							if(isset($ritu['day']) and $day_flag <= $ritu['day']){$per_log_list[$key][] = $item; $add_flag = true;}
						}
						if(!$add_flag){$per_log_list[(count($pickup_set['list']))][] = $item; $add_flag=true;}
				}
				
				$data  = array();
				for($cnt = 0; $cnt < $pickup_set['num']; $cnt++){
					$list_key = PickUP::Per($per_log_list);//確率毎の配列の中のどれから引くかキーを求める
				
					if(count($per_log_list[$list_key])){
						//確率毎の配列の中のアイテムの中から更にランダムでアイテムを決める
						$rnd = range(0,(count($per_log_list[$list_key])-1));
						shuffle($rnd);
						$data[] = $per_log_list[$list_key][$rnd[0]];
						unset($per_log_list[$list_key][$rnd[0]]);
						$per_log_list[$list_key] = array_merge($per_log_list[$list_key]);//Keyを連番に振りなおす
					}
				}
			}
		}

		$print_num = 0;
		if(count($data) > $pickup_set['num']){$print_num = $pickup_set['num'];}
		elseif(count($data) == 0){$print_num = 1;}
		else{$print_num = count($data);}
		print ListView_Page::Main($tmp,$data,1,$print_num,1);

		return;
	}
}

//Gallery Board - www.tenskystar.net
?>