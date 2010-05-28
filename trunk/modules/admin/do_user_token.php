<?php

$token = w2PgetParam($_POST, 'token', '');
$userId = w2PgetParam($_POST, 'user_id', '');

if ($userId > 0) {
    CUser::generateUserToken($userId, $token);
    $AppUI->redirect( 'm=admin&a=viewuser&user_id='.$userId );
} else {
    $AppUI->redirect( 'm=admin' );
}