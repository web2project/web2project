<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = isset($_REQUEST['del']) ? w2PgetParam($_REQUEST, 'del', false) : false;

$perms = &$AppUI->acl();

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Roles');

if ($del) {
	if ($perms->deleteUserRole(w2PgetParam($_REQUEST, 'role_id', 0), w2PgetParam($_REQUEST, 'user_id', 0))) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('failed to delete role', UI_MSG_ERROR);
		$AppUI->redirect();
	}
	return;
}

if (isset($_REQUEST['user_role']) && $_REQUEST['user_role']) {
	if ($perms->insertUserRole($_REQUEST['user_role'], $_REQUEST['user_id'])) {
		$AppUI->setMsg('added', UI_MSG_ALERT, true);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('failed to add role', UI_MSG_ERROR);
		$AppUI->redirect();
	}
}
?>