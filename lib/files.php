<?php
$lib[] = "Gallery Board - Data File Control Ver:1.4";
/* 
- 更新ログ -
　v1.4 22/02/03 php8対応。
 v1.3 19/06/12 Tag用の調整。

- データファイル 操作 -
 Files
  + Load - 読み込み
  + Save - 保存
  + Lock - ファイルロック
  - Load_Log - ログ読み込み
  - Save_Log - ログ保存
*/
$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Files Script Error!</div></html>";exit;}

class Files{

//読込
	public static function Load($File,$Type){
		global $data_file,$code_set;
		if($Type == "all"){
			return file_get_contents($File);
		}else{
			$rdata = file($File);//一行取り込み
			if($rdata === False){Error_Page::Main("エラー","データの読み込みに失敗しました。");}
			
			if($File == $data_file['data']){
				return  Files::Load_Log($rdata);
			}elseif((basename($File) == basename($data_file['pvc']) and $Type == "pv") or $Type == "good"){
				if(!method_exists('Counter_Files','Load_Count')){Error_Page::Main("システム エラー","カウンター用スクリプトが読み込まれていません。");}
				return  Counter_Files::Load_Count($rdata);
			}else{
				return  $rdata;
			}
		}
		return;
	}

//書込
	public static function Save($File,$Data,$Type = "String"){
		global $data_file,$lock;
//		$new_line = array(ent_no=>$ent_no,img=>$upname,entry=>$F[entry],msg=>$F['msg'],color=>$F[color],name=>$name,pass=>$F['pass'],res=>"",'date'=>$date,ip=>$host,res_cnt=>0,up_date=>$times);

		$fr = fopen($File,"w");
		if(!$fr){Error_Page::Main("エラー","ファイルが開けませんでした。");}
		else{
			if($lock['sw']){flock($fr, LOCK_EX);}
			rewind($fr);
			//整形
			if($Type == "log" and $File == $data_file['data']){
				$Data = Files::Save_Log($Data);
			}elseif((basename($File) == basename($data_file['pvc']) and $Type == "pv") or $Type == "good"){
				if(!method_exists('Counter_Files','Save_Count')){Error_Page::Main("システム エラー","カウンター用スクリプトが読み込まれていません。");}
				$Data = Counter_Files::Save_Count($Data);
			}elseif(is_array($Data)){Error_Page::Main("エラー","データ整形に失敗しました。");}
			
			$bytes = fwrite($fr,$Data);//書き込み
			fflush($fr); 
			if($lock['sw']){flock($fr, LOCK_UN);}
			fclose($fr);
			if($Data and $bytes == False){Error_Page::Main("エラー","データの書き込み失敗しました。");}
		}
		return;
	}

	public static function Lock($Lock_File,$Lock_Fr,$Type){
		global $lock;
	     if(!$lock['sw']){return;}
	    $flag = 10;
    	if($lock['sw']) {
			if(!$Lock_Fr){
				$Lock_Fr = fopen($Lock_File,"a");
				if($Lock_Fr === false){Error_Page::Main("エラー","LockFileが開けません");}
			}
			
			if($Type == "SH"){$l_type = 1;}//共有
			elseif($Type == "EX"){$l_type = 2;}//占有
			elseif($Type == "UN"){$l_type = 3;}//解除
			$lock_flag = flock($Lock_Fr,$l_type|LOCK_NB);
			if($Type == "UN" and $lock_flag){fclose($Lock_Fr);}
			if($lock_flag === False){Error_Page::Main("エラー","ファイルロックができませんでした。");}
			
			if($Type == "UN"){$Lock_Fr = "";}
		}
		return $Lock_Fr;
	}
	
