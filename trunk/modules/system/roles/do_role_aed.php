<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('roles')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$copy_role_id = w2PgetParam($_POST, 'copy_role_id', null);

$role = new CRole();

if (($msg = $role->bind($_POST))) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $role->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('Role deleted', UI_MSG_ALERT);
	}
} else {
	//Reformulated the store method to return the id of the role if sucessful, because the ids are managed by phpGALC
	//and therefore when we store the role, the role id is empty. So we need the id returned by phpGACL to be able to
	//copy permissions from other Roles.
	//If no valid id (by that I mean an integer value) is returned, then we trigger the Error Message $msg (not an integer).
	if (!(int)($msg = $role_id = $role->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$isNotNew = $_POST['role_id'];
		$AppUI->setMsg('Role ' . ($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK);
		if ($copy_role_id && $role_id) {
			$role->copyPermissions($copy_role_id, $role_id);
		}
	}
}
$AppUI->redirect('m=system&u=roles');