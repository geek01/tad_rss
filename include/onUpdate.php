<?php
function xoops_module_update_tad_rss(&$module, $old_version) {
    GLOBAL $xoopsDB;
    
		//if(!chk_chk1()) go_update1();

    mk_dir(XOOPS_ROOT_PATH.'/uploads/tad_rss');
    mk_dir(XOOPS_ROOT_PATH.'/uploads/tad_rss/thumbs');
    return true;
}
/*
function chk_chk1(){
	global $xoopsDB;
	$sql="select count(`counter`) from ".$xoopsDB->prefix("tad_link_content");
	$result=$xoopsDB->query($sql);
	if(empty($result)) return false;
	return true;
}


function go_update1(){
	global $xoopsDB;
	$sql="ALTER TABLE ".$xoopsDB->prefix("tad_link_content")." ADD `counter` smallint(5) NOT NULL";
	$xoopsDB->queryF($sql) or redirect_header(XOOPS_URL,3,  mysql_error());

	return true;
}

*/
//�إߥؿ�
function mk_dir($dir=""){
    //�Y�L�ؿ��W�٨q�Xĵ�i�T��
    if(empty($dir))return;
    //�Y�ؿ����s�b���ܫإߥؿ�
    if (!is_dir($dir)) {
        umask(000);
        //�Y�إߥ��Ѩq�Xĵ�i�T��
        mkdir($dir, 0777);
    }
}

//�����ؿ�
function full_copy( $source="", $target=""){
	if ( is_dir( $source ) ){
		@mkdir( $target );
		$d = dir( $source );
		while ( FALSE !== ( $entry = $d->read() ) ){
			if ( $entry == '.' || $entry == '..' ){
				continue;
			}

			$Entry = $source . '/' . $entry;
			if ( is_dir( $Entry ) )	{
				full_copy( $Entry, $target . '/' . $entry );
				continue;
			}
			copy( $Entry, $target . '/' . $entry );
		}
		$d->close();
	}else{
		copy( $source, $target );
	}
}


function rename_win($oldfile,$newfile) {
   if (!rename($oldfile,$newfile)) {
      if (copy ($oldfile,$newfile)) {
         unlink($oldfile);
         return TRUE;
      }
      return FALSE;
   }
   return TRUE;
}


function delete_directory($dirname) {
    if (is_dir($dirname))
        $dir_handle = opendir($dirname);
    if (!$dir_handle)
        return false;
    while($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname."/".$file))
                unlink($dirname."/".$file);
            else
                delete_directory($dirname.'/'.$file);
        }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
}

?>