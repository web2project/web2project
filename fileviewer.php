<?php
//file viewer
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$loginFromPage = 'fileviewer.php';

w2PsessionStart();
$AppUI = is_object($AppUI) ? $AppUI : new w2p_Core_CAppUI();
// check if session has previously been initialised
// if no ask for logging and do redirect
if (!isset($_SESSION['AppUI']) || isset($_GET['logout'])) {
	$_SESSION['AppUI'] = new w2p_Core_CAppUI();
	$AppUI = &$_SESSION['AppUI'];
	$AppUI->setConfig($w2Pconfig);
	$AppUI->checkStyle();

	if ($AppUI->doLogin()) {
		$AppUI->loadPrefs(0);
    }
	// check if the user is trying to log in
	if (isset($_POST['login'])) {
		$username = w2PgetParam($_POST, 'username', '');
		$password = w2PgetParam($_POST, 'password', '');
		$redirect = w2PgetParam($_POST, 'redirect', '');
		$ok = $AppUI->login($username, $password);
		if (!$ok) {
			//display login failed message
			$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : $w2Pconfig['host_style'];
			$AppUI->setMsg('Login Failed', UI_MSG_ERROR);
			require W2P_BASE_DIR . '/style/' . $uistyle . '/login.php';
			session_unset();
			exit;
		}
		header('Location: fileviewer.php?' . $redirect);
		exit;
	}

	$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : $w2Pconfig['host_style'];
	// check if we are logged in
	if ($AppUI->doLogin()) {
		$AppUI->setUserLocale();
		@include_once (W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php');
		@include_once (W2P_BASE_DIR . '/locales/core.php');
		setlocale(LC_TIME, $AppUI->user_locale);

		$redirect = @$_SERVER['QUERY_STRING'];
		if (strpos($redirect, 'logout') !== false) {
			$redirect = '';
		}
		if (isset($locale_char_set)) {
			header('Content-type: text/html;charset=' . $locale_char_set);
		}
		require W2P_BASE_DIR . '/style/' . $uistyle . '/login.php';
		session_unset();
		session_destroy();
		exit;
	}
}
$AppUI = &$_SESSION['AppUI'];
include_once W2P_BASE_DIR . '/locales/core.php';

$perms = &$AppUI->acl();

$canRead = canView('files');
if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$file_id = (int) w2PgetParam($_GET, 'file_id', 0);

if ($file_id) {
	// projects tat are denied access
	$project = new CProject;
	$allowedProjects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id, project_name', '', null, null, 'projects');
	$fileclass = new CFile;
	$fileclass->load($file_id);
	$allowedFiles = $fileclass->getAllowedRecords($AppUI->user_id, 'file_id, file_name');

	if (count($allowedFiles) && !array_key_exists($file_id, $allowedFiles)) {
		$AppUI->redirect(ACCESS_DENIED);
	}

	$q = new w2p_Database_Query;
	$q->addTable('files');
	if ($fileclass->file_project) {
		$project->setAllowedSQL($AppUI->user_id, $q, 'file_project');
	}
	$q->addWhere('file_id = ' . $file_id);

	$file = $q->loadHash();

	if (!$file) {
		$AppUI->redirect(ACCESS_DENIED);
	}

	$fname = W2P_BASE_DIR . '/files/' . $file['file_project'] . '/' . $file['file_real_filename'];
	if (!file_exists($fname)) {
		$AppUI->setMsg('fileIdError', UI_MSG_ERROR);
		$AppUI->redirect();
	}

	ob_end_clean();
	header('MIME-Version: 1.0');
	header('Pragma: ');
	header('Cache-Control: public');
	header('Content-length: ' . $file['file_size']);
	header('Content-type: ' . $file['file_type']);
	header('Content-transfer-encoding: 8bit');
	header('Content-disposition: attachment; filename="' . $file['file_name'] . '"');

	// read and output the file in chunks to bypass limiting settings in php.ini
	$handle = fopen(W2P_BASE_DIR . '/files/' . $file['file_project'] . '/' . $file['file_real_filename'], 'rb');
	if ($handle) {
		while (!feof($handle)) {
			print fread($handle, 8192);
		}
		fclose($handle);
	}
	flush();
} else {
	$AppUI->setMsg('fileIdError', UI_MSG_ERROR);
	$AppUI->redirect();
}