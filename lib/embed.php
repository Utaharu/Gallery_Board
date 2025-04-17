<?php
$lib[] = "Gallery Board - Embed Control Ver:1.0";
/*
- 更新ログ -
 v1.0 19/06/10 動画埋め込み表示用。

-　動画埋め込み -
 Embed
*/

$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 Embed Control Script Error!</div></html>";exit;}

class Embed{
	
	function __construct(){
		global $hp_url_type,$movie_set,$post_set;
		
		$movie_type = false;
		$movie_id = false;
		
		if(preg_match($hp_url_type,$_POST['url'])){
			//Movie Site
			if(preg_match("/" . $movie_set['youtube']['regexp'] . "/",$_POST['url'],$uid)){$movie_id = $uid[1]; $movie_type="youtube";}
			elseif(preg_match("/" . $movie_set['nico']['regexp'] . "/",$_POST['url'],$uid)){$movie_id = $uid[1]; $movie_type = "nico";}//Nico
			elseif($post_set['upload']['himado'] and preg_match("/" . $movie_set['himado']['regexp'] . "/",$_POST['url'],$uid)){$movie_id = $uid[1]; }//Himado
			elseif($post_set['upload']['daily'] and preg_match("/" . $movie_set['daily']['regexp'] . "/",$_POST['url'],$uid)){$movie_id = $uid[1]; $movie_type = "daily";}//Daily
			elseif($post_set['upload']['viemo'] and preg_match("/" . $movie_set['viemo']['regexp'] ."/",$_POST['url'],$uid)){$movie_id = $uid[1]; $movie_type="viemo";}
		}
		if($movie_type and isset($movie_set[$movie_type]['embed'])){
			print preg_replace('/\$movie_id/',$movie_id,$movie_set[$movie_type]['embed']);
		}else{print $_POST['url'];}
	}
}
//Gallery Board - www.tenskystar.net
?>