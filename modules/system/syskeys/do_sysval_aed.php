<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CSystem_SysVal();
$post = array('sysval_title' => w2PgetParam($_POST, 'sysval_title'),
                'sysval_key_id' => w2PgetParam($_POST, 'sysval_key_id'),
                'sysval_value' => w2PgetParam($_POST, 'sysval_value'), );
$svid = array('sysval_title' => w2PgetParam($_POST, 'sysval_id'));

if ($del) {
    $bind = $obj->bind($svid);
} else {
	$bind = $obj->bind($post);
}

if (!$bind) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect('m=system&u=syskeys');
}

$AppUI->setMsg('System Lookup Values', UI_MSG_ALERT);

$prefix  = 'System Lookup Values';
$action  = ($del) ? 'deleted' : 'stored';
$success = ($del) ? $obj->delete() : $obj->store();

if ($success) {
    $AppUI->setMsg($prefix . ' ' . $action);
} else {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
}
$AppUI->redirect('m=system&u=syskeys');