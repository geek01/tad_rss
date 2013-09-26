<?php
if(file_exists("mainfile.php")){
  include_once "mainfile.php";
}elseif("../../mainfile.php"){
  include_once "../../mainfile.php";
}

include_once XOOPS_ROOT_PATH."/modules/tad_rss/function.php";

//列出所有tad_rss資料
function list_tad_rss($maxitems=5){
  global $xoopsDB,$xoopsModule,$xoopsModuleConfig,$xoopsTpl;

  $sql = "select * from ".$xoopsDB->prefix("tad_rss")." where enable='1'";

  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  $data="";
  //$i=0;
  while($all=$xoopsDB->fetchArray($result)){
    //以下會產生這些變數： $rss_sn , $title , $url , $enable
    foreach($all as $k=>$v){
      $$k=$v;
    }
    
    $rss=get_rss_by_simplepie($rss_sn,$url,$maxitems);

    $data.="
        <ul data-role='listview' data-inset='false' data-header-theme='d' data-divider-theme='d'>
        <li data-role='list-divider'>{$title}</li>
        {$rss}
        </ul>
    ";
  }

  return $data;
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

  $web_title=$feed->get_title();
  $web_link=$feed->get_permalink();
  $web_description=$feed->get_description();

  $content="";

  foreach ($feed->get_items(0, $maxitems) as $item) {
    $href = $item->get_permalink();
    $title = $item->get_title();
    $date= $item->get_date("Y-m-d H:i");
    $description= $item->get_description();
    $description_clear=strip_tags($description);
    $description_body=strip_tags($description, '<p><a><img>');

    $content.="
      <li data-icon='false' class='inner-wrap'>
        <h2>{$title}</h2>
        <p style='color:#999'>{$description_clear}</p>
        <p style='color:#666'><strong>{$web_title} · {$date}</strong></p>
          <ul class='inner-content'>
            <li>
              <h2 style='font-size:1.5em'>{$title}</h2>
              <div class='inner-body'>{$description_body}</div>
              <div class='read-more'><a href='{$href}' data-role='button' data-inline='true' data-corners='false' style='width:80%'>View on {$web_title}</a></div>
            </li>
          </ul>
      </li>
    ";
  }

  return $content;
}

//取得所有RSS清單
function get_rss_cate_list(){
  global $xoopsDB,$xoopsUser;

  $isAdmin=isadmin();

  if ($xoopsUser) {
    $username=$xoopsUser->name();
  } else {
    $username="Anonymous";
  }

  $list="
  <ul data-role='listview' style='margin-top:-16px;'>
    <li data-icon='delete'>
      <a href='#' data-rel='close'>RSS Feeds List</a>
    </li>
    <li data-icon='false' style='font-size:0.8em;color:#999'>{$username}</li>
    <li data-icon='false'><a href='{$_SERVER['PHP_SELF']}'><img src='images/icons/rss.png' class='ui-li-icon'>All</a></li>";

  if($isAdmin){
    $controlnav="
    <div style='margin:50px -15px 0 -15px'>
    <div data-role='navbar' data-iconpos='left'>
      <ul>
        <li><a href='#add' data-icon='plus' data-transition='slideup'>Add</a></li>
        <li><a href='admin/main.php' data-icon='gear' rel='external'>Settings</a></li>
      </ul>
    </div>
    </div>";
  } elseif ($xoopsUser) {
    $controlnav="";
  } else {
    $controlnav="
    <div style='margin:50px -13px 0 -15px'>
      <a href='#login' data-role='button' data-inline='true' data-corners='false' data-shadow='false' style='margin:0;width:100%' data-transition='slideup'>Login</a>
    </div>";
  }

  $sql = "select * from ".$xoopsDB->prefix("tad_rss")." where enable='1'";
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  while(list($rss_sn,$title,$url,$web_url)=$xoopsDB->fetchRow($result)){

    $list.="<li data-icon='false'><a href='{$_SERVER['PHP_SELF']}?op=view&rss_sn={$rss_sn}'><img src='http://www.google.com/s2/favicons?domain={$web_url}' class='ui-li-icon'>{$title}</a></li>";
  }
  $list.="</ul>{$controlnav}";

  return $list;
}

//取得某個RSS資料
function get_one_rss($rss_sn){
  global $xoopsModuleConfig;
  $one=get_rss_data($rss_sn);
  $url=$one['url'];
  $num=2;
  $maxitems=$xoopsModuleConfig['show_num']*$num;
  $rss=get_rss_by_simplepie($rss_sn,$url,$maxitems);

      $data="
        <ul data-role='listview' data-inset='false' data-header-theme='c' data-divider-theme='c'>
        {$rss}
        </ul>
      ";

  return $data;
}

//以流水號取得某筆RSS資料
function get_rss_data($rss_sn=""){
  global $xoopsDB;
  if(empty($rss_sn))return;
  $sql = "select * from ".$xoopsDB->prefix("tad_rss")." where rss_sn='$rss_sn'";
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
  $data=$xoopsDB->fetchArray($result);
  return $data;
}

//add編輯表單
function add_rss_form(){
  global $xoopsUser;

  $isAdmin=isadmin();

  $data="";
  if($isAdmin){
  $data="
    <form action='{$_SERVER['PHP_SELF']}' method='post' id='myForm' enctype='multipart/form-data'>
      <div class='controls controls-row'>
        <input type='text' name='url' value='' id='url' placeholder='Enter RSS Feed URL'>
        <input type='hidden' name='op' value='insert_tad_rss'>
        <button type='submit'>Save</button>
      </div>
    </form>
  ";
  }
  return $data;
}

