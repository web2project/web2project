<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $titleBlock, $contact_id, $is_user;
$perms = &$AppUI->acl();
$canAddUsers = canAdd('users');

if ($canAddUsers && $contact_id && !$is_user) {
    $titleBlock->addButton('create user', '?m=users&a=addedit&contact_id=' . $contact_id);
}