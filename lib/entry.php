<?php
$lib[] = "Gallery Board - Entry Control Ver:2.2";
/*

2022/04/03 trash_listやら。編集・削除時など不要ファイルの処理。変数も含めて、注意。

- 更新ログ -
トリップIDの生成の変更・調整。
Get_DirCapacity ディレクトリ容量の取得における計算が異常だったのを修正。
 v2.2 22/02/22 カテゴリ用の処理の追加。
 v2.1 22/02/03 php8に対応。
 v2.0 19/12/01　連続投稿制限をcookieからデータ方式

- 記事の投稿・操作 -
 Entry
  + Parent_Post($Form,$Type) - 親記事の投稿
  + Res_Post($Form,$Preview_No) - レスの投稿
  + Operation($Type,$Preview_No,$Pass) - 記事編集　親記事編集・レスの削除の判別
  + Res_Delete($Item,$Delete_List,$Pass = False) - レスの削除処理

 Entry_File
  -+ Temp_Rename($Save_File) - 一時ファイル名からファイル名を変更
  -+ Temp_Save($Parent_No,$Res_No = 0) - アップロード指定されたファイルを一時保存 
  
 Entry_Check
  -+ Common_Check($Form) - 投稿時の共通チェック
  -+  NG_Word($Form) - 禁止ワードのチェック
  -+ Post_Limit($Posted_List,$Ip) - 投稿制限の確認と発布
  -+ Tag($Tags,$Max) - タグ数とタグの重複のチェック
  -+ Capacity_Limit($Items,$Add_Size = 0) - 容量設定のチェックと削除
  
 Entry_Option
  -+ Get_DirCapacity($Dir) - アップロードフォルダの使用容量を取得
  -+ Del_ImageList($Target_File) - 削除する画像の場所リストを作成
  -+ Tripy_Maker($Text,$Name,$Algo = "sha256") - トリップID作成
  -+ SendMail($No,$Item,$To) - 投稿通知
*/
$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Entry Control Script Error!</div></html>";exit;}

