<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to identify individual functions which will be
*   deprecated in coming releases.  In the documentation for each function,
*   you must describe two things:
*
*    * the specific version of web2project where the behavior will change; and
*    * a reference to the new/proper way of performing the same functionality.
*
* During Minor releases, this file will grow only to shrink as Major releases
*   allow us to delete functions.
*
* WARNING: This file does not identify class-level method deprecations.
*   In order to find those, you'll have to explore the individual classes.
*/

//There is an issue with international UTF characters, when stored in the database an accented letter
//actually takes up two letters per say in the field length, this is a problem with costcodes since
//they are limited in size so saving a costcode as REDACI�N would actually save REDACI� since the accent takes
//two characters, so lets unaccent them, other languages should add to the replacements array too...
function cleanText($text) {
	//This text file is not utf, its iso so we have to decode/encode
	$text = utf8_decode($text);
	$trade = array('�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'N', '�' => 'n');
	$text = strtr($text, $trade);
	$text = utf8_encode($text);

	return $text;
}

//This kludgy function echos children tasks as threads
function showgtask(&$a, $level = 0, $project_id = 0) {
    /* Add tasks to gantt chart */
    global $gantt_arr;
    if ($project_id) {
        $gantt_arr[$project_id][] = array($a, $level);
    } else {
        $gantt_arr[] = array($a, $level);
    }
}

function findgchild(&$tarr, $parent, $level = 0) {
    global $projects;
    $level = $level + 1;
    $n = count($tarr);
    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
            showgtask($tarr[$x], $level, $tarr[$x]['project_id']);
            findgchild($tarr, $tarr[$x]['task_id'], $level, $tarr[$x]['project_id']);
        }
    }
}

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

function notifyNewUser($address, $username) {
	global $AppUI;
	$mail = new Mail;
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			return false;
		}

		$mail->To($address);
        $emailManager = new w2p_Output_EmailManager();
        $emailManager->getNotifyNewUser($username);
        $mail->Subject('New Account Created');
		$mail->Body();
		$mail->Send();
	}
}

function notifyNewUserCredentials($address, $username, $logname, $logpwd) {
	global $AppUI, $w2Pconfig;
	$mail = new Mail;
	if ($mail->ValidEmail($address)) {
		if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
			$email = "web2project@" . $AppUI->cfg['site_domain'];
		}

		$mail->To($address);
		$mail->Subject('New Account Created - web2Project Project Management System');
		$mail->Body($username . ",\n\n" . "An access account has been created for you in our web2Project project management system.\n\n" . "You can access it here at " . w2PgetConfig('base_url') . "\n\n" . "Your username is: " . $logname . "\n" . "Your password is: " . $logpwd . "\n\n" .
			"This account will allow you to see and interact with projects. If you have any questions please contact us.");
		$mail->Send();
	}
}