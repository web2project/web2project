<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//addlink sql
$link_id = intval(w2PgetParam($_POST, 'link_id', 0));
$del = intval(w2PgetParam($_POST, 'del', 0));

$not = w2PgetParam($_POST, 'notify', '0');
if ($not != '0') {
	$not = '1';
}

$isNotNew = $_POST['link_id'];
$perms = &$AppUI->acl();
if ($del) {
	if (!$perms->checkModuleItem('links', 'delete', $link_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($isNotNew) {
	if (!$perms->checkModuleItem('links', 'edit', $link_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} else {
	if (!$perms->checkModule('links', 'add')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
}

$obj = new CLink();
if ($link_id) {
	$obj->_message = 'updated';
} else {
	$obj->_message = 'added';
}
$obj->link_date = date('Y-m-d H:i:s');
$obj->link_category = intval(w2PgetParam($_POST, 'link_category', 0));

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Link');
// delete the link
if ($del) {
	$obj->load($link_id);
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		if ($not == '1') {
			$obj->notify();
		}
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect('m=links');
	}
}

if (!$link_id) {
	$obj->link_owner = $AppUI->user_id;
}

if (($msg = $obj->store())) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
} else {
	$obj->load($obj->file_id);
	if ($not == '1')
		$obj->notify();
	$AppUI->setMsg($file_id ? 'updated' : 'added', UI_MSG_OK, true);
}

$AppUI->redirect();
?>