class Entry{

//Parent Entry Add Or Edit Proc
	public static function Parent_Post($Form,$Type){
		//Type == parent or rewrite
		global $php,$code_set,$_FILES,$date,$data_file,$lock,$ip,$admin,$edit_flag,$lock_fr,$post_set,$hp_url_type;

		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");
		//Post Time Limit Check
		if($post_set['continue']){
			$posted_list = Files::Load($data_file['post_limit'],"plimit");
			$posted_list = Entry_Check::Post_Limit($posted_list,$ip);
		}
		if($Form['name'] and $Type != "rewrite"){Common::Set_Cookie("form");}
		if(!$Form['name']){$Form['name'] = $post_set['no_name'];}
				
		Entry_Check::Common($Form);
		if($post_set['upload']['capacity'] > 0){$post_set['upload']['capacity'] += ($post_set['upload']['capacity']/1000*24);}
		if($Type == "rewrite" and !$Form['prevno']){Error_Page::Main("エラー","掲示番号が取得出来ませんでした。");}
		elseif(($post_set['hp_url'] == 2 or $post_set['hp_url'] == 3) and !$Form['hp_url']){Error_Page::Main("エラー","サイトURLは入力必須です。");}
		elseif(($post_set['mail']['sw'] == 2 or  $post_set['mail']['sw'] == 3) and !$Form['mail']){Error_Page::Main("エラー","メールアドレスは入力必須です");}
		elseif(!$Form['entry']){Error_Page::Main("エラー","題名が入力されていません。");}
		elseif($post_set['parent']['msg'] and !$Form['msg']){Error_Page::Main("エラー","コメントが入力されていません。");}
		elseif(!$Form['pass']){Error_Page::Main("エラー","パスワードが入力されていません。");}
		elseif($post_set['parent']['new'] and $admin['pass'] != $Form['pass']){Error_Page::Main("エラー","投稿出来るのは管理者のみです。");}
		elseif(!$Form['utp']){Error_Page::Main("エラー","アップロードするデータの種類を選んでください。");}
		elseif($Type == "parent" and $Form['utp'] == "file" and !$_FILES['up_file']['name']){Error_Page::Main("エラー","ファイルが選択されていません。");}
		elseif($Form['mode'] == "rewrite" and !$edit_flag){Error_Page::Main("エラー","不正な操作です");}
		else{
			if($post_set['word_check'] === 1 or $post_set['word_check'] === 3){Entry_Check::NG_Word($Form);}//禁止ワードチェック
			
			$trash_list = array();
			
			//Tag
			$tag_list = array();
			if($Form['tag']){
				$tag_list = Entry_Check::Tag($Form['tag'],$post_set['parent']['tag']);
			}
			
			//CategoryNo -> CategoryName
			$category_name = "";
			if($post_set['category']['sw']){
				if(is_numeric($Form['category'])){
				$category_name = Common::Get_CategoryName($Form['category']);
				}
			}

			//Get Entry No
			if($Type == "parent"){
				$ent_no = Files::Load($data_file['no'],"line");
				$ent_no = $ent_no[0]+1;
			}else{$ent_no = $Form['prevno'];}

			//MyServerRoot FileSave
			$save_file = Entry_File::Temp_Save($ent_no);
			$Er_File = array($save_file['tmp_path'],$save_file['thumb_tmp_path']);
			$save_file['change'] = false;
			
			//Get Item
			list($item,$log_num,$ent_no_list,$up_date_list) = Common::Get_Item("All");
			
			//New or Rewrite
			switch($Type){
				 //新規投稿の
				case "parent":
					//Trip Make
					if($post_set['trip']['sw'] and preg_match("/^(.*)#(.*)$/",$Form['name'],$glp)){	
						$Form['name'] = Entry_Option::Tripy_Maker($glp[0],$glp[1],$post_set['trip']['algo']);
					}					
					if($item){
						//投稿番号チェック
						if(array_search($ent_no,$ent_no_list) !== False){Error_Page::Main("エラー","掲示番号が正常ではありません。",$Er_File);}
						//連投チェック
						foreach ($item as $no=>$line){
							if($no < 5 and $line){
								if($item[$no]['entry'] == $Form['entry'] and $item[$no]['msg'] == $Form['msg']){Error_Page::Main("エラー","その内容は最近投稿されています。",$Er_File);}
							}else{break;}
						}
					}
					$save_file['change'] = true;
					if(!$save_file['file']){Error_Page::Main("エラー","アップロードされる画像ファイル情報の取得に失敗しました。",$Er_File);}					
					if($Form['utp'] == "file"){$up_img = array('url'=>"",'file'=>$save_file['file']);}
					if($Form['utp'] == "movie"){$up_img = array('url'=>$Form['up_movie'],'file'=>$save_file['file']);}//画像ファイル情報					
					//変数へ取り込み
					$new_line = array('ent_no'=>$ent_no,'img'=>$up_img,'entry'=>$Form['entry'],'msg'=>$Form['msg'],'color'=>$Form['color'],'name'=>$Form['name'],'pass'=>$Form['pass'],'res'=>"",'date'=>$date,'ip'=>$ip['addr'],'res_cnt'=>0,'up_date'=>time(),'hp_url'=>$Form['hp_url'],'mail'=>$Form['mail'],'mail_flag'=>$Form['news'],'category'=>$category_name,'tag'=>$tag_list);
					array_unshift($item,$new_line);
					array_unshift($up_date_list, $new_line['up_date']);
					array_unshift($ent_no_list,$new_line['ent_no']);
					$log_num++;
				break;
				//編集時の
				case "rewrite":
					$key = array_search($Form['prevno'],$ent_no_list);
					if($key !== False and $item[$key]['ent_no'] == $Form['prevno']){
						//Tripy Check
						if($post_set['trip']['sw'] and preg_match("/^(.*)#(.*)$/",$Form['name'],$new_glp)){
							if($Form['name'] != $item[$key]['name']){
								$Form['name'] = Entry_Option::Tripy_Maker($new_glp[0],$new_glp[1],$post_set['trip']['algo']);
							}
						}
						//New - File?
						$trash_file = "";
						if($_FILES['up_file']['name'] and $Form['utp'] == "file"){
							//Trash
							$trash_file = $item[$key]['img']['file'];
							
							$item[$key]['img'] = array('url'=>"",'file'=>$save_file['file']);
							$save_file['change'] = true;
						}elseif($Form['utp'] == "movie" and $Form['up_movie'] and $item[$key]['img']['url'] != $Form['up_movie']){
						//New - Movie?
							//Trash
							$trash_file = $item[$key]['img']['file'];
							$item[$key]['img'] = array('url'=>$Form['up_movie'],'file'=>$save_file['file']);
							$save_file['change'] = true;
						}
						//Add - Trash Image List
						if($trash_file){
							$parent_trash_list = Entry_Option::Trash_ImgList($trash_file);
							if(is_array($parent_trash_list)){$trash_list = array_merge($trash_list,$parent_trash_list);}
						}
						//レス削除
						$res_trash = array();
						if($Form['res_flag']){list($item[$key],$res_trash) = Entry::Res_Delete($item[$key],$Form['res_flag'],False);}
						if(count($res_trash) > 0){$trash_list = array_merge($trash_list,$res_trash);}
						//元のカテゴリ名を引き継ぐ。
						if(!$category_name){$category_name = $item[$key]['category'];}					
						//編集
						$item[$key]['name'] = $Form['name'];
						$item[$key]['entry'] = $Form['entry'];
						$item[$key]['msg'] = $Form['msg'];
						$item[$key]['color'] = $Form['color'];
						$item[$key]['pass'] = $Form['pass'];
						$item[$key]['ip'] = $ip['addr'];
						$item[$key]['tag'] = $tag_list;
						$item[$key]['up_date'] = time();
						$item[$key]['category'] = $category_name;
						$item[$key]['hp_url'] = $Form['hp_url'];
						$item[$key]['mail'] = $Form['mail'];
						$item[$key]['mail_flag'] = $Form['news'];
						$up_date_list[$key] = $item[$key]['up_date'];
					}
				break;
			}

			$del_no_list = array();
			//合計容量,親数 制限
			//最新の更新順 ソート
			if(is_array($item)){
				if(count($item) > 1){array_multisort($up_date_list,SORT_DESC,SORT_NUMERIC,$ent_no_list,SORT_DESC,SORT_NUMERIC,$item);}
			}
			$new_file_size = filesize($save_file['tmp_path']);
			list($item,$del_no_list) = Entry_Check::Capacity_Limit($item,$new_file_size);
			
			//投稿を保存
			Files::Save($data_file['data'],$item,"log");
			//削除
			//- ファイルのみ
			Maintain::Delete_TrashFile($trash_list);
			//- フォルダごと
			if(count($del_no_list) > 0){
				foreach ($del_no_list as $parent_no){
					Maintain::Del_Dir($post_set['upload']['dir']."/".$parent_no);
				}
			}
			//New Picture Rename or Delete (一時ファイル解除)
			if($save_file['flag'] and $save_file['change']){Entry_File::Temp_Rename($save_file);}//Rename
			if($save_file['flag'] and !$save_file['change']){@unlink($save_file['tmp_path']);	@unlink($save_file['thumb_tmp_path']);}//Delete
			
			if($Type == "parent"){//番号保存
				Files::Save($data_file['no'],$ent_no,"entry_no");
				if($post_set['mail']['ad_sw']){Entry_Option::SendMail($ent_no,$new_line,$post_set['mail']['from']);}//新規投稿時の管理者への通知用
				if($post_set['continue']){//Post Time Limit Save
					if(is_array($posted_list)){$posted_list = join("\r\n",$posted_list);}
					Files::Save($data_file['post_limit'],$posted_list);
				}
			}
		}
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		
		return;
	}

//Res Add Proc
	public static function Res_Post($Form,$Preview_No){
		global $php,$code_set,$date,$data_file,$lock,$ip,$_FILES,$post_set,$print_set,$lock_fr,$hp_url_type,$admin;

		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");

		$nomsg_er_flag = 0;
		if(!$Form['msg']){$nomsg_er_flag = 1;}
		if($post_set['res']['sw'] == 3 or $post_set['res']['sw'] == 4){if($Form['up_movie'] or $_FILES['up_file']['name']){$nomsg_er_flag = 0;}}
		//Post Time Limit - Check
		if($post_set['continue']){
			$posted_list = Files::Load($data_file['post_limit'],"plimit");
			$posted_list = Entry_Check::Post_Limit($posted_list,$ip);
		}
		if($Form['name']){Common::Set_Cookie("form");}
		if(!$Form['name']){$Form['name'] = $post_set['no_name'];}
		$new['name'] = $Form['name'];
		
		$flag = 0;
		Entry_Check::Common($Form);
		if(!$post_set['res']['sw']){Error_Page::Main("エラー","レス機能が有効ではありません。");}
		elseif($post_set['res']['sw'] == 4 and $admin['pass'] != $Form['pass']){Error_Page::Main("エラー","レスの投稿は管理者のみが可能です。");}
		elseif(!$Preview_No){Error_Page::Main("エラー","掲示番号が不正です。");}
		elseif(($post_set['hp_url'] == 3 or $post_set['hp_url'] == 4) and !$Form['hp_url']){Error_Page::Main("エラー","サイトURLは入力必須です。");}
		elseif(($post_set['mail']['sw'] == 3 or $post_set['mail']['sw'] == 4) and !$Form['mail']){Error_Page::Main("エラー","メールアドレスは入力必須です");}
		elseif($Form['news']){Error_Page::Main("エラー","レスに通知機能はありません。管理者に確認してください");}
		elseif($nomsg_er_flag){Error_Page::Main("エラー","コメントが入力されていません。");}
		elseif($post_set['res']['url'] and preg_match("/(https?|ftp|news)"."(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/i",$Form['msg'])){Error_Page::Main("エラー","Urlを含むコメントは投稿できません。");}
//		elseif(!$Form['pass']){Error_Page::Main("エラー","パスワードが入力されていません。");}
		elseif($post_set['res']['sw'] == 1 and ($Form['up_movie'] or $_FILES['up_file']['name'])){Error_Page::Main("エラー","アップロードは出来ないように設定されています。");}
		elseif($Form['utp'] === "file" and !$_FILES['up_file']['name']){Error_Page::Main("エラー","ファイルが選択されていません。");}
		else{
			if($post_set['word_check'] > 1){Entry_Check::NG_Word($Form);}//禁止ワードチェック
			
			//Tag
			$tag_list = array();
			if($Form['tag']){
				$tag_list = Entry_Check::Tag($Form['tag'],$post_set['res']['tag']);
			}
			
			//NewFile - ハッシュ算出
			if($Form['utp'] == "file" and $_FILES['up_file']['name']){$new_f_hash = sha1_file($_FILES['up_file']['tmp_name']);}
			list($Preview_No,$res_cnt)=explode("-",$Preview_No);
			 if($res_cnt == 0){Error_Page::Main("エラー","レス番号が不明です。");}
			
			if($post_set['trip']['sw'] and preg_match("/^(.*)#(.*)$/",$Form['name'],$glp)){$new['name'] = Entry_Option::Tripy_Maker($glp[0],$glp[1]);}//Trip
		
			list($item,$log_num,$ent_no_list) = Common::Get_Item("All");
			
			//親データ - 検索
			$key = False;
			if($ent_no_list){$key = array_search($Preview_No,$ent_no_list);}
			if($key === False){Error_Page::Main("エラー","レスをつける親記事が見つかりませんでした。");}
			
			$Er_File = array();
			$save_file = array('flag'=>false);
			if($item[$key]['ent_no'] == $Preview_No){
				$res_num = count($item[$key]['res']);//書き込みがあるレス数(削除済みは含まない)
				$item[$key]['res_cnt'] = $item[$key]['res_cnt']+1;//総レス数(削除済みを含む)
				
				if($post_set['res']['max_num'] > 0 and $item[$key]['res_cnt'] > $post_set['res']['max_num']){Error_Page::Main("エラー","総レス数が".$post_set['res']['max_num']."を越えている為、これ以上レスを投稿できません。");}
				//投稿後のページ表示用
				$page_back_flag = 1;
				if(!$print_set['res']['sort']){$page_back_flag = ceil((($res_num+1) / $print_set['res']['num']));}
				if($page_back_flag <= 0 or $print_set['res']['sort']){$page_back_flag = 1;}
				
				//ResCheck
				if($item[$key]['res']){
					$o_num = "";
			    	foreach($item[$key]['res'] as $resline){
						if($resline){
							$o_num++;
							if($res_cnt == $resline['ent_no']){Error_Page::Main("エラー","レス番号が異常です。");}//ResNo
							if($post_set['res']['multiple'] == 1 or $post_set['res']['multiple'] > 2){//投稿者、メッセージの重複
								if(($res_num-2) <= $o_num and $resline['name'] == $new['name'] and $resline['msg'] == $Form['msg']){Error_Page::Main("エラー","その内容は最近投稿されています。");}
							}
							if($post_set['res']['multiple'] >= 2){//アップロード内容の重複
								if($Form['utp'] == "file"){
									if($resline['img']['file'] and !preg_match("/^https?:\/\//",$resline['img']['file'])){$h_chkf=$resline['img']['file'];}
									else{$h_chkf="";}
									if($h_chkf and $new_f_hash == sha1_file(getcwd().$post_set['upload']['dir'].$h_chkf)){Error_Page::Main("エラー","そのファイルは最近投稿されています");}
								}elseif($Form['utp'] == "movie"){
									if($Form['up_movie'] == $resline['img']['url']){Error_Page::Main("エラー","その動画は最近投稿されています。");}
								}
							}
						}
					}
				}
				//MyServerRoute FileSave
				$up_img = array('url'=>"",'file'=>"");
				if($Form['utp']){
					$save_file = Entry_File::Temp_Save($Preview_No,$res_cnt);
					$Er_File = array($save_file['tmp_path'],$save_file['thumb_tmp_path']);
					//画像ファイル情報					
					if($Form['utp'] == "file"){$up_img = array('url'=>"",'file'=>$save_file['file']);}
					elseif($Form['utp'] == "movie"){$up_img = array('url'=>$Form['up_movie'],'file'=>$save_file['file']);}
				}
				//ResData - Add
				$item[$key]['res'][] = array('name'=>$new['name'],'msg'=>$Form['msg'],'color'=>$Form['color'],'date'=>$date,'ip'=>$ip['addr'],'img'=>$up_img,'ent_no'=>$res_cnt,'pass'=>$Form['pass'],'hp_url'=>$Form['hp_url'],'tag'=>$tag_list);
				$item[$key]['up_date'] = time();
					
				$flag=1;
			}

			if(!$flag){Error_Page::Main("エラー","該当する掲示が見つかりません。",$Er_File);}
			Files::Save($data_file['data'],$item,"log");
			//New Picture Rename
			if($save_file['flag']){
				Entry_File::Temp_Rename($save_file);
				//Post Time Limit Save
				if($post_set['continue']){
					if(is_array($posted_list)){$posted_list = join("\r\n",$posted_list);}
					Files::Save($data_file['post_limit'],$posted_list);
				}
			}
			//News
			if($post_set['mail']['sw'] > 0 and $post_set['mail']['user_sw']  > 0 and $item[$key]['mail'] and $item[$key]['mail_flag']){
				Entry_Option::SendMail($item[$key]['ent_no'],$item[$key]['res'][(count($item[$key]['res'])-1)],$item[$key]['mail']);
			}//Parent -> SendMail

			$Form['prevno'] = $Preview_No;
	  	}
		if($Form['mode'] == "child"){$Form['mode'] = "view";}

		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		
		return array($Form['mode'],$page_back_flag);
	}

//Operation Proc
	public static function Operation($Type,$Preview_No,$Pass){
		global $data_file,$lock,$F,$post_set,$edit_flag,$lock_fr,$admin;
		$edit_flag = false;
		$save_flag = false;
		//File Lock
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");
		
		$trash_list = array();
		if(!preg_match("/^[0-9]+(-[0-9]+)?$/",$Preview_No)){Error_Page::Main("エラー","掲示番号が不正です。");}
		elseif(!$Pass){Error_Page::Main("エラー","パスワードが入力されていません。");}
		else{
			$log_line = "";
			//Get Res No & 
			$prev_res_no = 0;	
			if(preg_match("/([0-9]+)-([0-9]+)/",$Preview_No,$glp)){
				$Preview_No = $glp[1];
				$prev_res_no = $glp[2];
			}
			if($prev_res_no and $post_set['res']['sw'] <= 0){Error_Page::Main("エラー","レス機能は無効の設定です。");}
			if($prev_res_no and $Type != "remove"){Error_Page::Main("エラー","レスは削除のみ行えます。");}
			
			list($items,,$ent_no_list) = Common::Get_Item("All",$Preview_No);
	
			//Entry Search
			$parent_key = False;
			if($ent_no_list){$parent_key = array_search($Preview_No,$ent_no_list);}
			if($parent_key === False){Error_Page::Main("エラー","該当する親記事が見つかりません");}
			else{
				if($items[$parent_key]['ent_no'] == $Preview_No){
					$item =& $items[$parent_key];
					//Res - Operation(Delete)
				   	if($prev_res_no and $Type == "remove"){
						list($item,$res_trash) = Entry::Res_Delete($item,array($prev_res_no),$Pass);
						if(count($res_trash) > 0){$trash_list = array_merge($trash_list,$res_trash);}
						$save_flag = true;
					}elseif(!$prev_res_no){
						//Parent - Operation
						//- Pass - Check
					  	$auth_flag = false;
						if($Pass == $admin['pass'] or $F['admin'] == hash("sha256",$admin['pass'])){$auth_flag = 1;}//Admin
						elseif($post_set['parent']['del_mode'] == 0 and $Pass == $item['pass']){$auth_flag = 2;}//ParentUser

						if($auth_flag){
							$save_flag = true;
							if($Type == "remove"){//Deleting
								Maintain::Del_Dir($post_set['upload']['dir']."/".$Preview_No);//Directory
								unset($items[$parent_key]);//Delete - Parent Data
								
							}elseif($Type == "rewrite"){$log_line = "rewrite"; $edit_flag = true;}//Rewrite Mode
							else{$log_line = $item;}
						}else{Error_Page::Main("エラー","パスワードが違うか、権限がありません。");}
		  			}
		  		}
		  	}
			
			if($save_flag and $Type == "remove"){
				Files::Save($data_file['data'],$items,"log");
				//画像の削除
				Maintain::Delete_TrashFile($trash_list);
			}
			
			$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
			return $log_line;
		}
	}
	
