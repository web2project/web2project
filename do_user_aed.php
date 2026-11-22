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

// Sign-up user names are letters and digits only. The check used to strip
// other characters but the account kept the name as posted, so "pat.doe"
// was checked as "patdoe" and stored as "pat.doe". Refuse such names, and
// store exactly the name that was checked (lower case, as the Users module
// stores them).
$username = w2PgetParam($_POST, 'user_username', '');
$username = is_string($username) ? strtolower(trim($username)) : '';
if (!preg_match('/^[a-z0-9]+$/', $username)
	|| strlen($username) < (int) w2PgetConfig('username_min_len', 4)) {
	w2p_signup_redirect('username');
}
$_POST['user_username'] = $username;
$user = new CUser();
$result = $user->loadAll(null, "user_username = '$username'");
if (count($result) > 0) {
	w2p_signup_redirect('existing-user');
}

// Check the address exactly as it will be stored. Stripping characters
// here made jane_doe@example.com and janedoe@example.com collide, and
// missed real duplicates that contain the stripped characters. A valid
// address can still contain a quote, so quote it for the query.
$email = w2PgetParam($_POST, 'contact_email', '');
$email = is_string($email) ? trim($email) : '';
if ('' == $email || !w2p_check_email($email)) {
	w2p_signup_redirect('email');
}
$_POST['contact_email'] = $email;
$contact = new CContact();
$q = new w2p_Database_Query();
$result = $contact->loadAll(null, 'contact_email = ' . $q->quote($email));
if (count($result) > 0) {
	w2p_signup_redirect('existing-email');
}

if (!$user->bind($_POST)) {
	w2p_signup_redirect('user');
}

if (!$contact->bind($_POST)) {
	w2p_signup_redirect('contact');
}

// A sign-up creates a new, normal account and a new contact, whatever else
// was posted. (GHSA-65x8-6jcq-fc5r)
$user->user_id = 0;
$user->user_type = 0;
$user->user_parent = 0;
$contact->contact_id = 0;

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
