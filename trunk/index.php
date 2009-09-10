<?php /* $Id$ $URL$ */

/*
Copyright (c) 2007-2008 The web2Project Development Team <w2p-developers@web2project.net>
Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>

This file is part of web2Project.

web2Project is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

web2Project is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2Project; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
//error_reporting( E_ALL );

$loginFromPage = 'index.php';
require_once 'base.php';

clearstatcache();
if (is_file(W2P_BASE_DIR . '/includes/config.php')) {
	require_once W2P_BASE_DIR . '/includes/config.php';
	if (isset($dPconfig)) {
		echo '<html><head><meta http-equiv="refresh" content="5; URL=' . W2P_BASE_URL . '/install/index.php"></head><body>';
		echo 'Fatal Error. It appears you\'re converting from dotProject.<br/><a href="./install/index.php">' . 'Click Here To Start the Conversion!</a> (forwarded in 5 sec.)</body></html>';
		exit();		
	}
} else {
	echo '<html><head><meta http-equiv="refresh" content="5; URL=' . W2P_BASE_URL . '/install/index.php"></head><body>';
	echo 'Fatal Error. You haven\'t created a config file yet.<br/><a href="./install/index.php">' . 'Click Here To Start Installation and Create One!</a> (forwarded in 5 sec.)</body></html>';
	exit();
}

if (!isset($GLOBALS['OS_WIN'])) {
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, 'WIN') !== false);
}

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/classes/permissions.class.php';
require_once W2P_BASE_DIR . '/includes/session.php';

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = w2PgetParam($_GET, 'suppressHeaders', false);

// manage the session variable(s)
w2PsessionStart(array('AppUI'));

// write the HTML headers
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
header("Content-type: text/html; charset=UTF-8");
// Force POSIX locale (to prevent functions such as strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// check if session has previously been initialised
if (!isset($_SESSION['AppUI']) || isset($_GET['logout'])) {
	if (isset($_GET['logout']) && isset($_SESSION['AppUI']->user_id)) {
		$AppUI = &$_SESSION['AppUI'];
		$user_id = $AppUI->user_id;
		addHistory('login', $AppUI->user_id, 'logout', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
	}

	$_SESSION['AppUI'] = new CAppUI;
}
$AppUI = &$_SESSION['AppUI'];
$last_insert_id = $AppUI->last_insert_id;

$AppUI->checkStyle();

// load the commonly used classes
require_once ($AppUI->getSystemClass('date'));
require_once ($AppUI->getSystemClass('w2p'));
require_once ($AppUI->getSystemClass('query'));

//Now that we have $AppUI lets add our ajax functions in
//require_once ($AppUI->getSystemClass('ajax'));

//Function for update lost action in user_access_log
$AppUI->updateLastAction($last_insert_id);
// load default preferences if not logged in
if ($AppUI->doLogin()) {
	$AppUI->loadPrefs(0);
}

//Function register logout in user_acces_log
if (isset($user_id) && isset($_GET['logout'])) {
	$AppUI->registerLogout($user_id);
}

// check is the user needs a new password
if (w2PgetParam($_POST, 'lostpass', 0)) {
	$uistyle = w2PgetConfig('host_style');
	$AppUI->setUserLocale();
	@include_once W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php';
	include_once W2P_BASE_DIR . '/locales/core.php';
	setlocale(LC_TIME, $AppUI->user_lang);
	if (w2PgetParam($_POST, 'sendpass', 0)) {
		require W2P_BASE_DIR . '/includes/sendpass.php';
		sendNewPass();
	} else {
		require W2P_BASE_DIR . '/style/' . $uistyle . '/lostpass.php';
	}
	exit();
}

// check if the user is trying to log in
// Note the change to REQUEST instead of POST.  This is so that we can
// support alternative authentication methods such as the PostNuke
// and HTTP auth methods now supported.
if (isset($_POST['login'])) {
	$username = w2PgetCleanParam($_POST, 'username', '');
	$password = w2PgetCleanParam($_POST, 'password', '');
	$redirect = w2PgetCleanParam($_POST, 'redirect', '');
	$AppUI->setUserLocale();
	@include_once (W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php');
	include_once W2P_BASE_DIR . '/locales/core.php';
	$ok = $AppUI->login($username, $password);
	if (!$ok) {
		$AppUI->setMsg('Login Failed', UI_MSG_ERROR);
	} else {
		//Register login in user_acces_log
		$AppUI->registerLogin();
	}
	addHistory('login', $AppUI->user_id, 'login', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
	$AppUI->redirect('' . $redirect);
}

// set the default ui style
$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');

// clear out main url parameters
$m = '';
$a = '';
$u = '';

// check if we are logged in
if ($AppUI->doLogin()) {
	// load basic locale settings
	$AppUI->setUserLocale();
	@include_once ('./locales/' . $AppUI->user_locale . '/locales.php');
	include_once ('./locales/core.php');
	setlocale(LC_TIME, $AppUI->user_lang);
	$redirect = $_SERVER['QUERY_STRING'] ? strip_tags($_SERVER['QUERY_STRING']) : '';
	if (strpos($redirect, 'logout') !== false) {
		$redirect = '';
	}

	if (isset($locale_char_set)) {
		header('Content-type: text/html;charset=' . $locale_char_set);
	}

	require W2P_BASE_DIR . '/style/' . $uistyle . '/login.php';
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}
$AppUI->setUserLocale();

// bring in the rest of the support and localisation files
$perms = &$AppUI->acl();

$def_a = 'index';
if (!isset($_GET['m']) && !empty($w2Pconfig['default_view_m'])) {
	if (!$perms->checkModule($w2Pconfig['default_view_m'], 'view', $AppUI->user_id)) {
		$m = 'public';
		$def_a = 'welcome';
	} else {
		$m = $w2Pconfig['default_view_m'];
		$def_a = !empty($w2Pconfig['default_view_a']) ? $w2Pconfig['default_view_a'] : $def_a;
		$tab = $w2Pconfig['default_view_tab'];
	}
} else {
	// set the module from the url
	$m = $AppUI->checkFileName(w2PgetCleanParam($_GET, 'm', getReadableModule()));
}
// set the action from the url
$a = $AppUI->checkFileName(w2PgetCleanParam($_GET, 'a', $def_a));
if ($m == 'projects' && $a == 'view' && $w2Pconfig['projectdesigner_view_project'] && !w2PgetParam($_GET, 'bypass') && !(isset($_GET['tab']))) {
	if ($AppUI->isActiveModule('projectdesigner')) {
		$m = 'projectdesigner';
		$a = 'index';
	}
}

/* This check for $u implies that a file located in a subdirectory of higher depth than 1
* in relation to the module base can't be executed. So it would'nt be possible to
* run for example the file module/directory1/directory2/file.php
* Also it won't be possible to run modules/module/abc.zyz.class.php for that dots are
* not allowed in the request parameters.
*/