	public static function Res_Delete($Item,$Delete_List,$Pass = False){
		global $post_set,$data_file,$admin;
		$trash_list = array();
		if(is_array($Item['res'])){
			$entno_list = array_column($Item['res'],"ent_no");
			if(is_array($Delete_List)){
				foreach($Delete_List as $line){
					if($line){
						$del_key = false;
						$del_key = array_search($line,$entno_list);
						if($del_key !== False and $Item['res'][$del_key]['ent_no'] == $line){
							//Pass - Check
							$auth_flag = 0;
							if($Pass !== False){
								if($Pass == $admin['pass']){$auth_flag = 1;}//Admin
								elseif(($post_set['res']['del_mode'] == 0 or $post_set['res']['del_mode'] == 1) and $Pass == $Item['res'][$del_key]['pass']){$auth_flag = 2;}//ResUser
								elseif(($post_set['res']['del_mode'] == 0 or $post_set['res']['del_mode'] == 2) and  $Pass == $Item['pass']){$auth_flag = 3;}//ParentUser
							}
							if($Pass === False or $Pass !== False and $auth_flag > 0){//Deleting
								$res_trash = Entry_Option::Trash_ImgList($Item['res'][$del_key]['img']['file']);
								if(count($res_trash) > 0){$trash_list = array_merge($trash_list,$res_trash);};
								//good Count Btm - Data Delete
								$good_file = getcwd().$post_set['upload']['dir']."/".$Item['ent_no']."/".$Item['res'][$del_key]['ent_no'] . $data_file['good'] . ".dat";
								if(file_exists($good_file)){$trash_list[] = $good_file;}
								unset($Item['res'][$del_key]);
							}else{Error_Page::Main("エラー","パスワードが違うか、権限が無いのでレスを削除できません。");}
						}
					}
				}
			}
		}
		return array($Item,$trash_list);
	}

/*
		if($item[$key]['res'] and $F['resflag']){
			foreach ($item[$key]['res'] as $res_key=>$res_line){
				if($res_line){
					$del_flag = False;
					$del_flag = array_search($res_line['ent_no'],$F['resflag']);
					if($del_flag !== False){
						$del_list[] = Entry_Option::Trash_ImgList($res_line['img']['file']);
						unset($item[$key]['res'][$res_key]);
						//Click Count Btm - Data Delete
						$c_cfile = getcwd().$post_set['upload']['dir']."/".$item[$key]['ent_no']."/".$res_line['ent_no'].".dat";
						if(file_exists($c_cfile)){@unlink($c_cfile);}
					}
				}
			}
		}		
	}
*/	
	
}

