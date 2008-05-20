<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = isset($_POST['del']) ? $_POST['del'] : 0;

$isNotNew = $_POST['message_id'];
$perms = &$AppUI->acl();
if ($del) {
	if (!($perms->checkModule('forums', 'delete') && ($AppUI->user_id == $_POST['message_author'] || $perms->checkModule('admin', 'edit')))) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($isNotNew) {
	if (!($perms->checkModule('forums', 'edit') && ($AppUI->user_id == $_POST['message_author'] || $perms->checkModule('admin', 'edit')))) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} else {
	if (!($perms->checkModule('forums', 'edit') && ($AppUI->user_id == $_POST['message_author'] || $perms->checkModule('admin', 'edit')))) {
		$AppUI->redirect('m=public&a=access_denied');
	}
}

$obj = new CForumMessage();

if (($msg = $obj->bind($_POST))) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Message');
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect();
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg($isNotNew ? 'updated' : 'added', UI_MSG_OK, true);
	}
	$parent = ($obj->message_parent == -1) ? $obj->message_id : $obj->message_parent;
	$AppUI->redirect('m=forums&a=viewer&forum_id=' . $obj->message_forum . '&message_id=' . $parent);
}
?>