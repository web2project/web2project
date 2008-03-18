<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Pedro A.
// The next lines tries to increase the processing time for php to render the image, that might be usefull when the system has
// several projects.
ini_set('max_execution_time', 180);
ini_set('memory_limit', $w2Pconfig['reset_memory_limit']);

include ($AppUI->getLibraryClass('jpgraph/src/jpgraph'));
include ($AppUI->getLibraryClass('jpgraph/src/jpgraph_gantt'));

global $AppUI, $company_id, $dept_ids, $department, $locale_char_set, $proFilter, $projectStatus, $showInactive, $showLabels, $showAllGantt, $user_id, $project_id, $project_original_id;

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

$pjobj = &new CProject;
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
//bl
$q->addOrder('project_name, task_start_date DESC');

//$projects = $q->loadList();
//print_r($q->prepare());
$projects = $q->loadHashList('project_id');
$q->clear();

$width = w2PgetParam($_GET, 'width', 600);
$start_date = w2PgetParam($_GET, 'start_date', 0);
$end_date = w2PgetParam($_GET, 'end_date', 0);

$showAllGantt = w2PgetParam($_REQUEST, 'showAllGantt', '1');

$graph = new GanttGraph($width);
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);

$graph->SetFrame(false);
$graph->SetBox(true, array(0, 0, 0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

/*$jpLocale = w2PgetConfig( 'jpLocale' );

if ($jpLocale) {

$graph->scale->SetDateLocale( $jpLocale );

}

** the jpgraph date locale is now set

** automatically by the user's locale settings

*/

//$graph->scale->SetDateLocale( $AppUI->user_lang[0] );

if ($start_date && $end_date) {
	$graph->SetDateRange($start_date, $end_date);
}

// Pedro A.
//
// The SetFont method changes the related bars caption text style and it will have all bars from its calling through all below
// until a new SetFont call is executed again on the same object e subobjects.
//
// LOGIC: $graph->scale->actinfo->SetFont(font name, font style, font size);
// EXAMPLE: $graph->scale->actinfo->SetFont(FF_CUSTOM, FS_BOLD, 10);
//
// Here is a list of possibilities you can use for the first parameter of the SetFont method:
// TTF Font families (you must have them installed to use them):
// FF_COURIER, FF_VERDANA, FF_TIMES, FF_COMIC, FF_CUSTOM, FF_GEORGIA, FF_TREBUCHE
// Internal fonts:
// FF_FONT0, FF_FONT1, FF_FONT2
//
// For the second parameter you have the TTF font style that can be:
// FS_NORMAL, FS_BOLD, FS_ITALIC, FS_BOLDIT, FS_BOLDITALIC
//

// Pedro A.
// This one will affect the captions of the columns on the left side, where you have the projects/tasks and dates
$graph->scale->actinfo->SetFont(FF_CUSTOM, FS_NORMAL, 8);
$graph->scale->actinfo->vgrid->SetColor('gray');
$graph->scale->actinfo->SetColor('darkgray');

//bl

//$graph->scale->actinfo->SetColTitles(array( $AppUI->_('Project name', UI_OUTPUT_RAW), $AppUI->_('Start Date', UI_OUTPUT_RAW), $AppUI->_('Proj. End', UI_OUTPUT_RAW), $AppUI->_('Actual End', UI_OUTPUT_RAW)),array(160,10, 70,70));

//$graph->scale->actinfo->SetColTitles(array( $AppUI->_('Project Name', UI_OUTPUT_RAW), $AppUI->_('Start Date', UI_OUTPUT_RAW), ),array(180,10));
$graph->scale->actinfo->SetColTitles(array($AppUI->_('Project name', UI_OUTPUT_RAW), $AppUI->_('Start Date', UI_OUTPUT_RAW), $AppUI->_('Finish', UI_OUTPUT_RAW), $AppUI->_('Actual End', UI_OUTPUT_RAW)), array(160, 10, 70, 70));

$original_project = new CProject();
$original_project->load($original_project_id);
$tableTitle = $original_project->project_name . ': ' . $AppUI->_('Multi-Project Gantt');
$graph->scale->tableTitle->Set($tableTitle);

// Use TTF font if it exists
// try commenting out the following two lines if gantt charts do not display
if (is_file(TTF_DIR . "FreeSans.ttf")) { // Pedro A.
	// This one will affect the title of the graph if you'd want to change its font you'd do something like:
	//    $graph->scale->tableTitle->SetFont(FF_FONT2);
	$graph->scale->tableTitle->SetFont(FF_CUSTOM, FS_BOLD, 10);
}

$graph->scale->SetTableTitleBackground('#eeeeee');
$graph->scale->tableTitle->Show(true);

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
	$graph->SetDateRange($start_date, $end_date);
} else {
	// find out DateRange from gant_arr
	$d_start = new CDate();
	$d_end = new CDate();
	$i = 0;
	foreach ($projects as $project) {
		$start = substr($project["project_start_date"], 0, 10);
		$end = substr($project["project_actual_end_date"], 0, 10);
		($start == '' || $start == null || $start == '0000-00-00') ? $d_start->Date() : $d_start->Date($start);
		($end == '' || $end == null || $end == '0000-00-00') ? $d_end->Date() : $d_end->Date($end);
		if ($i == 0) {
			$min_d_start = $d_start;
			$max_d_end = $d_end;
		} else {
			if (Date::compare($min_d_start, $d_start) > 0) {
				$min_d_start = $d_start;
			}
			if (Date::compare($max_d_end, $d_end) < 0) {
				$max_d_end = $d_end;
			}
		}
		$i++;
	}
}

// check day_diff and modify Headers
$day_diff = $min_d_start->dateDiff($max_d_end);
//print_r($projects);
//print_r($min_d_start);print_r($max_d_end);die;

if ($day_diff > 120 || !$day_diff) {
	//more than 120 days
	$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
} elseif ($day_diff > 60) {
	//more than 60 days and less of 120
	$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HWEEK);
	$graph->scale->week->SetStyle(WEEKSTYLE_WNBR);
}