$u = $AppUI->checkFileName(w2PgetCleanParam($_GET, 'u', ''));

// load module based locale settings
@include_once W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php';
include_once W2P_BASE_DIR . '/locales/core.php';

setlocale(LC_TIME, $AppUI->user_lang);
$m_config = w2PgetConfig($m);

// TODO: canRead/Edit assignements should be moved into each file

// check overall module permissions
// these can be further modified by the included action files
$canAccess = $perms->checkModule($m, 'access');
$canRead = $perms->checkModule($m, 'view');
$canEdit = $perms->checkModule($m, 'edit');
$canAuthor = $perms->checkModule($m, 'add');
$canDelete = $perms->checkModule($m, 'delete');

if (!$suppressHeaders) {
	// output the character set header
	if (isset($locale_char_set)) {
		header('Content-type: text/html;charset=' . $locale_char_set);
	}
}

// include the module class file - we use file_exists instead of @ so
// that any parse errors in the file are reported, rather than errors
// further down the track.
$modclass = $AppUI->getModuleClass($m);
if (file_exists($modclass)) {
	include_once ($modclass);
}
if ($u && file_exists(W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.class.php')) {
	include_once W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.class.php';
}

// include the module ajax file - we use file_exists instead of @ so
// that any parse errors in the file are reported, rather than errors
// further down the track.
$modajax = $AppUI->getModuleAjax($m);
if (file_exists($modajax)) {
	include_once ($modajax);
}
if ($u && file_exists(W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.ajax.php')) {
	include_once W2P_BASE_DIR . '/modules/' . $m . '/' . $u . '/' . $u . '.ajax.php';
}

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset($_POST['dosql'])) {
	require W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $AppUI->checkFileName($_POST['dosql']) . '.php';
}

// start output proper
include W2P_BASE_DIR . '/style/' . $uistyle . '/overrides.php';
if (isset($_POST['dosql']) && $_POST['dosql'] == 'do_file_co') {
	ob_start();
} else {
	if(!ob_start('ob_gzhandler')) { 
		ob_start();
	}
}

if (!$suppressHeaders) {
	require W2P_BASE_DIR . '/style/' . $uistyle . '/header.php';
}

if (W2P_PERFORMANCE_DEBUG) {
	$w2p_performance_setuptime = (array_sum(explode(' ', microtime())) - $w2p_performance_time);
}

//Set up extra tabs
if (!isset($_SESSION['all_tabs'][$m])) {
	// For some reason on some systems if you don't set this up
	// first you get recursive pointers to the all_tabs array, creating
	// phantom tabs.
	if (!isset($_SESSION['all_tabs'])) {
		$_SESSION['all_tabs'] = array();
	}
	$_SESSION['all_tabs'][$m] = array();
	$all_tabs = &$_SESSION['all_tabs'][$m];
	foreach ($AppUI->getActiveModules() as $dir => $module) {
		if (!$perms->checkModule($dir, 'access')) {
			continue;
		}
		$modules_tabs = $AppUI->readFiles(W2P_BASE_DIR . '/modules/' . $dir . '/', '^' . $m . '_tab.*\.php');
		foreach ($modules_tabs as $tab) {
			// Get the name as the subextension
			// cut the module_tab. and the .php parts of the filename
			// (begining and end)
			$nameparts = explode('.', $tab);
			$filename = substr($tab, 0, -4);
			if (count($nameparts) > 3) {
				$file = $nameparts[1];
				if (!isset($all_tabs[$file])) {
					$all_tabs[$file] = array();
				}
				$arr = &$all_tabs[$file];
				$name = $nameparts[2];
			} else {
				$arr = &$all_tabs;
				$name = $nameparts[1];
			}
			$arr[] = array('name' => ucfirst(str_replace('_', ' ', $name)), 'file' => W2P_BASE_DIR . '/modules/' . $dir . '/' . $filename, 'module' => $dir);

			/*
			** Don't forget to unset $arr again! $arr is likely to be used in the sequel declaring
			** any temporary array. This may lead to strange bugs with disappearing tabs (cf. #1767).
			** @author: gregorerhardt 	@date: 20070203
			*/
			unset($arr);
		}
	}
} else {
	$all_tabs = &$_SESSION['all_tabs'][$m];
}

//Set up extra crumbs
if (!isset($_SESSION['all_crumbs'][$m])) {
	// For some reason on some systems if you don't set this up
	// first you get recursive pointers to the all_crumbs array, creating
	// phantom crumbs.
	if (!isset($_SESSION['all_crumbs'])) {
		$_SESSION['all_crumbs'] = array();
	}
	$_SESSION['all_crumbs'][$m] = array();
	$all_crumbs = &$_SESSION['all_crumbs'][$m];
	foreach ($AppUI->getActiveModules() as $dir => $module) {
		if (!$perms->checkModule($dir, 'access')) {
			continue;
		}
		$modules_crumbs = $AppUI->readFiles(W2P_BASE_DIR . '/modules/' . $dir . '/', '^' . $m . '_crumb.*\.php');
		foreach ($modules_crumbs as $tab) {
			// Get the name as the subextension
			// cut the module_tab. and the .php parts of the filename
			// (begining and end)
			$nameparts = explode('.', $tab);
			$filename = substr($tab, 0, -4);
			if (count($nameparts) > 3) {
				$file = $nameparts[1];
				if (!isset($all_crumbs[$file])) {
					$all_crumbs[$file] = array();
				}
				$arr = &$all_crumbs[$file];
				$name = $nameparts[2];
			} else {
				$arr = &$all_crumbs;
				$name = $nameparts[1];
			}
			$arr[] = array('name' => ucfirst(str_replace('_', ' ', $name)), 'file' => W2P_BASE_DIR . '/modules/' . $dir . '/' . $filename, 'module' => $dir);

			unset($arr);
		}
	}
} else {
	$all_crumbs = &$_SESSION['all_crumbs'][$m];
}

$module_file = W2P_BASE_DIR . '/modules/' . $m . '/' . ($u ? ($u . '/') : '') . $a . '.php';
if (file_exists($module_file)) {
	require $module_file;
} else {
	// TODO: make this part of the public module?
	// TODO: internationalise the string.
	$titleBlock = new CTitleBlock('Warning', 'log-error.gif');
	$titleBlock->show();

	if (function_exists('styleRenderBoxTop')) {
		echo styleRenderBoxTop();
	}
	echo '<table width="100%" cellspacing="0" cellpadding="3" border="0" class="std">';
	echo '<tr>';
	echo '	<td>';
	echo $AppUI->_('Missing file. Possible Module "' . $m . '" missing!');
	echo '	</td>';
	echo '</tr>';
	echo '</table>';
}
if (!$suppressHeaders) {
	echo '<iframe name="thread" src="' . W2P_BASE_URL . '/modules/index.html" width="0" height="0" frameborder="0"></iframe>';
	echo '<iframe name="thread2" src="' . W2P_BASE_URL . '/modules/index.html" width="0" height="0" frameborder="0"></iframe>';
	require W2P_BASE_DIR . '/style/' . $uistyle . '/footer.php';
	if (W2P_PERFORMANCE_DEBUG) {
		$db_info = $db->ServerInfo();
		print ('<table width="100%" cellspacing="0" cellpadding="4" border="0">');
		print ('<tr valign="top">');
		print ('<td align="center" width="100%">');
		print ('	<table width="100%" cellspacing="0" cellpadding="4" border="0" class="std">');
		print ('	<tr valign="top">');
		print ('		<th width="100%">System Environment</th>');
		print ('	</tr>');
		print ('	<tr valign="top">');
		print ('	<td width="100%">');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000"><b>web2Project ' . $AppUI->getVersion() . '</b></p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">PHP version nr: ' . phpversion() . '</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">DB provider and version nr: ' . $db->dataProvider . ' ' . $db_info['version']. ' (' . $db_info['description'] . ')</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">DB Table Prefix: "' . w2PgetConfig('dbprefix') . '"</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Web Server: ' . safe_get_env('SERVER_SOFTWARE') . '</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Server Protocol | Gateway Interface: ' . safe_get_env('SERVER_PROTOCOL') . ' | ' . safe_get_env('GATEWAY_INTERFACE') . '</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Client Browser: ' . safe_get_env('HTTP_USER_AGENT') . '</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">URL Query: ' . safe_get_env('QUERY_STRING') . '</p>');
    if (file_exists($module_file)) {
  		$script_handle = fopen($module_file, "r");
  		if ($script_handle) {
  			$script_first_line = fgets($script_handle, 4096);
  			fclose($script_handle);
  		}
  		$script_first_line = substr(trim($script_first_line), 10, -4);
  		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">File Version ' . $script_first_line . '</p>');
    }
		$right_now_is = new CDate();
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Server Time | Timezone: ' . $right_now_is->format(FMT_DATERFC822) . ' | ' . date('T') . '</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">PHP Max. Execution Time: ' . ini_get('max_execution_time') . ' seconds</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Memory Limit: ' . (ini_get('memory_limit') ? str_replace('M', ' Mb', ini_get('memory_limit')) : 'Not Defined') . '</p>');
		print ('	</td>');
		print ('	</tr>');
		print ('	<tr valign="top">');
		print ('		<th width="100%">Performance</th>');
		print ('	</tr>');
		print ('	<tr valign="top">');
		print ('	<td width="100%">');
		if (function_exists('memory_get_usage')) {
			print ('	<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Memory Used: ' . sprintf('%01.2f Mb', memory_get_usage() / pow(1024, 2)) . '</p>');
			print ('	<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Memory Unused: ' . sprintf('%01d Kb', (memory_get_usage() - $w2p_performance_memory_marker) / 1024) . '</p>');
		}
		if (function_exists('memory_get_peak_usage')) {
			print ('	<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Memory Peak: ' . sprintf('%01d Kb', (memory_get_peak_usage() - $w2p_performance_memory_marker) / 1024) . '</p>');
		}
		printf('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">Setup in %.3f seconds</p>', $w2p_performance_setuptime);
		printf('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">ACLs checked in %.3f seconds</p>', $w2p_performance_acltime);
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">ACLs nr of checks: ' . $w2p_performance_aclchecks . '</p>');
		printf('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">w2P Data checked in %.3f seconds</p>', $w2p_performance_dbtime);
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">w2P DBQueries executed: ' . $w2p_performance_dbqueries . ' queries</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">w2P Old Queries executed: ' . $w2p_performance_old_dbqueries . ' queries</p>');
		print ('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000">w2P Total Queries executed: ' . (int)($w2p_performance_old_dbqueries + $w2p_performance_dbqueries) . ' queries</p>');
		printf('		<p style="margin: 0px;font-size: 7pt; text-align: center; color: #000000"><b>Page generated in %.3f seconds</b></p>', (array_sum(explode(' ', microtime())) - $w2p_performance_time));
		print ('	</td>');
		print ('	</tr>');
		print ('	</table>');
		print ('</td>');
		print ('</tr>');
		print ('</table>');
	}
	echo '
		<!--AJAX loading messagebox -->
		<div id="loadingMessage" style="alpha(opacity=100);opacity:1;position: fixed; left: 50%; top: 0;display: none;">
		<table width="80" cellpadding="3" cellspacing="3" border="0">
		<tr>
			<td>
				<b>' . $AppUI->_('Loading') . '</b>
			</td>
			<td>';
	echo w2PshowImage('progress.gif', '10', '10', 'spinner', 'Loading...');
	echo '
			</td>
		</tr>
		</table>
		</div>
		<!--End AJAX loading messagebox -->';
}
ob_end_flush();
?>