<?php
/* GalleryBoard - Html Template Control
v1.9 19/08/27 tag表示されないのを修正
- Html出力 -
 Html
  + Index - メイン画面
  + Image_Preview - 詳細出力
  + List_View - リスト出力
  + Var_Rep - 値の出力
  + Edit_Form - 入力フォーム出力
  + Get_ImageURL - 画像ファイルとリンク用URLの取得
  + Error - エラー画面
*/
$include_list = get_included_files();
$include_flag =  False;

if($php['set'] and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Html Script Error!</div></html>";exit;}

class Html{

	public static function Index($Page,$Caria){
		global $F,$data_file,$print_set,$post_set,$home,$php,$title,$help,$lock_fr,$code_set,$lock;
		$gb_cr = Html::Foot();

		//Template
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"SH");
			switch($Caria){
				case "pc":
					$main_tmp = Files::Load($data_file['template'],"all");
				break;
				default:
					$main_tmp = Files::Load($data_file['mb_template'],"all");
				break;
			}
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		
		if(!preg_match("/".base64_decode("aHR0cHM6XC9cL3d3d1wudGVuc2t5c3RhclwubmV0XC8=")."/",$gb_cr)){Html::Error("Error!","Error");}
		preg_match("/.*?Ver\.([0-9]\.[0-9](\.[0-9])?).+?/",$gb_cr,$glp);
		
		$item_key = False;

		if(($F['mode'] == "view" or $F['mode'] == "enter")){$item_key = $F['prevno'];}
		elseif($F['mode'] == "edit"){$edit_no = explode("-",$F['prevno']); $item_key = $edit_no[0];}
		elseif($F['mode'] != "new" and $F['mode'] != "op"){
			if($F['search']){$item_key = $F['search'];}
			else{$item_key = "";}
		}
	
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"EX");
			//Day Check -> DayLimit Delete
			if($post_set['daily_del'] > 0){
				if(file_exists("day_check.dat")){$last_day = Files::Load("day_check.dat","line");}
				$last_day = date_parse(date("Y-m-d",$last_day[0])." 00:00");
				$next_day = mktime(0,0,0,$last_day['month'],$last_day['day']+1,$last_day['year']);
				$times = time();
				if(($next_day - $times) < 0){
					Ctrl::Daily_Check();
					Files::Save("day_check.dat",$times,"day_check");
				}
			}
			//Get Log
			if($item_key !== False){list($item,$sum_cnt,$ent_no_list,$up_date_list) = Ctrl::Get_Item($item_key);}
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");

		//記事をソート
		if(!$F['mode'] and count($item) > 1){if($print_set['new']['position']){array_multisort($up_date_list,SORT_DESC,SORT_NUMERIC,$ent_no_list,SORT_DESC,SORT_NUMERIC,$item);}}

		if(!$Page or $Page < 0){$Page=1;}
		if($F['mode'] == "op"){$hdtitle = $title['op'];}
		elseif($F['mode'] == "new"){$hdtitle = $title['new'];}
		elseif($F['mode'] == "rewrite"){$hdtitle = $title['edit'];}
		elseif($F['prevno'] and count($item) === 1 and $item[0]['entry']){$hdtitle = str_replace('$entry',$item[0]['entry'],$title['view']);}
		elseif($F['page'] > 1 ){$hdtitle = $title['page'];}
		else{$hdtitle = $title['main'];}
		
/////////////
		$c_code = "PGRpdiBzdHlsZT0ibWFyZ2luOjE1cHggMDsgdGV4dC1hbGlnbjpjZW50ZXI7Ij5HYWxsZXJ5IEJvYXJkIFZlci4gLSA8YSBocmVmPSJodHRwczovL3d3dy50ZW5za3lzdGFyLm5ldC8iPuWkqeepuuOBruW9vOaWuTwvYT48L2Rpdj4K";
		$c_code = preg_replace("/".base64_decode("VmVyLg==")."/",base64_decode("VmVyLg==").$glp[1],base64_decode($c_code));
		$main_tmp .= mb_convert_encoding($c_code,$code_set['system'],$code_set['list']);
		$main_tmp = preg_replace("/<head>/",mb_convert_encoding(base64_decode("PCEtLSBHYWxsZXJ5IEJvYXJkIC0gk1aL84LMlN6V+yAoaHR0cDovL3d3dy50ZW5za3lzdGFyLm5ldC8pLS0+Cg==")."\n<head>",$code_set['system'],$code_set['list']),$main_tmp);

//1ページ アイテム 出力数
		if(!$F['mode']){
			if($Caria == "pc"){
				$col_num = $print_set['list']['pc_col'];
				$row_num = $print_set['list']['pc_row'];
			}else{
				$col_num = $print_set['list']['mb_col'];
				$row_num = $print_set['list']['mb_row'];
			}
			$list_num = $col_num * $row_num;
		}
		
		//Page数 計算
		$max_page = 1;
		if($list_num){$max_page = ceil(count($item) / $list_num);}
		if(($F['mode'] == "view" or $F['mode'] == "enter") and is_array($item[0]['res'])){$max_page = ceil(count($item[0]['res']) / $print_set['res']['num']);}
		if($max_page <= 0){$max_page=1;}
		if($max_page < $Page){$Page = $max_page;}
	
		//Preview Page
		$main_tmp = Html::Image_Preview($main_tmp,$item,$Page);
		//List Page
		$main_tmp = Html::List_View($main_tmp,$item,$row_num,$col_num,$Page);
		//Edit Form
		if($F['mode'] == "new"){$adt="parent";$prev_no = "";}
		elseif($F['mode'] == "edit"){$adt="rewrite";$prev_no = $F['prevno'];}
		elseif($F['mode'] == "view" or $F['mode'] == "enter"){$adt="child";$prev_no = $item[0]['ent_no']."-".($item[0]['res_cnt']+1);}
		$main_tmp = Html::Edit_Form($main_tmp,$adt,$prev_no);

		if(preg_match("/<!--\s?OpForm\s?-->(.+)<!--\s?OpForm\s?-->/is",$main_tmp,$op_match)){
			$opage_tmp = "";
			if($F['mode'] == "op"){
				$opage_tmp = $op_match[1];

				if(!preg_match("/[0-9]+-[0-9]+/",$F['prevno'])){$opage_tmp = preg_replace("/(<input.*?type=\"?radio\"?.*?name=\"?mode\"?.*?value=\"?edit\"?[^>]*?)>/is",'$1 checked>',$opage_tmp);}
				if(preg_match("/[0-9]+-[0-9]+/",$F['prevno'])){$opage_tmp = preg_replace("/(<input..*?name=\"?mode\"?.*?value=\"?remove[^>]*?)>/is",'$1 checked>',$opage_tmp);}
			}
			$main_tmp = preg_replace("/<!--\s?OpForm\s?-->.+<!--\s?OpForm\s?-->/is",$opage_tmp,$main_tmp);
		}

		$p=array();
		$r=array();
		
		//ページナビゲート1 ($back $next)
		if(preg_match('/(\$next|\$back)/',$main_tmp)){
			$pnum = $max_page;
			$next = $Page+1;
			$back = $Page-1;
			
			if($F['mode'] == "view" or $F['mode'] == "enter"){
				if($Page > 1){$back = $php['base_url'].$php['main']."?mode=view&amp;prevno=".$F['prevno']."&amp;page=".$back;}else{$back="";}
				if($Page < $max_page){$next = $php['base_url'].$php['main']."?mode=view&amp;prevno=".$F['prevno']."&amp;page=".$next;}else{$next="";}
			}else{
				if($Page > 1){$back = $php['base_url'].$php['main']."?page=$back";}else{$back="";}
				if($Page < $max_page){$next = $php['base_url'] . $php['main']."?page=".$next;}else{$next="";}
			}

			//ナビゲートリンクの解除
			if(preg_match_all("/(<a.*?>(.+?)<\/a>)/",$main_tmp,$glp,PREG_SET_ORDER)){
				for($x=0; $x < (count($glp)-1); $x++){
					if(!$back){
						$glp[$x][3] = preg_replace('/<a.*?href=\"?\$back[^>]+>.+?<\/a>/',$glp[$x][2],$glp[$x][1]);
						$main_tmp = str_replace($glp[$x][1],$glp[$x][3],$main_tmp);
					}
					if(!$next){
						$glp[$x][3] = preg_replace('/<a.*?href=\"?\$next[^>]+>.+?<\/a>/',$glp[$x][2],$glp[$x][1]);
						$main_tmp = str_replace($glp[$x][1],$glp[$x][3],$main_tmp);
					}
				}
			}
		}

		//ページナビゲート2 (ページ番号)
		$num_linker = "";
		if(strpos($main_tmp,'$p_navi') !== False){
			if($Page <= $max_page){
				$for_min = ($page - $print_set['navi']);
				if($for_min <= 0){$for_min = 1;}
				for($cnt = $for_min; $cnt <= ($Page + $print_set['navi']); $cnt++){
					if($max_page < $cnt){break;}
					if($Page != $cnt){
						if($F['mode'] == "view" or $F['mode'] == "enter"){$num_linker .= "<a href=\"".$php['base_url'].$php['main']."?mode=view&amp;prevno=".$F['prevno']."&amp;page=".$cnt."\">".$cnt."</a> ";}
						else{$num_linker .= "<a href=\"".$php['base_url'].$php['main']."?page=".$cnt."\">".$cnt."</a> ";}
					}else{$num_linker .= "<b>".$cnt."</b> ";}
	
				}
			}
		}

		$pm = array('/\$hdtitle/','/\$prevw/','/\$prevh/','/\$imgw/','/\$imgh/','/\$pnum/','/\$page/','/\$php/','/\$home/','/\$mhome/','/\$title/','/<!--.*?(EditForm|OpForm|ImagePreview|ImageList).*?-->/i','/\$page_max/','/\$next/','/\$back/','/\$p_navi/','/\$prev_no/','/\$movie_help/','/\$op_help/','/\$rw_c/','/\$search/','/\$css/','/\$javascript/');
		$rm = array($hdtitle,$print_set['preview']['img_w'],$print_set['preview']['img_h'],$print_set['list']['img_w'],$print_set['list']['img_h'],$pnum,$Page,$php['main'],$home['pc'],$home['mb'],$title['main'],"",$max_page,$next,$back,$num_linker,$F['prevno'],$help['movie'],$help['op'],$code_set['system'],$F['search'],$php['css'],$php['javascript']);
		$main_tmp = preg_replace($pm,$rm,$main_tmp);
		
		print $main_tmp;
	}

	public static function Image_Preview($Main_Tmp,$Item,$Page){
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
					if($Item[0]){
						//PV Count
						if($F['mode'] == "enter" and $count_set['pvc']['sw']){
							if(!method_exists('PV_Counter','PV_Up')){Html::Error("システム エラー","カウンター用スクリプトが読み込まれていません。");}
							PV_Counter::PV_Up($Item[0]['ent_no']);
						}
						//Good Count
						if($F['good']){
							if(!method_exists('Good_Counter','Good_Up')){Html::Error("システム エラー","カウンター用スクリプトが読み込まれていません。");}
							Good_Counter::Good_Up($Item,$F['good']);
						}
						
						//Parent Print
						$ib_html .= Html::Var_Rep($Item[0]['ent_no'],$Item[0],$ib_tmp,"view");
						//Res Num
						if(($F['mode'] == "view" or $F['mode'] == "enter") and count($Item[0]['res']) > 0){
							$resv_min = $print_set['res']['num'] * ($Page-1);
							$resv_max = $resv_min + $print_set['res']['num'];
							if(count($Item[0]['res']) < $resv_max){$resv_max = count($Item[0]['res']);}
						}else{$resv_min=0;$resv_max=0;}
						
						//最新のレスを先頭に
						if($print_set['res']['sort'] and count($Item[0]['res']) >1){$Item[0]['res'] = array_reverse($Item[0]['res']);}
						//Children (Res) Print
						$ri_html = "";
						for($res_cnt = $resv_min; $res_cnt < $resv_max; $res_cnt++){
							if($Item[0]['res'][$res_cnt]){
								//レス引用-ナビゲート
								$pm_cnt = preg_match_all("/&gt;&gt;([0-9]+)/",$Item[0]['res'][$res_cnt]['msg'],$glp);
								if($print_set['res']['q_navi'] and $pm_cnt > 0){
									for($b = 0; $b < $pm_cnt; $b++){
										$res_no_navi = 1;
										$res_pn_cnt = 0;
										//レス - 検索
										foreach ($Item[0]['res'] as $res_item){
											$res_pn_cnt++;
											if($res_item['ent_no'] == $glp[1][$b]){
												if($res_no_navi > $page or $res_no_navi < $page){$rpn_url = "?mode=view&amp;prevno=".$Item[0]['ent_no']."&amp;page=".$res_no_navi;}else{$rpn_url = "";}
												$Item[0]['res'][$res_cnt]['msg'] = str_replace("&gt;&gt;".$glp[1][$b],"<a href=\"$rpn_url#Res".$glp[1][$b]."\" class=\"ResNavigate\" name=\"Res_Box".$glp[1][$b]."\">&gt;&gt;".$glp[1][$b]."</a>",$Item[0]['res'][$res_cnt]['msg']);
												
												break;
											}
											if($res_pn_cnt >= ($print_set['res']['num'] * $res_no_navi)){$res_no_navi++;}
										}
									}
								}
								//レスを出力
								$ri_html .= Html::Var_Rep($Item[0]['ent_no'],$Item[0]['res'][$res_cnt],$ri_tmp,"res");
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
				}else{Html::Error("エラー","テンプレートが正しくありません(ImagePreview - Item)");}
			}
			$Main_Tmp = preg_replace("/<!--\s?ImagePreview\s?-->.+<!--\s?ImagePreview\s?-->/is",$ppage_html,$Main_Tmp);
		}else{
			if($F['mode'] == "view" or $F['mode'] == "enter"){Html::Error("エラー","テンプレートが正しくありません(ImagePreview)");}
		}
		return $Main_Tmp;
	}

	public static function List_View($Main_Tmp,$Item,$Row_Num,$Col_Num,$Page){
		global $F,$print_set;
		$lpage_html = "";
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
							for($y_for=0; $y_for < $Row_Num; $y_for++){
								$no=0;
								$no = $Col_Num * $Row_Num * ($Page-1) + $Col_Num * $y_for;
								for($x_for=0; $x_for < $Col_Num; $x_for++){
									if(!$Item[$no]['ent_no'] and !$print_set['list']['no_img']){break;}
									if($Item[$no]){
										//Parent Print
										$ib_html .= Html::Var_Rep($Item[$no]['ent_no'],$Item[$no],$ib_tmp,"view");
										$x_count++;
										if($x_count >= $Col_Num){
											$lb_html .= str_replace('$i_box',$ib_html,$lb_tmp);
											$ib_html="";
											$x_count=0;
											$y_count++;
										}
									}else{
										$ib_html .= $blank_tmp;
									}
									$no++;
								}
								//Blank Frame Print
								$lb_html .= str_replace('$i_box',$ib_html,$lb_tmp);
								$ib_html="";
							}
					}else{Html::Error("エラー","テンプレートが正しくありません。(ItemBox)");}
									
					$lpage_html = preg_replace("/<!--\s?ListBox\s?-->.+<!--\s?ListBox\s?-->/is",$lb_html,$lpage_tmp);
				}else{Html::Error("エラー","テンプレートが正しくありません(ListBox)");}
			}
			//LogPrint
			if($F['search'] and !$lpage_html){$lpage_html = "<div>一致するのはありません</div>\n";}
			$Main_Tmp = preg_replace("/<!--\s?ImageList\s?-->.+<!--\s?ImageList\s?-->/is",$lpage_html,$Main_Tmp);
		}else{Html::Error("エラー","テンプレートが正しくありません(ImageList)");}		

		return $Main_Tmp;
	}
		
	public static function Var_Rep($Parent_No,$Item,$Tmp,$Type){
		global $php,$post_set,$print_set,$count_set,$good_set,$data_file,$F,$hp_url_type,$movie_set,$code_set;
		
		//Site Url - View Ctrl	
		while(preg_match("/<!--\s?SiteUrl:(.+?)\s?-->/i",$Tmp,$match)){
			if($Item['hp_url'] and preg_match($hp_url_type,$Item['hp_url'])){
				$Tmp = preg_replace("/<!--\s?SiteUrl:.+?-->/is",$match[1],$Tmp,1);
			}else{
				$Tmp = preg_replace("/<!--\s?SiteUrl:.+?-->/is","",$Tmp,1);
			}
		}
		//Good Count - View Ctrl
		if(preg_match("/<!--\s?Good_Counter\s?-->(.+)<!--\s?Good_Counter\s?-->/is",$Tmp,$gd_match)){
			$gc_type = '/<!--\s?Good_Counter\s?-->(.+)<!--\s?Good_Counter\s?-->/is';
			if(((($good_set['sw'] === 1 and $Type == "view") or $good_set['sw'] === 2) and $Item['img']['file']) or $good_set['sw'] === 3){$rm = '$1';}else{$rm = '';}
			$Tmp = preg_replace($gc_type,"$rm",$Tmp);
		}

		$Item['msg'] = Ctrl::Convert_Link($Item['msg']);
		
		//Tag
		if(is_array($Item['tag'])){
			if(count($Item['tag']) >= 1 and ($Type == "view" and $post_set['parent']['tag'] == 0) or ($Type == "res" and $post_set['res']['tag'] == 0)){
				$Tmp = preg_replace("/<!--\s?Tag\s?-->.+<!--\s?Tag\s?-->/is","",$Tmp);
			}
		}
		
		if(preg_match('/\$tag([0-9]+)/',$Tmp)){
			while(preg_match('/\$tag([0-9]+)/',$Tmp,$mtg)){
				$pm = '/\$tag'.$mtg[1].'/i';
				if($Item['tag'][$mtg[1]-1]){$rm = "<a href=\"?search=" . urlencode("#") . $Item['tag'][$mtg[1]-1] . "\">#" . $Item['tag'][$mtg[1]-1] . "</a>";}
				elseif($mtg[1] == 1){$rm = "なし";}
				else{$rm = "";}
				if($pm){$Tmp = preg_replace($pm,$rm,$Tmp);}
			}
		}
		
		//String
		$tag_string = implode(" ",$Item['tag']);
		$tag_string = preg_replace("/([^\s]+)/","<a href=\"?search=" . urlencode("#") . "\\1\">#\\1</a>",$tag_string);
		if(!$tag_string){$tag_string = "無し";}

		//-- Good Count Btm --//
		$good_cnt = array();
		if(preg_match('/\$good_cnt/',$Tmp)){
			if($Type == "res"){$child_no = $Item['ent_no'];}
			if($child_no == ""){$child_no = 0;}
			//Count Data File Path
			$good_file['dir'] = getcwd().$post_set['upload']['dir']."/".$Parent_No;
			$good_file['path'] = $good_file['dir']."/".$child_no.$data_file['good'];
			if(file_exists($good_file['path'])){
				if($good_set['lock']){$lock_flag = Files::Lock($good_file['dir']."/".$good_set['lock_file'],$lock_flag,"SH");}
					$good_cnt = Files::Load($good_file['path'],"line");//Load
				if($good_set['lock']){$lock_flag = Files::Lock($cnt_file['dir']."/".$good_set['lock_file'],$lock_flag,"UN");}
				$good_cnt[0] = preg_replace('/[\r\n]/',"",$good_cnt[0]);
				$good_cnt = explode(",",$good_cnt[0]);
			}
			if(!isset($good_cnt[0]) or !is_numeric($good_cnt[0])){$good_cnt[0] = 0;}
		}
		//--------------------//
		
		$pm ="";
		$rm = "";
		
		if($Type == "view"){
			//Page View Count - View Ctrl
			if(preg_match("/<!--\s?PV_Counter:(.+?)\s?-->/i",$Tmp,$match)){
				if($count_set['pvc']['sw']){
					$Tmp = preg_replace("/<!--\s?PV_Counter:(.+?)\s?-->/i","$1",$Tmp);
				}else{
					$Tmp = preg_replace("/<!--\s?PV_Counter:(.+?)\s?-->/i","",$Tmp);
				}
			}
			//Page View Count Load
			$pv_cnt = array();
			if(preg_match('/\$pv_cnt/',$Tmp)){
				//Count Data File Path
				$cnt_file['dir'] = getcwd().$post_set['upload']['dir']."/".$Item['ent_no'];
				$cnt_file['path'] = $cnt_file['dir']."/".$data_file['pvc'];
				//Load
				if(file_exists($cnt_file['path']) and $count_set['pvc']['sw']){
					if($count_set['lock']){$lock_flag = Files::Lock($cnt_file['dir']."/".$count_set['lock_file'],$lock_flag,"SH");}
						$pv_cnt = Files::Load($cnt_file['path'],"line");
						$pv_cnt[0] = preg_replace('/[\r\n]/',"",$pv_cnt[0]);
						$pv_cnt = explode(",",$pv_cnt[0]);
					if($count_set['lock']){$lock_flag = Files::Lock($cnt_file['dir']."/".$count_set['lock_file'],$lock_flag,"UN");}
				}
				if(!isset($pv_cnt[0]) or !is_numeric($pv_cnt[0])){$pv_cnt[0] = 0;}
			}
			
			//New-Flag_Check
			if(!$Item['up_date']){$Item['up_date'] = (-$print_set['new']['time']) * 3600;}
			$new_flag = $Item['up_date'] + $print_set['new']['time'] * 3600;
			if($new_flag >= time()){$log_flag = $print_set['new']['mark'] ;}else{$log_flag = "";}
			//トリップ
			if($post_set['trip_sw'] and preg_match("/^(.+)#(.*)$/",$Item['name'],$glp)){
				$Item['name']= $glp[1];
				$user_trip = "◆$glp[2]";
			}
			//画像ファイル
			list($img_file,$img_url) = Html::Get_ImageURL($Item);
			if($img_file){
				//Get Image Style
				list($img_size,$img_center) = Ctrl::Get_ImgStyle($img_file);
				if($php['base_url'] and $img_file and preg_match("/^\.\//",$img_file)){$img_file = preg_replace("/\.\//",$php['base_url'],$img_file);}

				//List Mode -> View Mode URL
				if(!$F['mode'] or $F['mode'] == "Rss" or $F['mode'] == "Pickup"){
					if($count_set['pvc']['sw']){$url_mode = "enter";}else{$url_mode = "view";}
					$img_url = $php['base_url'].$php['main']."?mode=".$url_mode."&amp;prevno=" . $Item['ent_no'];

					if($print_set['preview']['page']){
						$pnv_page = ceil(count($Item['res']) / $print_set['res']['num']);
						if($pnv_page >  1){$img_url .= "&amp;page=".$pnv_page;}
					}
				}
				
				$lp_cnt++;
				$pm = array('/\$hp_url/','/\$(img|log)_no/','/\$log_user/','/\$res_num/','/\$img_file/','/\$img_url/','/\$log_entry/','/\$log_msg/','/\$log_color/','/\$log_day/','/\$log_host/','/\$img_center/','/\$img_size/','/\$log_flag/','/\$user_trip/','/\$pv_cnt/','/\$good_cnt/','/\$category/','/\$tag[^.]*?/');
				$rm = array($Item['hp_url'],$Parent_No,$Item['name'],count($Item['res']),$img_file,$img_url,$Item['entry'],$Item['msg'],$Item['color'],$Item['date'],$Item['ip'],$img_center,$img_size,$log_flag,$user_trip,$pv_cnt[0],$good_cnt[0],$Item['category'],$tag_string);
			}
		}elseif($Type == "res"){
			if($Item){
				//トリップ
				if($post_set['trip_sw']){
					if(preg_match("/^(.+)#(.*)$/",$Item['name'],$glp)){
						$Item['name'] = $glp[1];
						$Item['trip'] = "◆$glp[2]";
					}else{$Item['trip'] ="";}
				}
				//記事タイトル
				$Item['entry'] = $Item['date'] . $Item['ent_no'] . $Item['name'];
				//画像ファイル
				list($img_file,$img_url) = Html::Get_ImageURL($Item);
				//Get Image Size
				if($img_file){
					list($img_size,$img_center) = Ctrl::Get_ImgStyle($img_file);
					if($php['base_url'] and $img_file and preg_match("/^\.\//",$img_file)){$img_file = preg_replace("/\.\//",$php['base_url'],$img_file);}
				}

				//レスに添付が無い場合は表示領域を削除する
				if(!$img_file){
					$pm = array('/<a.*?href=.*?\$img_url.*?>.*<\/a>/','/<img.*?src=.?\$img_file.*?>/');
					$rm = array("","");
					$Tmp = preg_replace($pm,$rm,$Tmp);
				}
			
				//痴漢
				$pm = array('/\$hp_url/','/\$log_no/','/\$res_no/','/\$res_user/','/\$res_msg/','/\$res_color/','/\$res_day/','/\$res_host/','/\$img_url/','/\$img_file/','/\$res_entry/','/\$img_center/','/\$img_size/','/\$user_trip/','/\$good_cnt/','/\$tag[^.]*?/');
				$rm = array($hp_url,$Parent_No,$Item['ent_no'],$Item['name'],$Item['msg'],$Item['color'],$Item['date'],$Item['ip'],$img_url,$img_file,$Item['entry'],$img_center,$img_size,$Item['trip'],$good_cnt[0],$tag_string);
			}
		}
		
		//Movie URL -> A Tag - onClick Add = JavaScript :: Embed();
		if(($F['mode'] == "view" or $F['mode'] == "enter") and preg_match("/^https?:\/\//",$img_url) and $print_set['preview']['movie']){
			if(is_array($movie_set)){
				foreach($movie_set as $line){
					if($line['regexp']){
						if(preg_match("/". $line['regexp'] . "/",$img_url) and $line['embed']){
							$Tmp = preg_replace('/(<a.*?href=\"\$img_url\".*?)>/is',"$1 onClick=\"Embed(this,arguments[0]);\">",$Tmp);
							break;
						}
					}
				}
			}
		}
		
		//Template Replace
		if(!$pm){$return = "<div style=\"color:red; text-align:center; font-weight:bold\">No.".$Parent_No . " - Data Error (No Image)</div>";}
		else{$return = preg_replace($pm,$rm,$Tmp);}
		
		return $return;
 	}
	

