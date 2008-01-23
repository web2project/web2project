<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $titleBlock, $contact_id, $is_user;
$perms = &$AppUI->acl();
$canAddUsers = $perms->checkModule('admin', 'add');

if ($canAddUsers && $contact_id && !$is_user) {
	$titleBlock->addCrumb('?m=admin&a=addedituser&contact_id='.$contact_id, 'create a user');
}
?>