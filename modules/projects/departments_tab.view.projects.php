<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

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

$module = new w2p_Core_Module();
$fields = $module->loadSettings('projects', 'department_view');

if (0 == count($fields)) {
    $fieldList = array('project_color_identifier', 'project_priority',
        'project_name', 'company_name', 'project_start_date', 'project_duration',
        'project_end_date', 'project_actual_end_date', 'task_log_problem',
        'user_username', 'project_task_count', 'project_status');
    $fieldNames = array('Color', 'P', 'Project Name', 'Company', 'Start',
        'Duration', 'End', 'Actual', 'LP', 'Owner', 'Tasks', 'Status');
    $fields = array_combine($fieldList, $fieldNames);
}

$pstatus = w2PgetSysVal('ProjectStatus');
$customLookups = array('project_status' => $pstatus);

$listHelper = new w2p_Output_ListTable($AppUI);

echo $listHelper->startTable();
echo $listHelper->buildHeader($fields, true, 'departments&a=view&dept_id=' . $dept_id);

if (count($projects)) {
    foreach ($projects as $row) {
        $listHelper->stageRowData($row);

        $s = '<tr>';
        $s .= $listHelper->createCell('project_color_identifier', $row['project_color_identifier']);
        $s .= $listHelper->createCell('project_priority', $row['project_priority']);
        $s .= $listHelper->createCell('project_name', $row['project_name']);
        $s .= $listHelper->createCell('project_company', $row['project_company']);
        $s .= $listHelper->createCell('project_start_date', $row['project_start_date']);
        $s .= $listHelper->createCell('project_scheduled_hours', $row['project_scheduled_hours']);
        $s .= $listHelper->createCell('project_end_date', $row['project_end_date']);
        $s .= $listHelper->createCell('project_end_actual', $row['project_actual_end_date']);
        $s .= $listHelper->createCell('task_log_problem', $row['task_log_problem']);
        $s .= $listHelper->createCell('project_owner', $row['project_owner']);
        $s .= $listHelper->createCell('project_task_count', $row['project_task_count']);
        $s .= $listHelper->createCell('project_status', $row['project_status'], $customLookups);
        $s .= '</tr>';
        echo $s;
    }
} else {
    echo $listHelper->buildEmptyRow();
}
echo $listHelper->endTable();