<?php
$lib[] = "Gallery Board - RSS Control Ver:1.3";
/*
- 更新ログ -
 v1.3 22/02/07 php8対応。
 v1.2 18/03/16 Bug  $res_set -> $rss_set

- RSS出力 -
 Rss
  + Index - RSSを出力
*/

$include_list = get_included_files();
$include_flag =  False;

if(isset($php['set']) and is_array($include_list)){$include_flag = preg_grep("/".$php['set']."$/",$include_list);}
if($include_flag === False){print "<html><head><title>500 Error</title></head><div>500 RSS Control Script Error!</div></html>";exit;}

class Rss{

	public static function Index(){
		global $title,$php,$rss_set,$data_file,$lock,$lock_fr,$F,$post_set;
		// RSS Header
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

		// Get Data
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"SH");
			if($F['category'] and $post_set['category']['sw']){$get_type = "Search";$get_key=$F['category'];}
			else{$get_type = "All";$get_key = "";}
			
			list($data,,$ent_no_list,$up_date_list) = Common::Get_Item($get_type,$get_key);	
			
			if($rss_set['sort'] == 1){array_multisort($up_date_list,SORT_DESC,SORT_NUMERIC,$data);}//Sort
		$lock_fr = Files::Lock($lock['file'],$lock_fr,"UN");
		  
		$st = ($F['page']) ? $F['page'] : 0;
		
		for($i = $st; $i < $st+$rss_set['num']; $i++){
		    if(!$data[$i]) continue;
		
			list($img_file,$img_url) = Html::Get_ImageURL($data[$i]);
			
			$data[$i]['date'] = preg_replace("/(\(|（).*(\)|）)/","",$data[$i]['date']);
			$data[$i]['date'] = date('r', strtotime($data[$i]['date']));
//		    $data[$i]['msg'] = strip_tags($data[$i]['msg']);
//		    $data[$i]['msg']  = htmlspecialchars($data[$i]['msg'] ,ENT_XHTML and ENT_QUOTES,"UTF-8");//タグっ禁止
			//$desc = mb_strimwidth($desc0,0,500,"...","Shift_JIS");	//文字列丸める
			$rss .= Item_Printer::Main($data[$i]['ent_no'],0,$data[$i],$rss_set['item']);
		}
		$rss.='</channel></rss>';
		
		//- Print Out
			header('Content-Type:text/xml; charset=UTF-8');
			print $rss;

		exit;
	}
}
//Gallery Board - www.tenskystar.net
?>