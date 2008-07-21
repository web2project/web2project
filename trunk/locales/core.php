<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

if ($loginFromPage == 'index.php') {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

@readfile(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/common.inc');

// language files for specific locales and specific modules (for external modules) should be
// put in modules/[the-module]/locales/[the-locale]/[the-module].inc
// this allows for module specific translations to be distributed with the module

if (file_exists(W2P_BASE_DIR . '/modules/' . $m . '/locales/' . $AppUI->user_locale . '.inc')) {
	@readfile(W2P_BASE_DIR . '/modules/' . $m . '/locales/' . $AppUI->user_locale . '.inc');
} else {
	@readfile(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/' . $m . '.inc');
}

switch ($m) {
	case 'departments':
		@readfile(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/companies.inc');
		break;
	case 'system':
		@readfile(W2P_BASE_DIR . '/locales/' . $w2Pconfig['host_locale'] . '/styles.inc');
		break;
}
eval('$GLOBALS[\'translate\']=array(' . ob_get_contents() . "\n'0');");
ob_end_clean();
?>