<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $caller, $locale_char_set, $showWork,              $showLabels,
                $showPinned, $showArcProjs, $showHoldProjs, $showDynTasks, 
    $showLowTasks, $user_id;

w2PsetExecutionConditions($w2Pconfig);

$project_id = (int) w2PgetParam($_REQUEST, 'project_id', 0);
$f = w2PgetParam($_REQUEST, 'f', 0);

$project = new CProject;
$criticalTasks = ($project_id > 0) ? $project->getCriticalTasks($project_id) : null;
$criticalTasksInverted = ($project_id > 0) ? getCriticalTasksInverted($project_id) : null;

// pull valid projects and their percent complete information
$projects = $project->getAllowedProjects($AppUI->user_id, false);

// pull tasks
$q = new DBQuery;
$q->addTable('tasks', 't');
$q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date, task_duration, task_duration_type, task_priority, task_percent_complete, task_order, task_project, task_milestone, project_name, task_dynamic');
$q->addJoin('projects', 'p', 'project_id = t.task_project', 'inner');
$q->addOrder('project_id, task_start_date');
if ($project_id) {
	$q->addWhere('task_project = ' . (int)$project_id);
}
switch ($f) {
	case 'all':
		$q->addWhere('task_status > -1');
		break;
	case 'myproj':
		$q->addWhere('task_status > -1');
		$q->addWhere('project_owner = ' . (int)$AppUI->user_id);
		break;
	case 'mycomp':
		$q->addWhere('task_status > -1');
		$q->addWhere('project_company = ' . (int)$AppUI->user_company);
		break;
	case 'myinact':
		$q->addTable('user_tasks', 'ut');
		$q->addWhere('task_project = p.project_id');
		$q->addWhere('ut.user_id = ' . (int)$AppUI->user_id);
		$q->addWhere('ut.task_id = t.task_id');
		break;
	default:
		$q->addTable('user_tasks', 'ut');
		$q->addWhere('task_status > -1');
		$q->addWhere('task_project = p.project_id');
		$q->addWhere('ut.user_id = ' . (int)$AppUI->user_id);
		$q->addWhere('ut.task_id = t.task_id');
		break;
}

// get any specifically denied tasks
$task = new CTask;
$task->setAllowedSQL($AppUI->user_id, $q);
$proTasks = $q->loadHashList('task_id');
$orrarr[] = array('task_id' => 0, 'order_up' => 0, 'order' => '');

$end_max = '0000-00-00 00:00:00';
$start_min = date('Y-m-d H:i:s');

//pull the tasks into an array
foreach ($proTasks as $row) {

	//Check if start date exists, if not try giving it the end date.
	//If the end date does not exist then set it for today.
	//This avoids jpgraphs internal errors that render the gantt completely useless
	if ($row['task_start_date'] == '0000-00-00 00:00:00') {
		if ($row['task_end_date'] == '0000-00-00 00:00:00') {
			$todaydate = new CDate();
			$row['task_start_date'] = $todaydate->format(FMT_TIMESTAMP_DATE);
		} else {
			$row['task_start_date'] = $row['task_end_date'];
		}
	}

	$tsd = new CDate($row['task_start_date']);

	if ($tsd->before(new CDate($start_min))) {
		$start_min = $row['task_start_date'];
	}

	//Check if end date exists, if not try giving it the start date.
	//If the start date does not exist then set it for today.
	//This avoids jpgraphs internal errors that render the gantt completely useless
	if ($row['task_end_date'] == '0000-00-00 00:00:00') {
		if ($row['task_duration']) {
			$row['task_end_date'] = db_unix2dateTime(db_dateTime2unix($row['task_start_date']) + SECONDS_PER_DAY * convert2days($row['task_duration'], $row['task_duration_type']));
		} else {
			$todaydate = new CDate();
			$row['task_end_date'] = $todaydate->format(FMT_TIMESTAMP_DATE);
		}
	}

	$ted = new CDate($row['task_end_date']);

	if ($ted->after(new CDate($end_max))) {
		$end_max = $row['task_end_date'];
	}

	$projects[$row['task_project']]['tasks'][] = $row;
}
$q->clear();

