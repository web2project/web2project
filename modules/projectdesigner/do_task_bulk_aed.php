<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;
$project_id = w2PgetParam($_POST, 'project_id', 0);
$selected = w2PgetParam($_POST, 'bulk_selected_task', 0);
$bulk_task_project = w2PgetParam($_POST, 'bulk_task_project', '');
$bulk_task_parent = w2PgetParam($_POST, 'bulk_task_parent', '');
$bulk_task_dependency = w2PgetParam($_POST, 'bulk_task_dependency', '');
$bulk_task_priority = w2PgetParam($_POST, 'bulk_task_priority', '');
$bulk_task_access = w2PgetParam($_POST, 'bulk_task_access', '');
$bulk_task_assign = w2PgetParam($_POST, 'bulk_task_assign', '');
$bulk_task_hperc_assign = w2PgetParam($_POST, 'bulk_task_hperc_assign', '');
$bulk_task_assign_perc = w2PgetParam( $_POST, 'bulk_task_assign_perc', '' );
$bulk_task_unassign = w2PgetParam($_POST, 'bulk_task_unassign', '');
$bulk_task_other = w2PgetParam($_POST, 'bulk_task_other', '');
$bulk_task_owner = w2PgetParam($_POST, 'bulk_task_owner', '');
$bulk_task_type = w2PgetParam($_POST, 'bulk_task_type', '');
$bulk_task_duration = w2PgetParam($_POST, 'bulk_task_duration', '');
$bulk_task_durntype = w2PgetParam($_POST, 'bulk_task_durntype', '');
$bulk_task_start_date = w2PgetParam($_POST, 'add_task_bulk_start_date', '');
if ($bulk_task_start_date) {
	$start_date = new CDate($bulk_task_start_date);
	$bulk_start_date = $start_date->format(FMT_DATETIME_MYSQL);
}
$bulk_task_end_date = w2PgetParam($_POST, 'add_task_bulk_end_date', '');
if ($bulk_task_end_date) {
	$end_date = new CDate($bulk_task_end_date);
	$bulk_end_date = $end_date->format(FMT_DATETIME_MYSQL);
}
$bulk_move_date = (int) w2PgetParam($_POST, 'bulk_move_date', '0');
$bulk_task_percent_complete = w2PgetParam($_POST, 'bulk_task_percent_complete', '');

$perms = &$AppUI->acl();
if (!$perms->checkModule('tasks', 'edit')) {
	$AppUI->redirect('m=public&a=access_denied');
}

//Lets store the panels view options of the user:
$pdo = new CProjectDesignerOptions();
$pdo->pd_option_user = $AppUI->user_id;
$pdo->pd_option_view_project = w2PgetParam($_POST, 'opt_view_project', 0);
$pdo->pd_option_view_gantt = w2PgetParam($_POST, 'opt_view_gantt', 0);
$pdo->pd_option_view_tasks = w2PgetParam($_POST, 'opt_view_tasks', 0);
$pdo->pd_option_view_actions = w2PgetParam($_POST, 'opt_view_actions', 0);
$pdo->pd_option_view_addtasks = w2PgetParam($_POST, 'opt_view_addtsks', 0);
$pdo->pd_option_view_files = w2PgetParam($_POST, 'opt_view_files', 0);
$pdo->store();

