<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $company_id, $dept_ids, $department, $locale_char_set, $proFilter, $projectStatus, $showInactive, $showLabels, $showAllGantt, $sortTasksByName, $user_id, $w2Pconfig;

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
$proFilter = w2PgetParam($_REQUEST, 'proFilter', '-1');
$company_id = w2PgetParam($_REQUEST, 'company_id', 0);
$department = w2PgetParam($_REQUEST, 'department', 0);
$showLabels = w2PgetParam($_REQUEST, 'showLabels', 0);
$showInactive = w2PgetParam($_REQUEST, 'showInactive', 0);
$sortTasksByName = w2PgetParam($_REQUEST, 'sortTasksByName', 0);
$addPwOiD = w2PgetParam($_REQUEST, 'addPwOiD', 0);

$pjobj = new CProject();
$working_hours = $w2Pconfig['daily_working_hours'];

/*
** Load department info for the case where one
** wants to see the ProjectsWithOwnerInDeparment (PwOiD)
** instead of the projects related to the given department.
*/
if ($addPwOiD && $department > 0) {
	$owner_ids = array();
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('user_id');
	$q->addJoin('contacts', 'c', 'c.contact_id = user_contact', 'inner');
	$q->addWhere('c.contact_department = ' . (int)$department);
	$owner_ids = $q->loadColumn();
	$q->clear();
}

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
if ($department > 0 && !$addPwOiD) {
	$q->addWhere('project_departments.department_id = ' . (int)$department);
}
if ($proFilter == '-3') {
	$q->addWhere('pr.project_owner = ' . (int)$user_id);
} elseif ($proFilter != '-1') {
	$q->addWhere('pr.project_status = ' . (int)$proFilter);
}
if (!($department > 0) && $company_id != 0 && !$addPwOiD) {
	$q->addWhere('pr.project_company = ' . (int)$company_id);
}
// Show Projects where the Project Owner is in the given department
if ($addPwOiD && !empty($owner_ids)) {
	$q->addWhere('pr.project_owner IN (' . implode(',', $owner_ids) . ')');
}

if ($showInactive != '1') {
	$q->addWhere('pr.project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('pr.project_status <> ' . $template_status);
	}
}
$pjobj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');
$q->addGroup('pr.project_id');
$q->addOrder('pr.project_name, task_end_date DESC');

$projects = $q->loadList();
$q->clear();

// Don't push the width higher than about 1200 pixels, otherwise it may not display.
$width = min(w2PgetParam($_GET, 'width', 600), 1400);
$start_date = w2PgetParam($_GET, 'start_date', 0);
$end_date = w2PgetParam($_GET, 'end_date', 0);

$showAllGantt = w2PgetParam($_REQUEST, 'showAllGantt', '0');

$gantt = new GanttRenderer($width);
$gantt->localize($AppUI);

$tableTitle = ($proFilter == '-1') ? $AppUI->_('All Projects') : $projectStatus[$proFilter];
$gantt->setTitle($tableTitle);

