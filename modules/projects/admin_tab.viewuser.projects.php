<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $a, $addPwT, $AppUI, $buffer, $company_id, $department, $min_view, $m, $priority, $projects, $tab, $user_id, $orderdir, $orderby;

if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('UsrProjIdxOrderDir') ? ($AppUI->getState('UsrProjIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('UsrProjIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('UsrProjIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('UsrProjIdxOrderBy') ? $AppUI->getState('UsrProjIdxOrderBy') : 'project_end_date';
$orderdir = $AppUI->getState('UsrProjIdxOrderDir') ? $AppUI->getState('UsrProjIdxOrderDir') : 'asc';

$project = new CProject();
$projects = projects_list_data($user_id);
$items = $projects;

$module = new w2p_System_Module();
$fields = $module->loadSettings('projects', 'admin_view');

if (0 == count($fields)) {
    $fieldList = array('project_color_identifier', 'project_priority',
        'project_name', 'company_name', 'project_start_date', 'project_duration',
        'project_end_date', 'project_end_actual', 'task_log_problem',
        'project_owner', 'project_task_count', 'project_status');
    $fieldNames = array('Color', 'P', 'Project Name', 'Company', 'Start',
        'Duration', 'End', 'Actual', 'LP', 'Owner', 'Tasks', 'Status');

    $module->storeSettings('projects', 'admin_view', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

$pstatus = w2PgetSysVal('ProjectStatus');
$customLookups = array('project_status' => $pstatus);

$listHelper = new w2p_Output_ListTable($AppUI);

echo $listHelper->startTable();
echo $listHelper->buildHeader($fields, true, 'admin&a=view&user_id=' . $user_id);
echo $listHelper->buildRows($items, $customLookups);
echo $listHelper->endTable();