	private static function Load_Log($Data){
		//多次元配列に整形
		$ent_no_list = array();
		$up_date_list =  array();
		if(count($Data)){
			$data_key = 0;
			foreach($Data as $line){
				if($line){
					$line = preg_replace("/[\r\n]/","",$line);
					$item = explode("<>",$line);//Parentを各値に分解
					if($item[1]){//ImgFile
						$item[1] = explode("@",$item[1]);
					}
					//Parent Tag
					$item[16] = Common::Tag_Adjust($item[16]);
					
					//Res
					if($item[7]){
						$item[7] = explode("<->",$item[7]);//1Resに分解
						if(count($item[7])){
							$res_key = 0;
							foreach($item[7] as $res_line){
								if($res_line){
									$res_data = explode("<_>",$res_line);//Resを各値に分解
									if($res_data[5]){//ImgFile
										$res_data[5] = explode("@",$res_data[5]);
									}
									//Res Tag
									$res_data[9] = Common::Tag_Adjust($res_data[9]);
									
									$item[7][$res_key] = array('name'=>$res_data[0],'msg'=>$res_data[1],'color'=>$res_data[2],'date'=>$res_data[3],'ip'=>$res_data[4],'img'=>array('url'=>$res_data[5][0],'file'=>$res_data[5][1]),'ent_no'=>$res_data[6],'pass'=>$res_data[7],'hp_url'=>$res_data[8],'tag'=>$res_data[9]);
									$res_key++;
								}else{unset($item[7][$res_key]);}//内容が無い行を削除
							}
						}
					}
					if(!$item[7]){$item[7] = array();}

					//Parent
					//img url=img link / file=upload img,movie thumb
					$Data[$data_key] = array('ent_no'=>$item[0],'img'=>array('url'=>$item[1][0],'file'=>$item[1][1]),'entry'=>$item[2],'msg'=>$item[3],'color'=>$item[4],'name'=>$item[5],'pass'=>$item[6],'res'=>$item[7],'date'=>$item[8],'ip'=>$item[9],'res_cnt'=>$item[10],'up_date'=>$item[11],'hp_url'=>$item[12],'mail'=>$item[13],'mail_flag'=>$item[14],'category'=>$item[15],'tag'=>$item[16]);
					if(!is_numeric($Data[$data_key]['res_cnt']) or $Data[$data_key]['res_cnt'] <= 0){$Data[$data_key]['res_cnt'] = 0;}
					if(!is_numeric($Data[$data_key]['up_date']) or $Data[$data_key]['up_date'] <= 0){$Data[$data_key]['up_date'] = 0;}

					$ent_no_list[$data_key] = $Data[$data_key]['ent_no'];
					$up_date_list[$data_key] = $Data[$data_key]['up_date'];
					$data_key++;
				}else{unset($Data[$data_key]);}//内容が無い行を削除
			}
		}
		return array($Data,$ent_no_list,$up_date_list);
	}
	
	private static function Save_Log($Data){
		if(is_array($Data)){
			$ent_no_list = array();
			foreach($Data as $data_key => $line){
				if($line['ent_no']){
					//Res部 配列の結合
					if($line['res']){
						foreach($line['res'] as $res_key => $res_line){
							if($res_line){
								//整列
								$res_line = array('name'=>$res_line['name'],'msg'=>$res_line['msg'],'color'=>$res_line['color'],'date'=>$res_line['date'],'ip'=>$res_line['ip'],'img'=>array('url'=>$res_line['img']['url'],'file'=>$res_line['img']['file']),'ent_no'=>$res_line['ent_no'],'pass'=>$res_line['pass'],'hp_url'=>$res_line['hp_url'],'tag'=>$res_line['tag']);
								if(is_array($res_line['img'])){$res_line['img'] = implode("@",$res_line['img']);}//ImgFile
								if(is_array($res_line['tag'])){$res_line['tag'] = implode(" ",$res_line['tag']);}//Tag
								$line['res'][$res_key] = join("<_>",$res_line);//1Res 結合
							}else{unset($line['res'][$res_key]);}
						}
						if(count($line['res'])){$line['res'] = join("<->",$line['res']);}//全Res 結合
					}else{$line['res'] = "";}

					//整列
					$line = array('ent_no'=>$line['ent_no'],'img'=>array('url'=>$line['img']['url'],'file'=>$line['img']['file']),'entry'=>$line['entry'],'msg'=>$line['msg'],'color'=>$line['color'],'name'=>$line['name'],'pass'=>$line['pass'],'res'=>$line['res'],'date'=>$line['date'],'ip'=>$line['ip'],'res_cnt'=>$line['res_cnt'],'up_date'=>$line['up_date'],'hp_url'=>$line['hp_url'],'mail'=>$line['mail'],'mail_flag'=>$line['mail_flag'],'category'=>$line['category'],'tag'=>$line['tag']);
					//Parent部 配列の結合
					if(is_array($line['img'])){$line['img'] = implode("@",$line['img']);}//ImgFile
					if(is_array($line['tag'])){$line['tag'] = implode(" ",$line['tag']);}//Tag

					$ent_no_list[$data_key] = $Data[$data_key]['ent_no'];//記事番号リスト
					$Data[$data_key] = join("<>",$line);//1Parent 結合
					$Data[$data_key] = preg_replace("/[\r\n]/","",$Data[$data_key]);
				}else{unset($Data[$data_key]);}
			}
			if(count($Data)>1){array_multisort($ent_no_list,SORT_DESC,SORT_NUMERIC,$Data);}//記事番号 - 降順ソート
			$Data = join("\r\n",$Data);//全Parent 結合
		}
		return $Data;
	}
}
//Gallery Board - www.tenskystar.net
?>