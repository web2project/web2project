<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
global $AppUI, $company_id, $project_id, $task_id, $tab;
global $currentTabId, $m, $showProject, $xpg_min, $xpg_pagesize, $page, $owner_id;

// Only the Files module's own tabs are categories; elsewhere $tab is the
// host view's tab (Files is tab 4 on a company), so show every category.
$category_id = ($m == 'files') ? $tab-1 : -1;
$page = w2PgetParam($_GET, 'page', 1);
if (!isset($project_id)) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}

// (Outside the Files module the category is already -1. Inside it, the
// category tab now still applies when a project is selected; it used to
// be switched off, so the project and category filters could not combine.)

$file = new CFile();
// the owner filter only exists on the Files module's own page
$items = $file->list(['company' => ($m == 'companies') ? (int) $company_id : 0,
    'project' => $project_id, 'task' => $task_id, 'category' => $category_id,
    'owner' => ($m == 'files') ? (int) $owner_id : 0]);

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
// Keep the current view (e.g. a=view&project_id=1) in the page links so
// paging the Files tab of a project, task or company stays on that tab.
$pageParams = w2p_Utilities_Paginator::currentQueryParams();
echo $paginator->buildNavigation($AppUI, $m, $tab, $pageParams);

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->addBefore('edit', 'file_id');
$listTable->addDividers('file_project');

echo $listTable->startTable($m);
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();
echo $paginator->buildNavigation($AppUI, $m, $tab, $pageParams);