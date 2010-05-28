<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$contact_id = (int) w2PgetParam($_POST, 'contact_id', 0);
$user_id = (int) w2PgetParam($_POST, 'user_id', 0);
$isNewUser = (int) w2PgetParam($_POST, 'user_id', 0);

$perms = &$AppUI->acl();
if ($del) {
	if (!canDelete('admin')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
	if (!canDelete('users')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($isNewUser) {
	if (!canAdd('admin')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
	if (!canAdd('users')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} else {
	if ($user_id != $AppUI->user_id) {
		if (!canEdit('admin')) {
			$AppUI->redirect('m=public&a=access_denied');
		}
		if (!canEdit('users')) {
			$AppUI->redirect('m=public&a=access_denied');
		}
	}
}

$obj = new CUser();
$contact = new CContact();
if ($contact_id) {
	$contact->load($contact_id);
}

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}
if (!$contact->bind($_POST)) {
	$AppUI->setMsg($contact->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}
$obj->user_username = strtolower($obj->user_username);

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('User');

// !User's contact information not deleted - left for history.
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect();
	}
	return;
}
if ($isNewUser) {
	// If userName already exists quit with error and do nothing
	if (CUser::exists($obj->user_username) == true) {
		$AppUI->setMsg('already exists. Try another username.', UI_MSG_ERROR, true);
		$AppUI->redirect();
	}
	$contact->contact_owner = $AppUI->user_id;
}

if (($msg = $contact->store($AppUI))) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
} else {
	$obj->user_contact = $contact->contact_id;
	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		if ($isNewUser && w2PgetParam($_REQUEST, 'send_user_mail', 0)) {
			notifyNewUserCredentials($contact->contact_email, $contact->contact_first_name, $obj->user_username, $_POST['user_password']);
		}
		if (isset($_REQUEST['user_role']) && $_REQUEST['user_role']) {
			$perms = &$AppUI->acl();
			if ($perms->insertUserRole($_REQUEST['user_role'], $obj->user_id)) {
				$AppUI->setMsg('', UI_MSG_ALERT, true);
			} else {
				$AppUI->setMsg('failed to add role', UI_MSG_ERROR);
			}
		}
		$AppUI->setMsg($isNewUser ? 'added' : 'updated', UI_MSG_OK, true);
	}
	($isNewUser) ? $AppUI->redirect('m=admin&a=viewuser&user_id=' . $obj->user_id . '&tab=2') : $AppUI->redirect();
}