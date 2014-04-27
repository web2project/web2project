<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('FileIdxTab', $_GET, 'tab', 0);

$project_id = $AppUI->processIntState('FileIdxProject', $_REQUEST, 'project_id', 0);
$company_id = (isset($company_id)) ? $company_id : 0;
$task_id = (isset($task_id)) ? $task_id : 0;

$active = intval(!$AppUI->getState('FileIdxTab'));

$view_temp = w2PgetParam($_GET, 'view');
if (isset($view_temp)) {
	$view = w2PgetParam($_GET, 'view'); // folders or categories
	$AppUI->setState('FileIdxView', $view);
} else {
	$view = $AppUI->getState('FileIdxView');
	if ($view == '') {
		$view = 'folders';
	}
}
$folder = (int) w2PgetParam($_GET, 'folder', 0); // to pass to "new file" button

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
$titleBlock->addFilterCell('Filter', 'project_id', $projects, $project_id);

// override the $canEdit variable passed from the main index.php in order to check folder permissions
/** get permitted folders **/
$cfObj = new CFile_Folder();
$allowed_folders_ary = $cfObj->getAllowedRecords($AppUI->user_id);
$denied_folders_ary = $cfObj->getDeniedRecords($AppUI->user_id);

$limited = (count($allowed_folders_ary) < $cfObj->countFolders()) ? true : false;

if (!$limited) {
	$canEdit = true;
} elseif ($limited and array_key_exists($folder, $allowed_folders_ary)) {
	$canEdit = true;
} else {
	$canEdit = false;
}

if ($canEdit) {
    $titleBlock->addButton('new folder', '?m=files&a=addedit_folder');
    $titleBlock->addButton('new file', '?m=files&a=addedit&folder=' . $folder);
}
$titleBlock->show();

$file_types = w2PgetSysVal('FileType');

if ($tab != -1) {
	array_unshift($file_types, 'All Files');
}

$tabBox = new CTabBox('?m=files', W2P_BASE_DIR . '/modules/files/', $tab);
$i = -1;
foreach ($file_types as $file_type) {
	$fileList = CFile::getFileList($AppUI, $company_id, $project_id, $task_id, $i);
	$tabBox->add('index_table', $file_type . ' (' . count($fileList) . ')');
	++$i;
}
$tabBox->add('folders_table', 'Folder Explorer');
$tabBox->show();