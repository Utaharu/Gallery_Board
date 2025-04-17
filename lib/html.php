<?php
$lib[] = "Gallery Board - Html Control Ver:2.3";
/*
- 更新ログ -
 v2.4 22/04/  レスの削除処理の調整。 
 v2.3 22/03/13 コード上における、処理の分解。
 v2.2 22/03/08 Convert_Link移動。Convert_Linkが正しい動作をしていなかったのを修正。
 v2.1 22/02/22 カテゴリ用の処理の追加。 
 v2.0 22/02/03 php8に対応。
　v1.9 19/08/27 tag表示されないのを修正

- Html 出力 -
 Html
  + Index - 出力コントロール
  + Get_ImageURL - 画像ファイルとリンク用URLの取得
  - Category_Link - カテゴリリンクの出力
 
 ListView_Page
  + Main - リストページの出力
 
 Preview_Page
  + Main - 詳細ページの出力

 Page_Guide
  + NextBack 
  + Number

 Error_Page
  + Main - エラーページの出力

 Edit_Form
  + Main - 投稿フォーム出力
  - Load_Cookie - クッキーの読み込み
  - Category_List - カテゴリ欄の出力
  - Res_List - 削除用のレス情報の出力
  - Color_List - 文字色リストの出力
  - Required_Marker - 必須マークの出力
  - Upload_Limit - アップロード可能容量の出力
  
 Operation_Form
  + Main - 操作フォーム出力

 Item_Printer
  + Main - アイテム値の出力
  - Good_Count　- Good Countの出力制御
  - PV_Count - PageView Countの出力制御
  - Tag_Linker - タグの出力制御
  - Movie_Embed - 動画の埋め込み再生の出力制御
  - Convert_Link - コメントのURLにリンクタグを付与
  - Res_Quote - レス引用のリンク付与
*/
$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Html Script Error!</div></html>";exit;}

class Html{
	
	public static function Index($Page,$Caria){
		global $F,$data_file,$print_set,$post_set,$home,$php,$title,$help,$lock_fr,$code_set,$lock;
		$gb_cr = Html::Foot();
		if(!$Page or $Page < 0){$Page = 1;}
		$template_file = "";
		$row_num = 0;
		$col_num = 0;
		
		switch($Caria){
			case "pc":
				$template_file = $data_file['template']; 
				$col_num = $print_set['list']['pc_col'];
				$row_num = $print_set['list']['pc_row'];
			break;
			default:
				$template_file = $data_file['mb_templat'];
				$col_num = $print_set['list']['mb_col'];
				$row_num = $print_set['list']['mb_row'];				
			break;
		}
		
		//Load Template
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"SH");
			$main_tmp = Files::Load($template_file,"all");		
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		
		if(!preg_match("/".base64_decode("aHR0cHM6XC9cL3d3d1wudGVuc2t5c3RhclwubmV0XC8=")."/",$gb_cr)){Error_Page::Main("Error!","Error");}
		preg_match("/.*?Ver\.([0-9]+\.[0-9]+(?:\.[0-9]+)?).+?/",$gb_cr,$glp);

		//Item Get - Control
		$get_key = False;
		$get_mode = "";
		$parent_no = "";
		if(preg_match("/([0-9]+)(-([0-9]+))?/",$F['prevno'],$prev_match)){$parent_no = $prev_match[1];}
		if(($F['mode'] == "view" or $F['mode'] == "enter")){$get_mode = "EntryNo"; $get_key = $parent_no;}
		elseif($F['mode'] == "edit"){$get_mode = "EntryNo"; $get_key = $parent_no;}
		elseif($F['mode'] != "new" and $F['mode'] != "op"){
			if($F['search']){$get_mode = "Search"; $get_key = $F['search'];}
			elseif($post_set['category']['sw'] and $F['category']){$get_mode = "Search"; $get_key = "";}
			else{$get_mode = "All"; $get_key = "";}
		}
		
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");
			//DayLimit Delete
			Maintain::Daily_Check();
			//Get Log
			$items = array();
			if($get_key !== False){
				list($items,$sum_cnt,$ent_no_list,$up_date_list) = Common::Get_Item($get_mode,$get_key);
			}
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");

		//記事をソート
		if(!$F['mode'] and count($items) > 1){
			if($print_set['new']['position']){array_multisort($up_date_list,SORT_DESC,SORT_NUMERIC,$ent_no_list,SORT_DESC,SORT_NUMERIC,$items);}
		}
		
		//Title
		if($F['mode'] == "op"){$hdtitle = $title['op'];}
		elseif($F['mode'] == "new"){$hdtitle = $title['new'];}
		elseif($F['mode'] == "rewrite"){$hdtitle = $title['edit'];}
		elseif($F['prevno'] and count($items) === 1 and $items[0]['entry']){$hdtitle = str_replace('$entry',$items[0]['entry'],$title['view']);}
		elseif($F['page'] > 1 ){$hdtitle = $title['page'];}
		else{$hdtitle = $title['main'];}
		
		$c_code = "PGRpdiBzdHlsZT0ibWFyZ2luOjE1cHggMDsgdGV4dC1hbGlnbjpjZW50ZXI7Ij5HYWxsZXJ5IEJvYXJkIFZlci4gLSA8YSBocmVmPSJodHRwczovL3d3dy50ZW5za3lzdGFyLm5ldC8iPuWkqeepuuOBruW9vOaWuTwvYT48L2Rpdj4K";
		$c_code = preg_replace("/".base64_decode("VmVyLg==")."/",base64_decode("VmVyLg==").$glp[1],base64_decode($c_code));
		$main_tmp .= mb_convert_encoding($c_code,$code_set['system'],$code_set['list']);
		$main_tmp = preg_replace("/<head>/",mb_convert_encoding(base64_decode("PCEtLSBHYWxsZXJ5IEJvYXJkIC0gk1aL84LMlN6V+yAoaHR0cDovL3d3dy50ZW5za3lzdGFyLm5ldC8pLS0+Cg==")."<head>",$code_set['system'],$code_set['list']),$main_tmp);

		//Page数 計算
		$max_page = 1;
		if(!$F['mode']){$max_page = ceil(count($items) / ($col_num * $row_num));}//List Page
		if(($F['mode'] == "view" or $F['mode'] == "enter") and is_array($items[0]['res'])){$max_page = ceil(count($items[0]['res']) / $print_set['res']['num']);}//Preview Page
		if($max_page <= 0){$max_page = 1;}
		if($max_page < $Page){$Page = $max_page;}
		
		//出力制御
		//- Preview Page
		$main_tmp = Preview_Page::Main($main_tmp,$items,$Page);
		//- List Page
		$main_tmp = ListView_Page::Main($main_tmp,$items,$row_num,$col_num,$Page);
		//- Edit Form
		$prev_no = "";
		$edit_type = "";
		if($F['mode'] == "new"){$edit_type = "parent"; $prev_no = "";}
		elseif($F['mode'] == "edit"){$edit_type = "rewrite"; $prev_no = $F['prevno'];}
		elseif($F['mode'] == "view" or $F['mode'] == "enter"){$edit_type = "child"; $prev_no = $items[0]['ent_no'] . "-" . ($items[0]['res_cnt'] + 1);}
		$main_tmp = Edit_Form::Main($main_tmp,$edit_type,$prev_no);
		//- Operation Form
		$main_tmp = Operation_Form::Main($main_tmp,$prev_no);
		//- カテゴリのリンク出力
		$main_tmp = Html::Category_Link($main_tmp,$F['category']);		
		//- ページナビリンク 1 ($back $next)
		$main_tmp = Page_Guide::NextBack($main_tmp,$Page,$max_page);
		//- ページナビリンク 2 (ページ番号)
		$main_tmp = Page_Guide::Number($main_tmp,$Page,$max_page);

