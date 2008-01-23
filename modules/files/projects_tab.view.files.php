<?php /* PROJECTS $Id$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;
require_once ($AppUI->getModuleClass('files'));

$cfObj = new CFileFolder();
global $allowed_folders_ary;
$allowed_folders_ary = $cfObj->getAllowedRecords($AppUI->user_id);
global $denied_folders_ary;
$denied_folders_ary = $cfObj->getDeniedRecords($AppUI->user_id);

if (count($allowed_folders_ary) < $cfObj->countFolders()) {
	$limited = true;
}
if (!$limited) {
	$canEdit = true;
} elseif ($limited and array_key_exists($folder, $allowed_folders_ary)) {
	$canEdit = true;
} else {
	$canEdit = false;
}
$showProject = false;
require (W2P_BASE_DIR . '/modules/files/folders_table.php');
?>