<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// The category filter picks the matching category tab (tab 0 is All Files).
if (isset($_POST['category_id'])) {
	$AppUI->setState('FileIdxTab', (int) $_POST['category_id'] + 1);
}
$tab = $AppUI->processIntState('FileIdxTab', $_GET, 'tab', 0);

$project_id = $AppUI->processIntState('FileIdxProject', $_REQUEST, 'project_id', 0);
$owner_id = $AppUI->processIntState('FileIdxOwner', $_REQUEST, 'owner_id', 0);
$company_id = (isset($company_id)) ? $company_id : 0;
$task_id = (isset($task_id)) ? $task_id : 0;

$active = intval(!$AppUI->getState('FileIdxTab'));

$folder_id = (int) w2PgetParam($_GET, 'folder', 0); // to pass to "new file" button

// get the list of visible companies
$extra = array('from' => 'files', 'where' => 'projects.project_id = file_project', 'join' => 'project_departments', 'on' => 'projects.project_id = project_departments.project_id');

//get "Allowed" projects for filter list ("All" is always allowed when basing permission on projects)
$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$projects = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_RAW)), $projects);

// get SQL for allowed projects/tasks
$task = new CTask();
$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'file_task');

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Files', 'icon.png', $m);
$titleBlock->addFilterCell('Project', 'project_id', $projects, $project_id);

// owners of files, for the owner filter
$q = new w2p_Database_Query();
$q->addTable('files', 'f');
$q->addQuery('DISTINCT f.file_owner, c.contact_display_name');
$q->addJoin('users', 'u', 'u.user_id = f.file_owner', 'inner');
$q->addJoin('contacts', 'c', 'c.contact_id = u.user_contact', 'inner');
$q->addOrder('c.contact_display_name');
$owners = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_RAW)), $q->loadHashList());
$q->clear();
$titleBlock->addFilterCell('Owner', 'owner_id', $owners, $owner_id);

$categories = arrayMerge(array('-1' => $AppUI->_('All', UI_OUTPUT_RAW)), w2PgetSysVal('FileType'));
$titleBlock->addFilterCell('Category', 'category_id', $categories, $tab - 1);

// Folders have no permissions of their own (CFile_Folder::getAllowedRecords()
// returns every folder), so the Files module permissions apply.
$folder = new CFile_Folder();
$folder->setId($folder_id);
$allowed_folders_ary = $folder->getAllowedRecords($AppUI->user_id);
$denied_folders_ary = array();

$canEdit = canEdit('files');
if (canAdd('files')) {
    $titleBlock->addButton('new file', '?m=files&a=addedit&folder=' . $folder_id);
    $titleBlock->addButton('new folder', '?m=files&a=addedit_folder&file_folder_parent=' . $folder_id);
}
$titleBlock->show();

$file_types = w2PgetSysVal('FileType');

if ($tab != -1) {
	array_unshift($file_types, 'All Files');
}

$tabBox = new CTabBox('?m=files', W2P_BASE_DIR . '/modules/files/', $tab);
$i = -1;
foreach ($file_types as $file_type) {
	$fileList = CFile::getFileList($AppUI, $company_id, $project_id, $task_id, $i, $owner_id);
	$tabBox->add('index_table', $file_type . ' (' . count($fileList) . ')');
	++$i;
}
// The folder tree, after the category tabs so their numbers stay the same.
$tabBox->add('folders_table', 'Folders');
$tabBox->show();