		$pm = array('/\$hdtitle/','/\$prevw/','/\$prevh/','/\$imgw/','/\$imgh/','/\$page/','/\$php/','/\$home/','/\$mhome/','/\$title/','/<!--.*?(EditForm|OpForm|ImagePreview|ImageList).*?-->/i','/\$page_max/','/\$prev_no/','/\$movie_help/','/\$op_help/','/\$rw_c/','/\$search/','/\$css/','/\$javascript/');
		$rm = array($hdtitle,$print_set['preview']['img_w'],$print_set['preview']['img_h'],$print_set['list']['img_w'],$print_set['list']['img_h'],$Page,$php['main'],$home['pc'],$home['mb'],$title['main'],"",$max_page,$F['prevno'],$help['movie'],$help['op'],$code_set['system'],$F['search'],$php['lib']['css'],$php['lib']['javascript']);
		
		print preg_replace($pm,$rm,$main_tmp);
		
		return;
	}

	public static function Foot(){
		global $lib;
		$ver = @preg_filter("/Gallery\s?Board\s?Ver:([0-9]+\.[0-9]+(?:\.[0-9]+)?)/i",'$1',$lib)[0];
		$foot = "<div style=\"margin:15px 0; text-align:center;\">Gallery Board Ver.$ver - <a href=\"https://www.tenskystar.net/\">天空の彼方</a></div>\n";
		return $foot;
	}

