<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    refactor to use a core controller
// @todo    remove database query
global $AppUI;

$project_id                          = w2PgetParam($_POST, 'project_id', 0);
$selected                            = w2PgetParam($_POST, 'selected_task', array());
$bulk_task_project                   = w2PgetParam($_POST, 'bulk_task_project', '');
$bulk_task_parent                    = (int) w2PgetParam($_POST, 'bulk_task_parent', '');
$bulk_task_dependency                = w2PgetParam($_POST, 'bulk_task_dependency', '');
$bulk_task_priority                  = w2PgetParam($_POST, 'bulk_task_priority', '');
$bulk_task_user_priority             = w2PgetParam($_POST, 'bulk_task_user_priority', '');
$bulk_task_access                    = w2PgetParam($_POST, 'bulk_task_access', '');
$bulk_task_assign                    = w2PgetParam($_POST, 'bulk_task_assign', '');
$bulk_task_hperc_assign              = w2PgetParam($_POST, 'bulk_task_hperc_assign', '');
$bulk_task_assign_perc               = w2PgetParam($_POST, 'bulk_task_assign_perc', '' );
$bulk_task_unassign                  = w2PgetParam($_POST, 'bulk_task_unassign', '');
$bulk_task_other                     = w2PgetParam($_POST, 'bulk_task_other', '');
$bulk_task_owner                     = w2PgetParam($_POST, 'bulk_task_owner', '');
$bulk_task_type                      = w2PgetParam($_POST, 'bulk_task_type', '');
$bulk_task_duration                  = w2PgetParam($_POST, 'bulk_task_duration', '');
$bulk_task_durntype                  = w2PgetParam($_POST, 'bulk_task_durntype', '');
$bulk_task_start_date                = w2PgetParam($_POST, 'add_task_bulk_start_date', '');
$bulk_task_end_date                  = w2PgetParam($_POST, 'add_task_bulk_end_date', '');
$bulk_task_allow_other_user_tasklogs = w2PgetParam($_POST, 'bulk_task_allow_other_user_tasklogs', '');
$add_task_bulk_time_keep             = w2PgetParam($_POST, 'add_task_bulk_time_keep', '0');
$bulk_move_date                      = w2PgetParam($_POST, 'bulk_move_date', '0');
$bulk_task_percent_complete = w2PgetParam($_POST, 'bulk_task_percent_complete', '');

$userTZ = $AppUI->getPref('TIMEZONE');

if ($bulk_task_start_date) {
	$start_date_userTZ = $start_date = new w2p_Utilities_Date($bulk_task_start_date,$userTZ);
        $start_date->convertTZ('UTC');
	$bulk_start_date = $start_date->format(FMT_DATETIME_MYSQL);
}

if ($bulk_task_end_date) {
	$end_date_userTZ = $end_date = new w2p_Utilities_Date($bulk_task_end_date,$userTZ);
        $end_date->convertTZ('UTC');
	$bulk_end_date = $end_date->format(FMT_DATETIME_MYSQL);
}


if (!canEdit('tasks')) {
    $AppUI->redirect(ACCESS_DENIED);
}

$updateFields = array('bulk_task_percent_complete' => $bulk_task_percent_complete,
        'bulk_task_owner' => $bulk_task_owner, 'bulk_task_priority' => $bulk_task_priority,
        'bulk_task_access' => $bulk_task_access, 'bulk_task_type' => $bulk_task_type,
    );

