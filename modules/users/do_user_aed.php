<?php
/** @var w2p_Core_CAppUI $AppUI */
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

// The user form edits the contact details only. bind() took every posted
// field, so saving an account (one's own needs no users rights) could also
// set user_type (1 = "full access" in the Overall report), user_parent, the
// password hash, the login name or the linked contact. An existing account
// keeps those stored values; a new one starts as a normal user.
// (GHSA-65x8-6jcq-fc5r)
if (!$del && !$isNewUser) {
	$stored = new CUser();
	if (!$stored->load($user_id)) {
		$AppUI->redirect(ACCESS_DENIED);
	}
	foreach (array('user_username', 'user_password', 'user_type', 'user_parent', 'user_contact') as $field) {
		$obj->$field = $stored->$field;
	}
	$contact->contact_id = (int) $stored->user_contact;
} elseif ($isNewUser) {
	$obj->user_type = 0;
	$obj->user_parent = 0;
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
		// The add form sets the first role of a new account. Roles of an
		// existing account are changed only through do_userrole_aed, so a
		// user saving their own profile cannot give themselves a role.
		$user_role = (int) w2PgetParam($_POST, 'user_role', 0);
		if ($isNewUser && $user_role) {
			$perms = &$AppUI->acl();
			if ($perms->canChangeUserRole($user_role, $obj->user_id, true)
				&& $perms->insertUserRole($user_role, $obj->user_id)) {
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