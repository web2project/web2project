<?php /* $Id$ $URL$ */

/*
All files in this work are now covered by the following copyright notice.
Please note that included libraries in the lib directory may have their own license.

Copyright (c) 2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
Copyright (c) 2003-2007 The dotProject Development Team <core-developers@dotproject.net>

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

The full text of the GPL is in the COPYING file.
*/

//file viewer
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/includes/session.php';

$loginFromPage = 'fileviewer.php';

w2PsessionStart();

// check if session has previously been initialised
// if no ask for logging and do redirect
if (!isset($_SESSION['AppUI']) || isset($_GET['logout'])) {
	$_SESSION['AppUI'] = new CAppUI();
	$AppUI = &$_SESSION['AppUI'];
	$AppUI->setConfig($w2Pconfig);
	$AppUI->checkStyle();

	if ($AppUI->doLogin())
		$AppUI->loadPrefs(0);
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
	$AppUI->redirect('m=public&a=access_denied');
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
		$AppUI->redirect('m=public&a=access_denied');
	}

	$q = new w2p_Database_Query;
	$q->addTable('files');
	if ($fileclass->file_project) {
		$project->setAllowedSQL($AppUI->user_id, $q, 'file_project');
	}
	$q->addWhere('file_id = ' . $file_id);

	$file = $q->loadHash();

	if (!$file) {
		$AppUI->redirect('m=public&a=access_denied');
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
?>