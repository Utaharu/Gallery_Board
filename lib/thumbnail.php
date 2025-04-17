<?php
$lib[] = "Gallery Board - Thumbnail Control Ver:1.6";
/*
- 更新ログ -
サイズの計算をcommonから移動し、get_imgstyle->Size_Calculation
 v1.6 22/02/06 imagickとffmpegが利用出来る場合、apngのサムネイル生成に対応。
 v1.5 22/02/03 php8に対応。
  
- サムネイル操作 -
 Thumbnail
  + Size_Calculation - サイズの計算
  
 Get_Thumb - 動画サムネイルの取得
  + Check_MovieImage - 動画URLから動画サービスの判別とサムネイルURLを取得
  - RequestUrl_Replace - サムネイル取得用urlを置換
  - Youtube - YoutubeサムネイルURLの取得
  - Niconico - ニコニコ動画サムネイルURLの取得
  - Himawari - ひまわり動画サムネイルURLの取得
  - Dailymotion - Dailymotion動画サムネイルURLの取得
  - Viemo 

 Save_Thumb - サムネイルをサーバに保存
  + Upload_File(画像ファイルのパス,保存先) - ユーザがアップロードした画像のサムネイルを作成して保存
  + Movie_File(動画URL,動画サムネイルURL,保存先) - 動画サムネイルのURLから画像のサムネイルを作成して保存
  - Create_ImgFile(イメージリソース,Mime,保存先) - 画像ファイルを作成保存
*/
$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Thumbnail Control Script Error!</div></html>";exit;}

class Thumbnail{
//画像 - 表示サイズの計算
	public static function Size_Calculation($Img_Width,$Img_Height){
		global $F,$print_set;
		$print_size = array("width"=>0,"height"=>0);

		//計算
		if($Img_Height> 0 and $Img_Width > 0){
			// - 比の算出
			$img_rate['width'] = ceil($Img_Width / $Img_Height); 
			$img_rate['height'] = ceil($Img_Height/ $Img_Width);	
			// - 比に基づいた画像表示サイズの算出
			if($img_rate['height'] > $img_rate['width']){
				//縦長
				$print_size['height'] = $print_set['list']['img_h'];
				$print_size['width'] = round($Img_Width * ($print_set['list']['img_h'] / $Img_Height));
			}elseif($img_rate['height'] < $img_rate['width'] ){
				//横長
				$print_size['height'] = round($Img_Height * ($print_set['list']['img_w'] / $Img_Width));
				$print_size['width'] = $print_set['list']['img_w'];
			}else{
				//画像サイズ > 表示サイズ
				if($Img_Height> $print_set['list']['img_h'] or $Img_Width > $print_set['list']['img_w']){						
					if($print_set['list']['img_h'] > $print_set['list']['img_w']){
						//表示幅 < 表示高さの場合、狭い表示幅にあわせる。
						$print_size['height'] = round($Img_Height * ($print_set['list']['img_w'] / $Img_Width));
						$print_size['width'] = round($Img_Height * ($print_set['list']['img_w'] / $Img_Width));
					}elseif($print_set['list']['img_h'] < $print_set['list']['img_w']){
						//表示幅 > 表示高さの場合、狭い表示高さに合わせる。
						$print_size['height'] = round($Img_Width * ($print_set['list']['img_h'] / $Img_Height));
						$print_size['width'] = round($Img_Width * ($print_set['list']['img_h'] / $Img_Height));
					}else{
						$print_size['height'] = round($Img_Height* ($print_set['list']['img_w'] / $Img_Width));
						$print_size['width'] = round($Img_Width * ($print_set['list']['img_h'] / $Img_Height));
					}
				}else{
					$print_size['height'] = $Img_Height;
					$print_size['width'] = $Img_Width;
				}
			}

		}
		return array($print_size['width'],$print_size['height']);
  	}
}

class Get_Thumbnail extends Thumbnail{
	
