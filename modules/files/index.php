<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

$project_id = $AppUI->processIntState('FileIdxProject', $_REQUEST, 'project_id', 0);
$tab = $AppUI->processIntState('FileIdxTab', $_GET, 'tab', 0);

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
$titleBlock = new CTitleBlock('Files', 'folder5.png', $m, "$m.$a");
$titleBlock->addCell($AppUI->_('Filter') . ':');
$titleBlock->addCell(arraySelect($projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id), '', '<form name="pickProject" action="?m=files" method="post" accept-charset="utf-8">', '</form>');

// override the $canEdit variable passed from the main index.php in order to check folder permissions
/** get permitted folders **/
$cfObj = new CFileFolder();
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
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new file') . '">', '', '<form action="?m=files&a=addedit&folder=' . $folder . '" method="post" accept-charset="utf-8">', '</form>');
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new folder') . '">', '', '<form action="?m=files&a=addedit_folder" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->show();

$file_types = w2PgetSysVal('FileType');

$fts = $file_types;

if ($tab != -1) {
	array_unshift($file_types, 'All Files');
}

$tabBox = new CTabBox('?m=files', W2P_BASE_DIR . '/modules/files/', $tab);
$tabbed = $tabBox->isTabbed();
$i = -1;
foreach ($file_types as $file_type) {
	$fileList = CFile::getFileList($AppUI, $company_id, $project_id, $task_id, $i);
	$tabBox->add('index_table', $file_type . ' (' . count($fileList) . ')');
	++$i;
}
$tabBox->add('folders_table', 'Folder Explorer');
$tabBox->show();