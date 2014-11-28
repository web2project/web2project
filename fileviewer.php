<?php

require_once 'bootstrap.php';

$loginFromPage = 'fileviewer.php';

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

switch($_REQUEST['action']) {
    case 'login':
        $username = w2PgetParam($_POST, 'username', '');
        $password = w2PgetParam($_POST, 'password', '');
        $redirect = w2PgetParam($_POST, 'redirect', '');

        $AppUI->login($username, $password);
        $AppUI->redirect('fileviewer.php?' . $redirect);
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

$file_id = (int) w2PgetParam($_GET, 'file_id', 0);

if (!$file_id) {
    $AppUI->setMsg('fileIdError', UI_MSG_ERROR);
    $AppUI->redirect();
}

$file = new CFile;
if (!$file->load($file_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$exists = $file->getFileSystem()->exists($file->file_project, $file->file_real_filename);

if (!$exists) {
    $AppUI->setMsg('fileIdError', UI_MSG_ERROR);
    $AppUI->redirect();
}

ob_end_clean();
header('MIME-Version: 1.0');
header('Pragma: ');
header('Cache-Control: public');
header('Content-length: ' . $file->file_size);
header('Content-type: ' . $file->file_type);
header('Content-transfer-encoding: 8bit');
header('Content-disposition: attachment; filename="' . $file->file_name . '"');

$file->getFileSystem()->read($file->file_project, $file->file_real_filename);

flush();