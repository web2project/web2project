<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
include $AppUI->getModuleClass('contacts');
require_once ($AppUI->getSystemClass('libmail'));

$del = isset($_REQUEST['del']) ? w2PgetParam($_REQUEST, 'del', false) : false;
$notify_new_user = isset($_POST['notify_new_user']) ? $_POST['notify_new_user'] : 0;

$perms = &$AppUI->acl();
if (!$perms->checkModule('admin', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}
if (!$perms->checkModule('users', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Roles');

if ($_REQUEST['user_id']) {
	$user = new CUser();
	$user->load($_REQUEST['user_id']);
	$contact = new CContact();
	$contact->load($user->user_contact);
}

if ($del) {
	if ($perms->deleteUserRole(w2PgetParam($_REQUEST, 'role_id', 0), w2PgetParam($_REQUEST, 'user_id', 0))) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('failed to delete role', UI_MSG_ERROR);
		$AppUI->redirect();
	}
	return;
}

if (isset($_REQUEST['user_role']) && $_REQUEST['user_role']) {
	if ($perms->insertUserRole($_REQUEST['user_role'], $_REQUEST['user_id'])) {
		if ($notify_new_user) {
			notifyNewUser($contact->contact_email, $contact->contact_first_name);
		}
		$AppUI->setMsg('added', UI_MSG_ALERT, true);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('failed to add role', UI_MSG_ERROR);
		$AppUI->redirect();
	}
}
?>