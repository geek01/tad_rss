<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2009-10-20
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include "header.php";
$xoopsOption['template_main'] = "tad_rss_index_tpl.html";
include_once XOOPS_ROOT_PATH."/header.php";
/*-----------function區--------------*/

//列出所有tad_rss資料
function list_tad_rss($maxitems=5){
	global $xoopsDB,$xoopsModule,$xoopsModuleConfig,$xoopsTpl;


	$sql = "select * from ".$xoopsDB->prefix("tad_rss")." where enable='1'";

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

	$data="";
	$i=0;
	while($all=$xoopsDB->fetchArray($result)){
	  //以下會產生這些變數： $rss_sn , $title , $url , $enable
    foreach($all as $k=>$v){
      $$k=$v;
    }
    
    $rss=get_rss_by_simplepie($rss_sn,$url,$maxitems);


		$data[$i]['title']=$title;
		$data[$i]['rss_sn']=$rss_sn;
		$data[$i]['url']=$url;
		$data[$i]['link']=$rss['web']['link'];
		$data[$i]['content']=$rss['content'];

		//die($rss['content']);
		$i++;
	}
	$xoopsTpl->assign('data',$data);
}


//以 simplepie 來取得RSS
function get_rss_by_simplepie($rss_sn="",$url="",$maxitems=5){

	//require_once(XOOPS_ROOT_PATH.'/modules/tad_rss/class/simplepie/simplepie.inc');
	require_once(XOOPS_ROOT_PATH.'/modules/tad_rss/class/simplepie/SimplePie.compiled.php');
	$feed = new SimplePie();
	$feed->set_output_encoding(_CHARSET);
	$feed->set_feed_url($url);
	$feed->set_cache_location(XOOPS_ROOT_PATH."/uploads/simplepie_cache");
	$feed->init();
	$feed->handle_content_type();

	$arr['web']['title']=$feed->get_title();
	$arr['web']['link']=$feed->get_permalink();
	$arr['web']['description']=$feed->get_description();

  $content="";
  $i=0;
	foreach ($feed->get_items(0, $maxitems) as $item) {
		$href = $item->get_permalink();
		$title = $item->get_title();
		$date= $item->get_date("Y-m-d");
		$description= $item->get_description();

		$content[$i]['date']=$date;
		$content[$i]['href']=$href;
		$content[$i]['title']=$title;
		$content[$i]['description']=$description;
		$i++;
	}

	$arr['webinfo']="<a href='{$arr['web']['link']}' target='_blank'>{$arr['web']['title']}</a>";
	$arr['content']=$content;
	return $arr;
}


/*-----------執行動作判斷區----------*/
$op=(empty($_REQUEST['op']))?"":$_REQUEST['op'];

$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "isAdmin" , $isAdmin) ;
  
switch($op){


	default:
	list_tad_rss($xoopsModuleConfig['show_num']);
	break;
}

/*-----------秀出結果區--------------*/
include_once XOOPS_ROOT_PATH.'/footer.php';

?>
