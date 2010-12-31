<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$hid = w2PgetParam($_GET, 'hid', 'help.toc');

$inc = W2P_BASE_DIR . '/modules/help/' . $AppUI->user_locale . '/' . $hid . '.hlp';

if (!file_exists($inc)) {
	$inc = W2P_BASE_DIR . '/modules/help/en/' . $hid . '.hlp';
	if (!file_exists($inc)) {
		$hid = "help.toc";
		$inc = W2P_BASE_DIR . '/modules/help/' . $AppUI->user_locale . '/' . $hid . '.hlp';
		if (!file_exists($inc)) {
			$inc = W2P_BASE_DIR . '/modules/help/en/' . $hid . '.hlp';
		}
	}
}
if ($hid != 'help.toc') {
	echo '<a href="?m=help&amp;dialog=1">' . $AppUI->_('index') . '</a>';
}
readfile($inc);