//新增資料到tad_rss中
function insert_tad_rss(){
  global $xoopsDB,$xoopsUser;

  require_once(XOOPS_ROOT_PATH.'/modules/tad_rss/class/simplepie/SimplePie.compiled.php');
  $feed = new SimplePie();
  $feed->set_feed_url($_POST['url']);
  $feed->set_cache_location(XOOPS_ROOT_PATH."/uploads/simplepie_cache");
  $feed->init();
  $feed->handle_content_type();
  $feed->set_output_encoding(_CHARSET);
  $title=$feed->get_title();
  $web_url=$feed->get_permalink();

  $sql = "insert into ".$xoopsDB->prefix("tad_rss")."
  (`title` , `url`, `web_url` , `enable`)
  values('{$title}' , '{$_POST['url']}' , '{$web_url}' , '1')";
  $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());

  //取得最後新增資料的流水編號
  $rss_sn=$xoopsDB->getInsertId();
  return $rss_sn;
}

function login_panel(){
  global $xoopsUser;

  if(!$xoopsUser){
  $data="
    <form method='post' action='".XOOPS_URL."/user.php' data-ajax='false'>
      User:<br>
      <input type='text' maxlength='25' value='' size='12' name='uname'>
       Password:<br>
      <input type='password' maxlength='32' size='12' name='pass'><br>
      <input type='hidden' value='/modules/tad_rss/pda.php' name='xoops_redirect'>
      <input type='hidden' value='login' name='op'>
      <button type='submit' name='submit' value='Login'>Login</button><br>
    </form>
  ";
  }
  return $data;
}

//判斷是否為管理員
function isAdmin(){
  global $xoopsUser,$xoopsModule;
  $isAdmin=false;
  if ($xoopsUser) {
    $module_id = $xoopsModule->getVar('mid');
    $isAdmin=$xoopsUser->isAdmin($module_id);
  }
  return $isAdmin;
}

/*-----------執行動作判斷區----------*/
$op=(empty($_REQUEST['op']))?"":$_REQUEST['op'];
$rss_sn=(isset($_REQUEST['rss_sn']))?intval($_REQUEST['rss_sn']) : 0;

switch($op){

  case "view":
    $main=get_one_rss($rss_sn);
    $one=get_rss_data($rss_sn);
    $title=$one['title'];
  break;

  case "insert_tad_rss":
    insert_tad_rss();
    header("location: {$_SERVER['PHP_SELF']}");
  break;

  default:
    $main=list_tad_rss($xoopsModuleConfig['show_num']);
    $title=$xoopsModule->getVar('name');
  break;
}

/*-----------秀出結果區--------------*/
$menu=get_rss_cate_list();
$login=login_panel();
$add=add_rss_form();


echo "
<!DOCTYPE html>
<html lang='"._LANGCODE."'>
<head>
  <meta charset='"._CHARSET."'>
  <meta name='viewport' content='initial-scale=1.0, user-scalable=no'>
  <title>{$title}</title>  
  <link href='http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css' rel='stylesheet' type='text/css'/>  
  <style>
  /*.ui-header .ui-title {
    margin: 0.6em 2% 0.8em !important;
  }*/
  h2.ui-li-heading {
    white-space: normal;
    font-size: 15px;
  }
  .ui-li .ui-btn-inner a.ui-link-inherit {
    padding: 0.4em 15px;
  }
  #menu a.ui-link-inherit {
    padding: 0.8em 15px 0.8em 40px;
  }
  #menu a.ui-link-inherit img{
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.22);
    padding: 2px;
    background-color: #FFF;
    top: 0.8em;
  }
  .inner-content li{
    background-color: transparent;
    border: 0;
  }  
  .inner-body {
    white-space: normal;
  }
  .inner-body img{
    max-width:100% !important;
    height:auto;
  }
  .inner-body .ui-li-desc{
    font-size:1em;
    margin-top: 0.8em;
    white-space: normal;
  }
  .read-more {
    margin-top: 20px;
    text-align: center;
  }
  </style>
  
  <script src='".XOOPS_URL."/modules/tadtools/jquery/jquery.js' type='text/javascript'></script>
  <script>
    $(document).bind('mobileinit', function(){
      $.mobile.defaultPageTransition = 'slide';
      $.mobile.page.prototype.options.addBackBtn = true;
    });
  </script>
  <script>
  $(document).bind('pagebeforeshow', '#index', function(){    
    $('.ui-header').attr('data-position','fixed');
    $('.ui-body-null').trigger('create');
  });
  </script>
  <script src='http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js' type='text/javascript'></script>
   
</head>
<body>
<!-- Home -->
<div data-role='page' id='index'>
  <div data-theme='c' data-role='header' data-position='fixed'>
    <a href='#menu' data-icon='bars' data-iconpos='notext'>Menu</a>
    <h3>{$title}</h3>
  </div>
  <div data-role='content'>
    {$main}
  </div>
  <div data-role='panel' data-position='left' data-display='push' id='menu' data-theme='c'>
    {$menu}
  </div>
</div>
<!-- Login -->
<div data-role='page' id='login'>
  <div data-theme='c' data-role='header' data-position='fixed'>
    <h3>Administrator Login</h3>
  </div>
  <div data-role='content'>
    {$login}
  </div>
</div>
<!-- addrss -->
<div data-role='page' id='add'>
  <div data-theme='c' data-role='header' data-position='fixed'>
    <h3>Add</h3>
  </div>
  <div data-role='content'>
    {$add}
  </div>
</div>
</body>
</html>";
?>
