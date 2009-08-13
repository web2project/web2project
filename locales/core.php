<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

ob_start();

if (isset($perms)) {
  foreach ($AppUI->getActiveModules() as $dir => $module) {
    if (!$perms->checkModule($dir, 'access')) {
      continue;
    }
    $modules_tabs_crumbs = $AppUI->readFiles(W2P_BASE_DIR . '/modules/' . $dir . '/', '^' . $m . '_(tab|crumb).*\.php');
    if (count($modules_tabs_crumbs) > 0) {
      if (file_exists(W2P_BASE_DIR . '/modules/' . $dir . '/locales/' . $AppUI->user_locale . '/' . $dir . '.inc')) {
        readfile(W2P_BASE_DIR . '/modules/' . $dir . '/locales/' . $AppUI->user_locale . '/' . $dir . '.inc');
      } elseif (file_exists(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/' . $dir . '.inc')) {
        readfile(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/' . $dir . '.inc');
      } elseif (file_exists(W2P_BASE_DIR . '/modules/' . $dir . '/locales/' . $AppUI->user_locale . '.inc')) {
      	readfile(W2P_BASE_DIR . '/modules/' . $dir . '/locales/' . $AppUI->user_locale . '.inc');
      }
    }
  }
}

if (W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/common.inc') {
	readfile(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/common.inc');
}

// language files for specific locales and specific modules (for external modules) should be
// put in modules/[the-module]/locales/[the-locale]/[the-module].inc or
//        modules/[the-module]/locales/[the-locale].inc
// this allows for module specific translations to be distributed with the module

if (file_exists(W2P_BASE_DIR . '/modules/' . $m . '/locales/' . $AppUI->user_locale . '/' . $m . '.inc')) {
	readfile(W2P_BASE_DIR . '/modules/' . $m . '/locales/' . $AppUI->user_locale . '/' . $m . '.inc');
} elseif (file_exists(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/' . $m . '.inc')) {
	readfile(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/' . $m . '.inc');
} elseif (file_exists(W2P_BASE_DIR . '/modules/' . $m . '/locales/' . $AppUI->user_locale . '.inc')) {
	readfile(W2P_BASE_DIR . '/modules/' . $m . '/locales/' . $AppUI->user_locale . '.inc');
}

switch ($m) {
	case 'departments':
		if (file_exists(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/companies.inc')) {
			readfile(W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/companies.inc');
		}
		break;
	case 'system':
		if (file_exists(W2P_BASE_DIR . '/locales/' . $w2Pconfig['host_locale'] . '/styles.inc')) {
			readfile(W2P_BASE_DIR . '/locales/' . $w2Pconfig['host_locale'] . '/styles.inc');
		}
		break;
}
eval('$GLOBALS[\'translate\']=array(' . ob_get_contents() . "\n'0');");
ob_end_clean();