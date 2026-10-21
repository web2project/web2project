<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;

$cfObj = new CFile_Folder();
global $allowed_folders_ary;
$allowed_folders_ary = $cfObj->getAllowedRecords($AppUI->user_id);
global $denied_folders_ary;
$denied_folders_ary = $cfObj->getDeniedRecords($AppUI->user_id);

// Folders have no permissions of their own, so the Files module's apply.
$canEdit = canEdit('files');
$showProject = false;
require (W2P_BASE_DIR . '/modules/files/index_table.php');