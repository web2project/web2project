<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$notify_new_user = w2PgetParam($_POST, 'notify_new_user', 'off');
$user_id = (int) w2PgetParam($_POST, 'user_id', 0);

$perms = &$AppUI->acl();
if (!canEdit('admin')) {
	$AppUI->redirect('m=public&a=access_denied');
}
if (!canEdit('users')) {
	$AppUI->redirect('m=public&a=access_denied');
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Roles');

if ($user_id) {
	$user = new CUser();
	$user->load($user_id);
	$contact = new CContact();
	$contact->load($user->user_contact);
}

if ($del) {
	if ($perms->deleteUserRole(w2PgetParam($_POST, 'role_id', 0), w2PgetParam($_POST, 'user_id', 0))) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('failed to delete role', UI_MSG_ERROR);
		$AppUI->redirect();
	}
	return;
}

if (isset($_POST['user_role']) && $_POST['user_role']) {
    if ($perms->insertUserRole($_POST['user_role'], $user_id)) {
        if ('on' == $notify_new_user) {
            notifyNewUser($contact->contact_email, $contact->contact_first_name);
		}
		$AppUI->setMsg('added', UI_MSG_ALERT, true);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('failed to add role', UI_MSG_ERROR);
		$AppUI->redirect();
	}
}