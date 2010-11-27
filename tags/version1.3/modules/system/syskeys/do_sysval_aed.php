<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!$perms->checkModule('system', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CSysVal();
$post = array('sysval_title' => w2PgetParam($_POST, 'sysval_title'), 'sysval_key_id' => w2PgetParam($_POST, 'sysval_key_id'), 'sysval_value' => w2PgetParam($_POST, 'sysval_value'), );
$svid = array('sysval_title' => w2PgetParam($_POST, 'sysval_id'));

if ($del) {
	if (!$obj->bind($svid)) {
		$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
		$AppUI->redirect();
	}
} else {
	$del = 0;
	if (!$obj->bind($post)) {
		$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
		$AppUI->redirect();
	}
}

$AppUI->setMsg('System Lookup Values', UI_MSG_ALERT);
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg($_POST['sysval_id'] ? 'updated' : 'inserted', UI_MSG_OK, true);
	}
}
$AppUI->redirect('m=system&u=syskeys');