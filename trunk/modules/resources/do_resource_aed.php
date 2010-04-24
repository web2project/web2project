<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = w2PgetParam($_POST, 'del', 0);
$resource_id = intval(w2PgetParam($_POST, 'resource_id', 0));
$isNotNew = $_POST['resource_id'];
$perms = &$AppUI->acl();
if ($del) {
	if (!$perms->checkModuleItem('resources', 'delete', $resource_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($isNotNew) {
	if (!$perms->checkModuleItem('resources', 'edit', $resource_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} else {
	if (!canAdd('resources')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
}

$obj = new CResource();
$msg = '';

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

$AppUI->setMsg('Resource');
if ($del) {
	if (!$obj->canDelete($msg)) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect('', -1);
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg($_POST['resource_id'] ? 'updated' : 'added', UI_MSG_OK, true);
	}
	$AppUI->redirect();
}