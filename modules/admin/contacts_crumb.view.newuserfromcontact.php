<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $titleBlock, $contact_id, $is_user;
$perms = &$AppUI->acl();
$canAddUsers = canAdd('admin');

if ($canAddUsers && $contact_id && !$is_user) {
    $titleBlock->addCell('<form action="?m=admin&a=addedituser&contact_id=' . $contact_id . '" method="post" accept-charset="utf-8"><input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('create user') . '"></form>');
}