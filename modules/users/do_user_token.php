<?php

$token = w2PgetParam($_POST, 'token', '');
$user_id = w2PgetParam($_POST, 'user_id', '');

$user = new CUser();
$user->generateToken($user_id, $token);

if ($user_id) {
    $redirect = 'm=users&a=view&user_id='.$user_id;
} else {
    $redirect = 'm=users';
}
$AppUI->redirect($redirect);
