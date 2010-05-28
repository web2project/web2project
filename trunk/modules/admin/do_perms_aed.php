<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$perms = &$AppUI->acl();
if (!canEdit('admin')) {
	$AppUI->redirect('m=public&a=access_denied');
}
if (!canEdit('user')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$obj = &$AppUI->acl();

$AppUI->setMsg('Permission');
if ($del) {
	if ($obj->del_acl($_POST['permission_id'])) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$obj->recalcPermissions(null, $_POST['permission_user']);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	}
} else {
	if ($obj->addUserPermission()) {
		$AppUI->setMsg($isNotNew ? 'updated' : 'added', UI_MSG_OK, true);
	} else {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	}
	$AppUI->redirect();
}