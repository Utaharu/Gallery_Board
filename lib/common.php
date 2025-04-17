<?php
$lib[] = "Gallery Board - Common Control Ver:1.6";
/* 
- 更新ログ -
get_imgstyleをthumbnailに移動
 v1.6 22/02/22 カテゴリ用の処理の追加。
 v1.5 22/02/03 php8に対応。
 v1.4 19/11/26 Access部の分離

- コントロール -
 Common
  + Get_Date - 現在の日時の取得
  + Set_Cookie - クッキー
  + Get_Item - ログの取得
  + Tag_Adjust - タグ文字列を配列化
  + Get_CategoryName - カテゴリ番号からカテゴリ名を取得
*/

$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Common Control Script Error!</div></html>";exit;}

class Common{
	
	public static function Get_Date(){
		//時間取得   # 日本時間
		$times = time();
		list($sec,$min,$hour,$mday,$mon,$year,$wday)=localtime($times);
		$week = array('日','月','火','水','木','金','土');
		
		// 日時のフォーマット
		$date = sprintf("%04d/%02d/%02d(%s) %02d:%02d",
		       $year+1900,$mon+1,$mday,$week[$wday],$hour,$min);
		return $date;
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
	public static function Get_Item($Type,$Keyword = False){
		global $php,$data_file,$F,$print_set,$count_set,$post_set;
		list($data,$ent_no_list,$up_date_list) = Files::Load($data_file['data'],"line");
		$item_list = array();
		if($data){
			//if($F['mode'] == "view" or $F['mode'] == "enter" or $F['mode'] == "edit"){
			switch($Type){
				case "EntryNo":
					//EntryNo Search
					$key = False;
					if(is_numeric($Keyword)){
						if($ent_no_list and is_numeric($Keyword)){$key = array_search($Keyword,$ent_no_list);}
						if($key !== False){
							$item_list[] = $data[$key];
							$ent_no_list = array($data[$key]['ent_no']);
						}
					}
					if($key === False){Error_Page::Main("エラー","投稿が見つかりませんでした。");}
				break;
				case "Search":
					//KeyWord Search
					mb_regex_encoding("UTF-8");
					$Word_List = mb_ereg_replace("　"," ",$Keyword);
					$Word_List = explode(" ",$Word_List);
					$ent_no_list = array();
					$up_date_list = array();

					$category = False;
					if($post_set['category']['sw'] and $F['category']){$category = Common::Get_CategoryName($F['category']);}
					if(is_array($data) and is_array($Word_List)){
						foreach($data as $item){
							if($category !== False and ($category == "" or $item['category'] !== $category)){continue;}
							$Word_Flag = array_fill(0,count($Word_List),True);//ワード毎のフラグ 初期0化
							if($item){
								//Parent Search
								foreach($Word_List as $word_key=>$Word){
									if($Word){
										if(mb_ereg("^[#＃](.+)",$Word,$match_tag)){
											//Tag Search 2022/3/5 注意　（一致しない場合、フラグをFalseに。）
											if(!preg_grep("/^" . $match_tag[1] . "$/",$item['tag'])){$Word_Flag[$word_key] = False;}
										}else{
											if(
												mb_strpos($item['name'],$Word) === False and 
												mb_strpos($item['entry'],$Word) === False and 
												mb_strpos($item['msg'],$Word) === False and 
												mb_strpos($item['img']['url'],$Word) === False and 
												mb_strpos($item['img']['file'],$Word) === False and 
												count(preg_grep("/" . $Word . "/",$item['tag'])) <= 0
											){
												$Word_Flag[$word_key] = False;
											}
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
					if(count($item_list) <= 0){
						if($category !== False and !$category){Error_Page::Main("エラー","カテゴリーが見つかりませんでした。");}
					}
				break;
				default:
					$item_list = $data;
			}
		}
		$return = array($item_list,count($item_list),$ent_no_list,$up_date_list);
		return $return;
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
	
//CategoryName - Get & Check 
	public static function Get_CategoryName($Category){
		global $post_set,$print_set;
		$category_name = "";
		if(is_numeric($Category) and is_array($post_set['category']['name'])){
			//CategoryNo -> CategoryName
			if(count($post_set['category']['name']) >= $Category){
				$category_name = $post_set['category']['name'][(intval($Category)-1)];
			}
		}elseif(array_search($Category,$post_set['category']['name'],true) !== False){
			$category_name = $Category;
		}elseif($Category === $print_set['category']['all']){
			$category_name = false;
		}
		return $category_name;
	}

}
//Gallery Board - www.tenskystar.net
?>