<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $company_id, $deny, $canRead, $canEdit;
global $allowed_folders_ary, $denied_folders_ary;

$cfObj = new CFile_Folder();
$allowed_folders_ary = $cfObj->getAllowedRecords($AppUI->user_id);
$denied_folders_ary  = $cfObj->getDeniedRecords($AppUI->user_id);

$limited = (count($allowed_folders_ary) < $cfObj->countFolders()) ? true : false;

if (!$limited) {
	$canEdit = true;
} elseif ($limited and array_key_exists($folder, $allowed_folders_ary)) {
	$canEdit = true;
} else {
	$canEdit = false;
}
$showProject = false;

/**
 * TODO: This is a combination of ugly, ugly hacks.. by setting the
 *   $currentTabId to 100, we can effectively turn off the pagination headers
 *   on the file list.. which is good because otherwise the $tab parameter kept
 *   getting reset to zero which would create a link that goes to the files
 *   module instead of staying within the companies module.
 * Otherwise you have to make a hack to the $m like this:
 *
 * $m = 'companies&a=view&company_id=' . $company_id;
 */
$currentTabId = 100;

require (W2P_BASE_DIR . '/modules/files/index_table.php');