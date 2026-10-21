<?php
require_once 'base.php';
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$AppUI = new w2p_Core_CAppUI();
$defaultTZ = w2PgetConfig('system_timezone', 'UTC');
$defaultTZ = ('' == $defaultTZ) ? 'UTC' : $defaultTZ;
date_default_timezone_set($defaultTZ);

if (w2PgetConfig('activate_external_user_creation') != 'true') {
	die('You should not access this file directly');
}

/**
 * Back to the signup form with one of the messages in createuser.php.
 * (A redirect must end the request: without exit, a duplicate user name or
 * e-mail address still went on to create the account.)
 */
function w2p_signup_redirect($msg)
{
	header('Location: newuser.php?msg=' . urlencode($msg));
	exit();
}

if ('POST' != $_SERVER['REQUEST_METHOD']) {
	w2p_signup_redirect('data');
}

// Bot protection: proof-of-work, honeypot and fill time (#363).
$session = new w2p_System_Session();
$session->start();
$signupCheck = new w2p_Utilities_SignupCheck();
$failure = $signupCheck->verify($_POST);
if ('' != $failure) {
	w2p_signup_redirect($failure);
}

$username = w2PgetParam($_POST, 'user_username', 0);
$username = preg_replace("/[^A-Za-z0-9]/", "", $username);
$user = new CUser();
$result = $user->loadAll(null, "user_username = '$username'");
if (count($result) > 0) {
	w2p_signup_redirect('existing-user');
}

$email = w2PgetParam($_POST, 'contact_email', 0);
$email = preg_replace("/[^A-Za-z0-9@.+]/", "", $email);
$contact = new CContact();
$result = $contact->loadAll(null, "contact_email = '$email'");
if (count($result) > 0) {
	w2p_signup_redirect('existing-email');
}

if (!$user->bind($_POST)) {
	w2p_signup_redirect('user');
}

if (!$contact->bind($_POST)) {
	w2p_signup_redirect('contact');
}

$contact->contact_owner = 1;
$result = $contact->store();

if (count($contact->getError())) {
	w2p_signup_redirect('contact');
}

$user->user_contact = $contact->contact_id;
$result = $user->store(null, true);
if (count($user->getError())) {
	w2p_signup_redirect('user');
}

// The password is not sent: the user chose it, and e-mail is not a safe
// place to keep it.
notifyNewExternalUser($contact->contact_email, $contact->contact_first_name, $user->user_username, '');
notifyHR(w2PgetConfig('admin_email', 'admin@web2project.net'), 'w2P System Human Resources',
	$contact->contact_email, $contact->contact_first_name, $user->user_username,
	'', $user->user_id);

w2p_signup_redirect('ok');
