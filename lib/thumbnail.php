<?php
/*
 Gallery Board - Thumbnail
  v1.3 19/04/13 設定変数の変更による修正。
  - サムネイル操作 -
 Get_Thumb - 動画サムネイルの取得
  - Youtube - YoutubeサムネイルURLの取得
  - Niconico - ニコニコ動画サムネイルURLの取得
  - Himawari - ひまわり動画サムネイルURLの取得
  - Dailymotion - Dailymotion動画サムネイルURLの取得
  - Viemo 
  + Check_MovieImage - 動画URLから動画サービスの判別とサムネイルURLを取得

 Save_Thumb - サムネイルをサーバに保存
  + Upload_File(画像ファイルのパス,保存先) - ユーザがアップロードした画像のサムネイルを作成して保存
  + Movie_File(動画URL,動画サムネイルURL,保存先) - 動画サムネイルのURLから画像のサムネイルを作成して保存
  - Create_ImgFile(イメージリソース,Mime,保存先) - 画像ファイルを作成保存
*/
$include_list = get_included_files();
$include_flag =  False;

if($php['set'] and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Thumbnail Control Script Error!</div></html>";exit;}

class Get_Thumbnail {

	private static function Youtube($movieId){
		global $admin;
	 	
		if($admin['yt_api_key']){
			$json = Files::Load("https://www.googleapis.com/youtube/v3/videos?id=".$movieId."&key=".$admin['yt_api_key']."&part=snippet&fields=items(snippet/thumbnails)","all");
			$obj = @json_decode($json);
			if($obj !== false){return (string) $obj->{'items'}[0]->{'snippet'}->{'thumbnails'}->{'high'}->{'url'};}
			else{return null;}
		}else{
			return "https://i.ytimg.com/vi/".$movieId."/hqdefault.jpg";
		}
	}
  
	private static function Niconico($movieId){
		$xml = @simplexml_load_file("http://www.nicovideo.jp/api/getthumbinfo/" . $movieId);
		if($xml->attributes()->status == 'ok'){return (string) $xml->thumb->thumbnail_url;}
		else{return null;}
	}
	
	private static function Himawari($movieId){
		$return = null;
		$string = @Files::Load("http://himado.in/?mode=movie&id=" . $movieId,"all");
		$string = preg_replace("/&/","&amp;",$string);
		if($string){$xml = @simplexml_load_string($string);}
		
		if($xml !== false){
			$return = (string) $xml->img_small_url;
			if(!$return){$return = (string) $xml->img_big_url;}
		}
		return $return;
	}
	
	private static function Dailymotion($movie_Id){
		$json = Files::Load("https://api.dailymotion.com/video/" . $movie_Id."?fields=thumbnail_url","all");
		$obj = @json_decode($json);
		if($obj !== false){return (string) $obj->{'thumbnail_url'};}
		else{return null;}
	}
	
	private static function Viemo($movieId){
		$json = Files::Load("https://vimeo.com/api/oembed.json?url=https://vimeo.com/" . $movieId,"all");
		$obj = @json_decode($json);
		if($obj !== false){return preg_replace("/_(.*)(\.[jpg|png|gif])/","\\2",(string) $obj->{'thumbnail_url'});}
		else{return null;}
	}
	
	public static function Check_MovieImage($Movie_Url){
		global $post_set,$movie_set;
		//Movie Site Get Thumbnail URL
		$movie_type = false;
		if(preg_match("/" . $movie_set['youtube']['regexp'] . "/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Youtube($uid[1]); $movie_type="youtube";}
		elseif(preg_match("/" . $movie_set['nico']['regexp'] . "/i",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Niconico($uid[1]); $movie_type = "nico";}
		elseif($post_set['upload']['himado'] and preg_match("/" . $movie_set['himado']['regexp'] . "/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Himawari($uid[1]); $movie_type = "himado";}
		elseif($post_set['upload']['daily'] and preg_match("/" . $movie_set['daily']['regexp'] . "/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Dailymotion($uid[1]); $movie_type = "daily";}//Daily
		elseif($post_set['upload']['viemo'] and preg_match("/" . $movie_set['viemo']['regexp'] ."/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Viemo($uid[1]); $movie_type="viemo";}
		else{Html::Error("エラー","その動画URLには対応していません。");}
		if(!is_string($movie_img) or !$movie_img){Html::Error("エラー","動画サイトからサムネイルURLの取得に失敗しました。");}

		return array('file'=>$movie_img,'type'=>$movie_type);
	}
}

class Save_Thumbnail {
//Thumbnail	Save
	public static function Upload_File($img_file,$Save_Route){
		global $post_set,$print_set;
		if(!file_exists($img_file)){Html::Error("サムネイル エラー","作成元のファイルが見つかりません。");}
		else{
			list($img_w,$img_h,$img_mime) = getimagesize($img_file);//Image Size
			list($simg_w,$simg_h) = Ctrl::Get_ImgStyle($img_file,True);//サムネイルサイズ
			if($print_set['preview']['img_w'] >= $img_w and $print_set['preview']['img_h'] >= $img_h){$simg_w=$img_w;$simg_h=$img_h;}
				
				if($img_mime == IMAGETYPE_JPEG){$ir = imagecreatefromjpeg($img_file); }
				elseif($img_mime == IMAGETYPE_PNG){$ir = imagecreatefrompng($img_file);}
				elseif($img_mime == IMAGETYPE_GIF){$ir = imagecreatefromgif($img_file);}
				else{Html::Error("サムネイル エラー","対応してないファイルです。サムネイルを保存できません。");}
	
				if($ir){
					$alpha = imagecolortransparent($ir);//Get Alpha
					
					$thumb_img = imagecreatetruecolor($simg_w,$simg_h);//New Blank Canvas
					if($alpha != -1){//透過
						imagefill($thumb_img,0,0,$alpha);
						imagecolortransparent($thumb_img,$alpha);
					}
					if($img_mime == IMAGETYPE_PNG){imagealphablending($thumb_img, false);imagesavealpha($thumb_img, true);}//PNG Alpha
					
					$make_flag = imagecopyresampled($thumb_img,$ir,0,0,0,0,$simg_w,$simg_h,$img_w,$img_h);//Make Thumbnail
	
					imagedestroy($ir);//開放
	
					if($make_flag){
						//Save Thumbnail
						$er_desc = Save_Thumbnail::Create_ImgFile($thumb_img,$img_mime,$Save_Route);
					}else{$er_desc = "サムネイルの生成に失敗しました。";}
				}else{$er_desc = "元画像が開けませんでした。";}
				
				//Error ?
				if($er_desc !== True){
					if(file_exists($Save_Route)){unlink($Save_Route);}//Delete Thumbnail
					if(file_exists($img_file)){unlink($img_file);}//Delete UpLoad Image
					
					Html::Error("サムネイル エラー",$er_desc);
				}
			}
		return;
	}

//Get Movie Thumbnail
	public static function Movie_File($Movie_Url,$Image_File,$Save_Route){
		global $post_set;
		if(!preg_match("/^https?:\/\//",$Movie_Url)){Html::Error("動画イメージ エラー","動画URLの形式が正しくありません。");}
		else{
			if(!$Image_File['file']){$Image_File = Get_Thumbnail::Check_MovieImage($Movie_Url);}
			if($post_set['upload']['movie_img']){
				//動画サムネイルをDL保存
				$get_image = file_get_contents($Image_File['file']);
				if($get_image){
					$ir = imagecreatefromstring($get_image);
					list($ir_w,$ir_h,$img_mime) = getimagesize($Image_File['file']);//Canvas Size
					if($img_mime !== IMAGETYPE_JPEG and $img_mime !== IMAGETYPE_PNG and $img_mime !== IMAGETYPE_GIF){Html::Error("エラー","動画に設定されているサムネイルが、対応してないファイル形式です。");}
					if($ir){
						$ms_flag = $Image_File['type'];
						if(file_exists($post_set['backimg'][$ms_flag])){
							list($alpha_w,$alpha_h,$alpha_mime) = getimagesize($post_set['backimg'][$ms_flag]);//Alpha Image Size
							$alpha_img_str = file_get_contents($post_set['backimg'][$ms_flag]);//Get Alpha Image
							$alpha_ir = imagecreatefromstring($alpha_img_str);//Create Image resorce
	
							if($alpha_mime !== IMAGETYPE_JPEG and $alpha_mime !== IMAGETYPE_PNG and $alpha_mime !== IMAGETYPE_GIF){Html::Error("設定エラー",'設定の$post_set[\'backimg\'][\''.$ms_flag.'\']'."が対応してないファイル形式です。");}
							//New Blank Canvas
							$thumb_img = imagecreatetruecolor($ir_w,$ir_h);//Thumbnail
							$alpha_re = imagecreatetruecolor($ir_w,$ir_h);//Alpha
							//透過指定
							$alpha_back = imagecolorallocatealpha($alpha_ir,0,0,255,127);//透過色
							imagefill($alpha_ir,0,0,$alpha_back);	imagecolortransparent($alpha_ir,$alpha_back);
							imagefill($alpha_re,0,0,$alpha_back);	imagecolortransparent($alpha_re,$alpha_back);
							//Resize
							imagecopyresampled($alpha_re,$alpha_ir,0,0,0,0,$ir_w,$ir_h,$alpha_w,$alpha_h);//Alpha
							imagecopyresampled($thumb_img,$ir,0,0,0,0,$ir_w,$ir_h,$ir_w,$ir_h);//Thumbnail
							//Mixed
							imagecopy($thumb_img,$alpha_re,0,0,0,0,$ir_w,$ir_h);
	
							imagedestroy($alpha_ir);
							imagedestroy($alpha_re);
							imagedestroy($ir);
							$ir = $thumb_img;
						}else{
							$bg = imagecolorallocate($ir,0,0,0);//Back color
							$font = imagecolorallocate($ir,0,255,255);//Font color
						
							//Write Text
							if($ms_flag == "youtube"){$mi_cry = "YOUTUBE"; $ir_w -= 100; $ir_h -= 30; $str_size = 5;}
							elseif($ms_flag == "nico"){$mi_cry = "Nicovideo"; $ir_w -= 65; $ir_h -= 12; $str_size = 3;}
							elseif($ms_flag == "himado"){$mi_cry = "Himado"; $ir_w -= 90; $ir_h -= 20; $str_size = 15;}
							elseif($ms_flag == "daily"){$mi_cry = "Dailymotion"; $ir_w -= 120; $ir_h -= 20; $str_size = 15;}
							elseif($ms_flag == "viemo"){$mi_cry="Viemo"; $ir_w -= 90; $ir_h -= 30; $str_size = 5;}
							if($ms_flag and $ms_flag != "user-local"){
								$str_flag = imagestring($ir,$str_size,$ir_w,$ir_h,$mi_cry,$font);//Write
	//							if(!$str_flag){Html::Error("動画エラー","画像に書き込めませんでした。");}
							}
						}
					}
					$create_flag = 0;
					if(file_exists($Save_Route)){umask(0);chmod($Save_Route,0666);}
					$er_desc = Save_Thumbnail::Create_ImgFile($ir,$img_mime,$Save_Route);
					//Error ?
					
					if($er_desc !== True){
						if(file_exists($Save_Route)){unlink($Save_Route);}//Delete Movie Image
						$thumb_tmp = str_replace(basename($Save_Route),"tmp-s_".basename($Save_Route),$Save_Route);
						if(file_exists($thumb_tmp)){unlink($thumb_tmp);}//Delete Thumbnail
						
						Html::Error("動画イメージ エラー",$er_desc);
					}
				}
			}
		}
		return;
	}
	
	private static function Create_ImgFile($Img_Resource,$Img_Mime,$Save_Route){
		//Save Thumbnail
		$save_flag = False;
		switch($Img_Mime){
			case IMAGETYPE_JPEG:
				$save_flag = imagejpeg($Img_Resource,$Save_Route);
			break;
			case IMAGETYPE_PNG:
				$save_flag = imagepng($Img_Resource,$Save_Route);
			break;
			case IMAGETYPE_GIF:
				$save_flag = imagegif($Img_Resource,$Save_Route);
			break;
			default:
				$save_flag = "イメージの保存ができません。対応してないファイル形式です。";
		}
		imagedestroy($Img_Resource);//開放

		if($save_flag === True and file_exists($Save_Route)){umask(0);chmod($Save_Route,0644);}//Permission Change
		else{$save_flag = "イメージの保存に失敗しました。";}

		return $save_flag;
	}

}
//Gallery Board - www.tenskystar.net
?>