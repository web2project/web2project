<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (empty($s) || mb_strlen(mb_trim($s)) == 0) {
	$a = 'index';
	$AppUI->setMsg('Please enter a search value');
}