//Html Print - ImageURL Control. 
	public static function Get_ImageURL($Item){
		global $post_set,$movie_set,$php,$F;
		$img_file = ""; $img_url = "";
		if($Item['img']['file']){
			//画像ファイル
			if(!$Item['img']['url']){
				$img_file = "." . $post_set['upload']['dir'].$Item['img']['file'];
				$img_url = $img_file;
			}elseif($Item['img']['url']){
				if(!preg_match("/^https?:\/\//",$Item['img']['file'])){$img_file = "." . $post_set['upload']['dir'] . $Item['img']['file'];}
				else{$img_file = $Item['img']['file'];}
				$img_url = $Item['img']['url'];
			}
			//サムネイル表示
			if($post_set['upload']['thumbnail']['sw'] and $F['mode'] != "edit"){
				if($img_file and !preg_match("/^https?:\/\//",$img_file)){
					$thimg = pathinfo($img_file,PATHINFO_DIRNAME) . "/s_". pathinfo($img_file, PATHINFO_BASENAME);
					if(!file_exists($thimg)){
						if(!method_exists('Save_Thumbnail','Upload_File')){Error_Page::Main("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
						Save_Thumbnail::Upload_File($img_file,$thimg);
					}
					if(file_exists($thimg)){$img_file = $thimg;}
				}
			}
		}
		return array($img_file,$img_url);
	}

//Category Link
	private static function Category_Link($Main_Tmp,$Selected = ""){
		global $F,$post_set,$print_set;
		$cate_tmp = "";
		$category_html = "";
		$checked_flag = false;
		if(preg_match("/<!--\s?CategoryArea\s?-->(.+)<!--\s?CategoryArea\s?-->/is",$Main_Tmp,$cate_match)){
			if($post_set['category']['sw'] and count($post_set['category']['name']) > 0){
				$cate_tmp = $cate_match[1];
				if(preg_match("/<!--\s?CategoryList:(.+)\s?-->/is",$cate_tmp,$c_match)){
					//Category - 「All」 Add
					array_unshift($post_set['category']['name'],$print_set['category']['all']);
					
					for($cnt = 0; $cnt < count($post_set['category']['name']); $cnt++){
						$category_line = $c_match[1];
						$pm = array('/\$category_no/','/\$category_url/','/\$category/');
						$rm = array($cnt,"?category=".$cnt,$post_set['category']['name'][$cnt]);
						$category_line = preg_replace($pm,$rm,$category_line);
						
						if($Selected == $cnt or (!$Selected and $cnt == 0) or $Selected == $post_set['category']['name'][$cnt]){
							$pm  = array("/(<option[^>]+?)>/is","/(<input.*?type=\"?radio\"?[^>]+?)>/","/<a.+?>(.+?)<\/a>/");
							$rm  = array('$1 selected>','$1 checked>','$1');
							$category_line = preg_replace($pm,$rm,$category_line);
						}
						$category_html .= $category_line;
					}
				}
				$cate_tmp = preg_replace('/<!--\s?CategoryList:.+\s?-->/is',$category_html,$cate_tmp);
			}
		}
		return preg_replace("/<!--\s?CategoryArea\s?-->.+<!--\s?CategoryArea\s?-->/is",$cate_tmp,$Main_Tmp);
	}

}

//ListView Page
class ListView_Page extends Html{
// - Image List
	public static function Main($Main_Tmp,$Item,$Row_Num,$Col_Num,$Page){
		global $F,$print_set;
		$lpage_html = "";
		$item_min = 0;
		$item_max = 0;
		$item_sum = 0;
		
		//Image List Tmp
		if(preg_match("/<!--\s?ImageList\s?-->(.+)<!--\s?ImageList\s?-->/is",$Main_Tmp,$il_match)){
			if(!$F['mode'] or $F['mode'] == "Pickup"){
				$lpage_tmp = $il_match[1];
				if(preg_match("/<!--\s?ListBox\s?-->(.+)<!--\s?ListBox\s?-->/is",$lpage_tmp,$lb_match)){
					$lb_tmp = $lb_match[1];
					$lb_html = "";
					if(preg_match("/<!--\s?ItemBox\s?-->(.+)<!--\s?ItemBox\s?-->/is",$lb_tmp,$ib_match)){
						$lb_tmp = preg_replace("/<!--\s?ItemBox\s?-->.+<!--\s?ItemBox\s?-->/is",'$i_box',$lb_tmp);
						$ib_tmp = $ib_match[1];
						$blank_tmp = preg_replace("/<!--\s?Item\s?-->([.(\n\r\w\W]+?)<!--\s?Item\s?-->/i","<div style=\"margin:" . ($print_set['list']['img_h']/2-10)."px 0;\">".$print_set['list']['no_img']."</div>",$ib_tmp);
						
						$ib_html = "";
						$y_count = 0;
						$x_count = 0;
						
						//Item - 出力件数
						$item_sum = count($Item);
						if($item_sum > 0){
							$item_min = $Col_Num * $Row_Num * ($Page-1) + 1;
							$item_max = $item_min - 1;
						}
						
						for($y_for = 0; $y_for < $Row_Num; $y_for++){
							$key = $Col_Num * $Row_Num * ($Page-1) + $Col_Num * $y_for;
							for($x_for = 0; $x_for < $Col_Num; $x_for++){
								if(!$print_set['list']['no_img']){break;}
								if(count($Item) > $key){
									if($Item[$key]){
										//Parent Print
										$ib_html .= Item_Printer::Main($Item[$key]['ent_no'],0,$Item[$key],$ib_tmp);
										$item_max++;
									}
								}else{
									$ib_html .= $blank_tmp;
								}
								$x_count++;
								if($x_count >= $Col_Num){
									$lb_html .= str_replace('$i_box',$ib_html,$lb_tmp);
									$ib_html = "";
									$x_count = 0;
									$y_count++;
								}									
								$key++;
							}
							//Blank Frame Print
							$lb_html .= str_replace('$i_box',$ib_html,$lb_tmp);
						}
					}else{Error_Page::Main("エラー","テンプレートが正しくありません。(ItemBox)");}
									
					$lpage_html = preg_replace("/<!--\s?ListBox\s?-->.+<!--\s?ListBox\s?-->/is",$lb_html,$lpage_tmp);
				}else{Error_Page::Main("エラー","テンプレートが正しくありません(ListBox)");}
			}
			//LogPrint
			if($F['search'] and !$lpage_html){$lpage_html = "<div>一致するのはありません</div>\n";}
			
			//件数出力　- 置換
			$pm = array('/\$item_sum/','/\$item_min/','/\$item_max/');
			$rm = array($item_sum,$item_min,$item_max);
			$lpage_html = preg_replace($pm,$rm,$lpage_html);
			
		}else{Error_Page::Main("エラー","テンプレートが正しくありません(ImageList)");}		

		return preg_replace("/<!--\s?ImageList\s?-->.+<!--\s?ImageList\s?-->/is",$lpage_html,$Main_Tmp);
	}
	
}

//Preview Page
class Preview_Page extends Html{
// - Image Preview
	public static function Main($Main_Tmp,$Item,$Page){
		global $F,$print_set,$post_set,$count_set,$good_set;
		$ppage_html="";
		if(preg_match("/<!--\s?ImagePreview\s?-->(.+)<!--\s?ImagePreview\s?-->/is",$Main_Tmp,$ip_match)){
			if($F['mode'] == "view" or $F['mode'] == "enter"){
				$ppage_tmp = $ip_match[1];
				if(preg_match("/<!--\s?Item\s?-->(.+)<!--\s?Item\s?-->/is",$ppage_tmp,$ib_match)){
					$ib_tmp = $ib_match[1];
					$ppage_tmp = preg_replace("/<!--\s?Item\s?-->.+<!--\s?Item\s?-->/is",'$i_box',$ppage_tmp);
					
					if(preg_match("/<!--\s?ResItem\s?-->(.+)<!--\s?ResItem\s?-->/is",$ppage_tmp,$ri_match)){
						$ri_tmp = $ri_match[1];
						$ppage_tmp = preg_replace("/<!--\s?ResItem\s?-->(.+)<!--\s?ResItem\s?-->/is",'$r_item',$ppage_tmp);
					}
					if($Item){
						$Item =& $Item[0];
						//PV Count
						if($F['mode'] == "enter" and $count_set['pvc']['sw']){
							if(!method_exists('PV_Counter','PV_Up')){Error_Page::Main("システム エラー","カウンター用スクリプトが読み込まれていません。");}
							PV_Counter::PV_Up($Item['ent_no']);
						}
						//Good Count
						if($F['good']){
							if(!method_exists('Good_Counter','Good_Up')){Error_Page::Main("システム エラー","カウンター用スクリプトが読み込まれていません。");}
							Good_Counter::Good_Up($Item,$F['good']);
						}
						
						//Parent Print
						$ib_html = Item_Printer::Main($Item['ent_no'],0,$Item,$ib_tmp);
						
						//Res Num
						$resv_min = 0;
						$resv_max = 0;
						if(($F['mode'] == "view" or $F['mode'] == "enter") and count($Item['res']) > 0){
							$resv_min = $print_set['res']['num'] * ($Page-1);
							$resv_max = $resv_min + $print_set['res']['num'];
							if(count($Item['res']) < $resv_max){$resv_max = count($Item['res']);}
						}
						
						//最新のレスを先頭に
						if($print_set['res']['sort'] and count($Item['res']) >1){$Item['res'] = array_reverse($Item['res']);}
						//Children (Res) Print
						$ri_html = "";
						
						for($res_cnt = $resv_min; $res_cnt < $resv_max; $res_cnt++){
							if($Item['res'][$res_cnt]){
								//レスを出力
								$ri_html .= Item_Printer::Main($Item['ent_no'],$Item['res'][$res_cnt]['ent_no'],$Item,$ri_tmp);
							}
						}
						if(!$ri_html){$ri_html= "<div class=\"Item_Box\">レスはありません。</div>";}
		
						$ppage_html = str_replace('$r_item',$ri_html,$ppage_tmp);
						$ppage_html = str_replace('$i_box',$ib_html,$ppage_html);
						
						//レスフォーム
						if($post_set['res']['sw'] > 0){$ppage_html .= '$e_form';}
	
					}else{
						$ppage_html = "<div>詳細が見つかりませんでした。</div>\n";
					}
				}else{Error_Page::Main("エラー","テンプレートが正しくありません(ImagePreview - Item)");}
			}
			$Main_Tmp = preg_replace("/<!--\s?ImagePreview\s?-->.+<!--\s?ImagePreview\s?-->/is",$ppage_html,$Main_Tmp);
		}else{
			if($F['mode'] == "view" or $F['mode'] == "enter"){Error_Page::Main("エラー","テンプレートが正しくありません(ImagePreview)");}
		}
		return $Main_Tmp;
	}

}

//Page Guide
class Page_Guide extends Html{

//Page Navigation - NextBack
	public static function NextBack($Main_Tmp,$Page,$Max_Page){
		global $F,$php;
		$pnum = "";
		$next = "";
		$back = "";
		if(preg_match('/(\$next|\$back)/',$Main_Tmp)){
			$pnum = $Max_Page;
			$next_page = $Page+1;
			$back_page = $Page-1;
			
			if($F['mode'] == "view" or $F['mode'] == "enter"){
				if($Page > 1){
					$back = $php['base_url'].$php['main']."?mode=view&amp;prevno=".$F['prevno']."&amp;page=".$back_page;
				}else{$back="";}
				if($Page < $Max_Page){
					$next = $php['base_url'].$php['main']."?mode=view&amp;prevno=".$F['prevno']."&amp;page=".$next_page;
				}else{$next="";}
			}else{
				//Back Page Link
				if($Page > 1){
					$back = $php['base_url'].$php['main']."?";
					if($F['category']){$back .= "category=" . $F['category'] . "&";}
					if($F['search']){$back .= "search=" . $F['search'] . "&";}
					$back .= "page=" . $back_page;
				}else{$back="";}
				//Next Page Link
				if($Page < $Max_Page){
					$next = $php['base_url'] . $php['main']."?";
					if($F['category']){$next .= "category=" . $F['category'] . "&";}
					if($F['search']){$next .= "search=" . $F['search'] . "&";}
					$next .= "page=" . $next_page;
				}else{$next="";}
			}

			//ナビゲートリンクの解除
			if(preg_match_all("/(<a.*?>(.+?)<\/a>)/",$Main_Tmp,$glp,PREG_SET_ORDER)){
				for($x=0; $x < (count($glp)-1); $x++){
					if(!$back){
						$glp[$x][3] = preg_replace('/<a.*?href=\"?\$back[^>]+>.+?<\/a>/',$glp[$x][2],$glp[$x][1]);
						$Main_Tmp = str_replace($glp[$x][1],$glp[$x][3],$Main_Tmp);
					}
					if(!$next){
						$glp[$x][3] = preg_replace('/<a.*?href=\"?\$next[^>]+>.+?<\/a>/',$glp[$x][2],$glp[$x][1]);
						$Main_Tmp = str_replace($glp[$x][1],$glp[$x][3],$Main_Tmp);
					}
				}
			}
		}
		$pm = array('/\$pnum/','/\$next/','/\$back/');
		$rm = array($pnum,$next,$back);
		return preg_replace($pm,$rm,$Main_Tmp);
	}
	
//Page Navigation - Number
	public static function Number($Main_Tmp,$Page,$Max_Page){
		global $F,$php,$print_set;
		$num_linker = "";
		if(strpos($Main_Tmp,'$p_navi') !== False){
			if($Page <= $Max_Page){
				$for_min = ($Page - $print_set['navi']);
				if($for_min <= 0){$for_min = 1;}
				for($cnt = $for_min; $cnt <= ($Page + $print_set['navi']); $cnt++){
					if($Max_Page < $cnt){break;}
					if($Page != $cnt){
						if($F['mode'] == "view" or $F['mode'] == "enter"){
							$num_linker .= "<a href=\"".$php['base_url'].$php['main']."?mode=view&amp;prevno=".$F['prevno']."&amp;page=".$cnt."\">".$cnt."</a> ";
						}else{
							$num_linker .= "<a href=\"".$php['base_url'].$php['main']."?";
							if($F['category']){$num_linker .= "category=" . $F['category'] ."&";}
							if($F['search']){$num_linker .= "search=" . $F['search'] . "&";}
							$num_linker .= "page=".$cnt."\">".$cnt."</a> ";
						}
					}else{$num_linker .= "<b>".$cnt."</b> ";}
				}
			}
		}
		$pm = array('/\$p_navi/');
		$rm = array($num_linker);
		return preg_replace($pm,$rm,$Main_Tmp);
	}
	
}

//Error Page
class Error_Page extends Html{

	public static function Main($Err_Title,$Err_Msg,$DelFile = array()){
		global $title,$post_set,$lock_fr,$lock;
		setcookie("auth","",time()-1000);
		$D_Trace = debug_backtrace();
		$title = $Err_Title;
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		if(array_key_exists('up_file',$_FILES)){
			if($_FILES['up_file']['name'] and $_FILES['up_file']['error'] == UPLOAD_ERR_OK and file_exists($_FILES['up_file']['tmp_name'])){unlink($_FILES['up_file']['tmp_name']);}
		}
		
		if(is_array($DelFile)){
			foreach($DelFile as $line){
				if($line){
					if(is_file($line)){
						if(file_exists($line)){@unlink($line);}
					}
				}
			}
		}
		
		print "<head><title>".$Err_Title."</title></head>\n";
		print "<p style=\"text-align:center; font:bold;\">" . $Err_Title . "</p>\n";
		print "<div style=\"text-align:center; color:red;\">" . $Err_Msg . "</div>\n";
		print Html::Foot();
		
		exit;
	}
	
}

class Edit_Form extends Html{

//Edit Form - 投稿フォームの表示制御
 	public static function Main($Main_Tmp,$Type,$Entry_No){
		global $F,$php,$print_set,$post_set,$admin,$code_set;
		
		$ef_html = "";
		$ef_tmp = "";
		
		if(preg_match("/<!--\s?EditForm\s?-->(.+)<!--\s?EditForm\s?-->/is",$Main_Tmp,$ef_match)){
			if($F['mode'] == "new" or $F['mode'] == "edit" or $F['mode'] == "view" or $F['mode'] == "enter"){
				$ef_tmp = $ef_match[1];
				$item = array("entry"=>"","name"=>"","msg"=>"","color"=>"","pass"=>"","res"=>"","hp_url"=>"","mail"=>"","auth"=>"","tag"=>"","category"=>"",'ip'=>"");
				//Load Cookie
				$item = Edit_Form::Load_Cookie($Type,$item);
				//Edit - Load Item
				if($Type == "rewrite"){
					if(!method_exists('Entry','Operation')){Error_Page::Main("システム エラー","エントリー用スクリプトが読み込まれていません。");}
					$item = Entry::Operation("Edit",$Entry_No,$F['pass']);
					$item['msg'] = preg_replace("/(<BR>|<br\s?\/>)/i","\n",$item['msg']);
				}
		
				//投稿フォーム名
				$ftyu = "";
				if($Type == "parent"){$form_ent = $print_set['form']['new'];}
				elseif($Type == "rewrite"){$form_ent = $print_set['form']['edit'];$ftyu="<BR /> 差替える場合のみ、新しいファイルを指定してください。";}
				else{$form_ent=$print_set['form']['res'];$Type = "child";}
				
				//必須マーク
				$ef_tmp = Edit_Form::Required_Marker($ef_tmp,$Type);
		
				//アップロード可能サイズ
		 		$ef_tmp = Edit_Form::Upload_Limit($ef_tmp);
				
				//表示制御
				//- Title Area
				if($Type != "parent" and $Type != "rewrite"){
					$ef_tmp = preg_replace("/<!--\s?TitleArea\s?-->(.+)<!--\s?TitleArea\s?-->/is","",$ef_tmp);
				}
				//- Image Auth
				if(!$post_set['auth']['type']){
					$ef_tmp = preg_replace("/<!--\s?AuthArea\s?-->(.+)<!--\s?AuthArea\s?-->/is","",$ef_tmp);
				}
				//- SiteUrl Area
				if(!$post_set['hp_url']){
					$ef_tmp = preg_replace("/<!--\s?SiteUrlArea\s?-->(.+)<!--\s?SiteUrlArea\s?-->/is","",$ef_tmp);
				}
				//- Mail Area
				if(!$post_set['mail']['sw']){
					$ef_tmp = preg_replace("/<!--\s?MailArea\s?-->(.+)<!--\s?MailArea\s?-->/is","",$ef_tmp);
				}
				//- News Area
				if(($Type != "rewrite" and $Type != "parent") or !$post_set['mail']['sw'] or !$post_set['mail']['user_sw'] ){
					$ef_tmp = preg_replace("/<!--\s?NewsArea\s?-->(.+)<!--\s?NewsArea\s?-->/is","",$ef_tmp);
				}
				//- File Upload Area
				if(($post_set['upload']['mode'] == 1) or ($Type == "child" and $post_set['res']['sw'] == 1)){
					$ef_tmp = preg_replace("/<!--\s?FileUpArea\s?-->.+<!--\s?FileUpArea\s?-->/is","",$ef_tmp);
				}
				//- Movie Upload Area
				if(($post_set['upload']['mode'] < 1) or ($Type == "child" and $post_set['res']['sw'] == 1)){
					$ef_tmp = preg_replace("/<!--\s?MovieUpArea\s?-->.+<!--\s?MovieUpArea\s?-->/is","",$ef_tmp);
				}
				//- Tag Area
				if((($Type == "parent" or $Type == "rewrite") and !$post_set['parent']['tag']) or ($Type == "child" and !$post_set['res']['tag'])){
					$ef_tmp = preg_replace("/<!--\s?TagArea\s?-->.+<!--\s?TagArea\s?-->/is","",$ef_tmp);
				}
				//- Color List
				$ef_tmp = Edit_Form::Color_List($ef_tmp,$item['color']);
				//- Category List
				$ef_tmp = Edit_Form::Category_List($ef_tmp,$Type,$item['category']);
				
				$file_url = "";
				if($Type == "rewrite"){
					//画像ファイル
					list($img_file,$img_url) = Html::Get_ImageURL($item);
					if(preg_match("/^https?:\/\//",$img_url)){$file_url = $item['img']['url'];}
					elseif(!preg_match("/^https?:\/\//",$img_file)){$ef_tmp = preg_replace("/(<input.*?type=\"?file\"?.*?name=\"?up_file\"?[^>]+?)>/is",'$1> [<a href="'.$img_file.'" target="_blank">現在</a>]',$ef_tmp);}
				}

				//ラジオボタン - 自動チェック
				if($Type != "child"){
					$ef_tmp = preg_replace('/(<input.*?name=\"utp\".*?value=\"?file\"?[^>]+?)>/is','$1 checked>',$ef_tmp);
					if($file_url or $post_set['upload']['mode'] == 1){$ef_tmp = preg_replace('/(<input.*?name=\"utp\".*?value=\"?movie\"?[^>]+?)>/is','$1 checked>',$ef_tmp);}
				}
				
				//レス削除用
				$res_html = Edit_Form::Res_List($Type,$item['res']);
				
				//Tag
				$tag_string = "";
				if(is_array($item['tag'])){
					if(count($item['tag']) > 0){
						$tag_string = implode(" ",$item['tag']);
					}
				}
				
				$pm = array('/\$php/','/\$form_entry/','/\$name/','/\$entry/','/\$msg/','/\$tag/','/\$pass/','/\$file_url/','/\$ftyu/','/\$type/','/\$prevno/','/\$rule/','/<\/form>/i','/\$host/','/\$ia_tyu/','/\$hp_url/','/\$mail/');
				$rm = array($php['main'],$form_ent,$item['name'],$item['entry'],$item['msg'],$tag_string,$item['pass'],$file_url,$ftyu,$Type,$Entry_No,$print_set['rule'],$res_html."</form>",$item['ip'],$post_set['auth']['caution'],$item['hp_url'],$item['mail']);
				$ef_html = preg_replace($pm,$rm,$ef_tmp);
				
				$res_num = explode("-",$Entry_No); 
				if(($F['mode'] == "view" or $F['mode'] == "enter") and $post_set['res']['max_num'] > 0 and $res_num[1] > $post_set['res']['max_num']){$e_form = "<div style=\"color:red; text-align:center;\">レス数が".$post_set['res']['max_num']."を超えた為、これ以上レスを投稿できません。</div>\n";}
			}
			if($F['mode'] == "edit" and !$item){$ef_html = "<div>編集するデータが見つかりませんでした</div>\n";}

			if($F['mode'] == "new" or $F['mode'] == "edit"){$Main_Tmp = preg_replace("/<!--\s?EditForm\s?-->.+<!--\s?EditForm\s?-->/is",'$e_form',$Main_Tmp);}
			$pm = array("/<!--\s?EditForm\s?-->.+<!--\s?EditForm\s?-->/is",'/\$e_form/is');
			$rm = array("",$ef_html);
			$Main_Tmp = preg_replace($pm,$rm,$Main_Tmp);
		}
		return $Main_Tmp;
	}
	
	private static function Load_Cookie($Type,$Item){
		global $F,$code_set;
		if($Type == "parent" or $Type == "child"){
			if($F['name']){$Item['name'] = $F['name'];}
			elseif(array_key_exists('name',$_COOKIE)){
				$Item['name'] = mb_convert_encoding($_COOKIE['name'],$code_set['system'],$code_set['list']);
			}
			if($F['color']){$Item['color'] = $F['color'];}
			elseif(array_key_exists('color',$_COOKIE)){$Item['color'] = $_COOKIE['color'];}
			if($F['pass']){$Item['pass'] = $F['pass'];}
			elseif(array_key_exists('kword',$_COOKIE)){$Item['pass'] = $_COOKIE['kword'];}
			if($F['hp_url']){$Item['hp_url'] = $F['hp_url'];}
			elseif(array_key_exists('url',$_COOKIE)){$Item['hp_url'] = $_COOKIE['url'];}
		}
		return $Item;
	}
	
	private static function Category_List($Editform_tmp,$Type,$Selected){
		global $post_set;
		$cate_tmp = "";
		$category_html = "";
		
		if(preg_match("/<!--\s?CategoryArea\s?-->(.+)<!--\s?CategoryArea\s?-->/is",$Editform_tmp,$cate_match)){
			$checked_flag = false;
			if($post_set['category']['sw'] and count($post_set['category']['name']) > 0 and ($Type == "parent" or $Type == "rewrite")){
				$cate_tmp = $cate_match[1];
				if(preg_match("/<!--\s?CategoryList:(.+)\s?-->/is",$cate_tmp,$c_match)){
					$category_no = 0;
					if($Type == "rewrite" and $Selected and array_search($Selected,$post_set['category']['name']) === False){
						array_unshift($post_set['category']['name'],$Selected);
						$category_no = -1;
					}
					for($cnt = 0; $cnt < count($post_set['category']['name']); $cnt++){
						$category_no++;
						$category_line = $c_match[1];
						$pm = array('/\$category_no/','/\$category/');
						$rm = array($category_no,$post_set['category']['name'][$cnt]);
						$category_line = preg_replace($pm,$rm,$category_line);
						
						if($Selected == $post_set['category']['name'][$cnt]){
							$pm  = array("/(<option[^>]+?)>/is","/(<input.*?type=\"?radio\"?[^>]+?)>/");
							$rm  = array('$1 selected>','$1 checked>');
							$category_line = preg_replace($pm,$rm,$category_line);
						}
						$category_html .= $category_line;
					}
				}
				$cate_tmp = preg_replace('/<!--\s?CategoryList:.+\s?-->/is',$category_html,$cate_tmp);
			}
		}
		return preg_replace("/<!--\s?CategoryArea\s?-->.+<!--\s?CategoryArea\s?-->/is",$cate_tmp,$Editform_tmp);
	}
	
	private static function Res_List($Type,$Res){
		global $F,$post_set,$admin;
		$res_html="";
		if($Type == "rewrite" and $Res and ($post_set['res']['del_mode'] == 0 or $post_set['res']['del_mode'] == 2 or $F['pass'] == $admin['pass'])){
			foreach ($Res as $res_line){
				if($res_line['name']){
					if(!$res_html){$res_html .=  "<div>削除したいレスを選択してください。</div>\n";}
					
					//Img Link and Http Link
					$res_img = "";$res_url = "";
					list($res_img,$res_url) = Html::Get_ImageURL($res_line);
					if(preg_match("/^https?:\/\//",$res_url)){$res_url = $res_line['img']['url'];}
				
					$res_html .=  "<hr>\n"
									."<div style=\"display:block; clear:both;\">"
										."<div style=\"display:inline-block; width:40px; padding:10px 5px; float:left;\"><input type=\"checkbox\" name=\"res_flag[]\" value=\"".$res_line['ent_no']."\"></div>"
										."<div style=\"display:inline-block; width:500px;\">"
											."<div style=\"display:block; width:100%;\">"
												."<div style=\"display:inline-block; width:100px;\">Res.".$res_line['ent_no']. "</div>"
												."<div style=\"display:inline-block; width:200px;\">".$res_line['date']."</div>"
											."</div><div style=\"display:block; width:100%; padding-top:5px;\">"
												."<div style=\"display:inline-block; width:200px;\">".$res_line['name']."</div>"
												."<div style=\"display:inline-block; width:200px;\">(".$res_line['ip'].")</div>"
											."</div>"
										."</div>";
					if($res_img){$res_html .= "<div style=\"display:inline-block;\">[<a href=\"$res_url\" target=\"_blank\">添付</a>]</div>";}
					$res_html .= "</div>";
//							$F['mode'] = $Type;
				
				}
			}
		}
		if($Type == "rewrite" and $F['pass'] == $admin['pass']){$res_html .= "<input type=\"hidden\" name=\"admin\" value=\"".hash("sha256",$F['pass'])."\" />\n";}
		return $res_html;
	}
	
	private static function Color_List($Editform_tmp,$Selected = ""){
		global $print_set;
		$color_html = "";
		if(preg_match("/<!--\s?ColorList:(.+)\s?-->/is",$Editform_tmp,$cl_match)){
			for($cnt=0; $cnt < count($print_set['msg']['color']); $cnt++){
				$color_line = $cl_match[1];
				if($Selected == $print_set['msg']['color'][$cnt]){
					$pm  = array("/(<option[^>]+?)>/is","/(<input.*?type=\"?radio\"?[^>]+?)>/");
					$rm  = array('$1 selected>','$1 checked>');
					$color_line = preg_replace($pm,$rm,$color_line);
				}				
				$color_html .= str_replace('$color',$print_set['msg']['color'][$cnt],$color_line);
			}
		}
		return preg_replace('/<!--\s?ColorList:.+\s?-->/is',$color_html,$Editform_tmp);
	}
	
	private static function Required_Marker($Editform_tmp,$Type){
		global $post_set,$print_set;
		$required_mark = Array('name' => "",'mail'=>"",'auth'=>"",'msg'=>"",'hp_url'=> "",'pass'=>"",'title'=>"");
		if(!$post_set['no_name']){$required_mark['name'] = $print_set['r_mark'];}
		if($post_set['auth']['type']){$required_mark['auth'] = $print_set['r_mark'];}
		switch($Type){
			case "child":
//					$required_mark['pass'] = $print_set['r_mark'];
				if($post_set['res']['sw'] != 3 and $post_set['res']['sw'] != 4){$required_mark['msg']  = $print_set['r_mark'];}
				if($post_set['hp_url'] > 2 and $post_set['hp_url'] < 5){$required_mark['hp_url'] = $print_set['r_mark'];}
				if($post_set['mail']['sw'] >= 3){$required_mark['mail'] = $print_set['r_mark'];}
			break;
			case "parent":
				$required_mark['pass'] = $print_set['r_mark'];
				$required_mark['title'] = $print_set['r_mark'];
				if($post_set['hp_url'] > 1 and $post_set['hp_url'] < 4){$required_mark['hp_url'] = $print_set['r_mark'];}
				if($post_set['mail']['sw'] > 1 and  $post_set['mail']['sw'] < 4){$required_mark['mail'] = $print_set['r_mark'];}
				if($post_set['parent']['msg']){$required_mark['msg'] = $print_set['r_mark'];}
			break;
		}
		
		$pm = array('/\$h_mark\[name\]/','/\$h_mark\[pass\]/','/\$h_mark\[title\]/','/\$h_mark\[msg\]/','/\$h_mark\[hp_url\]/','/\$h_mark\[auth\]/','/\$h_mark\[mail\]/');
		$rm = array($required_mark['name'],$required_mark['pass'],$required_mark['title'],$required_mark['msg'],$required_mark['hp_url'],$required_mark['auth'],$required_mark['mail']);
		
		return preg_replace($pm,$rm,$Editform_tmp);
	}

//Get_UploadLimit
	private static function Upload_Limit($Editform_tmp){
		global $post_set;
		$unit_text = "";
		if($post_set['upload']['size'] > 0){$unit_text = "B";}
		
		$limit_string = "";
		$limit_val = $post_set['upload']['size'];
		
		$unit_list = array('KB','MB','GB','TB');
		for($pow_cnt = 1; $pow_cnt <= count($unit_list); $pow_cnt++){
			if($limit_val >= 1000){
				$unit_text = $unit_list[($pow_cnt-1)];
				$limit_val = $limit_val / 1000;
			}else{continue;}
		}
		$limit_string = $limit_val . " " . $unit_text;
		$limit_val = $limit_val * pow(1024,$pow_cnt);
		
		$pm = array('/\$up_size/','/\$max_file_size/');
		$rm = array($limit_string,$limit_val);
		
		return preg_replace($pm,$rm,$Editform_tmp);
	}
	
}

class Operation_Form extends Html{
//Operation Page
	public static function Main($Main_Tmp,$Entry_No){
		global $F;
		$opage_tmp = "";
		if(preg_match("/<!--\s?OpForm\s?-->(.+)<!--\s?OpForm\s?-->/is",$Main_Tmp,$op_match)){			
			if($F['mode'] == "op"){
				$opage_tmp = $op_match[1];

				if(!preg_match("/[0-9]+-[0-9]+/",$Entry_No)){$opage_tmp = preg_replace("/(<input.*?type=\"?radio\"?.*?name=\"?mode\"?.*?value=\"?edit\"?[^>]*?)>/is",'$1 checked>',$opage_tmp);}
				if(preg_match("/[0-9]+-[0-9]+/",$Entry_No)){$opage_tmp = preg_replace("/(<input..*?name=\"?mode\"?.*?value=\"?remove[^>]*?)>/is",'$1 checked>',$opage_tmp);}
			}
		}
		$Main_Tmp = preg_replace("/<!--\s?OpForm\s?-->.+<!--\s?OpForm\s?-->/is",$opage_tmp,$Main_Tmp);
		return $Main_Tmp;
	}
}


class Item_Printer extends Html{

//Item variable replace
	public static function Main($Parent_No,$Child_No,$Item,$Tmp){
		global $php,$post_set,$print_set,$count_set,$good_set,$data_file,$F,$hp_url_type,$movie_set,$code_set;
		
		//Res
		if($Child_No > 0){
			$res_ent_list = array_column($Item['res'],"ent_no");
			//ResNo Search
			$res_key = array_search($Child_No,$res_ent_list);
			if($res_key !== False){
				//Res Message - Quote Link
				$Item['res'][$res_key]['msg'] = Item_Printer::Res_Quote($Item,$Item['res'][$res_key]['msg']);
				$Item = $Item['res'][$res_key];
			}
		}
		
		//Message - Convert Link
		$Item['msg'] = Item_Printer::Convert_Link($Item['msg']);
		
		//Site Url - View Ctrl	
		while(preg_match("/<!--\s?SiteUrl:(.+?)\s?-->/i",$Tmp,$match)){
			$url_tmp = "";
			if($Item['hp_url'] and preg_match($hp_url_type,$Item['hp_url'])){
				$url_tmp = $match[1];
			}
			$Tmp = preg_replace("/<!--\s?SiteUrl:.+?-->/is",$url_tmp,$Tmp,1);
		}
		
		//Category
		$cate_html = "";
		if(preg_match("/<!--\s?Category\s?-->(.+)<!--\s?Category\s?-->/is",$Tmp,$cate_match)){
			if($post_set['category']['sw']){$cate_html = $cate_match[1];}
		}
		$Tmp = preg_replace("/<!--\s?Category\s?-->.+<!--\s?Category\s?-->/is",$cate_html,$Tmp);
		
		//Tag
		$Tmp = Item_Printer::Tag_Linker($Tmp,$Child_No,$Item['tag']);
		
		//Good Count
		$Tmp = Item_Printer::Good_Count($Tmp,$Parent_No,$Child_No);

		//トリップ
		if($post_set['trip']['sw']){
			if(preg_match("/^(.+)#(.*)$/",$Item['name'],$glp)){
				$Item['name'] = $glp[1];
				$Item['trip'] = "◆$glp[2]";
			}else{$Item['trip'] = "";}
		}
		$Tmp = preg_replace('/\$user_trip/',$Item['trip'],$Tmp);

		//出力する画像ファイル(元の画像 or サムネイル)
		$img_file = "";
		$img_url = "";
		list($img_file,$img_url) = Html::Get_ImageURL($Item);
		
		//Image Size
		$img_style = array("size"=>"","center"=>"");
		if($img_file){
			$img = array("width"=>0,"height"=>0);
			
			list($img['width'],$img['height']) = getimagesize($img_file);
			list($print_size['width'],$print_size['height']) = Thumbnail::Size_Calculation($img['width'],$img['height']);
			
			//画像枠内の表示位置を調整(センタリング)
			$style = array("x"=>0,"y"=>0);
			$style['y'] = ceil(($print_set['list']['img_h'] - $print_size['height']) / 2); 
			$style['x'] = ceil(($print_set['list']['img_h'] - $print_size['width']) / 2);
				
			if($img['width'] > $print_size['width'] or $img['height'] > $print_size['height']){
				$img_style['size'] = " height:".$print_size['height']."px; width:".$print_size['width']."px;";
			}
			$img_style['center'] = "padding: " . $style['y'] . "px ".$style['x']."px;";
				
			if($php['base_url'] and $img_file and preg_match("/^\.\//",$img_file)){$img_file = preg_replace("/\.\//",$php['base_url'],$img_file);}
		}
		$pm = array('/\$img_center/','/\$img_size/');
		$rm = array($img_style['center'],$img_style['size']);
		$Tmp = preg_replace($pm,$rm,$Tmp);
		
		$pm ="";
		$rm = "";
		if($Child_No == 0){
			//Page View Count
			$Tmp = Item_Printer::PV_Count($Tmp,$Item['ent_no']);
			
			//New-Flag_Check
			$new_flag = False;
			if(!$Item['up_date']){$Item['up_date'] = (-$print_set['new']['time']) * 3600;}
			$new_flag = $Item['up_date'] + $print_set['new']['time'] * 3600;
			if($new_flag >= time()){$log_flag = $print_set['new']['mark'] ;}else{$log_flag = "";}
			
			//List Mode -> View Mode URL
			if(!$F['mode'] or $F['mode'] == "Rss" or $F['mode'] == "Pickup"){
				if($count_set['pvc']['sw']){$url_mode = "enter";}else{$url_mode = "view";}
				$img_url = $php['base_url'].$php['main']."?mode=".$url_mode."&amp;prevno=" . $Item['ent_no'];

				if($print_set['preview']['page']){
					$pnv_page = ceil(count($Item['res']) / $print_set['res']['num']);
					if($pnv_page >  1){$img_url .= "&amp;page=".$pnv_page;}
				}
			}

			$pm = array('/\$hp_url/','/\$(img|log)_no/','/\$log_user/','/\$res_num/','/\$img_file/','/\$img_url/','/\$log_entry/','/\$log_msg/','/\$log_color/','/\$log_day/','/\$log_host/','/\$log_flag/','/\$category/');
			$rm = array($Item['hp_url'],$Parent_No,$Item['name'],count($Item['res']),$img_file,$img_url,$Item['entry'],$Item['msg'],$Item['color'],$Item['date'],$Item['ip'],$log_flag,$Item['category']);
		}else{
			if($Item){	
				//記事タイトル
				$Item['entry'] = $Item['date'] . $Item['ent_no'] . $Item['name'];

				//レスに添付が無い場合は表示領域を削除する
				if(!$img_file){
					$pm = array('/<a.*?href=.*?\$img_url.*?>.*<\/a>/','/<img.*?src=.?\$img_file.*?>/');
					$rm = array("","");
					$Tmp = preg_replace($pm,$rm,$Tmp);
				}
			
				//置換用変数
				$pm = array('/\$hp_url/','/\$log_no/','/\$res_no/','/\$res_user/','/\$res_msg/','/\$res_color/','/\$res_day/','/\$res_host/','/\$img_url/','/\$img_file/','/\$res_entry/');
				$rm = array($Item['hp_url'],$Parent_No,$Item['ent_no'],$Item['name'],$Item['msg'],$Item['color'],$Item['date'],$Item['ip'],$img_url,$img_file,$Item['entry']);
			}
		}
		
		//Movie Embed Control
		$Tmp = Item_Printer::Movie_Embed($Tmp,$img_url);
		
		//Template Replace
		if(!$pm){$return = "<div style=\"color:red; text-align:center; font-weight:bold\">No.".$Parent_No . " - Data Error (No Image)</div>";}
		else{$return = preg_replace($pm,$rm,$Tmp);}
		
		return $return;
 	}

//Good_Count Print Control
	private static function Good_Count($Tmp,$Parent_No,$Child_No = 0){
		global $data_file,$good_set,$post_set;
		//Good Count Button - Print Control
		$gc_html = "";
		if(preg_match("/<!--\s?Good_Counter\s?-->(.+)<!--\s?Good_Counter\s?-->/is",$Tmp,$gc_match)){
			if(((($good_set['sw'] === 1 and $Type == "view") or $good_set['sw'] === 2) and $Item['img']['file']) or $good_set['sw'] === 3){
				$gc_html = $gc_match[1];
			}
		}
		$Tmp = preg_replace("/<!--\s?Good_Counter\s?-->(.+)<!--\s?Good_Counter\s?-->/is",$gc_html,$Tmp);
		
		//-- Good Count Data --//
		$good_cnt = array();
		if(preg_match('/\$good_cnt/',$Tmp)){
			//Count Data File Path
			$good_file['dir'] = getcwd() . $post_set['upload']['dir'] . "/" . $Parent_No . "/";
			$good_file['path'] = $good_file['dir'] . $Child_No . $data_file['good'];
			if(file_exists($good_file['path'])){
				$lock_flag = false;
				$good_cnt = array();
				if($good_set['lock']){$lock_flag = Files::Lock($good_file['dir'] . $good_set['lock_file'],$lock_flag,"SH");}
					$good_cnt = Files::Load($good_file['path'],"line");//Load
				if($good_set['lock']){$lock_flag = Files::Lock($good_file['dir'] . $good_set['lock_file'],$lock_flag,"UN");}
				if(count($good_cnt) > 0){
					$good_cnt[0] = preg_replace('/[\r\n]/',"",$good_cnt[0]);
					$good_cnt = explode(",",$good_cnt[0]);
				}
			}
		}
		if(!isset($good_cnt[0]) or !is_numeric($good_cnt[0])){$good_cnt[0] = 0;}

		$pm = array('/\$good_cnt/');
		$rm = array($good_cnt[0]);
		return preg_replace($pm,$rm,$Tmp);
	}

//PreView_Count Print Control
	private static function PV_Count($Tmp,$Entry_No){
		global $data_file,$count_set,$post_set;
		//Page View Count - Print Control
		$pv_html = "";
		if(preg_match("/<!--\s?PV_Counter:(.+?)\s?-->/i",$Tmp,$pv_match)){
			if($count_set['pvc']['sw']){
				$pv_html = $pv_match[1];
			}
		}
		$Tmp = preg_replace("/<!--\s?PV_Counter:.+?\s?-->/i",$pv_html,$Tmp);
		
		//Page View Count Load
		$pv_cnt = array();
		if(preg_match('/\$pv_cnt/',$Tmp)){
			//Count Data File Path
			$cnt_file['dir'] = getcwd() . $post_set['upload']['dir'] . "/" . $Entry_No . "/";
			$cnt_file['path'] = $cnt_file['dir'] . $data_file['pvc'];
			$cnt_file['lock'] = $cnt_file['dir'] . $count_set['lock_file'];
			//Load
			if(file_exists($cnt_file['path']) and $count_set['pvc']['sw']){
				$lock_flag = False;
				if($count_set['lock']){$lock_flag = Files::Lock($cnt_file['lock'],$lock_flag,"SH");}
					$pv_cnt = Files::Load($cnt_file['path'],"line");
					$pv_cnt[0] = preg_replace('/[\r\n]/',"",$pv_cnt[0]);
					$pv_cnt = explode(",",$pv_cnt[0]);
				if($count_set['lock']){$lock_flag = Files::Lock($cnt_file['lock'],$lock_flag,"UN");}
			}
		}
		if(!isset($pv_cnt[0]) or !is_numeric($pv_cnt[0])){$pv_cnt[0] = 0;}
		
		return preg_replace('/\$pv_cnt/',$pv_cnt[0],$Tmp);
	}

//Tag Print Control
	private static function Tag_Linker($Tmp,$Type,$Tag){
		global $post_set;
		//Tag - Print Control
		$tag_html = "";
		if(preg_match("/<!--\s?Tag\s?-->(.+?)<!--\s?Tag\s?-->/is",$Tmp,$tag_match)){
			if(($Type === 0 and $post_set['parent']['tag'] > 0) or ($Type > 0 and $post_set['res']['tag'] > 0)){
				$tag_html = $tag_match[1];
			}
		}
		$Tmp = preg_replace("/<!--\s?Tag\s?-->.+?<!--\s?Tag\s?-->/is",$tag_html,$Tmp);
		
		//Each Print
		if(preg_match('/\$tag([0-9]+)/',$Tmp)){
			while(preg_match('/\$tag([0-9]+)/',$Tmp,$mtg)){
				$pm = '/\$tag'.$mtg[1].'/i';
				if($Tag[$mtg[1]-1]){$rm = "<a href=\"?search=" . urlencode("#") . $Tag[$mtg[1]-1] . "\">#" . $Tag[$mtg[1]-1] . "</a>";}
				elseif($mtg[1] == 1){$rm = "なし";}
				else{$rm = "";}
				if($pm){$Tmp = preg_replace($pm,$rm,$Tmp);}
			}
		}
		//- String Print
		$tag_string = implode(" ",$Tag);
		$tag_string = preg_replace("/([^\s]+)/","<a href=\"?search=" . urlencode("#") . "\\1\">#\\1</a>",$tag_string);
		if(!$tag_string){$tag_string = "無し";}
		
		return preg_replace('/\$tag[^.]*?/',$tag_string,$Tmp);
	}

//Movie Embed
	private static function Movie_Embed($Tmp,$Img_Url){
		global $F,$print_set,$movie_set;
		//Movie URL -> A Tag - onClick Add = JavaScript :: Embed();
		if(($F['mode'] == "view" or $F['mode'] == "enter") and preg_match("/^https?:\/\//",$Img_Url) and $print_set['preview']['movie']){
			if(is_array($movie_set)){
				foreach($movie_set as $line){
					if(!array_key_exists('regexp',$line)){$line['regexp'] = "";}
					if(!array_key_exists('embed',$line)){$line['embed'] = "";}
					if($line['regexp'] and preg_match("/". $line['regexp'] . "/",$Img_Url) and $line['embed']){
						$Tmp = preg_replace('/(<a.*?href=\"\$img_url\".*?)>/is',"$1 onClick=\"Embed(this,arguments[0]);\">",$Tmp);
						break;
					}
				}
			}
		}
		return $Tmp;
	}

//Message - Convert Link
	private static function Convert_Link($Msg){
    	//自動リンク
		$Msg = preg_replace("/(https?|ftp|news)"."(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/i","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $Msg);
		$Msg = preg_replace("/((?:\w+\.?)*\w+@(?:\w+\.)+\w+)/i","[<a href=\"mailto:\\1\">\\1</a>]", $Msg);
		return $Msg;
	}
	
//Res Quote Navigation
	private static function Res_Quote($Item,$Msg){
		global $print_set;
		//ResNo List
		$res_ent_list = array_column($Item['res'],"ent_no");
		$pm_cnt = preg_match_all("/&gt;&gt;([0-9]+)/",$Msg,$glp);
		if($print_set['res']['q_navi'] and $pm_cnt > 0){
			for($b = 0; $b < $pm_cnt; $b++){
				$res_no_navi = 0;
				$rpn_url = "";
				//ResNo Search
				$res_index = array_search($glp[1][$b],$res_ent_list);
				//Page番号計算
				$res_no_navi = ceil(($res_index + 1) / $print_set['res']['num']);
				if($res_no_navi <= 0){$res_no_navi = 0;}
				if($res_index !== False){
					$rpn_url = "?mode=view&amp;prevno=" . $Item['ent_no'] . "&amp;page=" . $res_no_navi . "#Res".$glp[1][$b];
					$Msg = preg_replace("/&gt;&gt;" . $glp[1][$b] . "/","<a href=\"$rpn_url\" class=\"ResNavigate\" name=\"Res_Box".$glp[1][$b]."\">&gt;&gt;".$glp[1][$b]."</a>",$Msg,1);
				}
			}
		}
		return $Msg;
	}

}

//Gallery Board - www.tenskystar.net
?>