class Entry_File extends Entry{
//TempFile Rename
	protected static function Temp_Rename($Save_File){
		global $post_set;
		//Existing Check
		if(file_exists($Save_File['file_path'])){Error_Page::Main("エラー","画像が既に存在する為、置換に失敗しました。",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		if($post_set['upload']['thumbnail']['sw'] and file_exists($Save_File['thumb_path'])){Error_Page::Main("エラー","サムネイルは既にある為、置換に失敗しました。",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		//Rename(Temp - FileName -> Save - FileName)
		if(file_exists($Save_File['tmp_path'])){rename($Save_File['tmp_path'],$Save_File['file_path']);}
		if($post_set['upload']['thumbnail']['sw'] and file_exists($Save_File['thumb_tmp_path'])){rename($Save_File['thumb_tmp_path'],$Save_File['thumb_path']);}
		//Rename Check
		if(!file_exists($Save_File['file_path'])){Error_Page::Main("エラー","画像の置換に失敗しました。",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		if($post_set['upload']['thumbnail']['sw'] and !file_exists($Save_File['thumb_path'])){Error_Page::Main("エラー","サムネイルの置換に失敗しました。 ",Array($Save_File['tmp_path'],$Save_File['thumb_tmp_path']));}
		return;
	}

//Upload Temp Save
	protected static function Temp_Save($Parent_No,$Res_No = 0){
		//TempFileとして保存
		global $F,$post_set;
		$upload_file = array('file'=>"",'type'=>"",);
		$save_file = array('name'=>"",'root'=>"",'dir'=>"",'dir_path'=>"",'file'=>"",'file_path'=>"",'tmp_path'=>"",'thumb_path'=>"",'thumb_tmp_path'=>"",'flag'=>false);
		if($F['utp'] == "file" and $_FILES['up_file']['name']){$upload_file = array('file'=>$_FILES['up_file']['tmp_name'],'type'=>'user-local');}
		elseif($F['utp'] == "movie" and preg_match("/^https?:\/\//",$F['up_movie'])){
			if(!method_exists('Get_Thumbnail','Check_MovieImage')){Error_Page::Main("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
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
						Error_Page::Main("エラー","対応していないファイルです。投稿に失敗しました。");
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
					if($mk_flag === False){Error_Page::Main("エラー","ディレクトリの生成に失敗。");}
					Files::Save($save_file['dir_path']."/index.html","<html><head><title>403 Forbidden</title></head>\n<body>\n<div>403 Forbidden</div>\n</html>","index_html");
					umask(0);
					chmod($save_file['dir_path']."/index.html", 0644);//パーミッションの変更
				}
			}
			//Save Control
			$save_file['flag'] = False;//Upload Flag
			if($_FILES['up_file']['name'] and $F['utp'] == "file"){
				$flag = move_uploaded_file($upload_file['file'], $save_file['tmp_path']);//Save
				if($flag == False or !file_exists($save_file['tmp_path'])){Error_Page::Main("エラー","アップロードが失敗しました。");}
				else{
					umask(0);
					chmod($save_file['tmp_path'], 0644);//パーミッションの変更
					if($post_set['upload']['thumbnail']['sw']){
						if(!method_exists('Save_Thumbnail','Upload_File')){Error_Page::Main("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
						Save_Thumbnail::Upload_File($save_file['tmp_path'],$save_file['thumb_tmp_path']);}//Make Thumbnail
				}
				$save_file['flag'] = file_exists($save_file['tmp_path']);
			}elseif($F['utp'] == "movie" and $F['up_movie'] and $post_set['upload']['movie_img']){
				if(!method_exists('Save_Thumbnail','Movie_File')){Error_Page::Main("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
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
	protected static function Common($Form){
		global $post_set,$hp_url_type,$code_set,$_FILES;
		if(!$post_set['mail']['sw'] and $Form['mail']){Error_Page::Main("エラー","メールアドレスの入力機能はONではありません。管理者に確認してください。");}
		if($Form['mail'] and !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$Form['mail'])){Error_Page::Main("エラー","メールアドレスの形式が正しくありません。");}
		if((!$post_set['mail']['sw'] and $Form['news']) or (!$post_set['mail']['user_sw'] and $Form['news'])){Error_Page::Main("エラー","メール通知の機能がONではありません。管理者に確認してください。");}
		if($Form['news'] and !$Form['mail']){Error_Page::Main("エラー","メール通知を利用するには、メールアドレスの入力が必要です。");}

		if($post_set['auth']['type']){
			if(!$_COOKIE['auth'] or !$Form['auth']){Error_Page::Main("エラー","画像認証コードを入力してください。[<span title=\"JavaScriptとCookieを利用しています。\n何度も失敗する場合は、有効になっているかご確認ください。\"  style=\"color:blue;\">?</span>]");}
			elseif($_COOKIE['auth']  != crypt($Form['auth'],$_COOKIE['auth'])){Error_Page::Main("エラー","画像認証コードが一致しません。再入力してください。[<span title=\"JavaScriptとCookieを利用しています。\n何度も失敗する場合は、有効になっているかご確認ください。\" style=\"color:blue;\">?</span>]");}
		}
		if(!$Form['name']){Error_Page::Main("エラー","名前が入力されていません。");}
		if($Form['hp_url'] != ""){
			if(!$post_set['hp_url']){Error_Page::Main("エラー","サイトURLの入力機能はONではありません。管理者に確認してください。");}
			elseif(!preg_match($hp_url_type,$Form['hp_url'])){Error_Page::Main("エラー","サイトURLの形式が正しくありません。");}
		}
		if($Form['msg']){
			if($post_set['msg']['no_jp'] and mb_detect_encoding($Form['msg']) == "ASCII"){Error_Page::Main("エラー","コメントに日本語が含まれていません。");}
			elseif($post_set['msg']['min_length'] > 0 and mb_strlen($Form['msg'],$code_set['system']) < $post_set['msg']['min_length']){Error_Page::Main("エラー","コメントは".$post_set['msg']['min_length']."文字以上、入力が必要です。");}
		}
		switch($Form['utp']){
			case "movie" :
				if($post_set['upload']['mode'] == 0 and $Form['up_movie']){Error_Page::Main("エラー","動画は投稿出来ないようにに設定されています。");}
				elseif(!preg_match("/^https?:\/\//",$Form['up_movie'])){Error_Page::Main("エラー","URLが入力されていません。");}
			break;
			case "file" :
				if($post_set['upload']['mode'] == 1 and $_FILES['up_file']['name']){Error_Page::Main("エラー","画像のアップロードは出来ないように設定されています。");}
				elseif($_FILES['up_file']['name'] and $_FILES['up_file']['error'] == UPLOAD_ERR_FORM_SIZE){Error_Page::Main("エラー","アップロード可能な容量を超えています。");}
			break;
			default:
			if($Form['utp'] and $Form['utp'] != "file" and $Form['utp'] != "movie"){Error_Page::Main("エラー","アップロードするデータの種類が不明です。");}
		}
		return;
	}

// 禁止ワード
	protected static function NG_Word($Form){
		global $data_file;
		if(file_exists($data_file['ng_word'])){
			$word_list = file($data_file['ng_word']);
			if (is_array($word_list)) {
				$er_flag = false;
				foreach ($word_list as $word){
					$word = trim($word);
					if($word == "") continue;
					if(mb_eregi($word,$Form['entry']) or mb_eregi($word,$Form['name']) or mb_eregi($word,$Form['msg'])){$er_flag = true; break; }//題名 or 投稿者名 or コメント
				}
				if($er_flag){Error_Page::Main("エラー","禁止ワードが含まれています。");}
			}
		}
		return;
	}

//投稿規制
	protected static function Post_Limit($Posted_List,$Ip){
		global $post_set,$data_file;
		$limit_flag = 0;
		$proc_flag = False;
		if(is_array($Posted_List)){
			//NextDay - Reset
			if(file_exists($data_file['post_limit'])){
				$update_time = date_parse(filemtime($data_file['post_limit']));
				$update_time = mktime(0,0,0,$update_time['month'],$update_time['day'],$update_time['year']);
				if(($update_time - time()) > (24 * 3600)){$Posted_List = array();}
			}
			//Check
			foreach ($Posted_List as $key=>$line){
				$line = preg_replace("/[\r\n]/","",$line);
				if($line){
					$line = explode("<>",$line);
					$line = array('ip'=>$line[0],'stamp'=>$line[1],'cnt'=>$line[2]);
					if($line['ip']){
						if($line['ip'] == $Ip['addr'] or $line['ip'] == $Ip['host']){
							$limit_flag = time() - $line['stamp'] - $post_set['continue'];
							$line['cnt']++;
							if($limit_flag <= 0){Error_Page::Main("エラー","アップロード後、".$post_set['continue'] . "秒以内の連続投稿は規制されています。");}
							$line = $line['ip'] . "<>" . time() . "<>" . $line['cnt'];
							$proc_flag = True;
						}
						$Posted_List[$key] = $line;
					}else{unset($Posted_List[$key]);}
				}else{unset($Posted_List[$key]);}
			}
		}else{$Posted_List = array();}
		if(!$proc_flag){$Posted_List[] = $Ip['addr'] . "<>" . time() . "<>1";}
		
		return $Posted_List;
	}
	
	public static function Tag($Tags,$Max){
		$tag_list = Common::Tag_Adjust($Tags);
		if(count($tag_list) > 0 and !$Max){Error_Page::Main("エラー","タグの登録は出来ない設定になっています。");}
		elseif($Max> 0 and count($tag_list) > $Max){Error_Page::Main("エラー","タグはスペース区切りで、" . $Max . "個までです。");}
		//Duplicate Check
		$check_tag = $tag_list;
		if(is_array($tag_list)){
			for($check_cnt = 0; $check_cnt < count($tag_list); $check_cnt++){
				$check_word = array_shift($check_tag);
				if($check_word and preg_grep("/^" . $check_word ."$/",$check_tag)){Error_Page::Main("エラー","重複しているタグがあります。");}
			}
		}
		return $tag_list;
	}

	public static function Capacity_Limit($Items,$Add_Size = 0){
		global $post_set;
		$del_no_list = array();
		if($post_set['upload']['capacity'] > 0 or $post_set['parent']['max_num'] > 0){
			//可能容量制限-現在の容量
			if($post_set['upload']['capacity']){$now_capacity = Entry_Option::Get_DirCapacity($post_set['upload']['dir']);}
			
			$log_num = count($Items);
			$total_capacity = $Add_Size;
			for($no = 1; $no <= $log_num; $no++){
				if($post_set['parent']['max_num'] > 0 and $post_set['parent']['max_num'] <= $log_num and $no >= $post_set['parent']['max_num']){$lim_type = 1;}
				elseif($post_set['upload']['capacity'] > 0 and $post_set['upload']['capacity'] <= $now_capacity){$lim_type = 2;}
				else{$lim_type = 0; break;}
		
				$del_file = array();
				$del_flag = 1;
				switch($lim_type){
					case 2:
						$item_capacity = 0;
						$item_capacity = Entry_Option::Get_DirCapacity($post_set['upload']['dir'] . "/" . $Items[$no]['ent_no']);
						if(($total_capacity + $item_capacity) < $post_set['upload']['capacity']){
							$del_flag = 0;
							//残すログの容量計算
							$total_capacity += $item_capacity;
						}
					break;
				}

				//削除対象ログの削除
				if($del_flag){
					$del_no_list[] = $Items[$no]['ent_no'];
					unset($Items[$no]);
				}
			}
		}
		return array($Items,$del_no_list);
	}

}


class Entry_Option extends Entry{
//Get Directory Capacity
	public static function Get_DirCapacity($Dir){
		$Total_Size = 0;
		$dirs = dir(".".$Dir);
		while(($ent = $dirs->read()) !== False){
			if($ent != ".." and $ent != "." ){
				$path = $Dir."/".$ent;
				if(is_dir(".".$path)){$Total_Size += Entry_Option::Get_DirCapacity($path);}
				else{$Total_Size += filesize(".".$path);}
			}
		}
		return $Total_Size;
	}
	
//Trash Image List Add
	protected static function Trash_ImgList($Target_File){
		global $post_set;
		$trash_list = array();
		if($Target_File and !preg_match("/^http:\/\//",$Target_File)){
			$trash_path = getcwd().$post_set['upload']['dir'].$Target_File;
			if(file_exists($trash_path)){
				array_push($trash_list,$trash_path);
				//Thunbnail
				$s_file_name = pathinfo($trash_path,PATHINFO_DIRNAME) . "/s_" . pathinfo($trash_path,PATHINFO_BASENAME);
				if(file_exists($s_file_name)){array_push($trash_list,$s_file_name);}//サムネイルがある場合は削除リストに追加
			}
		}
		return $trash_list;
	}
	
//Trip Maker
	protected static function Tripy_Maker($Text,$Name,$Algo = "sha256"){
		global $post_set;
		$hash = hash($Algo,$Text);
		if($hash === False){Error_Page::Main("システムエラー","不明なアルゴリズムです。");}
		$trip = crypt($Name,$hash);
		return $Name . "#" . $trip;
	}

//
	protected static function SendMail($No,$Item,$To){
		global $title,$post_set,$data_file;
		$mail_tmp = Files::Load($data_file['mail_temp'],"all");
		$pm = array('/\$log_no/','/\$name/','/\$date/','/\$msg/');
		$rm = array($No,$Item['name'],$Item['date'],$Item['msg']);
		$mail_tmp = preg_replace($pm,$rm,$mail_tmp);

		mb_language("Japanese");
		$from = mb_encode_mimeheader($post_set['mail']['name']);

		$mail_header = "From: ".$from."<" . $post_set['mail']['from'] . "> \r\n".
						"Return-Path: " . $post_set['mail']['from'] . " \r\n".
						"Replay-To: " . $post_set['mail']['from'] . " \r\n".
						"Sender: " . $post_set['mail']['from'] ." \r\n";		
		
		$flag = mb_send_mail($To,$post_set['mail']['title'],$mail_tmp,$mail_header,"-f ".$post_set['mail']['from']);
		return;
	}
}
//Gallery Board - www.tenskystar.net
?>