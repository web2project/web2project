<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('roles')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$copy_role_id = w2PgetParam($_POST, 'copy_role_id', null);

$role = new CSystem_Role();

if (!$role->bind($_POST)) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
    $AppUI->redirect('m=system&u=roles');
}

if ($del) {
	if ($role->delete()) {
		$AppUI->setMsg('Role deleted', UI_MSG_ALERT);
	} else {
		$AppUI->setMsg('This Role could not be deleted', UI_MSG_ERROR);
	}
} else {
	if (!$role->store()) {
		$AppUI->setMsg('This Role could not be stored', UI_MSG_ERROR);
	} else {
		$isNotNew = $_POST['role_id'];
		$AppUI->setMsg('Role ' . ($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK);
		if ($copy_role_id && $role_id) {
			$role->copyPermissions($copy_role_id, $role_id);
		}
	}
}
$AppUI->redirect('m=system&u=roles');