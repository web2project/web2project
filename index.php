<?php
/**
 * This file is part of web2Project.
 *
 * web2Project is free software; you can redistribute it and/or modify it under the terms of the Clear BSD License as
 *  published by MetaCarta. The full text of this license is included in LICENSE.
 */

require_once 'bootstrap.php';

$pageHandler = new w2p_Output_PageHandler($AppUI);
list($m, $a, $u) = $pageHandler->resolveParameters($w2Pconfig, $_REQUEST);

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
        $AppUI->redirect($redirect);
        break;
    case 'logout':
        $AppUI->logout();
        $AppUI->redirect();
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

// Required for the gantt charts
$suppressHeaders = w2PgetParam($_GET, 'suppressHeaders', false);
// write the HTML headers
if (!$suppressHeaders) {
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); // HTTP/1.1
    header('Pragma: no-cache'); // HTTP/1.0
    header("Content-type: text/html; charset=UTF-8");

    include $theme->resolveTemplate('header');
}

$pageHandler->loadExtras($_SESSION, $AppUI, $m, 'tabs');
$pageHandler->loadExtras($_SESSION, $AppUI, $m, 'crumbs');
$pageHandler->loadIncludes();

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