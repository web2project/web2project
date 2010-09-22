<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$resource_id = (int) w2PgetParam($_POST, 'resource_id', 0);
$del = (int) w2PgetParam($_POST, 'del', 0);
$isNotNew = $resource_id;

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
		$AppUI->redirect('m=resources');
	}
	if (($obj->delete($AppUI))) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect('m=resources');
	} else {
        $AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect('m=resources');
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg($resource_id ? 'updated' : 'added', UI_MSG_OK, true);
	}
	$AppUI->redirect('m=resources');
}