	public static function Check_MovieImage($Movie_Url){
		global $post_set,$movie_set;
		//Movie Site Get Thumbnail URL
		$movie_type = false;
		if(preg_match("/" . $movie_set['youtube']['regexp'] . "/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Youtube($uid[1]); $movie_type="youtube";}
		elseif(preg_match("/" . $movie_set['nico']['regexp'] . "/i",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Niconico($uid[1]); $movie_type = "nico";}
		elseif($post_set['upload']['himado'] and preg_match("/" . $movie_set['himado']['regexp'] . "/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Himawari($uid[1]); $movie_type = "himado";}
		elseif($post_set['upload']['daily'] and preg_match("/" . $movie_set['daily']['regexp'] . "/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Dailymotion($uid[1]); $movie_type = "daily";}//Daily
		elseif($post_set['upload']['viemo'] and preg_match("/" . $movie_set['viemo']['regexp'] ."/",$Movie_Url,$uid)){$movie_img = Get_Thumbnail::Viemo($uid[1]); $movie_type="viemo";}
		else{Error_Page::Main("エラー","その動画URLには対応していません。");}
		if(!is_string($movie_img) or !$movie_img){Error_Page::Main("エラー","動画サイトからサムネイルURLの取得に失敗しました。");}

		return array('file'=>$movie_img,'type'=>$movie_type);
	}


	private static function RequestUrl_Replace($Movie_Id,$Request_Url){
		return preg_replace('/\$movie_id/',$Movie_Id,$Request_Url);
	}

	private static function Youtube($Movie_Id){
		global $admin,$movie_set;
		if($admin['yt_api_key']){
			$json = Files::Load(Get_Thumbnail::RequestUrl_Replace($Movie_Id,$movie_set['youtube']['thumb_url']['v3']),"all");
			$obj = @json_decode($json);
			if($obj !== false){return (string) $obj->{'items'}[0]->{'snippet'}->{'thumbnails'}->{'high'}->{'url'};}
			else{return null;}
		}else{
			return Get_Thumbnail::RequestUrl_Replace($Movie_Id,$movie_set['youtube']['thumb_url']['default']);
		}
	}
  
	private static function Niconico($Movie_Id){
		global $movie_set;
		$xml = @simplexml_load_file(Get_Thumbnail::RequestUrl_Replace($Movie_Id,$movie_set['nico']['thumb_url']));
		if($xml->attributes()->status == 'ok'){return (string) $xml->thumb->thumbnail_url;}
		else{return null;}
	}
	
	private static function Himawari($Movie_Id){
		$return = null;
		$string = @Files::Load(Get_Thumbnail::RequestUrl_Replace($Movie_Id,$movie_set['himado']['thumb_url']),"all");
		$string = preg_replace("/&/","&amp;",$string);
		if($string){$xml = @simplexml_load_string($string);}
		
		if($xml !== false){
			$return = (string) $xml->img_small_url;
			if(!$return){$return = (string) $xml->img_big_url;}
		}
		return $return;
	}
	
	private static function Dailymotion($Movie_Id){
		global $movie_set;
		$json = Files::Load(Get_Thumbnail::RequestUrl_Replace($Movie_Id,$movie_set['daily']['thumb_url']),"all");
		$obj = @json_decode($json);
		if($obj !== false){return (string) $obj->{'thumbnail_url'};}
		else{return null;}
	}
	
	private static function Viemo($movieId){
		global $movie_set;
		$json = Files::Load(Get_Thumbnail::RequestUrl_Replace($Movie_Id,$movie_set['viemo']['thumb_url']),"all");
		$obj = @json_decode($json);
		if($obj !== false){return preg_replace("/_(.*)(\.[jpg|png|gif])/","\\2",(string) $obj->{'thumbnail_url'});}
		else{return null;}
	}
	
}

//Thumbnail	Save
class Save_Thumbnail extends Thumbnail{

	public static function Upload_File($Img_File,$Save_Route){
		global $post_set,$print_set;
		if(!file_exists($Img_File)){Error_Page::Main("サムネイル エラー","作成元のファイルが見つかりません。".$Img_File);}
		else{
			$img = array("width"=>0,"height"=>0,"mime"=>"");
			list($img['width'],$img['height'],$img['mime']) = getimagesize($Img_File);//Image Size
			
			$thumbnail = array("width"=>0,"height"=>0);
			list($thumbnail['width'],$thumbnail['height']) = Thumbnail::Size_Calculation($img['width'],$img['height']);//サムネイルサイズの計算

			if($print_set['preview']['img_w'] >= $img['width'] and $print_set['preview']['img_h'] >= $img['height']){
				$thumbnail['width'] = $img['width'];
				$thumbnail['height'] = $img['height'];
			}
				
				if($img['mime'] == IMAGETYPE_JPEG){$ir = imagecreatefromjpeg($Img_File); }
				elseif($img['mime'] == IMAGETYPE_PNG){
					$animation_frame_num = 1;
					$img_bytes = file_get_contents($Img_File);
					if($img_bytes){
						try
						{
							if(strpos(substr($img_bytes, 0, strpos($img_bytes, 'IDAT')),'acTL')!==false){			
								if(class_exists("Imagick")){
									$animation_image = new Imagick();
									try
									{
										$animation_image->readImage("apng:" . $Img_File);
									}
									catch (Exception $apng_excption)
									{
										$animation_image->readImage($Img_File);
									}
									$animation_frame_num = $animation_image->getNumberImages();
								}
							}
						}catch (Exception $imagick_exception){}
					}
					if($animation_frame_num < 2){
						//GD
						$ir = imagecreatefrompng($Img_File);
					}else{
						//AnimationPNG - Imagick Flag
						$ir = "AnimationPNG";
					}						
				}elseif($img['mime'] == IMAGETYPE_GIF){
					$animation_frame_num = 1;
					//Animation GIF Check - PECL Imagick use
					try
					{
						if(class_exists("Imagick")){
							$animation_image = new Imagick();
							$animation_image->readImage($Img_File);
							$animation_frame_num = $animation_image->getNumberImages();
						}
					} catch(Exception $imagick_exception){}
					//GIF Save Mode  Flag ((Not Animation GIF or Not available Imagick  = GD ) or (Animation GIF & Imagick available = Imagick use))
					if($animation_frame_num < 2){
						//GD
						$ir = imagecreatefromgif($Img_File);
					}else{
						//AnimationGIF - Imagick Flag
						$ir = "AnimationGIF";
					}
				}else{Error_Page::Main("サムネイル エラー","対応してないファイルです。サムネイルを保存できません。");}

				if($ir === False){$er_desc = "元画像が開けませんでした。";}
				elseif($ir === "AnimationGIF" or $ir === "AnimationPNG"){
					$simg_filter = 0;
					$simg_blur = 0;

					//AnimationImage - Imagick Use
					$animation_image->setFirstIterator();
					$animation_image = $animation_image->coalesceImages();
					
					//Frame Resize
					do {
						$animation_image->resizeImage($thumbnail['width'], $thumbnail['height'], $simg_filter, $simg_blur);
					} while ($animation_image->nextImage());

					$animation_image = $animation_image->optimizeImageLayers();
					$prefix  = "";
					if($ir == "AnimationPNG"){$prefix = "apng:";}
					$er_desc = $animation_image->writeImages($prefix . $Save_Route, true);//Save Thumbnail (Imagick - AnimationGIF)
					$animation_image->clear();

				}else{
					//Not AnimationGIF or Can't use Imagick = GD
					$alpha = imagecolortransparent($ir);//Get Alpha
					
					$thumb_img = imagecreatetruecolor($thumbnail['width'],$thumbnail['height']);//New Blank Canvas
					if($alpha != -1){//透過
						imagefill($thumb_img,0,0,$alpha);
						imagecolortransparent($thumb_img,$alpha);
					}
					if($img['mime'] == IMAGETYPE_PNG){imagealphablending($thumb_img, false);imagesavealpha($thumb_img, true);}//PNG Alpha
					
					$make_flag = imagecopyresampled($thumb_img,$ir,0,0,0,0,$thumbnail['width'],$thumbnail['height'],$img['width'],$img['height']);//Make Thumbnail
	
					imagedestroy($ir);//開放
	
					if($make_flag){
						//Save Thumbnail
						$er_desc = Save_Thumbnail::Create_ImgFile($thumb_img,$img['mime'],$Save_Route);
					}else{$er_desc = "サムネイルの生成に失敗しました。";}
				}
				//Error ?
				if($er_desc !== True){
					if(file_exists($Save_Route)){unlink($Save_Route);}//Delete Thumbnail
					if(file_exists($Img_File)){unlink($Img_File);}//Delete UpLoad Image
					
					Error_Page::Main("サムネイル エラー",$er_desc);
				}
			}
		return;
	}

//Get Movie Thumbnail
	public static function Movie_File($Movie_Url,$Image_File,$Save_Route){
		global $post_set;
		if(!preg_match("/^https?:\/\//",$Movie_Url)){Error_Page::Main("動画イメージ エラー","動画URLの形式が正しくありません。");}
		else{
			if(!$Image_File['file']){$Image_File = Get_Thumbnail::Check_MovieImage($Movie_Url);}
			if($post_set['upload']['movie_img']){
				//動画サムネイルをDL保存
				$get_image = file_get_contents($Image_File['file']);
				if($get_image){
					$ir = imagecreatefromstring($get_image);
					list($ir_w,$ir_h,$img_mime) = getimagesize($Image_File['file']);//Canvas Size
					if($img_mime !== IMAGETYPE_JPEG and $img_mime !== IMAGETYPE_PNG and $img_mime !== IMAGETYPE_GIF){Error_Page::Main("エラー","動画に設定されているサムネイルが、対応してないファイル形式です。");}
					if($ir){
						$ms_flag = $Image_File['type'];
						if(file_exists($post_set['backimg'][$ms_flag])){
							list($alpha_w,$alpha_h,$alpha_mime) = getimagesize($post_set['backimg'][$ms_flag]);//Alpha Image Size
							$alpha_img_str = file_get_contents($post_set['backimg'][$ms_flag]);//Get Alpha Image
							$alpha_ir = imagecreatefromstring($alpha_img_str);//Create Image resorce
	
							if($alpha_mime !== IMAGETYPE_JPEG and $alpha_mime !== IMAGETYPE_PNG and $alpha_mime !== IMAGETYPE_GIF){Error_Page::Main("設定エラー",'設定の$post_set[\'backimg\'][\''.$ms_flag.'\']'."が対応してないファイル形式です。");}
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
	//							if(!$str_flag){Error_Page::Main("動画エラー","画像に書き込めませんでした。");}
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
						
						Error_Page::Main("動画イメージ エラー",$er_desc);
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

		return $save_flag;
	}

}
//Gallery Board - www.tenskystar.net
?>