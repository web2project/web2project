<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
 * TODO: This controller is doing a lot of non-controller things that are making
 *   things not reusable and generally difficult to maintain.
 */
//$adjustStartDate = w2PgetParam($_POST, 'set_task_start_date');
$del = (int) w2PgetParam($_POST, 'del', 0);
$task_id = (int) w2PgetParam($_POST, 'task_id', 0);
$hassign = w2PgetParam($_POST, 'hassign');
$hperc_assign = w2PgetParam($_POST, 'hperc_assign');
$hdependencies = w2PgetParam($_POST, 'hdependencies', '');
$notify = (int) w2PgetParam($_POST, 'task_notify', 0);
$comment = w2PgetParam($_POST, 'email_comment', '');
//$sub_form = (int) w2PgetParam($_POST, 'sub_form', 0);
$new_task_project = (int) w2PgetParam($_POST, 'new_task_project', 0);
//$isNotNew = $task_id;

//$action = ($del) ? 'deleted' : 'stored';

// Find the task if we are set
$task_end_date = null;
$obj = new CTask();
if ($task_id) {
    $obj->load($task_id);
    $task_end_date = new w2p_Utilities_Date($obj->task_end_date);
}

if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

// Check to see if the task_project has changed
if ($new_task_project != 0 and $obj->task_project != $new_task_project) {
    $obj->moveTaskBetweenProjects($task_id, $obj->task_project, $new_task_project);
    /**
     * We have to bail out for a redirect here or otherwise the store() below
     *   will screw things up and assign the root task specified back to the
     *   original project.
     */
    $AppUI->redirect('m=projects&a=view&project_id='.$new_task_project);
}

// Map task_dynamic checkboxes to task_dynamic values for task dependencies.
if ($obj->task_dynamic != 1) {
    $task_dynamic_delay = w2PgetParam($_POST, 'task_dynamic_nodelay', '0');
    if (in_array($obj->task_dynamic, CTask::$tracking_dynamics)) {
        $obj->task_dynamic = $task_dynamic_delay ? 21 : 31;
    } else {
        $obj->task_dynamic = $task_dynamic_delay ? 11 : 0;
    }
}

// Let's check if task_dynamic is unchecked
if (!array_key_exists('task_dynamic', $_POST)) {
    $obj->task_dynamic = false;
}

// Make sure task milestone is set or reset as appropriate
if (!isset($_POST['task_milestone'])) {
    $obj->task_milestone = false;
}

//format hperc_assign user_id=percentage_assignment;user_id=percentage_assignment;user_id=percentage_assignment;
$tmp_ar = explode(';', $hperc_assign);
$i_cmp = sizeof($tmp_ar);

$hperc_assign_ar = array();
for ($i = 0; $i < $i_cmp; $i++) {
    $tmp = explode('=', $tmp_ar[$i]);
    if (count($tmp) > 1) {
        $hperc_assign_ar[$tmp[0]] = $tmp[1];
    } elseif ($tmp[0] != '') {
        $hperc_assign_ar[$tmp[0]] = 100;
    }
}

// let's check if there are some assigned departments to task
$obj->task_departments = implode(',', w2PgetParam($_POST, 'dept_ids', array()));

// convert dates to SQL format first
if ($obj->task_start_date) {
    $start_date = new w2p_Utilities_Date($obj->task_start_date);
    $obj->task_start_date = $start_date->format(FMT_DATETIME_MYSQL);
}
$end_date = null;
if ($obj->task_end_date) {
    if (strpos($obj->task_end_date, '2400') !== false) {
        $obj->task_end_date = str_replace('2400', '2359', $obj->task_end_date);
    }
    $end_date = new w2p_Utilities_Date($obj->task_end_date);
    $obj->task_end_date = $end_date->format(FMT_DATETIME_MYSQL);
}

// prepare (and translate) the module name ready for the suffix
if ($del) {
    $result = $obj->delete();
    if ($result) {
        $AppUI->setMsg('Task deleted');
        $AppUI->redirect('m=projects&a=view&project_id='.$obj->task_project);
    } else {
        $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
        $AppUI->redirect('m=tasks&a=view&task_id='.$task_id);
    }
}

$result = $obj->store();

if ($result) {
    if (isset($hassign)) {
        $obj->updateAssigned($hassign, $hperc_assign_ar);
    }

    // This call has to be here to make sure that old dependencies are
    // cleared on save, even if there's no new dependencies
    $obj->updateDependencies($hdependencies,  $obj->task_id);
    if (isset($hdependencies) && '' != $hdependencies) {
        // there are dependencies set!
        $nsd = new w2p_Utilities_Date($obj->get_deps_max_end_date($obj));

        if (isset($start_date)) {
            $shift = $nsd->compare($start_date, $nsd);
            if ($shift < 1) {
                $obj->task_start_date = $nsd->format(FMT_DATETIME_MYSQL);
                $obj->task_start_date = $AppUI->formatTZAwareTime($obj->task_start_date, '%Y-%m-%d %T');

                $ned = new w2p_Utilities_Date($obj->task_start_date);
                $ned->addDuration($obj->task_duration, $obj->task_duration_type);
                $obj->task_end_date = $ned->format(FMT_DATETIME_MYSQL);

                $obj->store();
            }
        }
    }

    $billingCategory = w2PgetSysVal('BudgetCategory');
	$budgets = array();
	foreach ($billingCategory as $id => $category) {
		$budgets[$id] = w2PgetParam($_POST, 'budget_'.$id, 0);
	}
	$obj->storeBudget($budgets);

    // Now add any task reminders
    // If there wasn't a task, but there is one now, and
    // that task date is set, we need to set a reminder.
    if (empty($task_end_date) || (!empty($end_date) && $task_end_date->dateDiff($end_date))) {
        $obj->addReminder();
    }
    $AppUI->setMsg($task_id ? 'Task updated' : 'Task added', UI_MSG_OK);

    // TODO: This is a hotfix for 1083, tasks_dosql.addedit.php is no longer run which is the root of the problem
    // as no pre or post_save function is defined anymore (i could not find the core reason for this so ergo hotfix
    if($AppUI->isActiveModule('resources')) {
        global $other_resources;
        $other_resources = w2PgetParam($_POST, 'hresource_assign');

        resource_postsave();
    }

    // If there is a set of post_save functions, then we process them
    if (isset($post_save)) {
        foreach ($post_save as $post_save_function) {
            $post_save_function();
        }
    }

    if ($notify) {
        $obj->notify($comment);
    }

    $redirect = 'm=projects&a=view&project_id='.$obj->task_project;
} else {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $redirect = 'm=tasks&a=addedit&task_id='.$obj->task_id;
}

$AppUI->redirect($redirect);