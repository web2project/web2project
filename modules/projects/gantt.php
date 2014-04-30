<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

global $AppUI, $dept_ids, $w2Pconfig;

w2PsetExecutionConditions($w2Pconfig);

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

$projectStatus = w2PgetSysVal('ProjectStatus');
$user_id = w2PgetParam($_REQUEST, 'user_id', $AppUI->user_id);

// prepare the type filter
if (isset($_POST['project_type'])) {
	$AppUI->setState('ProjIdxType', intval($_POST['project_type']));
}
$project_type = $AppUI->getState('ProjIdxType') !== null ? $AppUI->getState('ProjIdxType') : -1;

// prepare the users filter
if (isset($_POST['project_owner'])) {
	$AppUI->setState('ProjIdxowner', intval($_POST['project_owner']));
}
$owner = $AppUI->getState('ProjIdxowner') !== null ? $AppUI->getState('ProjIdxowner') : 0;

$statusFilter = (int) w2PgetParam($_REQUEST, 'proFilter', -1);
$company_id = w2PgetParam($_REQUEST, 'company_id', 0);
$department = w2PgetParam($_REQUEST, 'department', 0);
$showLabels = w2PgetParam($_REQUEST, 'showLabels', 0);
$showInactive = w2PgetParam($_REQUEST, 'showInactive', 0);
$sortTasksByName = w2PgetParam($_REQUEST, 'sortTasksByName', 0);
$addPwOiD = w2PgetParam($_REQUEST, 'addPwOiD', 0);

$pjobj = new CProject();

/*
** Load department info for the case where one
** wants to see the ProjectsWithOwnerInDeparment (PwOiD)
** instead of the projects related to the given department.
*/

if ($addPwOiD && $department > 0) {
    $owner_ids = __extract_from_projects_gantt($department);
}

$projects = __extract_from_projects_gantt2($department, $addPwOiD, $project_type, $owner, $statusFilter, $company_id, $owner_ids, $showInactive, $AppUI, $pjobj);

// Don't push the width higher than about 1200 pixels, otherwise it may not display.
$width = min(w2PgetParam($_GET, 'width', 600), 1400);
$start_date = w2PgetParam($_GET, 'start_date', 0);
$end_date = w2PgetParam($_GET, 'end_date', 0);

$showAllGantt = w2PgetParam($_REQUEST, 'showAllGantt', '0');



