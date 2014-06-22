<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = &$AppUI->acl();

$action = ($delete) ? 'deleted' : 'stored';

$success = ($delete) ?
    $obj->del_acl((int) $_POST['permission_id']) :
    $obj->addRolePermission();

if ($success) {
    if ($delete) {
        $obj->removeACLPermissions(w2PgetParam($_POST, 'permission_id', null));
    }
} else {
    $AppUI->setMsg($obj->msg(), UI_MSG_ERROR);
}

$AppUI->redirect('m=system&u=roles');