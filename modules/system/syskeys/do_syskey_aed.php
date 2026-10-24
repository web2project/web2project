<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CSystem_SysKey();

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
    $AppUI->redirect('m=system&u=syskeys&a=keys');
}

$AppUI->setMsg('System Lookup Keys', UI_MSG_ALERT);
// store() and delete() return true on success (errors are in getError())
if ($del) {
	if ($obj->delete()) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
	} else {
		$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	}
} else {
	if ($obj->store()) {
		$AppUI->setMsg(!empty($_POST['syskey_id']) ? 'updated' : 'inserted', UI_MSG_OK, true);
	} else {
		$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	}
}
$AppUI->redirect('m=system&u=syskeys&a=keys');