$width = min(w2PgetParam($_GET, 'width', 600), 1400);
$start_date = w2PgetParam($_GET, 'start_date', $start_min);
$end_date = w2PgetParam($_GET, 'end_date', $end_max);

//consider critical (concerning end date) tasks as well
$start_min = substr($criticalTasksInverted[0]['task_start_date'], 0, 10);
if ($start_min == '0000-00-00' || !$start_min) {
	$start_min = $projects[$project_id]['project_start_date'];
}
//	$end_max = ($projects[$project_id]['project_end_date'] > $criticalTasks[0]['task_end_date']) ? $projects[$project_id]['project_end_date'] : $criticalTasks[0]['task_end_date'];
$end_max = substr($criticalTasks[0]['task_end_date'], 0, 10);
if ($end_max == '0000-00-00' || !$end_max) {
	$end_max = $projects[$project_id]['project_end_date'];
}

$count = 0;

$gantt = new w2p_Output_GanttRenderer($AppUI, $width);
$gantt->localize();
$gantt->setTitle($projects[$project_id]['project_name'], '#'.$projects[$project_id]['project_color_identifier']);
// get the prefered date format

$field = ($showWork == '1') ? 'Work' : 'Dur';
$columnNames = array('Task name', $field, 'Start', 'Finish');
$columnSizes = array(200, 50, 75, 75);
$gantt->setColumnHeaders($columnNames, $columnSizes);

//-----------------------------------------
// nice Gantt image
// if diff(end_date,start_date) > 90 days it shows only
//week number
// if diff(end_date,start_date) > 240 days it shows only
//month number
//-----------------------------------------
if ($start_date && $end_date) {
	$min_d_start = new CDate($start_date);
	$max_d_end = new CDate($end_date);
} else {
	// find out DateRange from gant_arr
	$min_d_start = new CDate();
	$max_d_end = new CDate();
	$d_start = new CDate();
	$d_end = new CDate();
	for ($i = 0, $i_cmp = count($gantt_arr); $i < $i_cmp; $i++) {
		$a = $gantt_arr[$i][0];
		$start = substr($a['task_start_date'], 0, 10);
		$end = substr($a['task_end_date'], 0, 10);

		$d_start->Date($start);
		$d_end->Date($end);

		if ($i == 0) {
			$min_d_start = $d_start->duplicate();
			$max_d_end = $d_end->duplicate();
		} else {
			if (Date::compare($min_d_start, $d_start) > 0) {
				$min_d_start = $d_start->duplicate();
                $start_date = $start;
			}
			if (Date::compare($max_d_end, $d_end) < 0) {
				$max_d_end = $d_end->duplicate();
                $end_date = $end;
			}
		}
	}
}
$gantt->setDateRange($start_date, $end_date);

