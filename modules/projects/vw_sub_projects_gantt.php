<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $company_id, $dept_ids, $department, $locale_char_set,
    $proFilter, $projectStatus, $showInactive, $showLabels, $showAllGantt,
    $user_id, $w2Pconfig, $project_id, $project_original_id;

w2PsetExecutionConditions($w2Pconfig);

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

$projectStatus = w2PgetSysVal('ProjectStatus');
$projectStatus = arrayMerge(array('-2' => $AppUI->_('All w/o in progress')), $projectStatus);
$user_id = w2PgetParam($_REQUEST, 'user_id', $AppUI->user_id);

if ($AppUI->user_id == $user_id) {
	$projectStatus = arrayMerge(array('-3' => $AppUI->_('My projects')), $projectStatus);
} else {
	$projectStatus = arrayMerge(array('-3' => $AppUI->_('User\'s projects')), $projectStatus);
}

$proFilter = w2PgetParam($_REQUEST, 'proFilter', '0');
$company_id = w2PgetParam($_REQUEST, 'company_id', 0);
$department = w2PgetParam($_REQUEST, 'department', 0);
$showLabels = w2PgetParam($_REQUEST, 'showLabels', 1);
$showInactive = w2PgetParam($_REQUEST, 'showInactive', 1);
$original_project_id = w2PgetParam($_REQUEST, 'original_project_id', 1);

$pjobj = new CProject();
$working_hours = $w2Pconfig['daily_working_hours'];

// pull valid projects and their percent complete information
// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours

