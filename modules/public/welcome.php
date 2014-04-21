<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$titleBlock = new w2p_Theme_TitleBlock('', '', $m);
$titleBlock->show();

include $AppUI->getTheme()->resolveTemplate('public/welcome');