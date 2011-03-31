<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CFile();
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$file_id = (int) w2PgetParam($_POST, 'file_id', 0);
$isNotNew = (int) w2PgetParam($_POST, 'file_id', '0');
$cancel = (int) w2PgetParam($_POST, 'cancel', 0);
$duplicate = (int) w2PgetParam($_POST, 'duplicate', 0);
$redirect = w2PgetParam($_POST, 'redirect', '');
global $db;

$notify = w2PgetParam($_POST, 'notify', '0');
$notify = ($notify != '0') ? '1' : '0';

$notifyContacts = w2PgetParam($_POST, 'notify_contacts', 'off');
$notifyContacts = ($notifyContacts != '0') ? '1' : '0';

$perms = &$AppUI->acl();
if ($del) {
	if (!$perms->checkModuleItem('files', 'delete', $file_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($cancel) {
	if (!$perms->checkModuleItem('files', 'delete', $file_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($isNotNew) {
	if (!$perms->checkModuleItem('files', 'edit', $file_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} else {
	if (!canAdd('files')) {
		$AppUI->redirect('m=public&a=access_denied');
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

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('File');
// duplicate a file
if ($duplicate) {
	$obj->load($file_id);
	$new_file = new CFile();
	$new_file = $obj->duplicate();
	$new_file->file_project = 0;
	$new_file->file_folder = 0;
	if (!($dup_realname = $obj->duplicateFile($obj->file_project, $obj->file_real_filename))) {
		$AppUI->setMsg('Could not duplicate file, check file permissions', UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$new_file->file_real_filename = $dup_realname;
		$new_file->file_date = str_replace("'", '', $db->DBTimeStamp(time()));
		if (($msg = $new_file->store($AppUI))) {
			$AppUI->setMsg($msg, UI_MSG_ERROR);
			$AppUI->redirect($redirect);
		} else {
			$AppUI->setMsg('duplicated', UI_MSG_OK, true);
			$AppUI->redirect($redirect);
		}
	}
}
// delete the file
if ($del) {
	$obj->load($file_id);
	if (($msg = $obj->delete($AppUI))) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
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

	if ($upload['size'] < 1) {
		if (!$file_id) {
			$AppUI->setMsg('Upload file size is zero. Process aborted.', UI_MSG_ERROR);
			$AppUI->redirect($redirect);
		}
	} else {

		// store file with a unique name
		$obj->file_name = $upload['name'];
		$obj->file_type = $upload['type'];
		$obj->file_size = $upload['size'];
		$obj->file_date = str_replace("'", '', $db->DBTimeStamp(time()));
		$obj->file_real_filename = uniqid(rand());

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

if (!$file_id) {
	$obj->file_owner = $AppUI->user_id;
	if (!$obj->file_version_id) {
		$q = new w2p_Database_Query;
		$q->addTable('files');
		$q->addQuery('file_version_id');
		$q->addOrder('file_version_id DESC');
		$q->setLimit(1);
		$latest_file_version = $q->loadResult();
		$q->clear();
		$obj->file_version_id = $latest_file_version + 1;
	} else {
		$q = new w2p_Database_Query;
		$q->addTable('files');
		$q->addUpdate('file_checkout', '');
		$q->addWhere('file_version_id = ' . (int)$obj->file_version_id);
		$q->exec();
		$q->clear();
	}
}

$result = $obj->store($AppUI);

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=files&a=addedit');
}
if ($result) {
	// Notification
	$obj->load($obj->file_id);
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
    $AppUI->redirect('m=public&a=access_denied');
}