$q = new DBQuery;
$q->addTable('projects', 'pr');
$q->addQuery('DISTINCT pr.project_id, project_color_identifier, project_name, project_start_date, project_end_date,
                max(t1.task_end_date) AS project_actual_end_date, SUM(task_duration * task_percent_complete *
                IF(task_duration_type = 24, ' . $working_hours . ', task_duration_type))/ SUM(task_duration *
                IF(task_duration_type = 24, ' . $working_hours . ', task_duration_type)) AS project_percent_complete,
                project_status, project_active');
$q->addJoin('tasks', 't1', 'pr.project_id = t1.task_project');
$q->addJoin('companies', 'c1', 'pr.project_company = c1.company_id');
if ($department > 0) {
	$q->addWhere('project_departments.department_id = ' . (int)$department);
}

if (!($department > 0) && $company_id != 0) {
	$q->addWhere('project_company = ' . (int)$company_id);
}

$q->addWhere('project_original_parent = ' . (int)$original_project_id);

$pjobj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');
$q->addGroup('pr.project_id');
$q->addOrder('project_name, task_start_date DESC');

$projects = $q->loadHashList('project_id');
$q->clear();

$width = w2PgetParam($_GET, 'width', 600);
$start_date = w2PgetParam($_GET, 'start_date', 0);
$end_date = w2PgetParam($_GET, 'end_date', 0);

$showAllGantt = w2PgetParam($_REQUEST, 'showAllGantt', '1');

$gantt = new w2p_Output_GanttRenderer($AppUI, $width);
$gantt->localize();

$original_project = new CProject();
$original_project->load($original_project_id);
$tableTitle = $original_project->project_name . ': ' . $AppUI->_('Multi-Project Gantt');
$gantt->setTitle($tableTitle, '#eeeeee');

$columnNames = array('Project name', 'Start Date', 'Finish', 'Actual End');
$columnSizes = array(200, 75, 75, 75);
$gantt->setColumnHeaders($columnNames, $columnSizes);

/*
 *  TODO: Technically, doing the date math below using the strtotime is bad
 *     form because it is suseptible to the 2038 date bug. Hopefully, we'll
 *     either have this bug fixed and resolved by then and/or no one is
 *     scheduling projects 28 years into the future. Regardless, it's much 
 *     easier than actual date math.
 *     ~ caseydk 22 Aug 2010
 */
if (!$start_date || !$end_date) {
	$i = 0;
	foreach ($projects as $project) {
		$start = substr($project["project_start_date"], 0, 10);
        $lastTask = $pjobj->getCriticalTasks($project['project_id']);
        $end = substr($lastTask[0]['task_end_date'], 0, 10);

        $d_start = strtotime($start);
        $d_end = strtotime($end);
		if ($i == 0) {
			$min_d_start = $d_start;
            $start_date = $start;
			$max_d_end = $d_end;
            $end_date = $end;
		} else {
            if ($d_start < $min_d_start) {
                $min_d_start = $d_start;
                $start_date = $start;
            }
            if ($d_end > $max_d_end) {
                $max_d_end = $d_end;
                $end_date = $end;
            }
		}
		$i++;
	}
}
$gantt->SetDateRange($start_date, $end_date);

$row = 0;
if (!is_array($projects) || sizeof($projects) == 0) {
    $d = new CDate();
    $columnValues = array('project_name' => $AppUI->_('No projects found'),
                        'start_date' => $d->getDate(), 'end_date' => $d->getDate(),
                        'actual_end' => '');
    $gantt->addBar($columnValues, ' ' , 0.6, 'red');
} else {
    if (is_array($projects)) {
        //pull all tasks into an array keyed by the project id, and get the tasks in hierarchy
        if ($showAllGantt) {
            // insert tasks into Gantt Chart
            // select for tasks for each project
            // pull tasks
            $q = new DBQuery;
            $q->addTable('tasks', 't');
            $q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date, task_duration, task_duration_type, task_priority, task_percent_complete, task_order, task_project, task_milestone, project_id, project_name, task_dynamic');
            $q->addJoin('projects', 'p', 'project_id = t.task_project');
            $q->addOrder('project_id, task_start_date');
            $q->addWhere('project_original_parent = ' . (int)$original_project_id);

            //$tasks = $q->loadList();
            $task = new CTask();
            $task->setAllowedSQL($AppUI->user_id, $q);

            $proTasks = $q->loadHashList('task_id');
            $orrarr[] = array('task_id' => 0, 'order_up' => 0, 'order' => '');

            $end_max = '0000-00-00 00:00:00';
            $start_min = date('Y-m-d H:i:s');
            //pull the tasks into an array
            foreach ($proTasks as $rec) {
                if ($rec['task_start_date'] == '0000-00-00 00:00:00') {
                    $rec['task_start_date'] = date('Y-m-d H:i:s');
                }
                $tsd = new CDate($rec['task_start_date']);
                if ($tsd->before(new CDate($start_min))) {
                    $start_min = $rec['task_start_date'];
                }
                // calculate or set blank task_end_date if unset
                if ($rec['task_end_date'] == '0000-00-00 00:00:00') {
                    if ($rec['task_duration']) {
                        $rec['task_end_date'] = db_unix2dateTime(db_dateTime2unix($rec['task_start_date']) + SECONDS_PER_DAY * convert2days($rec['task_duration'], $rec['task_duration_type']));
                    } else {
                        $rec['task_end_date'] = '';
                    }
                }
                $ted = new CDate($rec['task_end_date']);
                if ($ted->after(new CDate($end_max))) {
                    $end_max = $rec['task_end_date'];
                }
                $projects[$rec['task_project']]['tasks'][] = $rec;
            }
            $q->clear();

            reset($projects);
            foreach ($projects as $p) {
                $tnums = count($p['tasks']);
                for ($i = 0; $i < $tnums; $i++) {
                    $task = $p['tasks'][$i];
                    if ($task['task_parent'] == $task['task_id']) {
                        showgtask($task, 0, $p['project_id']);
                        findgchild($p['tasks'], $task['task_id'], 0, $p['project_id']);
                    }
                }
            }
        }

        foreach ($projects as $p) {
            if ($locale_char_set == 'utf-8') {
                $name = strlen(utf8_decode($p['project_name'])) > 25 ? substr(utf8_decode($p['project_name']), 0, 22) . '...' : utf8_decode($p['project_name']);
            } else {
                //while using charset different than UTF-8 we need not to use utf8_deocde
                $name = strlen($p['project_name']) > 25 ? substr($p['project_name'], 0, 22) : $p['project_name'];
            }
            //using new jpGraph determines using Date object instead of string
            $start = ($p['project_start_date'] > '0000-00-00 00:00:00') ? $p['project_start_date'] : date('Y-m-d H:i:s');
            $start = new CDate($start);
            $start = $start->getDate();
            $end_date = $p['project_end_date'];
            $end_date = new CDate($end_date);
            $end = $end_date->getDate();
            $progress = (int) $p['project_percent_complete'];
            $caption = ' ';
            if (!$start || $start == '0000-00-00') {
                $start = !$end ? date('Y-m-d') : $end;
                $caption .= $AppUI->_('(no start date)');
            }
            if (!$end) {
                $end = $start;
                $caption .= ' ' . $AppUI->_('(no end date)');
            } else {
                $cap = '';
            }
            if ($showLabels) {
                $caption .= $AppUI->_($projectStatus[$p['project_status']]) . ', ';
                $caption .= $p['project_active'] > 0 ? $AppUI->_('active') : $AppUI->_('archived');
            }
            $enddate = new CDate($end);
            $startdate = new CDate($start);
            $actual_end = $p['project_actual_end_date'] ? $p['project_actual_end_date'] : $end;
            $actual_enddate = new CDate($actual_end);
            $actual_enddate = $actual_enddate->after($startdate) ? $actual_enddate : $enddate;

            $columnValues = array('project_name' => $name, 'start_date' => $start,
                              'end_date' => $end, 'actual_end' => $actual_enddate->getDate());
            $gantt->addBar($columnValues, $caption, 0.6, $p['project_color_identifier'],
                $p['project_active'], $progress, $p['project_id']);

            // If showAllGant checkbox is checked
            if ($showAllGantt) {
                // insert tasks into Gantt Chart
                // cycle for tasks for each project
                for ($i = 0, $i_cmp = count($gantt_arr[$p['project_id']]); $i < $i_cmp; $i++) {
                    $t = $gantt_arr[$p['project_id']][$i][0];
                    $level = $gantt_arr[$p['project_id']][$i][1];
                    if ($t['task_end_date'] == null) {
                        $t['task_end_date'] = $t['task_start_date'];
                    }
                    $tStart = ($t['task_start_date'] > '0000-00-00 00:00:00') ? $t['task_start_date'] : date('Y-m-d H:i:s');
                    $tEnd = ($t['task_end_date'] > '0000-00-00 00:00:00') ? $t['task_end_date'] : date('Y-m-d H:i:s');
                    $tStartObj = new CDate($tStart);
                    $tEndObj = new CDate($tEnd);

                    if ($t['task_milestone'] != 1) {
                        $advance = str_repeat('  ', $level+2);
                        $name = mb_strlen($advance . $t['task_name']) > 35 ? mb_substr($advance . $t['task_name'], 0, 33) . '...' : $advance . $t['task_name'];
                        $height = ($t['task_dynamic'] == 1) ? 0.1 : 0.6;

                        $columnValues = array('project_name' => $name, 'start_date' => $tStartObj->getDate(),
                                          'end_date' => $tEndObj->getDate(), 'actual_end' => '');
                        $gantt->addBar($columnValues, '', $height, $p['project_color_identifier'],
                            $p['project_active'], $progress, $p['project_id']);
                    } else {
                        $name = $advance.'* ' . $t['task_name'];
                        $milestone = substr($t['task_start_date'], 0, 10);
                        $milestoneDate = new CDate($milestone);
                        $gantt->addMilestone(array($name, '', $milestoneDate->format($df)), $t['task_start_date']);
                    }

                    // End of insert workers for each task into Gantt Chart
                }
                // End of insert tasks into Gantt Chart
            }
            // End of if showAllGant checkbox is checked
        }
    } // End of check for valid projects array.
}

$gantt->render();