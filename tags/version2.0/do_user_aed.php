<?php /* $Id$ $URL$ */
require_once 'base.php';
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

require_once W2P_BASE_DIR . '/lib/captcha/Functions.php';
/*
CAPTCHA control condition...
*/
if (strlen($_POST['spam_check']) > 0) {
	$cid = md5_decrypt($_POST['cid']);
	if ($cid == strtoupper($_POST['spam_check'])) {
		$passed = true;
	} else {
		$passed = false;
		echo "<script language='javascript'>
            alert('Error: You didn\'t provide the correct Anti Spam Security ID or all required data. Please try again.');
            history.go(-1);
	        </script>";
		exit;
	}
} else {
	$passed = false;
	echo "
          <script language='javascript'>
                alert('Error: You didn\'t provide the Anti Spam Security ID. Please try again.');
                history.go(-1);
          </script>
         ";
	exit;
}

require_once W2P_BASE_DIR . '/includes/config.php';

if (!isset($GLOBALS['OS_WIN'])) {
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, 'WIN') !== false);
}

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$AppUI = new CAppUI();

// Create the roles class container
require_once W2P_BASE_DIR . '/modules/system/roles/roles.class.php';
if (w2PgetConfig('activate_external_user_creation') != 'true') {
	die('You should not access this file directly');
}

$username = w2PgetParam($_POST, 'user_username', 0);
$contactListByUsername = CContact::getContactByUsername($username);

if ($contactListByUsername != 'User Not Found') {
	error_reporting(0);
	echo "<script language='javascript'>
          alert('The username you selected already exists, please select another or if that user name is yours request the password recovery through the dedicated link.');
          history.go(-2);
        </script>";
	die();
}

$email = w2PgetParam($_POST, 'contact_email', 0);
$contactListByEmail = CContact::getContactByEmail($email);

if ($contactListByEmail != 'User Not Found') {
	error_reporting(0);
	echo "<script language='javascript'>
          alert('The email you selected already exists, please select another or if that email is yours request the password recovery through the dedicated link.');
          history.go(-2);
        </script>";
	die();
}

$user = new CUser();
if (!$user->bind($_POST)) {
	$AppUI->setMsg($user->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

$contact = new CContact();
if (!$contact->bind($_POST)) {
	$AppUI->setMsg($contact->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('User');

$isNewUser = !(w2PgetParam($_REQUEST, 'user_id', 0));

if ($isNewUser) {
	// check if a user with the param Username already exists
	if( is_array($contactListByUsername)) {
		$AppUI->setMsg('This username is not available, please try another.', UI_MSG_ERROR, true);
		$AppUI->redirect();		
	} else {
		$contact->contact_owner = $AppUI->user_id;
	}
}

$result = $contact->store($AppUI);
if ($result) {
    $contactArray = array('email_primary' => $_POST['contact_email'],
        'phone_primary' => $_POST['contact_phone']);
    $contact->setContactMethods($contactArray);

	$user->user_contact = $contact->contact_id;
	if (($msg = $user->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
        $contactMethods = $contact->getContactMethods(array('email_primary'));
        if ($isNewUser) {
            notifyNewExternalUser($contactMethods['email_primary'], $contact->contact_first_name, $user->user_username, $_POST['user_password']);
		}
		notifyHR('hr@yourdomain.com', 'w2P System Human Resources', $contactMethods['email_primary'], $contact->contact_first_name, $user->user_username, $_POST['user_password'], $user->user_id);

		$q = new DBQuery;
		$q->addTable('users', 'u');
        $q->leftJoin('contacts_methods', 'cm', 'cm.contact_id = u.user_contact');
        $q->addWhere("cm.method_name = 'email_primary'");
        $q->addQuery('cm.method_value AS contact_email');
		$q->addWhere('u.user_username = \'admin\'');
		$admin_user = $q->loadList();
	}
} else {
    $AppUI->setMsg($msg, UI_MSG_ERROR);
}

echo "<script language='javascript'>
	      alert('The User Administrator has been notified to grant you access to the system and an email message was sent to you with your login info. Thank you very much.');
	      history.go(-2);
      </script>";

function notifyNewExternalUser($address, $username, $logname, $logpwd) {
	global $AppUI;
	$mail = new Mail;
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			$email = 'web2project@web2project.net';
		}

		$mail->To($address);
		$mail->Subject('New Account Created');
		$mail->Body('You have signed up for a new account on ' . w2PgetConfig('company_name') . ".\n\n" . "Once the administrator approves your request, you will receive an email with confirmation.\n" . "Your login information are below for your own record:\n\n" . 'Username:	' . $logname . "\n" . 'Password:	' . $logpwd . "\n\n" . "You may login at the following URL: " . W2P_BASE_URL . "\n\n" . "Thank you very much.\n\n" . 'The ' . w2PgetConfig('company_name') . " Support Staff.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****');
		$mail->Send();
	}
}

function notifyHR($address, $username, $uaddress, $uusername, $logname, $logpwd, $userid) {
	global $AppUI;
	$mail = new Mail;
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			$email = 'web2project@web2project.net';
		}

		$mail->To($address);
		$mail->Subject('New External User Created');
		$mail->Body('A new user has signed up on ' . w2PgetConfig('company_name') . ". Please go through the user details below:\n" . 'Name:	' . $uusername . "\n" . 'Username:	' . $logname . "\n" . 'Email:	' . $uaddress . "\n\n" . 'You may check this account at the following URL: ' . W2P_BASE_URL . '/index.php?m=admin&a=viewuser&user_id=' . $userid . "\n\n" . "Thank you very much.\n\n" . 'The ' . w2PgetConfig('company_name') . " Taskforce.\n\n" . '****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****');
		$mail->Send();
	}
}