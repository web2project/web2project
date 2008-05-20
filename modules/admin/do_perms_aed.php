<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = isset($_POST['del']) ? $_POST['del'] : 0;

$perms = &$AppUI->acl();
if (!$perms->checkModule('admin', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}
if (!$perms->checkModule('users', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$obj = &$AppUI->acl();

$AppUI->setMsg('Permission');
if ($del) {
	if ($obj->del_acl($_REQUEST['permission_id'])) {
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
?>