<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$link_id = (int) w2PgetParam($_GET, 'link_id', 0);

$link = new CLink();

if (!$link->load($link_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

// Only redirect to http(s)/ftp/mailto URLs, whatever is stored.
$url = w2p_safe_url(html_entity_decode($link->link_url, ENT_QUOTES));
if ('' == $url) {
    $AppUI->setMsg('Link');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=links');
}
header('Location: ' . str_replace(array("\r", "\n"), '', $url));