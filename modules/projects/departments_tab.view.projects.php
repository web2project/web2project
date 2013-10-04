<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $a, $addPwOiD, $buffer, $dept_id, $department, $min_view,
	$m, $priority, $projects, $tab, $user_id, $orderdir, $orderby;

if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('DeptProjIdxOrderDir') ? ($AppUI->getState('DeptProjIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('DeptProjIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('DeptProjIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('DeptProjIdxOrderBy') ? $AppUI->getState('DeptProjIdxOrderBy') : 'project_end_date';
$orderdir = $AppUI->getState('DeptProjIdxOrderDir') ? $AppUI->getState('DeptProjIdxOrderDir') : 'asc';

/*
 *  TODO:  This is a *nasty* *nasty* kludge that should be cleaned up.
 * Unfortunately due to the global variables from dotProject, we're stuck with
 * this mess for now.
 * 
 * May God have mercy on our souls for the atrocity we're about to commit.
 */ 
$tmpDepartments = $department;
$department = $dept_id;
$project = new CProject();
$projects = projects_list_data($user_id);
$department = $tmpDepartments;
$items = $projects;

$module = new w2p_System_Module();
$fields = $module->loadSettings('projects', 'department_view');

if (0 == count($fields)) {
    $fieldList = array('project_color_identifier', 'project_priority',
        'project_name', 'company_name', 'project_start_date', 'project_duration',
        'project_end_date', 'project_end_actual', 'task_log_problem',
        'project_owner', 'project_task_count', 'project_status');
    $fieldNames = array('Color', 'P', 'Project Name', 'Company', 'Start',
        'Duration', 'End', 'Actual', 'LP', 'Owner', 'Tasks', 'Status');

    $module->storeSettings('projects', 'department_view', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

$pstatus = w2PgetSysVal('ProjectStatus');
$customLookups = array('project_status' => $pstatus);

$listHelper = new w2p_Output_ListTable($AppUI);

echo $listHelper->startTable();
echo $listHelper->buildHeader($fields, true, 'departments&a=view&dept_id=' . $dept_id);
echo $listHelper->buildRows($items, $customLookups);
echo $listHelper->endTable();