$row = 0;
if (!is_array($projects) || sizeof($projects) == 0) {
	$d = new CDate();
	$bar = new GanttBar($row++, array(' ' . $AppUI->_('No projects found'), ' ', ' ', ' '), $d->getDate(), $d->getDate(), ' ', 0.6);
	$bar->title->SetCOlor('red');
	$graph->Add($bar);
}

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
		$task = &new CTask;
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
		/* $handle = fopen ( 'gantt.txt', 'w');
		$data = print_r($projects, true);
		fwrite($handle, $data);
		fclose($handle);*/
		//This kludgy function echos children tasks as threads

		function showgtask(&$a, $level = 0, $project_id) {
			/* Add tasks to gantt chart */
			global $gantt_arr;
			$gantt_arr[$project_id][] = array($a, $level);
		}

		function findgchild(&$tarr, $parent, $level = 0) {
			global $projects;
			$level = $level + 1;
			$n = count($tarr);
			for ($x = 0; $x < $n; $x++) {
				if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
					showgtask($tarr[$x], $level, $tarr[$x]['project_id']);
					findgchild($tarr, $tarr[$x]['task_id'], $level, $tarr[$x]['project_id']);
				}
			}
		}

		reset($projects);
		//$p = &$projects[$project_id];
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

		/* $handle = fopen ( 'gantt2.txt', 'w');
		$data = print_r($gantt_arr, true);
		fwrite($handle, $data);
		fclose($handle);*/
	}

	foreach ($projects as $p) {
		if ($locale_char_set == 'utf-8' && function_exists('utf8_decode')) {
			// Pedro A.
			// Depending on the font size you may increase or decrease the ammount of characters displayed from the project name by changing the:
			// ...25...23... ratio
			// to
			// ...30...28...   //more or
			// ...20...18...   //less
			$name = strlen(utf8_decode($p['project_name'])) > 35 ? substr(utf8_decode($p['project_name']), 0, 33) . '...' : utf8_decode($p['project_name']);
		} else {
			//while using charset different than UTF-8 we need not to use utf8_deocde
			$name = strlen($p['project_name']) > 25 ? substr($p['project_name'], 0, 22) . 'xxx' : $p['project_name'];
		}
		//using new jpGraph determines using Date object instead of string
		$start = ($p['project_start_date'] > '0000-00-00 00:00:00') ? $p['project_start_date'] : date('Y-m-d H:i:s');
		$end_date = $p['project_end_date'];
		$end_date = new CDate($end_date);
		//	$end->addDays(0);
		$end = $end_date->getDate();
		$start = new CDate($start);
		//	$start->addDays(0);
		$start = $start->getDate();
		$progress = $p['project_percent_complete'] + 0;
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

		$bar = new GanttBar($row++, array($name, $startdate->format($df), $enddate->format($df), $actual_enddate->format($df)), $start, $actual_end, $cap, 0.6);
		$bar->progress->Set(min(($progress / 100), 1));

		// Pedro A.
		// This one will affect the style for the project names, alternative example:
		//      $bar->title->SetFont(FF_FONT1);
		$bar->title->SetFont(FF_CUSTOM, FS_BOLD, 7);
		$bar->SetFillColor('#' . $p['project_color_identifier']);
		$bar->SetPattern(BAND_SOLID, '#' . $p['project_color_identifier']);

		//adding captions
		$bar->caption = new TextProperty($caption);
		$bar->caption->Align('left', 'center');
		
		// Pedro A.
		// This one will affect the style for the caption of the projects status that appear on the right of the bar if they are selected to show, alternative example:
		//    $bar->title->SetFont(FF_FONT0);
		$bar->caption->SetFont(FF_CUSTOM, FS_NORMAL, 8);

		// gray out templates, completes, on ice, on hold
		if ($p['project_active'] < 1 || $p['project_percentage_complete'] > 99.9) {
			$bar->caption->SetColor('darkgray');
			$bar->title->SetColor('darkgray');
			$bar->SetColor('darkgray');
			$bar->SetFillColor('gray');
			//$bar->SetPattern(BAND_SOLID,'gray');
			$bar->progress->SetFillColor('darkgray');
			$bar->progress->SetPattern(BAND_SOLID, 'darkgray', 98);
		}

		$graph->Add($bar);
		// If showAllGant checkbox is checked

		if ($showAllGantt) {
			// insert tasks into Gantt Chart
			// cycle for tasks for each project
			//$row = 1;
			for ($i = 0, $i_cmp = count($gantt_arr[$p['project_id']]); $i < $i_cmp; $i++) {
				$t = $gantt_arr[$p['project_id']][$i][0];
				/* $handle = fopen ( 'gantt3.txt', 'a+');
				$data = print_r($t, true);
				fwrite($handle, $data);
				fclose($handle);*/
				$level = $gantt_arr[$p['project_id']][$i][1];
				// 		foreach($tasks as $t)
				// 		{
				if ($t['task_end_date'] == null) {
					$t['task_end_date'] = $t['task_start_date'];
				}
				$tStart = ($t['task_start_date'] > '0000-00-00 00:00:00') ? $t['task_start_date'] : date('Y-m-d H:i:s');
				$tEnd = ($t['task_end_date'] > '0000-00-00 00:00:00') ? $t['task_end_date'] : date('Y-m-d H:i:s');
				$tStartObj = new CDate($tStart);
				$tEndObj = new CDate($tEnd);

				if ($t['task_milestone'] != 1) {
					$advance = str_repeat('  ', $level);
					// Pedro A.
					// Depending on the font size you may increase or decrease the ammount of characters displayed from the task name by changing the:
					// ...25...23... ratio
					// to
					// ...30...28...   //more or
					// ...20...18...   //less

					//$bar2 = new GanttBar($row++, array((strlen($advance.$t['task_name']) > 35 ? substr($advance.$t['task_name'], 0, 33).'...' : $advance.$t['task_name']), $tStartObj->format($df),  $tEndObj->format($df), ' '), $tStart, $tEnd, ' ', $t['task_dynamic'] == 1 ? 0.1 : 0.6);
					$bar2 = new GanttBar($row++, array((strlen($advance . $t['task_name']) > 35 ? substr($advance . $t['task_name'], 0, 33) . '...' : $advance . $t['task_name']), $tStartObj->format($df), $tEndObj->format($df), ' '), $tStart, $tEnd, ' ', $t['task_dynamic'] == 1 ? 0.1 : 0.6);
					$bar2->title->SetColor(bestColor('#ffffff', '#' . $p['project_color_identifier'], '#000000'));

					// Pedro A.
					// This one will affect the style for the tasks names non milestones, alternative example:
					//                      $bar2->title->SetFont(FF_FONT0);
					$bar2->title->SetFont(FF_CUSTOM, FS_NORMAL, 7);
					$bar2->SetFillColor('#' . $p['project_color_identifier']);
					$graph->Add($bar2);
				} else {
					$bar2 = new MileStone($row++, '* ' . $t['task_name'], $t['task_start_date'], $tStartObj->format($df));
					$bar2->title->SetColor('#CC0000');
					// Pedro A.
					// This one will affect the style for the milestones tasks names, alternative example:
					//                      $bar2->title->SetFont(FF_FONT0);
					$bar2->title->SetFont(FF_CUSTOM, FS_NORMAL, 7);
					$graph->Add($bar2);
				}

				// Insert workers for each task into Gantt Chart - bl commented out
				/*$q  = new DBQuery;
				$q->addTable('user_tasks', 't');
				$q->addQuery('DISTINCT user_username, t.task_id');
				$q->addJoin('users', 'u', 'u.user_id = t.user_id', 'inner');
				$q->addWhere("t.task_id = ".$t["task_id"]);
				$q->addOrder('user_username ASC');
				$workers = $q->loadList();
				$q->clear();
				$workersName = '';
				foreach($workers as $w) {	
				$workersName .= ' '.$w['user_username'];

				$bar3 = new GanttBar($row++, array('   * '.$w['user_username'], ' ', ' ',' '), '0', '0;', 0.6);							
				$bar3->title->SetColor(bestColor( '#ffffff', '#'.$p['project_color_identifier'], '#000000' ));
				$bar3->SetFillColor('#'.$p['project_color_identifier']);		
				$graph->Add($bar3);
				}*/
				// End of insert workers for each task into Gantt Chart
			}
			// End of insert tasks into Gantt Chart
		}
		// End of if showAllGant checkbox is checked
	}
	/* $handle = fopen ( 'gantt4.txt', 'w');
	$data = print_r($graph, true);
	fwrite($handle, $data);
	fclose($handle);*/
} // End of check for valid projects array.

$today = date('y-m-d');
$vline = new GanttVLine($today, $AppUI->_('Today', UI_OUTPUT_RAW));
// Pedro A.
// This one will affect the style for the "Today" expression on the graphs bottom, alternative example:
// $vline->title->SetFont(FF_FONT0);
$vline->title->SetFont(FF_CUSTOM, FS_BOLD, 9);
$graph->Add($vline);
$graph->Stroke();
?>