//投稿フォームの表示制御
 	public static function Edit_Form($Main_Tmp,$Type,$Entry_No){
		global $F,$php,$print_set,$post_set,$admin,$_COOKIE,$code_set;

		if(preg_match("/<!--\s?EditForm\s?-->(.+)<!--\s?EditForm\s?-->/is",$Main_Tmp,$ef_match)){
			if($F['mode'] == "new" or $F['mode'] == "edit" or $F['mode'] == "view" or $F['mode'] == "enter"){
				$ef_tmp = $ef_match[1];
	
				if($Type == "parent" or $Type == "child"){
					if($F['name']){$item['name'] = $F['name'];}elseif($_COOKIE['name']){$item['name'] = mb_convert_encoding($_COOKIE['name'],$code_set['system'],$code_set['list']);}
					if($F['color']){$item['color'] = $F['color'];}elseif($_COOKIE['color']){$item['color'] = $_COOKIE['color'];}
					if($F['pass']){$item['pass'] = $F['pass'];}elseif($_COOKIE['kword']){$item['pass'] = $_COOKIE['kword'];}
					if($F['hp_url']){$item['hp_url'] = $F['hp_url'];}elseif($_COOKIE['url']){$item['hp_url'] = $_COOKIE['url'];}
				}
				//必須マーク
				$h_mark = Array('name' => "",'mail'=>"",'auth'=>"",'msg'=>"",'hp_url'=> "",'pass'=>"",'title'=>"");
				if(!$post_set['no_name']){$h_mark['name'] = $print_set['r_mark'];}
				if($post_set['auth']['type']){$h_mark['auth'] = $print_set['r_mark'];}
				if($Type == "child"){
		//					$h_mark['pass'] = $print_set['r_mark'];
					if($post_set['res']['sw'] != 3 and $post_set['res']['sw'] != 4){$h_mark['msg']  = $print_set['r_mark'];}
					if($post_set['hp_url'] > 2 and $post_set['hp_url'] < 5){$h_mark['hp_url'] = $print_set['r_mark'];}
					if($post_set['mail']['sw'] >= 3){$h_mark['mail'] = $print_set['r_mark'];}
				}elseif($Type == "parent"){
					$h_mark['pass'] = $print_set['r_mark'];
					$h_mark['title'] = $print_set['r_mark'];
					if($post_set['hp_url'] > 1 and $post_set['hp_url'] < 4){$h_mark['hp_url'] = $print_set['r_mark'];}
					if($post_set['mail']['sw'] > 1 and  $post_set['mail']['sw'] < 4){$h_mark['mail'] = $print_set['r_mark'];}
					if($post_set['parent']['msg']){$h_mark['msg'] = $print_set['r_mark'];}
				}
		
				//アップロード可能サイズの単位
				$p_num = 0;
				if($post_set['upload']['size'] > 0){$unit_text = "B";}
				$unit_list = array('KB','MB','GB','TB');
				foreach($unit_list as $unit){
					if($post_set['upload']['size'] >= 1000){$unit_text = $unit; $post_set['upload']['size'] = $post_set['upload']['size']/1000; $p_num++;}
					else{continue;}
				}
		 		$max_file_size = $post_set['upload']['size'] * pow(1024,$p_num);
		
				if($Type == "rewrite"){//編集用
					if(!method_exists('Entry','Operation')){Html::Error("システム エラー","エントリー用スクリプトが読み込まれていません。");}
					$item = Entry::Operation("Edit",$Entry_No,$F['pass']);
					$item['msg'] = preg_replace("/(<BR>|<br\s?\/>)/i","\n",$item['msg']);
				}
		
				//投稿フォーム名
				if($Type == "parent"){$form_ent=$print_set['form']['new'];}
				elseif($Type == "rewrite"){$form_ent=$print_set['form']['edit'];$ftyu="<BR /> 差替える場合のみ、新しいファイルを指定してください。";}
				else{$form_ent=$print_set['form']['res'];$Type="child";}
				
				//image_auth
				if(!$post_set['auth']['type']){
					$ef_tmp = preg_replace("/<!--\s?AuthArea\s?-->(.+)<!--\s?AuthArea\s?-->/is","",$ef_tmp);
				}
				
				//表示制御
				if($Type != "parent" and $Type != "rewrite"){
					$ef_tmp = preg_replace("/<!--\s?TitleArea\s?-->(.+)<!--\s?TitleArea\s?-->/is","",$ef_tmp);
				}
				
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
				
				//文字色
				if(preg_match("/<!--\s?ColorList:(.+)\s?-->/is",$ef_tmp,$cl_match)){
					for($cnt=0; $cnt < count($print_set['msg']['color']); $cnt++){
						$color_line = $cl_match[1];
						if($item['color'] == $print_set['msg']['color'][$cnt]){
							$pm  = array("/(<option[^>]+?)>/is","/(<input.*?type=\"?radio\"?[^>]+?)>/");
							$rm  = array('$1 selected>','$1 checked>');
							$color_line = preg_replace($pm,$rm,$color_line);
						}
						
						$color_html .= str_replace('$color',$print_set['msg']['color'][$cnt],$color_line);
					}
					
					$ef_tmp = preg_replace('/<!--\s?ColorList:.+\s?-->/is',$color_html,$ef_tmp);
				}
				
				//カテゴリ
				if(preg_match("/<!--\s?CategoryArea\s?-->(.+)<!--\s?CategoryArea\s?-->/is",$ef_tmp,$cate_match)){
					$cate_tmp = "";
					if($Type == "parent" or $Type == "rewrite"){
						$cate_tmp = $cate_match[1];
						if(preg_match("/<!--\s?CategoryList:(.+)\s?-->/is",$cate_tmp,$c_match)){
							for($cnt=0; $cnt < count($post_set['parent']['category']); $cnt++){
								$category_line = $c_match[1];
								if($item['category'] == $post_set['parent']['category'][$cnt]){
									$pm  = array("/(<option[^>]+?)>/is","/(<input.*?type=\"?radio\"?[^>]+?)>/");
									$rm  = array('$1 selected>','$1 checked>');
									$category_line = preg_replace($pm,$rm,$category_line);
								}
								$category_html .= str_replace('$category',$post_set['parent']['category'][$cnt],$category_line);
							}
						}
							
						$cate_tmp = preg_replace('/<!--\s?CategoryList:.+\s?-->/is',$category_html,$cate_tmp);
					}
					$ef_tmp = preg_replace("/<!--\s?CategoryArea\s?-->.+<!--\s?CategoryArea\s?-->/is",$cate_tmp,$ef_tmp);
				}
				
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
				$res_html="";
				if($Type == "rewrite" and $item['res'] and ($post_set['res']['del_mode'] == 0 or $post_set['res']['del_mode'] == 2 or $F['pass'] == $admin['pass'])){
					$rescnt=0;
					foreach ($item['res'] as $res_line){
						if($res_line['name']){
							if($rescnt == 0){$res_html .=  "<div>削除したいレスを選択してください。</div>\n";}
							
							//Img Link and Http Link
							$res_img = "";$res_url = "";
							list($res_img,$res_url) = Html::Get_ImageURL($res_line);
							if(preg_match("/^https?:\/\//",$img_url)){$file_url = $item['img']['url'];}
						
							$res_html .=  "<hr>\n"
											."<div style=\"display:block; clear:both;\">"
												."<div style=\"display:inline-block; width:40px; padding:10px 5px; float:left;\"><input type=\"checkbox\" name=\"resflag[]\" value=\"".$res_line['ent_no']."\"></div>"
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
						
							$rescnt++;
						}
					}
				}
				if($Type == "rewrite" and $F['pass'] == $admin['pass']){$res_html .= "<input type=\"hidden\" name=\"admin\" value=\"".hash("sha256",$F['pass'])."\" />\n";}
				
				//Tag
				$tag_string = "";
				if(is_array($item['tag'])){
					if(count($item['tag']) > 0){
						$tag_string = implode(" ",$item['tag']);
					}
				}
				
				$pm = array('/\$php/','/\$form_entry/','/\$name/','/\$entry/','/\$msg/','/\$tag/','/\$pass/','/\$file_url/','/\$up_size/','/\$ftyu/','/\$type/','/\$prevno/','/\$max_file_size/','/\$rule/','/<\/form>/i','/\$host/','/\$ia_tyu/','/\$hp_url/','/\$mail/','/\$h_mark\[name\]/','/\$h_mark\[pass\]/','/\$h_mark\[title\]/','/\$h_mark\[msg\]/','/\$h_mark\[hp_url\]/','/\$h_mark\[auth\]/','/\$h_mark\[mail\]/');
				$rm = array($php['main'],$form_ent,$item['name'],$item['entry'],$item['msg'],$tag_string,$item['pass'],$file_url,$post_set['upload']['size']." " .$unit_text,$ftyu,$Type,$Entry_No,$max_file_size,$print_set['rule'],$res_html."</form>",$item['ip'],$post_set['auth']['caution'],$item['hp_url'],$item['mail'],$h_mark['name'],$h_mark['pass'],$h_mark['title'],$h_mark['msg'],$h_mark['hp_url'],$h_mark['auth'],$h_mark['mail']);
				
				$e_form = preg_replace($pm,$rm,$ef_tmp);
				
				$res_num = explode("-",$Entry_No); 
				if(($F['mode'] == "view" or $F['mode'] == "enter") and $post_set['res']['max_num'] > 0 and $res_num[1] > $post_set['res']['max_num']){$e_form = "<div style=\"color:red; text-align:center;\">レス数が".$post_set['res']['max_num']."を超えた為、これ以上レスを投稿できません。</div>\n";}
			}
			if($F['mode'] == "edit" and !$item){$e_form = "<div>編集するデータが見つかりませんでした</div>\n";}

			if($F['mode'] == "new" or $F['mode'] == "edit"){$Main_Tmp = preg_replace("/<!--\s?EditForm\s?-->.+<!--\s?EditForm\s?-->/is",'$e_form',$Main_Tmp);}
			$pm = array("/<!--\s?EditForm\s?-->.+<!--\s?EditForm\s?-->/is",'/\$e_form/is');
			$rm = array("",$e_form);
			$Main_Tmp = preg_replace($pm,$rm,$Main_Tmp);
		}
		return $Main_Tmp;
	}
	
	public static function Get_ImageURL($Item){
		global $post_set,$movie_set,$php,$F;
		$img_file=""; $img_url="";
		if($Item['img']['file']){
			//画像ファイル
			if(!$Item['img']['url']){
				$img_file = ".". $post_set['upload']['dir'].$Item['img']['file'];
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
						if(!method_exists('Save_Thumbnail','Upload_File')){Html::Error("システム エラー","サムネイル用スクリプトが読み込まれていません。");}
						Save_Thumbnail::Upload_File($img_file,$thimg);
					}
					if(file_exists($thimg)){$img_file = $thimg;}
				}
			}
		}
		return array($img_file,$img_url);
	}
 
	public static function Error($ern,$er,$DelFile = array()){
		global $_FILES,$title,$post_set,$lock_fr,$lock;
		setcookie("auth","",time()-1000);
		$D_Trace = debug_backtrace();
		$title = $ern;
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		if($_FILES['up_file']['name'] and $_FILES['up_file']['error'] == UPLOAD_ERR_OK and file_exists($_FILES['up_file']['tmp_name'])){unlink($_FILES['up_file']['tmp_name']);}
		
		if(is_array($DelFile)){
			foreach($DelFile as $line){
				if($line){
					if(is_file($line)){
						if(file_exists($line)){@unlink($line);}
					}
				}
			}
		}
		
		print "<head><title>".$ern."</title></head>\n";
		print "<p style=\"text-align:center; font:bold;\">$ern</p>\n";
		print "<div style=\"text-align:center; color:red;\">$er</div>\n";
		print Html::Foot();
		
		exit;
	}

	public static function Foot(){
		$foot = "<div style=\"margin:15px 0; text-align:center;\">Gallery Board Ver.9.1 - <a href=\"https://www.tenskystar.net/\">天空の彼方</a></div>\n";
		return $foot;
	}

}
//Gallery Board - www.tenskystar.net
?>