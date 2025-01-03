<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    remove database query

global $caller, $showWork, $showLabels, $showPinned, $showArcProjs,
        $showHoldProjs, $showDynTasks, $showLowTasks, $user_id;

w2PsetExecutionConditions($w2Pconfig);

$project_id = (int) w2PgetParam($_REQUEST, 'project_id', 0);
$f = w2PgetParam($_REQUEST, 'f', 0);

$showLabels = (int) w2PgetParam($_REQUEST, 'showLabels', 0);
$showWork = (int) w2PgetParam($_REQUEST, 'showWork', 0);

$ganttTaskFilter = (int) w2PgetParam($_REQUEST, 'ganttTaskFilter', 0);
$showPinned = (int) w2PgetParam( $_REQUEST, 'showPinned', false );
$showArcProjs = (int) w2PgetParam( $_REQUEST, 'showArcProjs', false );
$showHoldProjs = (int) w2PgetParam( $_REQUEST, 'showHoldProjs', false );
$showDynTasks = (int) w2PgetParam( $_REQUEST, 'showDynTasks', false );
$showLowTasks = (int) w2PgetParam( $_REQUEST, 'showLowTasks', true);

// Get the state of formatting variables here /////////////////////////////////////////////////////
$showTaskNameOnly = (int) w2PgetParam($_REQUEST, 'showTaskNameOnly', 0);
$showNoMilestones = (int) w2PgetParam($_REQUEST, 'showNoMilestones', 0);
$showMilestonesOnly = (int) w2PgetParam($_REQUEST, 'showMilestonesOnly', 0);
$showhgrid = (int) w2PgetParam($_REQUEST, 'showhgrid', 0);
$printpdfhr = (int) w2PgetParam($_REQUEST, 'printpdfhr', 0);
$addLinksToGantt = (int) w2PgetParam($_REQUEST, 'addLinksToGantt', 0);
$monospacefont = (int) w2PgetParam($_REQUEST, 'monospacefont', 0);
// Get the state of formatting variables here /////////////////////////////////////////////////////

$df = $AppUI->getPref('SHDATEFORMAT');
$project = new CProject();
$criticalTasks = ($project_id > 0) ? $project->getCriticalTasks($project_id) : null;
$criticalTasksInverted = ($project_id > 0) ? getCriticalTasksInverted($project_id) : null;

// pull valid projects and their percent complete information
$projects = $project->getAllowedProjects($AppUI->user_id, false);

##############################################
/* gantt is called now by the todo page, too.
** there is a different filter approach in todo
** so we have to tweak a little bit,
** also we do not have a special project available
*/
$caller = w2PgetParam($_REQUEST, 'caller', null);

$task = new CTask();

if ($caller == 'todo') {
    $user_id = w2PgetParam($_REQUEST, 'user_id', $AppUI->user_id);

    $projects[$project_id]['project_name'] = $AppUI->_('Todo for') . ' ' . CContact::getContactByUserid($user_id);
    $projects[$project_id]['project_color_identifier'] = 'ff6000';

    $proTasks = __extract_from_tasks_gantt1($user_id, $showArcProjs, $showLowTasks, $showHoldProjs, $showDynTasks, $showPinned, $task, $AppUI);
} else {
    $proTasks = __extract_from_tasks_gantt2($showNoMilestones, $showMilestonesOnly, $ganttTaskFilter, '', $project_id, $f, $AppUI, $task);
}

$orrarr[] = array('task_id' => 0, 'order_up' => 0, 'order' => '');

$end_max = '0000-00-00 00:00:00';
$start_min = $AppUI->convertToSystemTZ(date('Y-m-d H:i:s'));

//pull the tasks into an array
if ($caller != 'todo') {
    $criticalTasks = $project->getCriticalTasks($project_id);
}

foreach ($proTasks as $row) {
    $row['task_start_date'] = __extract_from_projects_gantt3($row);

    $tsd = new w2p_Utilities_Date($row['task_start_date']);
    if ($tsd->before(new w2p_Utilities_Date($start_min))) {
        $start_min = $row['task_start_date'];
    }

    $row['task_end_date'] = __extract_from_projects_gantt4($row);


    $ted = new w2p_Utilities_Date($row['task_end_date']);
    if ($ted->after(new w2p_Utilities_Date($end_max))) {
        $end_max = $row['task_end_date'];
    }
    if ($ted->after(new w2p_Utilities_Date($projects[$row['task_project']]['project_end_date']))
        || $projects[$row['task_project']]['project_end_date'] == '') {
        $projects[$row['task_project']]['project_end_date'] = $row['task_end_date'];
    }

    $projects[$row['task_project']]['tasks'][] = $row;
}

$width = min(w2PgetParam($_GET, 'width', 600), 1400);
$start_date = w2PgetParam($_GET, 'start_date', $start_min);
$end_date = w2PgetParam($_GET, 'end_date', $end_max);

