<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

// retrieve any state parameters
if (isset($_REQUEST['project_id'])) {
	$AppUI->setState('FileIdxProject', w2PgetParam($_REQUEST, 'project_id', null));
}

$project_id = $AppUI->getState('FileIdxProject', 0);

$AppUI->setState('FileIdxTab', w2PgetParam($_GET, 'tab'));
$tab = $AppUI->getState('FileIdxTab', 0);
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
$folder = w2PgetParam($_GET, 'folder', 0); // to pass to "new file" button

require_once ($AppUI->getModuleClass('projects'));

// get the list of visible companies
$extra = array('from' => 'files', 'where' => 'projects.project_id = file_project', 'join' => 'project_departments', 'on' => 'projects.project_id = project_departments.project_id');

//get "Allowed" projects for filter list ("All" is always allowed when basing permission on projects)
$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'file_project');
$projects = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_RAW)), $projects);

// get SQL for allowed projects/tasks
$task = new CTask();
$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'file_task');

// setup the title block
$titleBlock = new CTitleBlock('Files', 'folder5.png', $m, "$m.$a");
$titleBlock->addCell($AppUI->_('Filter') . ':');
$titleBlock->addCell(arraySelect($projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id), '', '<form name="pickProject" action="?m=files" method="post">', '</form>');

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
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new file') . '">', '', '<form action="?m=files&a=addedit&folder=' . $folder . '" method="post">', '</form>');
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new folder') . '">', '', '<form action="?m=files&a=addedit_folder" method="post">', '</form>');
}
$titleBlock->show();

$file_types = w2PgetSysVal('FileType');

$fts = $file_types;

if ($tab != -1) {
	array_unshift($file_types, 'All Files');
}

$tabBox = new CTabBox('?m=files', W2P_BASE_DIR . '/modules/files/', $tab);
$tabbed = $tabBox->isTabbed();
$i = 0;
foreach ($file_types as $file_type) {
	$q = new DBQuery;
	$q->addQuery('count(file_id)');
	$q->addTable('files', 'f');
	$q->addJoin('projects', 'p', 'p.project_id = file_project');
	$q->addJoin('project_departments', 'pd', 'p.project_id = pd.project_id');
	$q->addJoin('departments', '', 'pd.department_id = dept_id');
	$q->addJoin('tasks', 't', 't.task_id = file_task');
	if (count($allowedProjects)) {
		$q->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
	}
	if (count($allowedTasks)) {
		$q->addWhere('( ( ' . implode(' AND ', $allowedTasks) . ') OR file_task = 0 )');
	}
	if (isset($catsql)) {
		$q->addWhere($catsql);
	}
	//if (isset($task_type) && (int) $task_type > 0) {
	if (isset($company_id) && (int) $company_id > 0) {
		$q->addWhere('project_company = ' . (int)$company_id);
	}
	if (isset($project_id) && (int) $project_id > 0) {
		$q->addWhere('file_project = ' . (int)$project_id);
	}
	if (isset($task_id) && (int) $task_id > 0) {
		$q->addWhere('file_task = ' . (int)$task_id);
	}
	$key = array_search($file_type, $fts);
	if ($i > 0 || !$tabbed) {
		$q->addWhere('file_category = ' . (int)$key);
	}
	$tabBox->add('index_table', $file_type . ' (' . $q->loadResult() . ')');
	++$i;
}
$tabBox->add('folders_table', 'Folder Explorer');
$tabBox->show();