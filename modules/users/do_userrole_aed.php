<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    refactor to use a core controller

$del = (int) w2PgetParam($_POST, 'del', 0);

$notify_new_user = w2PgetParam($_POST, 'notify_new_user', 'off');
$user_id = (int) w2PgetParam($_POST, 'user_id', 0);
$role_id = (int) ($del ? w2PgetParam($_POST, 'role_id', 0) : w2PgetParam($_POST, 'user_role', 0));

$perms = &$AppUI->acl();

// The account must exist and the current user must be allowed to edit
// that specific account, not just the users module.
$user = new CUser();
if (!$user_id || !$user->load($user_id) || (int) $user->user_id !== $user_id
    || !$perms->checkModuleItem('users', 'edit', $user_id)) {
	$AppUI->redirect(ACCESS_DENIED);
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Roles');

if (!$role_id) {
	$AppUI->redirect('m=users&a=view&user_id=' . $user_id);
}

// Role changes are checked per role and per account: no changes to one's
// own roles and nothing involving the Administrator role without System
// edit rights (see w2p_Extensions_Permissions::canChangeUserRole()).
if (!$perms->canChangeUserRole($role_id, $user_id)) {
	$AppUI->redirect(ACCESS_DENIED);
}

if ($del) {
	if ($perms->deleteUserRole($role_id, $user_id)) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
	} else {
		$AppUI->setMsg('failed to delete role', UI_MSG_ERROR);
	}
    $AppUI->redirect('m=users&a=view&user_id=' . $user_id);
}

if ($perms->insertUserRole($role_id, $user_id)) {
    if ('on' == $notify_new_user) {
        $contact = new CContact();
        $contact->load($user->user_contact);
        notifyNewUser($contact->contact_email, $contact->contact_first_name);
	}
	$AppUI->setMsg('added', UI_MSG_ALERT, true);
} else {
	$AppUI->setMsg('failed to add role', UI_MSG_ERROR);
}
$AppUI->redirect('m=users&a=view&user_id=' . $user_id);