//consider critical (concerning end date) tasks as well
if (!is_null($criticalTasks)) {
    if ($caller != 'todo') {
        $start_min = $projects[$project_id]['project_start_date'];
        $end_max = (($projects[$project_id]['project_end_date'] > $criticalTasks[0]['task_end_date']) 
                    ? $projects[$project_id]['project_end_date'] : $criticalTasks[0]['task_end_date']);
    } else {
        $start_min = substr($criticalTasksInverted[0]['task_start_date'], 0, 10);
        if ($start_min == '0000-00-00' || !$start_min) {
            $start_min = $projects[$project_id]['project_start_date'];
        }
        $end_max = substr($criticalTasks[0]['task_end_date'], 0, 10);
        if ($end_max == '0000-00-00' || !$end_max) {
            $end_max = $projects[$project_id]['project_end_date'];
        }
    }
}

$count = 0;

// If hyperlinks are to be added then the graph is of a set width///////
if ($addLinksToGantt == '1') {
    $width = 1450 ;
}

if (!$start_date || !$end_date) {
    // find out DateRange from gant_arr
    $d_start = new w2p_Utilities_Date();
    $d_end = new w2p_Utilities_Date();
    $taskArray = count($gantt_arr);
    for ($i = 0, $i_cmp = $taskArray; $i < $i_cmp; $i++) {
        $a = $gantt_arr[$i][0];
        $start = substr($a['task_start_date'], 0, 10);
        $end = substr($a['task_end_date'], 0, 10);

        $d_start->Date($start);
        $d_end->Date($end);

        if ($i == 0) {
            $min_d_start = $d_start;
            $max_d_end = $d_end;
            $start_date = $start;
            $end_date = $end;
        } else {
            if (Date::compare($min_d_start, $d_start) > 0) {
                $min_d_start = $d_start;
                $start_date = $start;
            }
            if (Date::compare($max_d_end, $d_end) < 0) {
                $max_d_end = $d_end;
                $end_date = $end;
            }
        }
    }
}
if ('0000-00-00' == substr($start_date, 0, 10)) {
    $start_date = date('Y-m-d', time() - 60 * 60 * 24 * 7);
}
if ('0000-00-00' == substr($end_date, 0, 10)) {
    $end_date = date('Y-m-d', time() + 60 * 60 * 24 * 7);
}
$gantt = new w2p_Output_GanttRenderer($AppUI, $width);
$gantt->localize();
$pname = $projects[$project_id]['project_name'];
$gantt->setTitle($pname);
$field = ($showWork == '1') ? 'Work' : 'Dur';

if ($showTaskNameOnly == '1') {
    $columnNames = array('Task name');
    $columnSizes = array(300);
} else {
    if ($caller == 'todo') {
        $columnNames = array('Task name', 'Project name', $field, 'Start', 'Finish');
        $columnSizes = array(200, 160, 40, 75, 75);
    } else {
        $columnNames = array('Task name', $field, 'Start', 'Finish');
        $columnSizes = array(200, 50, 80, 80);
    }
}
$gantt->setColumnHeaders($columnNames, $columnSizes);
$gantt->setProperties(array('showhgrid' => true));
$gantt->setDateRange($start_date, $end_date);

$gantt_arr = array();
reset($projects);
$displayed = array();
foreach ($projects as $p) {
    $tnums = isset($p['tasks']) ? count($p['tasks']) : 0;

    for ($i = 0; $i < $tnums; $i++) {
        $t = $p['tasks'][$i];
        if ($caller == 'todo') {
            if ($showDynTasks) {
                if ($t['task_parent'] == $t['task_id']) {
                    showgtask($t);
                    findchild_gantt($p['tasks'], $t['task_id']);
                }
            } else {
                showgtask($t);
            }
        } else {
            if ($t['task_parent'] == $t['task_id']) {
                showgtask($t);
                findchild_gantt($p['tasks'], $t['task_id']);
            }
        }
    }
}
$hide_task_groups = false;
if ($hide_task_groups) {
	for ($i = 0, $i_cmp = count($gantt_arr); $i < $i_cmp; $i++) {
		// remove task groups
		if ($i != count($gantt_arr) - 1 && $gantt_arr[$i + 1][1] > $gantt_arr[$i][1]) {
			// it's not a leaf => remove
			array_splice($gantt_arr, $i, 1);
			continue;
		}
	}
}

$gantt->loadTaskArray($gantt_arr);

