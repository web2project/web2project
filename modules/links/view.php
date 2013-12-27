<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$link_id = (int) w2PgetParam($_GET, 'link_id', 0);

$link = new CLink();

if (!$link->load($link_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

if (!$link) {
    $AppUI->setMsg('Link');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

header("Location: " . $link->link_url);