if (!$start_date || !$end_date) {
  // find out DateRange from $projects array
  $projectCount = count($projects);
  for ($i = 0, $i_cmp = $projectCount; $i < $i_cmp; $i++) {
  	$start = substr($projects[$i]['project_start_date'], 0, 10);
  	$end = substr($projects[$i]['project_end_date'], 0, 10);
  	if (0 == strlen($end)) {
  	  $lastTask = $pjobj->getCriticalTasks($projects[$i]['project_id']);
  	  $projects[$i]['project_actual_end_date'] = $lastTask[0]['task_end_date'];
  	  $projects[$i]['project_end_date'] = $lastTask[0]['task_end_date'];
  	  $end = substr($lastTask[0]['task_end_date'], 0, 10);
  	}

  	$d_start = new CDate($start);
  	$d_end = new CDate($end);
  
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
$gantt->setDateRange($start_date, $end_date);

$row = 0;

if (!is_array($projects) || 0 == count($projects)) {
  $d = new CDate();
  $gantt->addBar($AppUI, ' ' . $AppUI->_('No projects found'), '', $d->getDate(), $d->getDate(), ' ', 0.6, 'red');
} else {
	foreach ($projects as $p) {

		if ($locale_char_set == 'utf-8' && function_exists('utf8_decode')) {
			$name = strlen(utf8_decode($p['project_name'])) > 25 ? substr(utf8_decode($p['project_name']), 0, 22) . '...' : utf8_decode($p['project_name']);
		} else {
			//while using charset different than UTF-8 we need not to use utf8_decode
			$name = strlen($p['project_name']) > 25 ? substr($p['project_name'], 0, 22) . '...' : $p['project_name'];
		}

		//using new jpGraph determines using Date object instead of string
		$start = ($p['project_start_date'] > '1969-12-31 19:00:00') ? $p['project_start_date'] : '';
		$end_date = ($p['project_end_date'] > '1969-12-31 19:00:00') ? $p['project_end_date'] : $p['project_actual_end_date'];

		$end_date = new CDate($end_date);
		$end = $end_date->getDate();

		$start = new CDate($start);
		$start = $start->getDate();

		$progress = (int) $p['project_percent_complete'];

		$caption = '';
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
			$caption .= $p['project_active'] != 0 ? $AppUI->_('active') : $AppUI->_('archived');
		}
		$enddate = new CDate($end);
		$startdate = new CDate($start);
		$actual_end = intval($p['project_actual_end_date']) ? $p['project_actual_end_date'] : $end;

		$gantt->addBar($name, $start, $end, $actual_end, $caption, 0.6, $p['project_color_identifier'], $p['project_active'], $progress);

		// If showAllGant checkbox is checked
		if ($showAllGantt) {
			// insert tasks into Gantt Chart
			// select for tasks for each project

			$q = new DBQuery;
			$q->addTable('tasks');
			$q->addQuery('DISTINCT tasks.task_id, tasks.task_name, tasks.task_start_date, tasks.task_end_date, tasks.task_duration, tasks.task_duration_type, tasks.task_milestone, tasks.task_dynamic');
			$q->addJoin('projects', 'p', 'p.project_id = tasks.task_project');
			$q->addWhere('p.project_id = ' . (int)$p['project_id']);
			if ($sortTasksByName) {
				$q->addOrder('tasks.task_name');
			} else {
				$q->addOrder('tasks.task_end_date ASC');
			}
			$tasks = $q->loadList();
			$q->clear();
			foreach ($tasks as $t) {
				//Check if start date exists, if not try giving it the end date.
				//If the end date does not exist then set it for today.
				//This avoids jpgraphs internal errors that render the gantt completely useless
				if ($t['task_start_date'] == '0000-00-00 00:00:00') {
					if ($t['task_end_date'] == '0000-00-00 00:00:00') {
						$todaydate = new CDate();
						$t['task_start_date'] = $todaydate->format(FMT_TIMESTAMP_DATE);
					} else {
						$t['task_start_date'] = $t['task_end_date'];
					}
				}
						
				//Check if end date exists, if not try giving it the start date.
				//If the start date does not exist then set it for today.
				//This avoids jpgraphs internal errors that render the gantt completely useless
				if ($t['task_end_date'] == '0000-00-00 00:00:00') {
					if ($t['task_duration']) {
						$t['task_end_date'] = db_unix2dateTime(db_dateTime2unix($t['task_start_date']) + SECONDS_PER_DAY * convert2days($t['task_duration'], $t['task_duration_type']));
					} else {
						$todaydate = new CDate();
						$t['task_end_date'] = $todaydate->format(FMT_TIMESTAMP_DATE);
					}
				}

				$tStart = intval($t['task_start_date']) ? $t['task_start_date'] : $start;
				$tEnd = intval($t['task_end_date']) ? $t['task_end_date'] : $end;
				$tStartObj = new CDate($t['task_start_date']);
				$tEndObj = new CDate($t['task_end_date']);

				if ($t['task_milestone'] != 1) {
				  $gantt->addSubBar(substr(' --' . $t['task_name'], 0, 20). '...', 
				    $tStart, $tEnd, $caption, $t['task_dynamic'] == 1 ? 0.1 : 0.6, $p['project_color_identifier'], $progress);
				} else {
				  $gantt->addMilestone('-- ' . $t['task_name'], $t['task_start_date']);
				}

				// Insert workers for each task into Gantt Chart
				$q = new DBQuery;
				$q->addTable('user_tasks', 't');
				$q->addQuery('DISTINCT contact_first_name, contact_last_name, t.task_id');
				$q->addJoin('users', 'u', 'u.user_id = t.user_id', 'inner');
				$q->addJoin('contacts', 'c', 'c.contact_id = u.user_contact', 'inner');
				$q->addWhere('t.task_id = ' . (int)$t['task_id']);
				$q->addOrder('user_username ASC');
				$workers = $q->loadList();
				$q->clear();
				foreach ($workers as $w) {
				  $label = '   * ' . $w['contact_first_name'] . ' ' . $w['contact_last_name'];
				  //$gantt->addSubSubBar($label, $t['task_start_date'], $t['task_end_date']);
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