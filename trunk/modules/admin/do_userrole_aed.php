<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
include $AppUI->getModuleClass('contacts');
require_once ($AppUI->getSystemClass('libmail'));

$del = isset($_REQUEST['del']) ? w2PgetParam($_REQUEST, 'del', false) : false;
$notify_new_user = isset($_POST['notify_new_user']) ? $_POST['notify_new_user'] : 0;

$perms = &$AppUI->acl();

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

function notifyNewUser($address, $username) {
	global $AppUI, $baseUrl;
	$mail = new Mail;
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			return false;
		}

		$mail->To($address);
		$mail->Subject('New Account Created');
		$mail->Body("Dear $username,\n\n" . "Congratulations! Your account has been activated by the administrator.\n" . "Please use the login information provided earlier.\n\n" . "You may login at the following URL: " . W2P_BASE_URL . "\n\n" . "If you have any difficulties or questions, please ask the administrator for help.\n" . "Assuring you the best of our attention at all time.\n\n" . "Our Warmest Regards,\n\n" . "The Support Staff.\n\n" . "****PLEASE KEEP THIS EMAIL FOR YOUR RECORDS****");
		$mail->Send();
	}
}
?>