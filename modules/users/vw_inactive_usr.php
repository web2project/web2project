<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $w2Pconfig, $canEdit, $stub, $where, $orderby;

$users = w2PgetUsersList($stub, $where, $orderby);
$canLogin = false;

require W2P_BASE_DIR . '/modules/users/vw_usr.php';
