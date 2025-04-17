<?php
/*
GalleryBoard - Entry
 v1.9 19/06/15 tag用の処理の追加。
- 記事の投稿・操作 -
 Entry
  + Parent_Post - 親記事の投稿
  + Res_Post - レスの投稿
  + Operation - 記事編集　親記事編集・レスの削除

 Entry_File
  +- Temp_Rename - 一時ファイル名からファイル名を変更
  +- Temp_Save - アップロード指定されたファイルを一時保存 
 Entry_Check
  +- Common_Check - 投稿時の共通チェック
  +- NG_Word - 禁止ワードのチェック
  +- Post_Limit - 投稿制限の確認と発布
 Entry_Option
  +- Get_DirCapacity - アップロードフォルダの使用容量を取得
  +- Del_ImageList - 削除する画像の場所リストを作成
  +- Tripy_Maker - トリップID作成
  +- SendMail - 投稿通知
*/
$include_list = get_included_files();
$include_flag =  False;

if($php['set'] and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Entry Control Script Error!</div></html>";exit;}

Class Entry{

//Parent Entry Add Or Edit Proc
	public static function Parent_Post($type){
		//Type == parent or rewrite
		global $php,$F,$code_set,$_FILES,$date,$data_file,$lock,$ip,$admin,$edit_flag,$lock_fr,$post_set,$hp_url_type;

		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");

		if($post_set['continue']){Entry_Check::Post_Limit("check");}
		if($F['name'] and $type != "rewrite"){Ctrl::Set_Cookie("form");}
		if(!$F['name']){$F['name'] = $post_set['no_name'];}
				
		Entry_Check::Common($F);
		if($post_set['upload']['capacity'] > 0){$post_set['upload']['capacity'] += ($post_set['upload']['capacity']/1000*24);}
		if($type == "rewrite" and !$F['prevno']){Html::Error("エラー","掲示番号が取得出来ませんでした。");}
		elseif(($post_set['hp_url'] == 2 or $post_set['hp_url'] == 3) and !$F['hp_url']){Html::Error("エラー","サイトURLは入力必須です。");}
		elseif(($post_set['mail']['sw'] == 2 or  $post_set['mail']['sw'] == 3) and !$F['mail']){Html::Error("エラー","メールアドレスは入力必須です");}
		elseif(!$F['entry']){Html::Error("エラー","題名が入力されていません。");}
		elseif($post_set['parent']['msg'] and !$F['msg']){Html::Error("エラー","コメントが入力されていません。");}
		elseif(!$F['pass']){Html::Error("エラー","パスワードが入力されていません。");}
		elseif($post_set['parent']['new'] and $admin['pass'] != $F['pass']){Html::Error("エラー","投稿出来るのは管理者のみです。");}
		elseif(!$F['utp']){Html::Error("エラー","アップロードするデータの種類を選んでください。");}
		elseif($type == "parent" and $F['utp'] == "file" and !$_FILES['up_file']['name']){Html::Error("エラー","ファイルが選択されていません。");}
		elseif($F['mode'] == "rewrite" and !$edit_flag){Html::Error("エラー","不正な操作です");}
		else{
			if($post_set['word_check'] === 1 or $post_set['word_check'] === 3){Entry_Check::NG_Word();}//禁止ワードチェック
			
			//Tag
			$tag_list = array();
			if($F['tag']){
				$tag_list = Ctrl::Tag_Adjust($F['tag']);
				if(count($tag_list) > 0 and !$post_set['parent']['tag']){Html::Error("エラー","タグの登録は出来ない設定になっています。");}
				elseif($post_set['parent']['tag'] > 0 and count($tag_list) > $post_set['parent']['tag']){Html::Error("エラー","タグはスペース区切りで、".$post_set['parent']['tag']."個までです。");}
				//Duplicate Check
				$check_tag = $tag_list;
				if(is_array($tag_list)){
					for($check_cnt = 0; $check_cnt < count($tag_list); $check_cnt++){
						$check_word = array_shift($check_tag);
						if($check_word and preg_grep("/^" . $check_word ."$/",$check_tag)){Html::Error("エラー","重複しているタグがあります。");}
					}
				}
			}
		
			$del_list = array();
			
			//Get Entry No
			if($type == "parent"){$ent_no = Files::Load($data_file['no'],"line"); $ent_no = $ent_no[0]+1;}
 			else{$ent_no = $F['prevno'];}

			//MyServerRoot FileSave
			$save_file = Entry_File::Temp_Save($ent_no);
			$Er_File = array($save_file['tmp_path'],$save_file['thumb_tmp_path']);
			$save_file['change'] = false;

			if($post_set['trip_sw'] and $type != "rewrite" and preg_match("/^(.*)#(.*)$/",$F['name'],$glp)){$F['name'] = Entry_Option::Tripy_Maker($glp[0],$glp[1]);}//Trip
			//可能容量制限-現在の容量
			if($post_set['upload']['capacity']){$dir_capacity = Entry_Option::Get_DirCapacity($post_set['upload']['dir']);}
			//New UploadFile - GetFileSize
			$sum_fs = 0;
			if($save_file['tmp_path']){$sum_fs = filesize($save_file['tmp_path']);}
			
			list($item,$log_num,$ent_no_list,$up_date_list) = Ctrl::Get_Item();//Get Item
			//New or Rewrite
			switch($type){
				 //新規投稿の
				case "parent":
					$save_file['change'] = true;
					if(!$save_file['file']){Html::Error("エラー","アップロードされる画像ファイル情報の取得に失敗しました。",$Er_File);}
					if($item){
						//投稿番号チェック
						if(array_search($ent_no,$ent_no_list) !== False){Html::Error("エラー","掲示番号が正常ではありません。",$Er_File);}
						//連投チェック
						foreach ($item as $no=>$line){
							if($no < 5 and $line){
								if($item[$no]['entry'] == $F['entry'] and $item[$no]['msg'] == $F['msg']){Html::Error("エラー","その内容は最近投稿されています。",$Er_File);}
							}else{break;}
						}
					}
	
					if($F['utp'] == "file"){$up_img = array('url'=>"",'file'=>$save_file['file']);}
					if($F['utp'] == "movie"){$up_img = array('url'=>$F['up_movie'],'file'=>$save_file['file']);}//画像ファイル情報
					$new_line = array('ent_no'=>$ent_no,'img'=>$up_img,'entry'=>$F['entry'],'msg'=>$F['msg'],'color'=>$F['color'],'name'=>$F['name'],'pass'=>$F['pass'],'res'=>"",'date'=>$date,'ip'=>$ip['addr'],'res_cnt'=>0,'up_date'=>time(),'hp_url'=>$F['hp_url'],'mail'=>$F['mail'],'mail_flag'=>$F['news'],'category'=>$F['category'],'tag'=>$tag_list);
					array_unshift($item,$new_line);
					array_unshift($up_date_list, $new_line['up_date']);
					array_unshift($ent_no_list,$new_line['ent_no']);
					$log_num++;
				break;
				//編集時の
				case "rewrite":
					$key = array_search($F['prevno'],$ent_no_list);
					if($item_key !== False and $item[$key]['ent_no'] == $F['prevno']){
						//Tripy Check
						if($post_set['trip_sw'] and preg_match("/^(.*)#(.*)$/",$F['name'],$new_glp)){
							if(preg_match("/^(.*)#(.*)$/",$item[$key]['name'],$log_glp)){
								if($new_glp[0] != $log_glp[0]){$item[$key]['name'] = Entry_Option::Tripy_Maker($new_glp[0],$new_glp[1]);}
							}
						}
						//New - File?
						if($_FILES['up_file']['name'] and $F['utp'] == "file"){
							//Trash
							$trash_file = $item[$key]['img']['file'];
							
							$item[$key]['img'] = array('url'=>"",'file'=>$save_file['file']);
							$save_file['change'] = true;
						}elseif($F['utp'] == "movie" and $F['up_movie'] and $item[$key]['img']['url'] != $F['up_movie']){
						//New - Movie?
							//Trash
							$trash_file = $item[$key]['img']['file'];
							$item[$key]['img'] = array('url'=>$F['up_movie'],'file'=>$save_file['file']);
							$save_file['change'] = true;
						}
						//Add - Trash Image List
						if($trash_file){$del_list = Entry_Option::Del_ImgList($trash_file,$del_list);}
						//レス削除								
						if($item[$key]['res'] and $F['resflag']){
							foreach ($item[$key]['res'] as $res_key=>$res_line){
								if($res_line){
									$del_flag = False;
									$del_flag = array_search($res_line['ent_no'],$F['resflag']);
									if($del_flag !== False){
										$del_list = Entry_Option::Del_ImgList($res_line['img']['file'],$del_list);
										unset($item[$key]['res'][$res_key]);
										//Click Count Btm - Data Delete
										$c_cfile = getcwd().$post_set['upload']['dir']."/".$item[$key]['ent_no']."/".$res_line['ent_no'].".dat";
										if(file_exists($c_cfile)){@unlink($c_cfile);}
									}
								}
							}
						}
						
						$item[$key]['entry'] = $F['entry'];		$item[$key]['msg'] = $F['msg'];		$item[$key]['color'] = $F['color'];
						$item[$key]['pass'] = $F['pass'];		$item[$key]['ip'] = $ip['addr'];				$item[$key]['tag'] = $tag_list;
						$item[$key]['category'] = $F['category'];		$item[$key]['up_date'] = time();
						$item[$key]['hp_url'] = $F['hp_url'];	$item[$key]['mail'] = $F['mail'];	$item[$key]['mail_flag'] = $F['news'];
						$up_date_list[$key] = $item[$key]['up_date'];
					}
				break;
			}
			
			//最新の更新順 ソート
			if(is_array($item)){
				if(count($item) > 1){array_multisort($up_date_list,SORT_DESC,SORT_NUMERIC,$ent_no_list,SORT_DESC,SORT_NUMERIC,$item);}
			}

			$del_no_list = array();
			//可能 容量,親数 制限 
			if($post_set['upload']['capacity'] > 0  or $post_set['parent']['max_num'] > 0){
				for($no = 1; $no <= $log_num; $no++){
					if($post_set['parent']['max_num'] > 0 and $post_set['parent']['max_num'] <= $log_num and $no >= $post_set['parent']['max_num']){$lim_type = 1;}
					elseif($post_set['upload']['capacity'] > 0 and $post_set['upload']['capacity'] <= $dir_capacity){$lim_type = 2;}
					else{$lim_type = 0; continue;}
			
					$del_flag = 0;
					$del_file = array();
					
					if($lim_type){
						$one_fs = 0;
						//親
						if($item[$no]['img']['file'] and !preg_match("/^https?:\/\//",$item[$no]['img']['file'])){$one_fs = filesize(getcwd() . $post_set['upload']['dir'] . $item[$no]['img']['file']);}
						//子
						if($item[$no]['res']){
							foreach ($item[$no]['res'] as $res_item){
								if($res_item){
									if($res_item['img']['file'] and !preg_match("/^https?:\/\//",$res_item['img']['file'])){$one_fs +=  filesize(getcwd() .$post_set['upload']['dir']. $res_item['img']['file']);}
								}
							}
						}
						$del_flag = 1;
						if($lim_type == 1){$del_flag = 1;}
						elseif($lim_type == 2){
							if(($sum_fs+$one_fs) < $post_set['upload']['capacity']){$del_flag = 0; $sum_fs += $one_fs;}//残すログの容量計算
						}else{$del_flag = 0;}
					}
					//削除対象ログの削除
					if($del_flag){
						$del_no_list[] = $item[$no]['ent_no'];
						unset($item[$no]);
					}
				}
			}
			//投稿を保存
			Files::Save($data_file['data'],$item,"log");
			//不要な画像の削除
			if(count($del_list) > 0){foreach ($del_list as $one){if(file_exists($one)){unlink($one);}}}
			if(count($del_no_list) > 0){foreach ($del_no_list as $parent_no){Ctrl::Del_Dir($post_set['upload']['dir']."/".$parent_no);}}//Directory
			//New Picture Rename or Delete (一時ファイル解除)
			if($save_file['flag'] and $save_file['change']){Entry_File::Temp_Rename($save_file);}//Rename
			if($save_file['flag'] and !$save_file['change']){@unlink($save_file['tmp_path']);	@unlink($save_file['thumb_tmp_path']);}//Delete
			
			if($type == "parent"){//番号保存
				Files::Save($data_file['no'],$ent_no,"entry_no");
				if($post_set['mail']['ad_sw']){Entry_Option::SendMail($ent_no,$new_line,$post_set['mail']['from']);}//新規投稿時の管理者への通知用
				if($post_set['continue']){Entry_Check::Post_Limit("set");}//投稿制限
			}
		}
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		
		return;
	}

//Res Add Proc
	public static function Res_Post($prevno){
		global $php,$F,$code_set,$date,$data_file,$lock,$ip,$_FILES,$post_set,$print_set,$lock_fr,$hp_url_type,$admin;

		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");

		$nomsg_er_flag=0;
		if(!$F['msg']){$nomsg_er_flag = 1;}
		if($post_set['res']['sw'] == 3 or $post_set['res']['sw'] == 4){if($F['up_movie'] or $_FILES['up_file']['name']){$nomsg_er_flag = 0;}}

		if($post_set['continue']){Entry_Check::Post_Limit("check");}
		if($F['name']){Ctrl::Set_Cookie("form");}
		if(!$F['name']){$F['name'] = $post_set['no_name'];}
		$new['name'] = $F['name'];
		
		$flag=0;
		Entry_Check::Common($F);
		if(!$post_set['res']['sw']){Html::Error("エラー","レス機能が有効ではありません。");}
		elseif($post_set['res']['sw'] == 4 and $admin['pass'] != $F['pass']){Html::Error("エラー","レスの投稿は管理者のみが可能です。");}
		elseif(!$prevno){Html::Error("エラー","掲示番号が不正です。");}
		elseif(($post_set['hp_url'] == 3 or $post_set['hp_url'] == 4) and !$F['hp_url']){Html::Error("エラー","サイトURLは入力必須です。");}
		elseif(($post_set['mail']['sw'] == 3 or $post_set['mail']['sw'] == 4) and !$F['mail']){html::error("エラー","メールアドレスは入力必須です");}
		elseif($F['news']){Html::Error("エラー","レスに通知機能はありません。管理者に確認してください");}
		elseif($nomsg_er_flag){Html::Error("エラー","コメントが入力されていません。");}
		elseif($post_set['res']['url'] and preg_match("/(https?|ftp|news)"."(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/i",$F['msg'])){Html::Error("エラー","Urlを含むコメントは投稿できません。");}
//		elseif(!$F['pass']){Html::Error("エラー","パスワードが入力されていません。");}
		elseif($post_set['res']['sw'] == 1 and ($F['up_movie'] or $_FILES['up_file']['name'])){Html::Error("エラー","アップロードは出来ないように設定されています。");}
		elseif($F['utp'] === "file" and !$_FILES['up_file']['name']){Html::Error("エラー","ファイルが選択されていません。");}
		else{
			if($post_set['word_check'] > 1){Entry_Check::NG_Word();}//禁止ワードチェック
			
			//Tag
			if($F['tag']){
				$tag_list = Ctrl::Tag_Adjust($F['tag']);
				if(count($tag_list) > 0 and !$post_set['res']['tag']){Html::Error("エラー","タグの登録は出来ない設定になっています。");}
				elseif($post_set['res']['tag'] > 0 and count($tag_list) > $post_set['res']['tag']){Html::Error("エラー","タグはスペース区切りで、".$post_set['res']['tag']."個までです。");}
				//Duplicate Check
				$check_tag = $tag_list;
				if(is_array($tag_list)){
					for($check_cnt = 0; $check_cnt < count($tag_list); $check_cnt++){
						$check_word = array_shift($check_tag);
						if($check_word and preg_grep("/^" . $check_word ."$/",$check_tag)){Html::Error("エラー","重複しているタグがあります。");}
					}
				}				
			}
			
			//NewFile - ハッシュ算出
			if($F['utp'] == "file" and $_FILES['up_file']['name']){$new_f_hash = sha1_file($_FILES['up_file']['tmp_name']);}
			list($prevno,$res_cnt)=explode("-",$prevno);
			 if($res_cnt == 0){Html::Error("エラー","レス番号が不明です。");}
			
			if($post_set['trip_sw'] and preg_match("/^(.*)#(.*)$/",$F['name'],$glp)){$new['name'] = Entry_Option::Tripy_Maker($glp[0],$glp[1]);}//Trip
		
			list($item,$log_num,$ent_no_list) = Ctrl::Get_Item();
			
			//親データ - 検索
			$key = False;
			if($ent_no_list){$key = array_search($prevno,$ent_no_list);}
			if($key === False){Html::Error("エラー","レスをつける親記事が見つかりませんでした。");}
			
			$Er_File = array();
			if($item[$key]['ent_no'] == $prevno){
				$res_num = count($item[$key]['res']);//書き込みがあるレス数(削除済みは含まない)
				$item[$key]['res_cnt'] = $item[$key]['res_cnt']+1;//総レス数(削除済みを含む)
				
				if($post_set['res']['max_num'] > 0 and $item[$key]['res_cnt'] > $post_set['res']['max_num']){Html::Error("エラー","総レス数が".$post_set['res']['max_num']."を越えている為、これ以上レスを投稿できません。");}
				if(!$print_set['res']['sort']){$page_back_flag = ceil((($res_num+1) / $print_set['res']['num']));} if($page_back_flag <= 0 or $print_set['res']['sort']){$page_back_flag=1;}//投稿後のページ表示用
				
				//ResCheck
				if($item[$key]['res']){
			    	foreach($item[$key]['res'] as $resline){
						if($resline){
							$o_num++;
							if($res_cnt == $resline['ent_no']){Html::Error("エラー","レス番号が異常です。");}//ResNo
							if($post_set['res']['multiple'] == 1 or $post_set['res']['multiple'] > 2){//投稿者、メッセージの重複
								if(($res_num-2) <= $o_num and $resline['name'] == $new['name'] and $resline['msg'] == $F['msg']){Html::Error("エラー","その内容は最近投稿されています。");}
							}
							if($post_set['res']['multiple'] >= 2){//アップロード内容の重複
								if($F['utp'] == "file"){
									if($resline['img']['file'] and !preg_match("/^https?:\/\//",$resline['img']['file'])){$h_chkf=$resline['img']['file'];}
									else{$h_chkf="";}
									if($h_chkf and $new_f_hash == sha1_file(getcwd().$post_set['upload']['dir'].$h_chkf)){Html::Error("エラー","そのファイルは最近投稿されています");}
								}elseif($F['utp'] == "movie"){
									if($F['up_movie'] == $resline['img']['url']){Html::Error("エラー","その動画は最近投稿されています。");}
								}
							}
						}
						
					}
				}
				//MyServerRoute FileSave
				if($F['utp']){
					$save_file = Entry_File::Temp_Save($prevno,$res_cnt);
					$Er_File = array($save_file['tmp_path'],$save_file['thumb_tmp_path']);
					//画像ファイル情報
					if($F['utp'] == "file"){$up_img = array('url'=>"",'file'=>$save_file['file']);}
					elseif($F['utp'] == "movie"){$up_img = array('url'=>$F['up_movie'],'file'=>$save_file['file']);}
				}
				//ResData - Add
				$item[$key]['res'][] = array('name'=>$new['name'],'msg'=>$F['msg'],'color'=>$F['color'],'date'=>$date,'ip'=>$ip['addr'],'img'=>$up_img,'ent_no'=>$res_cnt,'pass'=>$F['pass'],'hp_url'=>$F['hp_url'],'tag'=>$tag_list);
				$item[$key]['up_date'] = time();
					
				$flag=1;
			}

			if(!$flag){Html::Error("エラー","該当する掲示が見つかりません。",$Er_File);}
			Files::Save($data_file['data'],$item,"log");
			//New Picture Rename
			if($save_file['flag']){
				Entry_File::Temp_Rename($save_file);
				if($post_set['continue']){Entry_Check::Post_Limit("set");}//投稿制限
			}
			//News
			if($post_set['mail']['sw'] > 0 and $post_set['mail']['user_sw']  > 0 and $item[$key]['mail'] and $item[$key]['mail_flag']){Entry_Option::SendMail($item[$key]['ent_no'],$item[$key]['res'][(count($item[$key]['res'])-1)],$item[$key]['mail']);}//ParentへSendMail

			$F['prevno'] = $prevno;
	  	}
		if($F['mode'] == "child"){$F['mode'] = array("view"); if($page_back_flag > 1){array_push($F['mode'],$page_back_flag);}}

		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		
		return $F['mode'];
	}

//Operation Proc
	public static function Operation($type,$prevno,$pass){
		global $data_file,$lock,$F,$post_set,$edit_flag,$lock_fr,$admin;
		$flag=0; $edit_flag=0;
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");
		
		$del_list = array();
		if(!$prevno){Html::Error("エラー","掲示番号が不正です。");}
		elseif(!$pass){Html::Error("エラー","パスワードが入力されていません。");}
		else{
			list($data,,$ent_no_list) = Ctrl::Get_Item($prevno);
			if($post_set['res']['sw'] > 0 and preg_match("/([0-9]+)-([0-9]+)/",$prevno,$glp)){$prevno = $glp[1]; $prev_res_no = $glp[2];}
			if($prev_res_no and $type != "remove"){Html::Error("エラー","レスは削除のみ行えます。");}
			
			//Entry Search
			$parent_key = False;
			if($ent_no_list){$parent_key = array_search($prevno,$ent_no_list);}
			if($ent_no_list === False){Html::Error("エラー","該当する掲示が見つかりません");}
			else{
				$item = $data[$parent_key];
				if($item['ent_no'] == $prevno){
					//Res - Operation(Delete)
				   	if($prev_res_no and $type == "remove"){
						foreach($item['res'] as $res_key => $res_line){
							if($prev_res_no == $res_line['ent_no']){
								//Pass - Check
								$auth_flag = 0;
								if($pass == $admin['pass']){$auth_flag = 1;}//Admin
								elseif(($post_set['res']['del_mode'] == 0 or $post_set['res']['del_mode'] == 1) and $pass == $res_line['pass']){$auth_flag = 2;}//ResUser
								elseif(($post_set['res']['del_mode'] == 0 or $post_set['res']['del_mode'] == 2) and  $pass == $item['pass']){$auth_flag = 3;}//ParentUser
								
								if($auth_flag){//Deleting
									$flag = 1;
									$del_list = Entry_Option::Del_ImgList($res_line['img']['file'],$del_list);
									
									unset($data[$parent_key]['res'][$res_key]);//Delete - Res
									
									//Click Count Btm - Data Delete
									$c_cfile = getcwd().$post_set['upload']['dir']."/".$prevno."/".$prev_res_no.".dat";
									if(file_exists($c_cfile)){@unlink($c_cfile);}
									
									break;
								}else{Html::Error("エラー","パスワードが違うか、権限がありません。");}
							}
			  			}
					}
					//Parent - Operation
					if(!$prev_res_no){
						//Pass - Check
					  	$auth_flag = 0;
						if($pass == $admin['pass'] or $F['admin'] == hash("sha256",$admin['pass'])){$auth_flag = 1;}//Admin
						elseif($post_set['parent']['del_mode'] == 0 and $pass == $item['pass']){$auth_flag = 2;}//ParentUser

						if($auth_flag){
							$flag=1;
							if($type == "remove"){//Deleting
								Ctrl::Del_Dir($post_set['upload']['dir']."/".$prevno);//Directory
								unset($data[$parent_key]);//Delete - Parent Data
								
							}elseif($type == "rewrite"){$log_line = "rewrite"; $edit_flag = 1;}//Rewrite Mode
							else{$log_line = $item;}
						}else{Html::Error("エラー","パスワードが違うか、権限がありません。");}
		  			}
		  		}
		  	}
			
			if($flag and $type == "remove"){
				Files::Save($data_file['data'],$data,"log");
				//画像の削除
				if(count($del_list) > 0){foreach ($del_list as $one){if(file_exists($one)){unlink($one);}}}
			}elseif(!$flag){Html::Error("エラー","該当する掲示が見つかりません。");}
			
			$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
			return $log_line;
		}
	}	
}

class Entry_File extends Entry{
//TempFile Rename
	protected static function Temp_Rename($Save_File){
		global $post_set;
		//Existing Check
		if(file_exists($Save_File['file_path'])){Html::Error("エラー","画像が既に存在する為、置換に失敗しました。",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		if($post_set['upload']['thumbnail']['sw'] and file_exists($Save_File['thumb_path'])){Html::Error("エラー","サムネイルは既にある為、置換に失敗しました。",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		//Rename(Temp - FileName -> Save - FileName)
		if(file_exists($Save_File['tmp_path'])){rename($Save_File['tmp_path'],$Save_File['file_path']);}
		if($post_set['upload']['thumbnail']['sw'] and file_exists($Save_File['thumb_tmp_path'])){rename($Save_File['thumb_tmp_path'],$Save_File['thumb_path']);}
		//Rename Check
		if(!file_exists($Save_File['file_path'])){Html::Error("エラー","画像の置換に失敗しました。",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		if($post_set['upload']['thumbnail']['sw'] and !file_exists($Save_File['thumb_path'])){Html::Error("エラー","サムネイルの置換に失敗しました。 ",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		return;
	}

//Upload Temp Save
	protected static function Temp_Save($Parent_No,$Res_No = 0){
		//TempFileとして保存
		global $F,$_FILES,$post_set;
		if($F['utp'] == "file" and $_FILES['up_file']['name']){$upload_file = array('file'=>$_FILES['up_file']['tmp_name'],'type'=>'user-local');}
		elseif($F['utp'] == "movie" and preg_match("/^https?:\/\//",$F['up_movie'])){
			if(!method_exists('Get_Thumbnail','Check_MovieImage')){Html::Error("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
			$upload_file = Get_Thumbnail::Check_MovieImage($F['up_movie']);
		}
	
		if(is_string($upload_file['file']) and $upload_file['file'] != ""){
			//Save File Info
			if(!$post_set['upload']['movie_img'] and $F['utp'] == "movie" and preg_match("/^https?:\/\//",$F['up_movie'])){
				//Movie Image Direct Load
				$save_file['file'] = $upload_file['file'];
			}else{
				list(,,$img_mime) = getimagesize($upload_file['file']);
				switch($img_mime){
					case IMAGETYPE_JPEG: $ext = ".jpg"; break;
					case IMAGETYPE_PNG: $ext = ".png"; break;
					case IMAGETYPE_GIF: $ext = ".gif"; break;
					default :
						Html::Error("エラー","対応していないファイルです。投稿に失敗しました。");
					break;
				}
				if($Res_No){$Save_No = $Parent_No."-".$Res_No;}
				else{$Save_No = $Parent_No;}
				$save_file['name'] = $Save_No . $ext;//FileName
				$save_file['root'] =  getcwd().$post_set['upload']['dir'];//Root
				$save_file['dir']  = "/".$Parent_No;//Directory
				$save_file['dir_path'] = $save_file['root'].$save_file['dir'];//Directory - FullPath
				$save_file['file'] = $save_file['dir'] . "/" . $save_file['name'];//Root
				$save_file['file_path'] = $save_file['dir_path'] . "/" . $save_file['name'];//File - Full Path
				$save_file['tmp_path']  = $save_file['dir_path'] . "/tmp-" . $save_file['name'];//Temp Path
				$save_file['thumb_tmp_path'] = $save_file['dir_path'] . "/tmp-s_".$save_file['name'];//Thumb Temp Path
				$save_file['thumb_path']= $save_file['dir_path'] . "/s_".$save_file['name'];//Thumb Path
				//Make Directory
				if(!$Res_No and !is_dir($save_file['dir_path'])){
					umask(0);
					$mk_flag = mkdir($save_file['dir_path'],0777);
					if($mk_flag === False){Html::Error("エラー","ディレクトリの生成に失敗。");}
					Files::Save($save_file['dir_path']."/index.html","<html><head><title>403 Forbidden</title></head>\n<body>\n<div>403 Forbidden</div>\n</html>","index_html");
					umask(0);
					chmod($save_file['dir_path']."/index.html", 0644);//パーミッションの変更
				}
			}
			//Save Control
			$save_file['flag'] = False;//Upload Flag
			if($_FILES['up_file']['name'] and $F['utp'] == "file"){
				$flag = move_uploaded_file($upload_file['file'], $save_file['tmp_path']);//Save
				if($flag == False or !file_exists($save_file['tmp_path'])){Html::Error("エラー","アップロードが失敗しました。");}
				else{
					umask(0);
					chmod($save_file['tmp_path'], 0644);//パーミッションの変更
					if($post_set['upload']['thumbnail']['sw']){
						if(!method_exists('Save_Thumbnail','Upload_File')){Html::Error("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
						Save_Thumbnail::Upload_File($save_file['tmp_path'],$save_file['thumb_tmp_path']);}//Make Thumbnail
				}
				$save_file['flag'] = file_exists($save_file['tmp_path']);
			}elseif($F['utp'] == "movie" and $F['up_movie'] and $post_set['upload']['movie_img']){
				if(!method_exists('Save_Thumbnail','Movie_File')){Html::Error("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
				Save_Thumbnail::Movie_File($F['up_movie'],$upload_file, $save_file['tmp_path']);
				if($post_set['upload']['thumbnail']['sw'] and !preg_match("/^https?:\/\//",$save_file['file'])){Save_Thumbnail::Upload_File($save_file['tmp_path'],$save_file['thumb_tmp_path']);}//Make Thumbnail
				$save_file['flag'] = file_exists($save_file['tmp_path']);
			}
		}
		return $save_file;
	}
}

class Entry_Check extends Entry{
// 共通チェック
	protected static function Common($F){
		global $post_set,$hp_url_type,$code_set,$_FILES;
		if(!$post_set['mail']['sw'] and $F['mail']){Html::Error("エラー","メールアドレスの入力機能はONではありません。管理者に確認してください。");}
		if($F['mail'] and !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$F['mail'])){Html::Error("エラー","メールアドレスの形式が正しくありません。");}
		if((!$post_set['mail']['sw'] and $F['news']) or (!$post_set['mail']['user_sw'] and $F['news'])){Html::Error("エラー","メール通知の機能がONではありません。管理者に確認してください。");}
		if($F['news'] and !$F['mail']){Html::Error("エラー","メール通知を利用するには、メールアドレスの入力が必要です。");}

		if($post_set['auth']['type']){
			if(!$_COOKIE['auth'] or !$F['auth']){Html::Error("エラー","画像認証コードを入力してください。[<span title=\"JavaScriptとCookieを利用しています。\n何度も失敗する場合は、有効になっているかご確認ください。\"  style=\"color:blue;\">?</span>]");}
			elseif($_COOKIE['auth']  != crypt($F['auth'],$_COOKIE['auth'])){Html::Error("エラー","画像認証コードが一致しません。再入力してください。[<span title=\"JavaScriptとCookieを利用しています。\n何度も失敗する場合は、有効になっているかご確認ください。\" style=\"color:blue;\">?</span>]");}
		}
		if(!$F['name']){Html::Error("エラー","名前が入力されていません。");}
		if($F['hp_url'] != ""){
			if(!$post_set['hp_url']){Html::Error("エラー","サイトURLの入力機能はONではありません。管理者に確認してください。");}
			elseif(!preg_match($hp_url_type,$F['hp_url'])){Html::Error("エラー","サイトURLの形式が正しくありません。");}
		}
		if($F['msg']){
			if($post_set['msg']['no_jp'] and mb_detect_encoding($F['msg']) == "ASCII"){Html::Error("エラー","コメントに日本語が含まれていません。");}
			elseif($post_set['msg']['min_length'] > 0 and mb_strlen($F['msg'],$code_set['system']) < $post_set['msg']['min_length']){Html::Error("エラー","コメントは".$post_set['msg']['min_length']."文字以上、入力が必要です。");}
		}
		switch($F['utp']){
			case "movie" :
				if($post_set['upload']['mode'] == 0 and $F['up_movie']){Html::Error("エラー","動画は投稿出来ないようにに設定されています。");}
				elseif(!preg_match("/^https?:\/\//",$F['up_movie'])){Html::Error("エラー","URLが入力されていません。");}
			break;
			case "file" :
				if($post_set['upload']['mode'] == 1 and $_FILES['up_file']['name']){Html::Error("エラー","画像のアップロードは出来ないように設定されています。");}
				elseif($_FILES['up_file']['name'] and $_FILES['up_file']['error'] == UPLOAD_ERR_FORM_SIZE){Html::Error("エラー","アップロード可能な容量を超えています。");}
			break;
			default:
			if($F['utp'] and $F['utp'] != "file" and $F['utp'] != "movie"){Html::Error("エラー","アップロードするデータの種類が不明です。");}
		}
		return;
	}

// 禁止ワード
	protected static function NG_Word(){
		global $F,$data_file;
		if(file_exists($data_file['ng_word'])){
			$word_list = file($data_file['ng_word']);
			if (is_array($word_list)) {
				$er_flag = false;
				foreach ($word_list as $word){
					$word = trim($word);
					if($word == "") continue;
					if(mb_eregi($word,$F['entry']) or mb_eregi($word,$F['name']) or mb_eregi($word,$F['msg'])){$er_flag = true; break; }//題名 or 投稿者名 or コメント
				}
				if($er_flag){Html::Error("エラー","禁止ワードが含まれています。");}
			}
		}
		return;
	}

//投稿規制
	protected static function Post_Limit($Type){
		global $_COOKIE,$post_set;
		if($Type == "check"){
			if($_COOKIE['plim']){
				$p_flag = time() - $_COOKIE['plim'] - $post_set['continue'];
				if($p_flag <= 0){Html::Error("エラー","アップロード後、".$post_set['continue'] . "秒以内の連続投稿は規制されています。");}
			}
		}elseif($Type == "set"){
			Ctrl::Set_Cookie("limit");
		}
		
		return;
	}
}

class Entry_Option extends Entry{
//Get Directory Capacity
	protected static function Get_DirCapacity($Dir,$Sum_Size = 0){
		$dirs = dir(".".$Dir);
		while(($ent = $dirs->read()) !== False){
			if($ent != ".." and $ent != "." ){
				$path = $Dir."/".$ent;
				if(is_dir(".".$path)){$Sum_Size += Entry_Option::Get_DirCapacity($path,$Sum_Size);}
				else{$Sum_Size += filesize(".".$path);}
			}
		}
		return $Sum_Size;
	}
	
//Trash Image List Add
	protected static function Del_ImgList($Target_File,$File_List){
		global $post_set;
		if($Target_File and !preg_match("/^http:\/\//",$Target_File)){
			$trash_path = getcwd().$post_set['upload']['dir'].$Target_File;
			if(file_exists($trash_path)){
				array_push($File_List,$trash_path);
				//Thunbnail
				$s_file_name = pathinfo($trash_path,PATHINFO_DIRNAME) . "/s_" . pathinfo($trash_path,PATHINFO_BASENAME);
				if(file_exists($s_file_name)){array_push($File_List,$s_file_name);}//サムネイルがある場合は削除リストに追加
			}
		}
		return $File_List;
	}
	
//Trip Maker
	protected static function Tripy_Maker($Text,$Name){
		$hash = sha1($Text);
		$trip = crypt($Name,$hash); 
		return $Name . "#" . $trip;
	}

//
	protected static function SendMail($no,$item,$to){
		global $title,$post_set,$data_file;
		$mail_tmp = Files::Load($data_file['mail_temp'],"all");
		$pm = array('/\$log_no/','/\$name/','/\$date/','/\$msg/');
		$rm = array($no,$item['name'],$item['date'],$item['msg']);
		$mail_tmp = preg_replace($pm,$rm,$mail_tmp);

		mb_language("Japanese");
		$from = mb_encode_mimeheader($post_set['mail']['name']);

		$mail_header = "From: ".$from."<" . $post_set['mail']['from'] . "> \r\n".
						"Return-Path: " . $post_set['mail']['from'] . " \r\n".
						"Replay-To: " . $post_set['mail']['from'] . " \r\n".
						"Sender: " . $post_set['mail']['from'] ." \r\n";		
		
		$flag = mb_send_mail($to,$post_set['mail']['title'],$mail_tmp,$mail_header,"-f ".$post_set['mail']['from']);
		return;
	}
}
//Gallery Board - www.tenskystar.net
?>