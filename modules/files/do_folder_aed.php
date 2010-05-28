<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$file_folder_id = (int) w2PgetParam($_POST, 'file_folder_id', 0);
$isNotNew = $file_folder_id;
$redirect = w2PgetParam($_POST, 'redirect', '');

$perms = &$AppUI->acl();
if ($del) {
	if (!canDelete('files')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($isNotNew) {
	if (!canEdit('files')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} else {
	if (!canAdd('files')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
}

$obj = new CFileFolder();
if ($file_folder_id) {
	$obj->_message = 'updated';
	$oldObj = new CFileFolder();
	$oldObj->load($file_folder_id);

} else {
	$obj->_message = 'added';
}

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect($redirect);
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('File Folder');
// delete the file folder
if ($del) {
	$obj->load($file_folder_id);
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect($redirect);
	}
}

if (($msg = $obj->store())) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
} else {
	$obj->load($obj->file_folder_id);
	$AppUI->setMsg($file_folder_id ? 'updated' : 'added', UI_MSG_OK, true);
}
$AppUI->redirect($redirect);