if (is_array($selected) && count($selected)) {
	$upd_task = new CTask();
	foreach ($selected as $key => $val) {
		if ($key) {
			$upd_task->load($key);
		}

		//Action: Modify Percent Complete
		if ($bulk_task_percent_complete != '' && ((int) $_POST['bulk_task_percent_complete'] == (int) $bulk_task_percent_complete)) {
			if ($upd_task->task_id) {
				$upd_task->task_percent_complete = $bulk_task_percent_complete;
				$upd_task->store($AppUI);
			}
		}

		//Action: Move Task Date
		if (isset($_POST['bulk_move_date']) && $bulk_move_date != '' && $bulk_move_date) {
			if ($upd_task->task_id && ((int) $upd_task->task_dynamic != 1 && !$upd_task->getDependencies($upd_task->task_id))) {
				$offSet = $bulk_move_date;
				$start_date = new CDate($upd_task->task_start_date);
				$start_date->addDays($offSet);
				$upd_task->task_start_date = $start_date->format(FMT_DATETIME_MYSQL);
				$end_date = new CDate($upd_task->task_end_date);
				$end_date->addDays($offSet);
				$upd_task->task_end_date = $end_date->format(FMT_DATETIME_MYSQL);
				$upd_task->store($AppUI);
				$upd_task->shiftDependentTasks();
			}
		}

		//Action: Modify Start Date
		if (isset($_POST['add_task_bulk_start_date']) && $bulk_task_start_date != '' && $bulk_start_date) {
			if ($upd_task->task_id) {
				$upd_task->task_start_date = $bulk_start_date;
				$upd_task->store($AppUI);
			}
		}

		//Action: Modify End Date
		if (isset($_POST['add_task_bulk_end_date']) && $bulk_task_end_date != '' && $bulk_end_date) {
			if ($upd_task->task_id) {
				$upd_task->task_end_date = $bulk_end_date;
				$upd_task->store($AppUI);
				$upd_task->pushDependencies($upd_task->task_id, $upd_task->task_end_date);
			}
		}

		//Action: Modify Duration
		if (isset($_POST['bulk_task_duration']) && $bulk_task_duration != '' && is_numeric($bulk_task_duration)) {
			if ($upd_task->task_id) {
				$upd_task->task_duration = $bulk_task_duration;
				//set duration type to hours (1)
				$upd_task->task_duration_type = $bulk_task_durntype ? $bulk_task_durntype : 1;
				$upd_task->store($AppUI);
			}
		}

		//Action: Modify Task Owner
		if (isset($_POST['bulk_task_owner']) && $bulk_task_owner != '' && $bulk_task_owner) {
			if ($upd_task->task_id) {
				$upd_task->task_owner = $bulk_task_owner;
				$upd_task->store($AppUI);
			}
		}

		//Action: Move to Project
		if (isset($_POST['bulk_task_project']) && $bulk_task_project != '' && $bulk_task_project) {
			if ($upd_task->task_id) {
				$upd_task->task_project = $bulk_task_project;
				//Set parent to self task
				$upd_task->task_parent = $key;
				$upd_task->store($AppUI);
			}
		}

		//Action: Change parent
		if (isset($_POST['bulk_task_parent']) && $bulk_task_parent != '') {
			if ($upd_task->task_id) {
				//If parent is self task
				if ($bulk_task_parent == '0') {
					$upd_task->task_parent = $key;
					$upd_task->store($AppUI);
					//if not, then the task will be child to the selected parent
				} else {
					$upd_task->task_parent = $bulk_task_parent;
					$upd_task->store($AppUI);
				}
			}
		}

		//Action: Change dependency
		if (isset($_POST['bulk_task_dependency']) && $bulk_task_dependency != '') {
			if ($upd_task->task_id) {
				//If parent is self task
				//print_r($bulk_task_dependency);die;
				if ($bulk_task_dependency == '0') {
					$upd_task->task_dynamic = 0;
					$upd_task->store($AppUI);
					$q = new DBQuery;
					$q->setDelete('task_dependencies');
					$q->addWhere('dependencies_task_id=' . $upd_task->task_id);
					$q->exec();
				} elseif (!($bulk_task_dependency == $upd_task->task_id)) {
					$upd_task->task_dynamic = 31;
					$upd_task->store($AppUI);
					$q = new DBQuery;
					$q->addTable('task_dependencies');
					$q->addReplace('dependencies_task_id', $upd_task->task_id);
					$q->addReplace('dependencies_req_task_id', $bulk_task_dependency);
					$q->exec();
					//Lets recalc the dependency
					$dep_task = new CTask();
					$dep_task->load($bulk_task_dependency);
					if ($dep_task->task_id) {
						$dep_task->shiftDependentTasks();
					}
				}
			}
		}

		//Action: Modify priority
		if (isset($_POST['bulk_task_priority']) && $bulk_task_priority != '') {
			if ($upd_task->task_id) {
				$upd_task->task_priority = $bulk_task_priority;
				$upd_task->store($AppUI);
			}
		}

		//Action: Modify Access
		if (isset($_POST['bulk_task_access']) && $bulk_task_access != '') {
			if ($upd_task->task_id) {
				$upd_task->task_access = $bulk_task_access;
				$upd_task->store($AppUI);
			}
		}

		//Action: Modify Type
		if (isset($_POST['bulk_task_type']) && $bulk_task_type != '') {
			if ($upd_task->task_id) {
				$upd_task->task_type = $bulk_task_type;
				$upd_task->store($AppUI);
			}
		}

		//Action: Assign User
		if (isset($_POST['bulk_task_hperc_assign']) && $bulk_task_hperc_assign != '') {
			//format hperc_assign user_id=percentage_assignment;user_id=percentage_assignment;user_id=percentage_assignment;
			$bulk_task_assign = ',';
			$tmp_ar = explode(';', $bulk_task_hperc_assign);
			$hperc_assign_ar = array();
			for ($i = 0, $i_cmp = sizeof($tmp_ar); $i < $i_cmp; $i++) {
				$tmp = explode('=', $tmp_ar[$i]);
				if (count($tmp) > 1) {
					$hperc_assign_ar[$tmp[0]] = $tmp[1];
					$bulk_task_assign .= $tmp[0] . ',';
				} else {
					$hperc_assign_ar[$tmp[0]] = 100;
					$bulk_task_assign .= $tmp[0] . ',';
				}
			}
			$upd_task = new CTask();
			$upd_task->load($key);
			if ($upd_task->task_id) {
				$upd_task->updateAssigned($bulk_task_assign, $hperc_assign_ar, false, false);
			}
			//$upd_task->updateAssigned($bulk_task_assign,array($bulk_task_assign=>$bulk_task_assign_perc),false,false);
			if ($upd_task->task_project && $upd_task->task_id && $upd_task->task_notify) {
				$upd_task->notify();
			}
		}

		//Action: Unassign User
		if (isset($_POST['bulk_task_unassign']) && $bulk_task_unassign != '') {
			$upd_task = new CTask();
			$upd_task->load($key);
			if ($upd_task->task_id) {
				$upd_task->removeAssigned($bulk_task_unassign);
			}
		}

		//Action: Other Actions
		if (isset($_POST['bulk_task_other']) && $bulk_task_other != '') {

			if ($upd_task->task_id) {
				//Option 1 - Mark as finished
				if ($bulk_task_other == '1') {
					$upd_task->task_percent_complete = 100;
					if (!$upd_task->task_end_date || $upd_task->task_end_date == '0000-00-00 00:00:00') {
						$end_date = null;
						$end_date = new CDate();
						$upd_task->task_end_date = $end_date->format(FMT_DATETIME_MYSQL);
					}
					$upd_task->store($AppUI);
					//Option 2 - Mark as milestone
				} elseif ($bulk_task_other == '2') {
					$upd_task->task_milestone = 1;
					$upd_task->store($AppUI);
					//Option 3 - Mark as non milestone
				} elseif ($bulk_task_other == '3') {
					$upd_task->task_milestone = 0;
					$upd_task->store($AppUI);
					//Option 4 - Mark as dynamic
				} elseif ($bulk_task_other == '4') {
					$upd_task->task_dynamic = 1;
					$upd_task->store($AppUI);
					//Option 5 - Mark as non dynamic
				} elseif ($bulk_task_other == '5') {
					$upd_task->task_dynamic = 0;
					$upd_task->store($AppUI);
					//Option 6 - Add Task Reminder
				} elseif ($bulk_task_other == '6') {
					$upd_task->addReminder();
					//Option 7 - Mark as non dynamic
				} elseif ($bulk_task_other == '7') {
					$upd_task->clearReminder(true);
					//Option 8 - Mark as active
				} elseif ($bulk_task_other == '8') {
					$upd_task->task_status = '0';
					$upd_task->store($AppUI);
					//Option 10 - Empty tasks description
				} elseif ($bulk_task_other == '10') {
					$upd_task->task_description = '';
					$upd_task->store($AppUI);
					//Option 99 (always at the bottom) - Delete
				} elseif ($bulk_task_other == '99') {
					$upd_task->delete($AppUI);
				}
			}
		}
		echo db_error();
	}
}
$AppUI->redirect('m=projectdesigner&project_id=' . $project_id);