reset($projects);
foreach ($projects as $p) {
	$tnums = count($p['tasks']);

	for ($i = 0; $i < $tnums; $i++) {
		$t = $p['tasks'][$i];
		if ($t['task_parent'] == $t['task_id']) {
			showgtask($t);
			findgchild($p['tasks'], $t['task_id']);
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

	if ($hide_task_groups) {
		$level = 0;
	}

	$name = $a['task_name'];
	if ($locale_char_set == 'utf-8') {
		$name = utf8_decode($name);
	}
	$name = strlen($name) > 34 ? substr($name, 0, 33) . '.' : $name;
	$name = str_repeat(' ', $level) . $name;

	if ($caller == 'todo') {
		$pname = $a['project_name'];
		if ($locale_char_set == 'utf-8') {
			$pname = mb_strlen($pname) > 14 ? mb_substr($pname, 0, 5) . '...' . mb_substr($pname, -5, 5) : $pname;
		} else {
			$pname = strlen($pname) > 14 ? substr($pname, 0, 5) . '...' . substr($pname, -5, 5) : $pname;
		}
	}
	//using new jpGraph determines using Date object instead of string
	$start = $a['task_start_date'];
	$end_date = $a['task_end_date'];

	$end_date = new CDate($end_date);
	$end = $end_date->getDate();

	$start = new CDate($start);
	$start = $start->getDate();

	$progress = (int) $a['task_percent_complete'];

	if ($progress > 100) {
		$progress = 100;
	} elseif ($progress < 0) {
		$progress = 0;
	}

	$flags = ($a['task_milestone'] ? 'm' : '');

	$cap = '';
	if (!$start || $start == '0000-00-00') {
		$start = !$end ? date('Y-m-d') : $end;
		$cap .= '(no start date)';
	}

	if (!$end) {
		$end = $start;
		$cap .= ' (no end date)';
	} else {
		$cap = '';
	}

	$caption = '';
	if ($showLabels == '1') {
		$q = new DBQuery;
		$q->addTable('user_tasks', 'ut');
		$q->addTable('users', 'u');
		$q->addTable('contacts', 'c');
		$q->addQuery('ut.task_id, u.user_username, ut.perc_assignment');
		$q->addQuery('c.contact_first_name, c.contact_last_name');
		$q->addWhere('u.user_id = ut.user_id');
		$q->addWhere('u.user_contact = c.contact_id');
		$q->addWhere('ut.task_id = ' . (int)$a['task_id']);
		$res = $q->loadList();
		foreach ($res as $rw) {
			switch ($rw['perc_assignment']) {
				case 100:
					$caption = $caption . '' . $rw['contact_first_name'] . ' ' . $rw['contact_last_name'] . ';';
					break;
				default:
					$caption = $caption . '' . $rw['contact_first_name'] . ' ' . $rw['contact_last_name'] . ' [' . $rw['perc_assignment'] . '%];';
					break;
			}
		}
		$q->clear();
		$caption = mb_substr($caption, 0, mb_strlen($caption) - 1);
	}

	if ($flags == 'm') {
		$start = new CDate($start);
		$start->addDays(0);
		$s = $start->format($df);
		if ($caller == 'todo') {
            $gantt->addMilestone(array($name, $pname, '', $s, $s), $a['task_start_date']);
		} else {
            $gantt->addMilestone(array($name, '', $s, $s), $a['task_start_date']);
		}
	} else {
		$type = $a['task_duration_type'];
		$dur = $a['task_duration'];
		if ($type == 24) {
			$dur *= $w2Pconfig['daily_working_hours'];
		}

		if ($showWork == '1') {
			$work_hours = 0;
			$q = new DBQuery;
			$q->addTable('tasks', 't');
			$q->addJoin('user_tasks', 'u', 't.task_id = u.task_id', 'inner');
			$q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2) AS wh');
			$q->addWhere('t.task_duration_type = 24');
			$q->addWhere('t.task_id = ' . (int)$a['task_id']);

			$wh = $q->loadResult();
			$work_hours = $wh * $w2Pconfig['daily_working_hours'];
			$q->clear();

			$q = new DBQuery;
			$q->addTable('tasks', 't');
			$q->addJoin('user_tasks', 'u', 't.task_id = u.task_id', 'inner');
			$q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2) AS wh');
			$q->addWhere('t.task_duration_type = 1');
			$q->addWhere('t.task_id = ' . (int)$a['task_id']);

			$wh2 = $q->loadResult();
			$work_hours += $wh2;
			$q->clear();
			//due to the round above, we don't want to print decimals unless they really exist
			$dur = $work_hours;
		}

		$dur .= ' h';
		$enddate = new CDate($end);
		$startdate = new CDate($start);
        $height = ($a['task_dynamic'] == 1) ? 0.1 : 0.6;
		if ($caller == 'todo') {
            $columnValues = array('task_name' => $name, 'project_name' => $pname,
              'duration' => $dur, 'start_date' => $start, 'end_date' => $end,
              'actual_end' => $end);
		} else {
            $columnValues = array('task_name' => $name, 'duration' => $dur,
              'start_date' => $start, 'end_date' => $end, 'actual_end' => $end);
		}
        $gantt->addBar($columnValues, $caption, $height, '8F8FBD', true, $progress, $a['task_id']);
	}
	$q->clear();
}

$gantt->render();