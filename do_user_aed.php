<?php
require_once 'base.php';
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/lib/captcha/Functions.php';

$AppUI = new w2p_Core_CAppUI();
$defaultTZ = w2PgetConfig('system_timezone', 'UTC');
$defaultTZ = ('' == $defaultTZ) ? 'UTC' : $defaultTZ;
date_default_timezone_set($defaultTZ);

/*
CAPTCHA control condition...
*/
$passed = false;
if (strlen($_POST['spam_check']) > 0) {
	$cid = md5_decrypt($_POST['cid']);
	if ($cid == strtoupper($_POST['spam_check'])) {
		$passed = true;
	} else {
		header('Location: newuser.php?msg=data');
	}
} else {
	header('Location: newuser.php?msg=spam');
}

if (w2PgetConfig('activate_external_user_creation') != 'true') {
	die('You should not access this file directly');
}

$username = w2PgetParam($_POST, 'user_username', 0);
$username = preg_replace("/[^A-Za-z0-9]/", "", $username);
$user = new CAdmin_User();
$result = $user->loadAll(null, "user_username = '$username'");
if (count($result)) {
    header('Location: newuser.php?msg=existing-user');
}

$email = w2PgetParam($_POST, 'contact_email', 0);
$contact = new CContact();
$result = $contact->loadAll(null, "contact_email = '$email'");
if (count($result)) {
	header('Location: newuser.php?msg=existing-email');
}

if (!$user->bind($_POST)) {
	$AppUI->setMsg($user->getError(), UI_MSG_ERROR);
    header('Location: newuser.php?msg=user');
}

if (!$contact->bind($_POST)) {
	$AppUI->setMsg($contact->getError(), UI_MSG_ERROR);
	header('Location: newuser.php?msg=contact');
}

$result = $contact->store();
if (count($contact->getError())) {
    header('Location: newuser.php?msg=contact');
} else {
    $user->user_contact = $contact->contact_id;
    $result = $user->store(null, true);
    if (count($user->getError())) {
        header('Location: newuser.php?msg=user');
    } else {
        notifyNewExternalUser($contact->contact_email, $contact->contact_first_name, $user->user_username, $_POST['user_password']);
        notifyHR(w2PgetConfig('admin_email', 'admin@web2project.net'), 'w2P System Human Resources',
            $contact->contact_email, $contact->contact_first_name, $user->user_username,
            $_POST['user_password'], $user->user_id);
        $AppUI->setMsg('The User Administrator has been notified to grant you access to the system and an email message was sent to you with your login info. Thank you.', UI_MSG_OK);
    }
}

$AppUI->redirect();