if (is_array($selected) && count($selected)) {
    $upd_task = new CTask();
    foreach ($selected as $val) {
        $upd_task->load($val);

        foreach ($updateFields as $name => $value) {
            if ($value != '' && ((int) $_POST[$name] == (int) $value)) {
                if ($upd_task->task_id) {
                    $upd_task->{str_replace('bulk_', '', $name)} = $value;
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                }
            }
        }

        //Action: Bulk Move Tasks
        if ($bulk_move_date) {
            $start_date = new w2p_Utilities_Date($upd_task->task_start_date);
            $end_date   = new w2p_Utilities_Date($upd_task->task_end_date);

            $start_date->addDuration($bulk_move_date, 24);
            $upd_task->task_start_date = $start_date->format(FMT_DATETIME_MYSQL);

            $end_date->addDuration($bulk_move_date, 24);
            $upd_task->task_end_date = $end_date->format(FMT_DATETIME_MYSQL);

            $result = $upd_task->store();
        }

        //Action: Move Task Date
        if ($bulk_task_start_date || $bulk_task_end_date) {
            $start_date_old = new w2p_Utilities_Date($upd_task->task_start_date);
            $end_date_old = new w2p_Utilities_Date($upd_task->task_end_date);

            if ($bulk_task_start_date) {
                $time = strtotime($bulk_task_start_date);
                $upd_task->task_start_date = date('Y-m-d H:i:s', $time);
                if ($add_task_bulk_time_keep) {
                    $tmp_start_date = new w2p_Utilities_Date($upd_task->task_start_date, $userTZ);
                    $tmp_start_date->setHour($start_date_old->getHour());
                    $tmp_start_date->setMinute($start_date_old->getMinute());
                    $upd_task->task_start_date = $tmp_start_date->format(FMT_DATETIME_MYSQL);
                }
            }

            if ($bulk_task_end_date) {
                $time = strtotime($bulk_task_end_date);
                $upd_task->task_end_date = date('Y-m-d H:i:s', $time);
                if ($add_task_bulk_time_keep) {
                    $tmp_end_date = new w2p_Utilities_Date($upd_task->task_end_date, $userTZ);
                    $tmp_end_date->setHour($end_date_old->getHour());
                    $tmp_end_date->setMinute($end_date_old->getMinute());
                    $upd_task->task_end_date = $tmp_end_date->format(FMT_DATETIME_MYSQL);
                }
            }
            $result = $upd_task->store();
        }

        //Action: Modify Duration
        if (isset($_POST['bulk_task_duration']) && $bulk_task_duration != '' && is_numeric($bulk_task_duration)) {
            if ($upd_task->task_id) {
                $upd_task->task_duration = $bulk_task_duration;
                //set duration type to hours (1)
                $upd_task->task_duration_type = $bulk_task_durntype ? $bulk_task_durntype : 1;
                $result = $upd_task->store();
                if (!$result) {
                    break;
                }
            }
        }

        //Action: Move to Project
        if (isset($_POST['bulk_task_project']) && $bulk_task_project != '' && $bulk_task_project) {
            if ($upd_task->task_id) {
				$upd_task->moveTaskBetweenProjects($upd_task->task_id,$upd_task->task_project,$bulk_task_project);
            }
        }
        //Action: Change parent
        if ($bulk_task_parent) {
            $new_parent = (-1 == $bulk_task_parent) ? $upd_task->task_id : $bulk_task_parent;
            $upd_task->task_parent = $new_parent;
            $result = $upd_task->store();
        }

        //Action: Change dependency
        if (isset($_POST['bulk_task_dependency']) && $bulk_task_dependency != '') {
            if ($upd_task->task_id) {
                //If parent is self task
                if ($bulk_task_dependency == '0') {
                    $upd_task->task_dynamic = 0;
                    $upd_task->store();
                    $q = new w2p_Database_Query;
                    $q->setDelete('task_dependencies');
                    $q->addWhere('dependencies_task_id=' . $upd_task->task_id);
                    $q->exec();
                } elseif (!($bulk_task_dependency == $upd_task->task_id)) {
                    $upd_task->task_dynamic = 31;
                    $upd_task->store();
                    $q = new w2p_Database_Query;
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
                $upd_task->updateDynamics();
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
            $upd_task->load($val);
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
            $upd_task->load($val);
            if ($upd_task->task_id) {
                $upd_task->removeAssigned($bulk_task_unassign);
            }
        }

        // Action: Allow user to add task logs for others
        if (isset($_POST['bulk_task_allow_other_user_tasklogs']) && $bulk_task_allow_other_user_tasklogs != '') {
            $upd_task = new CTask();
            $upd_task->load($val);
            if ($upd_task->task_id) {
                $upd_task->task_allow_other_user_tasklogs = $bulk_task_allow_other_user_tasklogs;
                $result = $upd_task->store();
                if (!$result) {
                    break;
                }
            }
        }
        //Action: Set user task priority for current user ($APPUI->userid)
        if (($upd_task->task_id)&& ($bulk_task_user_priority!="") ) {
            $assigned_users=$upd_task->assignees($upd_task->task_id);
            if (array_key_exists("$AppUI->user_id",$assigned_users )) {
                $upd_task->updateUserSpecificTaskPriority($bulk_task_user_priority, $AppUI->user_id);
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
                        $end_date = new w2p_Utilities_Date();
                        $upd_task->task_end_date = $end_date->format(FMT_DATETIME_MYSQL);
                    }
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    //Option 2 - Mark as milestone
                } elseif ($bulk_task_other == '2') {
                    $upd_task->task_milestone = 1;
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    //Option 3 - Mark as non milestone
                } elseif ($bulk_task_other == '3') {
                    $upd_task->task_milestone = 0;
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    //Option 4 - Mark as dynamic
                } elseif ($bulk_task_other == '4') {
                    $upd_task->task_dynamic = 1;
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    //Option 5 - Mark as non dynamic
                } elseif ($bulk_task_other == '5') {
                    $upd_task->task_dynamic = 0;
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    //Option 6 - Add Task Reminder
                } elseif ($bulk_task_other == '6') {
                    $upd_task->addReminder();
                    //Option 7 - Mark as non dynamic
                } elseif ($bulk_task_other == '7') {
                    $upd_task->clearReminder(true);
                    //Option 8 - Mark as active
                } elseif ($bulk_task_other == '8') {
                    $upd_task->task_status = '0';
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    // Option 9 - Mark as inactive
                } elseif ($bulk_task_other == '9') {
                    $upd_task->task_status = '-1';
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    //Option 10 - Empty tasks description
                } elseif ($bulk_task_other == '10') {
                    $upd_task->task_description = '';
                    $result = $upd_task->store();
                    if (!$result) {
                        break;
                    }
                    //Option 99 (always at the bottom) - Delete
                } elseif ($bulk_task_other == '99') {
                    $result = $upd_task->delete();
                    if (!$result) {
                        break;
                    }
                }
            }
        }
        $AppUI->setMsg($upd_task->getError(), UI_MSG_ERROR);
	}
}
if (!$result) {
    $AppUI->setMsg($result, UI_MSG_ERROR);
}
$AppUI->redirect('m=projectdesigner&project_id=' . $project_id);
