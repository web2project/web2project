<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
global $AppUI, $company_id, $project_id, $task_id;
global $currentTabId, $m, $showProject, $xpg_min, $xpg_pagesize, $page;

$tab = ($m == 'files') ? $currentTabId-1 : $currentTabId;
$page = w2PgetParam($_GET, 'page', 1);
if (!isset($project_id)) {
    $project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}

$category_id = 0;
if (($company_id || $project_id || $task_id) && !($m == 'files')) {
    $category_id = 0;
} else {
    // TODO: the filtering is not working as expected in the flat view
    $category_id = ($tab < 0) ? 0 : $tab + 1;
}

$items = CFile::getFileList($AppUI, $company_id, $project_id, $task_id, $tab);

$module = new w2p_System_Module();
$fields = $module->loadSettings('files', 'index_list');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('file_name', 'file_description',
        'file_version', 'file_category', 'file_folder', 'file_project', 'file_task',
        'file_owner', 'file_datetime');
    $fieldNames = array('File Name', 'Description', 'Version', 'Category',
        'Folder', 'Project Name', 'Task Name', 'Owner', 'Date',);

    //$module->storeSettings('files', 'index_list', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

$tab++;         // ugly hack.. without this, tab is sometimes -1 which flips the display into flat mode
$page = (int) w2PgetParam($_GET, 'page', 1);
$paginator = new \Web2project\Utilities\Paginator($items);
$items = $paginator->getItemsOnPage($page);

$fileTypes = w2PgetSysVal('FileType');
$customLookups = array('file_category' => $fileTypes);

$listTable = new w2p_Output_HTML_ProjectListTable($AppUI);
$listTable->setProjectIdName('file_project');
echo $paginator->buildNavigation($AppUI, $m, $tab);
echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();
echo $paginator->buildNavigation($AppUI, $m, $tab);