<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$rm = (int) w2PgetParam($_POST, 'rm', 0);
$store = (int) w2PgetParam($_POST, 'store', 0);
$chUTP = (int) w2PgetParam($_POST, 'chUTP', 0);
$percentage_assignment = (int) w2PgetParam($_POST, 'percentage_assignment', 0);
$user_task_priority = (int) w2PgetParam($_POST, 'user_task_priority', 0);
$user_id = (int) w2PgetParam($_POST, 'user_id', 0);
$task_user_assign = $_POST['task_user_assign'];

$perms = &$AppUI->acl();

// Prepare a task array, containing an array with user ids for each task
$tasks = explode('|', $task_user_assign);
$task_list = array();
foreach ($tasks as $task) {
	if (isset($task) && $task != '') {
		$task_list[substr($task, 0, strpos($task, ':'))] = explode(',', substr($task, strpos($task, ':') + 1));
	}
}

$hperc_assign_ar = array();
foreach ($tasks as $task) {
	if (isset($task) && $task != '') {
		foreach (explode(',', substr($task, strpos($task, ':') + 1)) as $puid) {
			$hperc_assign_ar[$puid] = $percentage_assignment;
		}
	}
}

foreach ($task_list as $task_id => $user_list) {
	$_POST['task_id'] = $task_id;
	// verify that task_id is not NULL
	if ($_POST['task_id'] > 0) {
		//check permissions, if user does not have permission then fail silently
		if (!$perms->checkModuleItem('tasks', 'edit', $_POST['task_id'])) {
			continue;
		}
		
		$obj = new CTask();

		if (!$obj->bind($_POST)) {
			$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
			$AppUI->redirect();
		}

		// process the user specific task priority
		if ($chUTP == 1) {
			$obj->updateUserSpecificTaskPriority($user_task_priority, $user_id);
			$AppUI->setMsg('User specific Task Priority updated', UI_MSG_OK, true);
		} else {
			// If '-' clicked...
			if ($del) {
				$ok = true;
				foreach($user_list as $user_id) {
					if (($msg = $obj->removeAssigned($user_id))) {
						$AppUI->setMsg($msg, UI_MSG_ERROR);
						$ok = false;
					}
				}
				if ($ok) {
					$AppUI->setMsg('User(s) unassigned from Task', UI_MSG_OK);
				} else {
					$AppUI->setMsg('Some User(s) could not be unassigned from Task', UI_MSG_ERROR);
				}
			// If '+' clicked...
			} elseif (!$rm) {
				$overAssignment = $obj->updateAssigned(implode(',', $user_list), $hperc_assign_ar, false, false);
				//check if OverAssignment occured, database has not been updated in this case
				if ($overAssignment) {
					$AppUI->setMsg('The following Users have not been assigned in order to prevent from Over-Assignment:', UI_MSG_ERROR);
					$AppUI->setMsg('<br>' . $overAssignment, UI_MSG_ERROR, true);
				} else {
					$AppUI->setMsg('User(s) assigned to Task', UI_MSG_OK);
				}
			// If '<->' clicked...
			} elseif ($rm) {
				$overAssignment = $obj->updateAssigned(implode(',', $user_list), $hperc_assign_ar, true, false);
				//check if OverAssignment occured, database has not been updated in this case
				if ($overAssignment) {
					$AppUI->setMsg('The following Users have not been assigned in order to prevent from Over-Assignment:', UI_MSG_ERROR);
					$AppUI->setMsg('<br>' . $overAssignment, UI_MSG_ERROR, true);
				} else {
					$AppUI->setMsg('User(s) assigned to Task', UI_MSG_OK);
				}
			}
		}

		if ($store == 1) {
			if (!$obj->store()) {
				$AppUI->setMsg($obj->getError(), UI_MSG_ERROR, true);
			} else {
				$AppUI->setMsg('Task(s) updated', UI_MSG_OK, true);
			}
		}
	}
}
$AppUI->redirect();