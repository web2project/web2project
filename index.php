<?php
/**
 * This file is part of web2Project.
 *
 * web2Project is free software; you can redistribute it and/or modify it under the terms of the Clear BSD License as
 *  published by MetaCarta. The full text of this license is included in LICENSE.
 */

require_once 'bootstrap.php';

$loginFromPage = 'index.php';

// Required for the gantt charts
$suppressHeaders = w2PgetParam($_GET, 'suppressHeaders', false);

// check if session has previously been initialised
if (!isset($_SESSION['AppUI'])) {
	$_SESSION['AppUI'] = new w2p_Core_CAppUI();
}
$AppUI = &$_SESSION['AppUI'];

$AppUI->setStyle();
// load default preferences if not logged in
if ($AppUI->loginRequired()) {
	$AppUI->loadPrefs(0);
}

// load module based locale settings
$AppUI->setUserLocale();
include W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php';
include W2P_BASE_DIR . '/locales/core.php';
setlocale(LC_TIME, $AppUI->user_lang);

// set the default ui style
$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');
include W2P_BASE_DIR . '/style/' . $uistyle . '/overrides.php';
$uiName = str_replace('-', '', $uistyle);

$uiClass = 'style_' . $uiName;
$theme = new $uiClass($AppUI);

switch($_REQUEST['action']) {
    case 'lostpass':
        include $theme->resolveTemplate('lostpass');
        exit();
    case 'sendpass':
        sendNewPass();
        break;
    case 'login':
        $username = w2PgetParam($_POST, 'username', '');
        $password = w2PgetParam($_POST, 'password', '');
        $redirect = w2PgetParam($_POST, 'redirect', '');

        $AppUI->login($username, $password);
        $AppUI->redirect('' . $redirect);
        break;
    case 'logout':
        $AppUI->logout();
        break;
    default:
    // do nothing
}

if ($AppUI->loginRequired()) {
    include $theme->resolveTemplate('login');
    exit;
}

if (W2P_PERFORMANCE_DEBUG) {
    $w2p_performance_setuptime = (array_sum(explode(' ', microtime())) - $w2p_performance_time);
}

ob_start();

// write the HTML headers
if (!$suppressHeaders) {
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); // HTTP/1.1
    header('Pragma: no-cache'); // HTTP/1.0
    header("Content-type: text/html; charset=UTF-8");

    include $theme->resolveTemplate('header');
}

$frontpage = new w2p_Core_FrontPageController($AppUI, new w2p_FileSystem_Loader());
list($m, $a, $u) = $frontpage->resolveParameters($w2Pconfig, $_REQUEST);
$frontpage->loadIncludes();

$pageHandler = new w2p_Output_PageHandler();
$pageHandler->loadExtras($_SESSION, $AppUI, $m, 'tabs');
$pageHandler->loadExtras($_SESSION, $AppUI, $m, 'crumbs');

$module_file = W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $a . '.php';
if (!file_exists($module_file)) {
    $module_file = W2P_BASE_DIR . '/modules/public/missing_module.php';
}

require $module_file;

if (!$suppressHeaders) {
 	//Theme footer goes before the performance box
    $AppUI->getTheme()->loadCalendarJS();
    include $theme->resolveTemplate('footer');
	if (W2P_PERFORMANCE_DEBUG) {
		include $theme->resolveTemplate('performance');
	}

	//close the body and html here, instead of on the theme footer.
	echo '</body>
          </html>';
}
ob_end_flush();