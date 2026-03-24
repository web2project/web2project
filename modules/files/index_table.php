<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
global $AppUI, $company_id, $project_id, $task_id, $tab;
global $currentTabId, $m, $showProject, $xpg_min, $xpg_pagesize, $page;

$category_id = ($m == 'files') ? $tab-1 : $tab;
$page = w2PgetParam($_GET, 'page', 1);
if (!isset($project_id)) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}

if ($project_id > 0 || $task_id > 0) {
  $category_id = -1;
}

$file = new CFile();
$items = $file->list(['project' => $project_id, 'task' => $task_id, 'category' => $category_id]);

$module = new w2p_System_Module();
$fields = $module->loadSettings('files', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('file_name', 'file_description',
        'file_category', 'file_task', 'file_owner', 'file_datetime');
    $fieldNames = array('File Name', 'Description', 
        'Category', 'Task Name', 'Owner', 'Date',);

    $module->storeSettings('files', 'index_list', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}
$file_categories = w2PgetSysVal('FileType');
$customLookups = array('file_category' => $file_categories);

$paginator = new w2p_Utilities_Paginator($items);
$items = $paginator->getItemsOnPage($page);
echo $paginator->buildNavigation($AppUI, $m, $tab);

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->addBefore('edit', 'file_id');
$listTable->addDividers('file_project');

echo $listTable->startTable($m);
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();
echo $paginator->buildNavigation($AppUI, $m, $tab);