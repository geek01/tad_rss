<?php
//  ------------------------------------------------------------------------ //
// ���Ҳե� tad �s�@
// �s�@����G2009-10-20
// $Id:$
// ------------------------------------------------------------------------- //

include_once "../../mainfile.php";
include_once "function.php";

//�P�_�O�_��ӼҲզ��޲z�v��
$isAdmin=false;
if ($xoopsUser) {
    $module_id = $xoopsModule->getVar('mid');
    $isAdmin=$xoopsUser->isAdmin($module_id);
}

$interface_menu[_TAD_TO_MOD]="index.php";
if($isAdmin){
  $interface_menu[_TAD_TO_ADMIN]="admin/main.php";
}
?>