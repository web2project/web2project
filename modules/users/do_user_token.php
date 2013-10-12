<?php

$token = w2PgetParam($_POST, 'token', '');
$userId = w2PgetParam($_POST, 'user_id', '');

if ($userId > 0) {
    CUser::generateUserToken($userId, $token);
    $redirect = 'm=users&a=view&user_id='.$userId;
} else {
    $redirect = 'm=users';
}
$AppUI->redirect($redirect);