<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$link_id = (int) w2PgetParam($_GET, 'link_id', 0);

$link = new CLink();
$link->link_id = $link_id;

$canView   = $link->canView();
$canAccess = $link->canAccess();

if (!$canAccess || !$canView) {
	$AppUI->redirect(ACCESS_DENIED);
}

$link->load();

header("Location: " . $link->link_url);