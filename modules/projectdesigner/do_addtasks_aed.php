<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;

$perms = &$AppUI->acl();
if (!canAdd('tasks')) {
	$AppUI->redirect(ACCESS_DENIED);
}

//Lets store the panels view options of the user:
$pdo = new CProjectDesigner();
$pdo->bind($_POST);
$pdo->store();

//Lets store the task lines
$elements = $_POST;
$project_id = (int) w2PgetParam($_POST, 'project', 0);
$taskErrors = array();

foreach ($elements as $element => $on) {
	if ((substr($element, 0, 14) == 'add_task_line_') && ($on != '')) {

		$tline = new CTask();
//TODO: clean this whole thing up..
		$tline->task_id = 0;
		$tline->task_name = $elements['add_task_name_' . $on];
		$tline->task_project = $project_id;
		$start_date = '';
		if ($elements['add_task_start_date_' . $on]) {
			$date = new w2p_Utilities_Date($elements['add_task_start_date_' . $on]);
			$start_date = $date->format(FMT_DATETIME_MYSQL);
			$start_date = $AppUI->convertToSystemTZ($start_date);
		}
		$tline->task_start_date = $start_date;
		$end_date = '';
		if ($elements['add_task_end_date_' . $on]) {
			$date = new w2p_Utilities_Date($elements['add_task_end_date_' . $on]);
			$end_date = $date->format(FMT_DATETIME_MYSQL);
			$end_date = $AppUI->convertToSystemTZ($end_date);
		}
		$tline->task_end_date = $end_date;
		$tline->task_duration = $elements['add_task_duration_' . $on];
		$tline->task_duration_type = $elements['add_task_durntype_' . $on];
		$tline->task_priority = $elements['add_task_priority_' . $on];
		$tline->task_type = $elements['add_task_type_' . $on];
		$tline->task_access = $elements['add_task_access_' . $on];
		$tline->task_description = $elements['add_task_description_' . $on] ? $elements['add_task_description_' . $on] : '';
		$tline->task_owner = $AppUI->user_id;
        $tline->task_dynamic = 31;
		if ($elements['add_task_extra_' . $on] == '1') {
			$tline->task_milestone = '1';
		} elseif ($elements['add_task_extra_' . $on] == '2') {
			$tline->task_dynamic = '1';
		} elseif ($elements['add_task_extra_' . $on] == '3') {
			$tline->task_status = '-1';
		}

        $result = $tline->store();
        $tline->updateAssigned($AppUI->user_id, array($AppUI->user_id => 100));
        if (count($tline->getError())) {
            $taskErrors[] = $tline->getError();
        }
	}
}
if (count($taskErrors) > 0) {
    $AppUI->setMsg($result, UI_MSG_ERROR);
}
$AppUI->redirect('m=projectdesigner&project_id=' . $project_id);