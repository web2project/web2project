<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $project_id, $task_id, $showProject, $tab, $search_string, $m;

$type_filter = ($m == 'links') ? $tab-1 : -1;

if ($task_id && !$project_id) {
    $task = new CTask();
    $task->load($task_id);
    $project_id = $task->task_project;
}

$page = (int) w2PgetParam($_GET, 'page', 1);

if (!isset($project_id)) {
    $project_id = (int) w2PgetParam($_POST, 'project_id', 0);
}

$link = new CLink();
$items = $link->getProjectTaskLinksByCategory(null, $project_id, $task_id, $type_filter, $search_string);

$module = new w2p_System_Module();
$fields = $module->loadSettings('links', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('link_name', 'link_description', 'link_category', 'link_task', 'link_owner', 'link_date');
    $fieldNames = array('Link Name', 'Description', 'Category', 'Task Name', 'Owner', 'Date');

    $module->storeSettings('links', 'index_list', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

$page = (int) w2PgetParam($_GET, 'page', 1);
$paginator = new w2p_Utilities_Paginator($items);
$items = $paginator->getItemsOnPage($page);

$link_types = w2PgetSysVal('LinkType');
$customLookups = array('link_category' => $link_types);

$listTable = new w2p_Output_HTML_ProjectListTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');
$listTable->addBefore('edit', 'link_id');
$listTable->addBefore('url', 'link_url');
$listTable->setProjectIdName('link_project');

echo $paginator->buildNavigation($AppUI, $m, $tab);
echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();
echo $paginator->buildNavigation($AppUI, $m, $tab);