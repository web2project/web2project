<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$token = w2PgetParam($_POST, 'token', '');
$user_id = (int) w2PgetParam($_POST, 'user_id', 0);

// A feed URL gives read access to the user's calendar: only the user, or
// someone who may edit users, can replace it.
if (!$user_id || ($user_id != $AppUI->user_id && !canEdit('users'))) {
	$AppUI->redirect(ACCESS_DENIED);
}

$user = new CUser();
$user->generateToken($user_id, $token);

$AppUI->redirect('m=users&a=view&user_id=' . $user_id);
