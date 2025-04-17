<?php
/*
  GalleryBoard - RSS
v1.2 18/03/16 Bug  $res_set -> $rss_set
- RSS出力 -
 Rss
  + Index - RSSを出力
*/
require_once("config.php");//configの場所

//- 以下 PHP知る者以外は触るべからず -//
//Load
if($F['mode'] != "Rss"){
	require_once($php['files']);
	require_once($php['html']);
	require_once($php['ctrl']);
}
if($post_set['upload']['thumbnail']['sw']){require_once($php['thumb']);}
//Main Call
Rss::Index();
exit;
		
class Rss{

	public static function Index(){
		global $title,$php,$rss_set,$data_file,$lock,$F;
		$rss = <<< EOM
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xml:lang="ja">
 <channel>
   <title>{$title['rss']}</title>
   <link>{$php['base_url']}{$php['main']}</link>
   <description>{$rss_set['desc']}</description>
   <language>ja</language>
   <docs>http://blogs.law.harvard.edu/tech/rss/</docs>
EOM;

		$lock_fr = Files::Lock($lock['file'],$lock_fr,"SH");
			list($data,$ent_no_list,$up_date_list) = Files::Load($data_file['data'],"line");
			if($rss_set['sort'] == 1){array_multisort($up_date_list,SORT_DESC,SORT_NUMERIC,$data);}//Sort
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		  
		$st = ($F['page']) ? $F['page'] : 0;
		for($i = $st; $i < $st+$rss_set['num']; $i++){
		    if(!$data[$i]) continue;
		
			list($img_file,$img_url) = Html::Get_ImageURL($data[$i]);
			
			$data[$i]['date'] = preg_replace("/(\(|（).*(\)|）)/","",$data[$i]['date']);
			$data[$i]['date'] = date('r', strtotime($data[$i]['date']));
		    $data[$i]['msg'] = strip_tags($data[$i]['msg']);
		    $data[$i]['msg']  = htmlspecialchars($data[$i]['msg'] ,ENT_XHTML and ENT_QUOTES,"UTF-8");//タグっ禁止
			//$desc = mb_strimwidth($desc0,0,500,"...","Shift_JIS");	//文字列丸める
			$rss .= Html::Var_Rep($data[$i]['ent_no'],$data[$i],$rss_set['item'],"view");
		}
		$rss.='</channel></rss>';

		header('Content-Type:text/xml; charset=UTF-8');
		print $rss;

		return;
	}
}
?>