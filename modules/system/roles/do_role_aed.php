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

$action = ($del) ? 'deleted' : 'stored';
$success = ($del) ? $role->delete() : $role->store();

if ($success) {
    $AppUI->setMsg('Role '.$action, UI_MSG_OK, true);
    if ($copy_role_id) {
        $role->copyPermissions($copy_role_id, $role->role_id);
    }
} else {
    $AppUI->setMsg($role->getError(), UI_MSG_ERROR);
}
$AppUI->redirect('m=system&u=roles');