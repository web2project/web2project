<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    refactor to use a core controller

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CUser();
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect('m=users');
}
$contact = new CContact();
if (!$contact->bind($_POST)) {
	$AppUI->setMsg($contact->getError(), UI_MSG_ERROR);
    $AppUI->redirect('m=users');
}

$action = ($del) ? 'deleted' : 'stored';

$contact_id = (int) w2PgetParam($_POST, 'contact_id', 0);
$user_id = (int) w2PgetParam($_POST, 'user_id', 0);
$isNewUser = !$user_id;

$perms = &$AppUI->acl();
if ($del) {

} elseif ($isNewUser) {
	if (!canAdd('users')) {
		$AppUI->redirect(ACCESS_DENIED);
	}
} else {
	if ($user_id != $AppUI->user_id) {
        // @todo shouldn't this check for the specific user?
		if (!canEdit('users')) {
			$AppUI->redirect(ACCESS_DENIED);
		}
	}
}

$obj->user_username = strtolower($obj->user_username);

// !User's contact information not deleted - left for history.
if ($del) {
    $result = $obj->delete();
    $message = ($result) ? 'User deleted' : $obj->getError();
    $redirect    = ($result) ? 'm=users'  : ACCESS_DENIED;
    $status  = ($result) ? UI_MSG_ALERT   : UI_MSG_ERROR;

    $AppUI->setMsg($message, $status);
    $AppUI->redirect($redirect);
}

$contact->contact_owner = ($contact->contact_owner) ? $contact->contact_owner : $AppUI->user_id;

$contactArray = $contact->getContactMethods();
$result = $contact->store();

if ($result) {
	$contact->setContactMethods($contactArray);
	$obj->user_contact = $contact->contact_id;

    if ($obj->store()) {
        if ($isNewUser && w2PgetParam($_POST, 'send_user_mail', 0)) {
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
		$AppUI->setMsg($isNewUser ? 'User added' : 'User updated', UI_MSG_OK, true);
        $redirect = 'm=users&a=view&user_id='.$obj->user_id.'&tab=2';
    } else {
        $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
        $redirect = 'm=users';
    }
} else {
    $AppUI->setMsg($contact->getError(), UI_MSG_ERROR);
    $redirect = 'm=users&a=addedit&user_id='.$obj->user_id;
}

$AppUI->redirect($redirect);