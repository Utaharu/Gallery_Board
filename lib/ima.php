<?php
/* GalleryBoard - Image Auth
 v1.0 16/12/26
- 画像認証 -
 Auth
  + Get_Image - 認証画像の出力
*/
$include_list = get_included_files();
$include_flag =  False;

if($php['set'] and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Image Auth Script Error!</div></html>";exit;}

class Auth{
//Image Auth
	public static function Get_Image(){
		global $post_set;
		if($post_set['auth']['type'] and !function_exists("imagecreate")){Html::Error("設定エラー","GDのimagecreateが利用できないため、画像認証は使えません。");}
		
		$rand_img = "";
		switch($post_set['auth']['type']){
			case "rand1":
				for($cnt = 1; $cnt <= $post_set['auth']['num']; $cnt++){
					$rand_img .= rand(0,9);
				}
				break;
			case "rand2" :
				for($cnt = 1; $cnt <= $post_set['auth']['num']; $cnt++){
					$text_set = "A1BC2DEF3GH4IJ5KL6MN7OP8QR9ST0UVWXYZ";
					$rand_num = rand(0,(strlen($text_set)-1));
					$rand_img .= substr($text_set,$rand_num,1);
				}
				break;
		 	default : 
				$rand_img = $post_set['auth']['type'];
				break;
		}
		$im = imagecreate(20*$post_set['auth']['num'],30);
		$back = imagecolorallocate($im, 255,255,255);
		$black = imagecolorallocate($im, 0,0,0);
		for($cnt = 0; $cnt < strlen($rand_img); $cnt++){
			$one = substr($rand_img,$cnt,1);
			imagestring($im, rand(1,5), (5 + 20 * $cnt), 13-rand(0,5)*rand(1,2), $one, $black);
		}
	
		setcookie("auth",crypt($rand_img,"auth"));
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Content-Type: image/png');
		imagepng($im);
		imagedestroy($im);
		exit;
	}

}

//Gallery Board - www.tenskystar.net
?>