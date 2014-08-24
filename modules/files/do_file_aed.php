<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    refactor to use a core controller

$delete = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CFile();
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect('m=files&a=addedit');
}

$action = ($delete) ? 'deleted' : 'stored';
$file_id = (int) w2PgetParam($_POST, 'file_id', 0);
$isNotNew = (int) w2PgetParam($_POST, 'file_id', '0');
$cancel = (int) w2PgetParam($_POST, 'cancel', 0);
$redirect = w2PgetParam($_POST, 'redirect', 'm=files');
$notify = w2PgetParam($_POST, 'notify', '0');
$notify = ($notify != '0') ? '1' : '0';

$notifyContacts = w2PgetParam($_POST, 'notify_contacts', 'off');
$notifyContacts = ($notifyContacts != '0') ? '1' : '0';

if ($delete) {
	if (!$obj->canDelete()) {
		$AppUI->redirect(ACCESS_DENIED);
	}
} elseif ($cancel) {
	if (!$obj->canDelete()) {
		$AppUI->redirect(ACCESS_DENIED);
	}
} elseif ($isNotNew) {
	if (!$obj->canEdit()) {
		$AppUI->redirect(ACCESS_DENIED);
	}
} else {
	if (!$obj->canCreate()) {
		$AppUI->redirect(ACCESS_DENIED);
	}
}

if ($file_id) {
	$obj->_message = 'updated';
	$oldObj = new CFile();
	$oldObj->load($file_id);
} else {
	$obj->_message = 'added';
}
$obj->file_category = (int) w2PgetParam($_POST, 'file_category', 0);
$version = w2PgetParam($_POST, 'file_version', 0);
$revision_type = w2PgetParam($_POST, 'revision_type', 0);

if (strcasecmp('major', $revision_type) == 0) {
	$major_num = strtok($version, '.') + 1;
	$_POST['file_version'] = $major_num;
}

// delete the file
if ($delete) {
	$result = $obj->delete();

    if (count($obj->getError())) {
		$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
		$AppUI->redirect($redirect);
	}

    if ($result) {
		$obj->notify($notify);
        $obj->notifyContacts($notifyContacts);

		$AppUI->setMsg($action, UI_MSG_OK, true);
		$AppUI->redirect($redirect);
	}
}
// cancel the file checkout
if ($cancel) {
	$obj->cancelCheckout($file_id);
	$AppUI->setMsg('checkout canceled', UI_MSG_OK, true);
	$AppUI->redirect($redirect);
}

if (!ini_get('safe_mode')) {
	set_time_limit(600);
}
ignore_user_abort(1);

$upload = null;
if (isset($_FILES['formfile'])) {
	$upload = $_FILES['formfile'];
    $mime_type = explode('/', $upload['type']);
    $extension = $mime_type[1];
    $allowed   = w2PgetConfig('file_types', '');
    $allowed   = str_replace(' ', '', $allowed);
    $extensions = explode(',', $allowed);

    if (!in_array($extension, $extensions)) {
        $AppUI->setMsg('This is not an allowed file type. Only these are allowed: ' . $allowed, UI_MSG_ERROR);
        $AppUI->holdObject($obj);
        $AppUI->redirect('m=files&a=addedit');
    }

	if ($upload['size'] < 1) {
		if (!$file_id) {
			$AppUI->setMsg('Upload file size is zero. Process aborted.', UI_MSG_ERROR);
            $AppUI->holdObject($obj);
			$AppUI->redirect('m=files&a=addedit');
		}
	} else {

		// store file with a unique name
		$obj->file_name = $upload['name'];
		$obj->file_type = $upload['type'];
		$obj->file_size = $upload['size'];

		$res = $obj->moveTemp($upload);
		if (!$res) {
			$AppUI->redirect($redirect);
		}

	}
}

// move the file on filesystem if the affiliated project was changed
if ($file_id && ($obj->file_project != $oldObj->file_project)) {
	$res = $obj->moveFile($oldObj->file_project, $oldObj->file_real_filename);
	if (!$res) {
		$AppUI->setMsg('File could not be moved', UI_MSG_ERROR);
		$AppUI->redirect($redirect);
	}
}

$result = $obj->store();

if (count($obj->getError())) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=files&a=addedit');
}
if ($result) {
	// Notification
	$obj->notify($notify);
    $obj->notifyContacts($notifyContacts);

    $AppUI->setMsg($file_id ? 'updated' : 'added', UI_MSG_OK, true);

	if ($obj->file_task) {
		$redirect = 'm=tasks&a=view&task_id='.$obj->file_task;
	} elseif ($obj->file_project) {
		$redirect = 'm=projects&a=view&project_id='.$obj->file_project;
	} else {
		$redirect = 'm=files';
	}
    $AppUI->redirect($redirect);
} else {
    $AppUI->redirect(ACCESS_DENIED);
}