if (!$start_date || !$end_date) {
    // find out DateRange from $projects array
    $projectCount = count($projects);
    for ($i = 0, $i_cmp = $projectCount; $i < $i_cmp; $i++) {
        $start = substr($projects[$i]['project_start_date'], 0, 10);
        if (0 == strlen($start)) {
            $start = date('Y-m-d');
        }
        $end = substr($projects[$i]['project_end_date'], 0, 10);
        if (0 == strlen($end)) {
            $lastTask = $pjobj->getCriticalTasks($projects[$i]['project_id']);
            $projects[$i]['project_actual_end_date'] = $lastTask[0]['task_end_date'];
            $projects[$i]['project_end_date'] = $lastTask[0]['task_end_date'];
            $end = substr($lastTask[0]['task_end_date'], 0, 10);
        }

        $d_start = new w2p_Utilities_Date($start);
        $d_end = new w2p_Utilities_Date($end);

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

$gantt = new w2p_Output_GanttRenderer($AppUI, $width);
$gantt->localize();

$tableTitle = ($statusFilter == '-1') ? $AppUI->_('All Projects') : $projectStatus[$statusFilter];
$gantt->setTitle($tableTitle);
$columnNames = array('Project name', 'Start Date', 'Finish', 'Actual End');
$columnSizes = array(160, 75, 75, 75);
$gantt->setColumnHeaders($columnNames, $columnSizes);
$gantt->setProperties(array('showhgrid' => true));
$gantt->setDateRange($start_date, $end_date);

$row = 0;


if (!is_array($projects) || 0 == count($projects)) {
    $d = new w2p_Utilities_Date();
    $columnValues = array('project_name' => $AppUI->_('No projects found'), 
                        'start_date' => $d->getDate(), 'end_date' => $d->getDate(),
                        'actual_end' => '');
    $gantt->addBar($columnValues, ' ' , 0.6, 'red');
} else {
	foreach ($projects as $p) {

        $pname = $p['project_name'];
        $pname = (mb_strlen($pname) > 30) ? (mb_substr($pname, 0, 25) . '...') : $pname;

		//using new jpGraph determines using Date object instead of string
        $start_date = (int) ($p['project_start_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($p['project_start_date'], '%Y-%m-%d %T')) : new w2p_Utilities_Date();
        $start = $start_date->getDate();

        $end_date = (int) ($p['project_end_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($p['project_end_date'], '%Y-%m-%d %T')) : new w2p_Utilities_Date();
		$end = $end_date->getDate();

        $actual_end = (int) ($p['project_actual_end_date']) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($p['project_actual_end_date'], '%Y-%m-%d %T')) : $end_date;
        $actual_end = $actual_end->getDate();

        $progress = (int) $p['project_percent_complete'];

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
			$caption .= $AppUI->_($projectStatus[$p['project_status']]) . ', ';
			$caption .= ($p['project_active']) ? $AppUI->_('active') : $AppUI->_('archived');
		}		

        $columnValues = array('project_name' => $pname, 'start_date' => $start,
                          'end_date' => $end, 'actual_end' => $actual_end);
		$gantt->addBar($columnValues, $caption, 0.6, $p['project_color_identifier'],
            $p['project_active'], $progress, $p['project_id']);

		// If showAllGant checkbox is checked
		if ($showAllGantt) {
			// insert tasks into Gantt Chart
			// select for tasks for each project

            $task = new CTask();
            $orderBy = ($sortTasksByName) ? 'task_name' : 'task_end_date ASC';
            $tasks = $task->getAllowedTaskList(null, $p['project_id'], $orderBy);
            $bestColor = bestColor('#ffffff', '#' . $p['project_color_identifier'], '#000000');

			foreach ($tasks as $t) {
                $name = $t['task_name'];
                $name = ((mb_strlen($name) > 34) ? (mb_substr($name, 0, 30) . '...') : $name);

                $t['task_start_date'] = __extract_from_projects_gantt3($t);
                $t['task_end_date'] = __extract_from_projects_gantt4($t);

                if ($t['task_milestone'] != 1) {
                    $columnValues = array('task_name' => $name,
                        'start_date' => $t['task_start_date'], 'end_date' => $t['task_end_date'], 'actual_end' => '');
                    $height = ($t['task_dynamic'] == 1) ? 0.1 : 0.6;
                    $gantt->addBar($columnValues, $t['task_percent_complete'].'% '.$AppUI->_('Complete'),
                        $height, $p['project_color_identifier'], $p['project_active'],
                        $t['task_percent_complete'], $t['task_id']);
                } else {
                    $gantt->addMilestone(array('-- ' . $name), $t['task_start_date']);
                }

                $task->task_id = $t['task_id'];
                $workers = $task->assignees($task->task_id);

                foreach ($workers as $w) {
                    $columnValues = array('user_name' => '    * '.$w['contact_display_name'],
                        'start_date' => $t['task_start_date'], 'end_date' => $t['task_end_date'], 'actual_end' => '');
                    $height = ($t['task_dynamic'] == 1) ? 0.1 : 0.6;
                    $gantt->addBar($columnValues, $w['user_name'], 0.6, $p['project_color_identifier'],
                        true, $t['task_percent_complete'], $t['task_id']);
                }
                // End of insert workers for each task into Gantt Chart
			}
			unset($tasks);
			// End of insert tasks into Gantt Chart
		}
		// End of if showAllGant checkbox is checked
	}
} // End of check for valid projects array.

unset($projects);

$gantt->render();
