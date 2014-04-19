<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

use Web2project\Controllers\Permissions;

$del = (int) w2PgetParam($_POST, 'del', 0);

$user_id = (int) w2PgetParam($_POST, 'user_id', 0);

$controller = new Permissions($AppUI->acl(), $del, 'Permission',
    'm=users&a=view&user_id=' . $user_id, 'm=users&a=view&user_id=' . $user_id);

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);