<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$titleBlock = new w2p_Theme_TitleBlock('Access Denied', 'error.png', $m);
$titleBlock->show();

include $AppUI->getTheme()->resolveTemplate('public/access_denied');