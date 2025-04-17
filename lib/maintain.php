<?php
$lib[] = "Gallery Board - Maintain Ver:1.0";
/* 
- 更新ログ -
 v1.0 22/03/08 Commonから分離

- コントロール -
 Maintenance
  + Daily_Check - 経過日数による自動削除
  + Del_Dir - 記事フォルダの削除
*/

class Maintain{
//親記事-経過日数
	public static function Daily_Check(){
		global $post_set,$data_file;
		if($post_set['daily_del'] > 0){
			//Day Check
			if(file_exists("day_check.dat")){$last_day = Files::Load("day_check.dat","line");}
			$last_day = date_parse(date("Y-m-d",$last_day[0])." 00:00");
			$next_day = mktime(0,0,0,$last_day['month'],$last_day['day']+1,$last_day['year']);
			$times = time();
			if(($next_day - $times) < 0){
				$del_no_list = array();
				list($Item,) = Common::Get_Item("All");
				
				if(is_array($Item)){
					foreach ($Item as $no=>$line){
						if($line){
							//文字列の日付をタイムスタンプに
							$day = date_parse_from_format("Y/m/d(w) H:i",$Item[$no]['date']); 
							$day = mktime($day['hour'],$day['minute'],0,$day['month'],$day['day'],$day['year']);
							//Delete Check
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
					foreach ($del_no_list as $parent_no){Maintain::Del_Dir($post_set['upload']['dir']."/".$parent_no);}//Delete Directory
				}
				Files::Save("day_check.dat",$times,"day_check");
			}
		}
		return;
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
							//Delete File
							$d_flag = unlink(getcwd().$Dir."/".$ent);
						}else{
							Maintain::Del_Dir($Dir."/".$ent);
						}
					}
				}
				//Delete Directory
				$del_flag =  rmdir(getcwd().$Dir);
			}
		}
		return $del_flag;
	}

	public static function Delete_TrashFile($File_List){
		if(count($File_List) > 0){
			foreach ($File_List as $one){
				if(file_exists($one)){unlink($one);}
			}
		}
		return;
	}
	
	
}
//Gallery Board - www.tenskystar.net
?>