$row = 0;
for ($i = 0, $i_cmp = count($gantt_arr); $i < $i_cmp; $i++) {
    $a = $gantt_arr[$i][0];
    $level = $gantt_arr[$i][1];
    $caption = '';

    $tmpTask = new CTask();
    $tmpTask->load($a['task_id']);
    $canAccess = $tmpTask->canAccess();
    if ($canAccess) {
        if ($hide_task_groups) {
            $level = 0;
        }

        $name = $a['task_name'];
        $name = ((mb_strlen($name) > 35) ? (mb_substr($name, 0, 30) . '...') : $name);
        $name = str_repeat('   ', $level) . $name;

        $pname = $a['project_name'];
        $pname = (mb_strlen($pname) > 25) ? (mb_substr($pname, 0, 20) . '...') : $pname;

        //using new jpGraph determines using Date object instead of string
        $start = (int) ($a['task_start_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($a['task_start_date'], '%Y-%m-%d %T')) : new w2p_Utilities_Date();
        $start = $start->getDate();

        $end   = (int) ($a['task_end_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($a['task_end_date'], '%Y-%m-%d %T')) : new w2p_Utilities_Date();
        $end = $end->getDate();

        $progress = (int) $a['task_percent_complete'];

        if ($progress > 100) {
            $progress = 100;
        } elseif ($progress < 0) {
            $progress = 0;
        }

        $flags = ($a['task_milestone'] ? 'm' : '');

        $caption = '';
        if (!$start || $start == '0000-00-00') {
            $start = !$end ? date('Y-m-d') : $end;
            $caption .= $AppUI->_('(no start date)');
        }

        if (!$end) {
            $end = $start;
            $caption .= ' ' . $AppUI->_('(no end date)');
        }

        if ($showLabels == '1') {
            $res = $task->assignees($a['task_id']);
            foreach ($res as $rw) {
                switch ($rw['perc_assignment']) {
                    case 100:
                        $caption .= $rw['contact_display_name'] . ';';
                        break;
                    default:
                        $caption .= $rw['contact_display_name'] . ' [' . $rw['perc_assignment'] . '%];';
                        break;
                }
            }
            $caption = mb_substr($caption, 0, mb_strlen($caption) - 1);
        }

        if ($flags == 'm') {
            // if hide milestones is ticked this bit is not processed//////////////////////////////////////////
            if ($showNoMilestones != '1') {
                $start = new w2p_Utilities_Date($start);
                $start->addDays(0);
                $start_mile = $start->getDate();
                $s = $start->format($df);
                $mile_date = $start->format($df . ' ' . $AppUI->getPref('TIMEFORMAT'));
                $mile_date_stamp = strtotime($start_mile);

                $today = new w2p_Utilities_Date();
                $today->convertTZ($AppUI->getPref('TIMEZONE'));

                $today_mile = $today->getDate();
                $today_date = $today->format($df . ' ' . $AppUI->getPref('TIMEFORMAT'));
                $today_date_stamp = strtotime($today_mile);

                // honour the choice to show task names only///////////////////////////////////////////////////
                if ($showTaskNameOnly == '1') {
                    $fieldArray = array($name);
                } else {
                    if ($caller == 'todo') {
                        $fieldArray = array($name, $pname, '', $s, $s);
                    } else {
                        $fieldArray = array($name, '', $s, $s);
                    }
                }
                ///////////////////////////////////////////////////////////////////////////////////////
                //set color for milestone according to progress
                //red for 'not started' #990000
                //yellow for 'in progress' #FF9900
                //green for 'achieved' #006600
                // blue for 'planned' #0000FF
                if ($a['task_percent_complete'] == 100)  {
                    $color = '#006600';
                } else {
                    if ($mile_date_stamp < $today_date_stamp) {
                        $color = '#990000';
                    } else {
                        if ($a['task_percent_complete'] == 0)  {
                            $color = '#0000FF';
                        } else {
                            $color = '#FF9900';
                        }
                    }
                }

            // if the milestone is near the end of the date range for which we are showing the chart
            // make the caption go on the left side of the milestone marker
            $task_start_date = $AppUI->formatTZAwareTime($a['task_start_date'], '%Y-%m-%d %T');
            /*
             * TODO: This is an ugly hack to correct the placement of the
             *   milestones on the gantt charts. I have no clue why this
             *   adjustment is needed, but it is..
             *                  ~ caseydk 02 August 2012
             */
            $my_time = strtotime($task_start_date) + 24 *60*60;
            $task_start_date = date('Y-m-d', $my_time);

            $captionToTheLeft = false;
            if ($mile_date_stamp + 72*60*60 >= strtotime($end_date)) {
                $captionToTheLeft = true;
            }
            $gantt->addMilestone($fieldArray, $a['task_start_date'], $color, 0, $captionToTheLeft);
            }
        } else {
            $type = $a['task_duration_type'];
            $dur = $a['task_duration'];
            if ($type == 24) {
                $dur *= $w2Pconfig['daily_working_hours'];
            }

            if ($showWork == '1') {
                $dur = $a['task_hours_worked'];
            }
            $dur = round($dur, 0);

            $dur .= ' h';
            $height = ($a['task_dynamic'] == 1) ? 0.1 : 0.6;
            if ($showTaskNameOnly == '1') {
                $columnValues = array('task_name' => $name);
            } else {
                if ($caller == 'todo') {
                    $columnValues = array('task_name' => $name, 'project_name' => $pname,
                        'duration' => $dur, 'start_date' => $start, 'end_date' => $end,
                        'actual_end' => '');
                } else {
                    $columnValues = array('task_name' => $name, 'duration' => $dur,
                        'start_date' => $start, 'end_date' => $end, 'actual_end' => '');
                }
            }
            $gantt->addBar($columnValues, $caption, $height, '8F8FBD', true, $progress, $a['task_id']);
        }
    }
}

$gantt->render();
