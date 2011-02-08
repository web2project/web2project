<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $caller, $locale_char_set, $showWork, $sortByName, $showLabels, 
    $gantt_arr, $showPinned, $showArcProjs, $showHoldProjs, $showDynTasks,
    $showLowTasks, $user_id, $w2Pconfig;
global $gantt_map, $currentGanttImgSource, $currentImageMap;

w2PsetExecutionConditions($w2Pconfig);

$f = w2PgetParam($_REQUEST, 'f', 0);
$project_id = (int) w2PgetParam($_REQUEST, 'project_id', 0);

$showLabels = (int) w2PgetParam($_REQUEST, 'showLabels', 0);
$sortByName = (int) w2PgetParam($_REQUEST, 'sortByName', 0);
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

// pull valid projects and their percent complete information
$projects = $project->getAllowedProjects($AppUI->user_id, false);

##############################################
/* gantt is called now by the todo page, too.
** there is a different filter approach in todo
** so we have to tweak a little bit,
** also we do not have a special project available
*/
$caller = w2PgetParam($_REQUEST, 'caller', null);

if ($caller == 'todo') {
	$user_id = w2PgetParam($_REQUEST, 'user_id', $AppUI->user_id);

	$projects[$project_id]['project_name'] = $AppUI->_('Todo for') . ' ' . CContact::getContactByUserid($user_id);
	$projects[$project_id]['project_color_identifier'] = 'ff6000';

	$q = new w2p_Database_Query;
	$q->addQuery('t.*');
	$q->addQuery('project_name, project_id, project_color_identifier');
	$q->addQuery('tp.task_pinned');
	$q->addTable('tasks', 't');
    $q->innerJoin('projects', 'pr', 'pr.project_id = t.task_project');
 	$q->leftJoin('user_tasks', 'ut', 'ut.task_id = t.task_id AND ut.user_id = ' . (int) $user_id);
	$q->leftJoin('user_task_pin', 'tp', 'tp.task_id = t.task_id and tp.user_id = ' . (int)$user_id);
	$q->addWhere('(t.task_percent_complete < 100 OR t.task_percent_complete IS NULL)');
	$q->addWhere('t.task_status = 0');
	if (!$showArcProjs) {
		$q->addWhere('pr.project_active = 1');
		if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
			$q->addWhere('pr.project_status <> ' . (int)$template_status);
		}
	}
	if (!$showLowTasks) {
		$q->addWhere('task_priority >= 0');
	}
	if (!$showHoldProjs) {
		$q->addWhere('project_active = 1');
	}
	if (!$showDynTasks) {
		$q->addWhere('task_dynamic <> 1');
	}
	if ($showPinned) {
		$q->addWhere('task_pinned = 1');
	}

    $q->addGroup('t.task_id');
    $q->addOrder('t.task_end_date, t.task_priority DESC');
} else {
	// pull tasks
	$q = new w2p_Database_Query();
	$q->addTable('tasks', 't');
	$q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date,'.
		' task_duration, task_duration_type, task_priority, task_percent_complete,'.
		' task_order, task_project, task_milestone, task_access, task_owner, '.
        ' project_name, project_color_identifier, task_dynamic');
	$q->addJoin('projects', 'p', 'project_id = t.task_project', 'inner');

    // don't add milestones if box is checked//////////////////////////////////////////////////////////
    if ($showNoMilestones) {
        $q->addWhere('task_milestone != 1');
    }
    if ($showMilestonesOnly) {
        $q->addWhere('task_milestone = 1');
    }
    if ($ganttTaskFilter) {
        $q->addWhere($where);
    }
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
			$q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
			$q->addWhere('ut.user_id = '.$AppUI->user_id);
			break;
		default:
			$q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
			$q->addWhere('ut.user_id = '.$AppUI->user_id);
			break;
	}

    $q->addOrder('p.project_id, t.task_end_date');
}

// get any specifically denied tasks
$task = new CTask();
$task->setAllowedSQL($AppUI->user_id, $q);
$proTasks = $q->loadHashList('task_id');
$q->clear();

$orrarr[] = array('task_id'=>0, 'order_up'=>0, 'order'=>'');
$end_max = '0000-00-00 00:00:00';
$start_min = date('Y-m-d H:i:s');

//pull the tasks into an array
if ($caller != 'todo') {
	$criticalTasks = $project->getCriticalTasks($project_id);
}

foreach ($proTasks as $row) {
	//Check if start date exists, if not try giving it the end date.
	//If the end date does not exist then set it for today.
	//This avoids jpgraphs internal errors that render the gantt completely useless
	if ($row['task_start_date'] == '0000-00-00 00:00:00') {
		if ($row['task_end_date'] == '0000-00-00 00:00:00') {
			$todaydate = new w2p_Utilities_Date();
			$row['task_start_date'] = $todaydate->format(FMT_TIMESTAMP_DATE);
		} else {
			$row['task_start_date'] = $row['task_end_date'];
		}
	}

	$tsd = new w2p_Utilities_Date($row['task_start_date']);
	if ($tsd->before(new w2p_Utilities_Date($start_min))) {
		$start_min = $row['task_start_date'];
	}

	//Check if end date exists, if not try giving it the start date.
	//If the start date does not exist then set it for today.
	//This avoids jpgraphs internal errors that render the gantt completely useless
	if ($row['task_end_date'] == '0000-00-00 00:00:00') {
		if ($row['task_duration']) {
			$row['task_end_date'] = db_unix2dateTime(db_dateTime2unix($row['task_start_date']) + SECONDS_PER_DAY * convert2days($row['task_duration'], $row['task_duration_type']));
		} else {
			$todaydate = new w2p_Utilities_Date();
			$row['task_end_date'] = $todaydate->format(FMT_TIMESTAMP_DATE);
		}
	}

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
if ($caller != 'todo') {
	$start_min = $projects[$project_id]['project_start_date'];
	$end_max = (($projects[$project_id]['project_end_date'] > $criticalTasks[0]['task_end_date']) 
				? $projects[$project_id]['project_end_date'] : $criticalTasks[0]['task_end_date']);
}

$count = 0;

// If hyperlinks are to be added then the graph is of a set width///////
if ($addLinksToGantt == '1') {
	$width = 1450 ;
}

$gantt = new w2p_Output_GanttRenderer($AppUI, $width);
$gantt->localize();
$gantt->setTitle($projects[$project_id]['project_name'], '#'.$projects[$project_id]['project_color_identifier']);
	$field = ($showWork == '1') ? 'Work' : 'Dur';

	if ($showTaskNameOnly == '1') {
	    $columnNames = array('Task name');
	    $columnSizes = array(300);
	} else {
		if ($caller == 'todo') {
			$columnNames = array('Task name', 'Project name', $field, 'Start', 'Finish');
			$columnSizes = array(180, 135, 40, 75, 75);
		} else {
			$columnNames = array('Task name', $field, 'Start', 'Finish');
			$columnSizes = array(250, 60, 80, 80);
		}
	}
	$gantt->setColumnHeaders($columnNames, $columnSizes);
	$gantt->setProperties(array('showhgrid' => true));

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
$gantt->setDateRange($start_date, $end_date);

reset($projects);
foreach ($projects as $p) {
	$parents = array();
    $tnums = count($p['tasks']);

	for ($i = 0; $i < $tnums; $i++) {
		$t = $p['tasks'][$i];
        if (!isset($parents[$t['task_parent']])) {
			$parents[$t['task_parent']] = false;
		}
		if ($t['task_parent'] == $t['task_id']) {
			$parents[$t['task_parent']] = true;
			showgtask($t);
			findgchild($p['tasks'], $t['task_id']);
		}
	}
}
$gantt->loadTaskArray($gantt_arr);

$row = 0;
for ($i = 0, $i_cmp = count($gantt_arr); $i < $i_cmp; $i++) {
    $a = $gantt_arr[$i][0];
    $level = $gantt_arr[$i][1];
	$caption = '';

    $canAccess = canTaskAccess($a['task_id'], $a['task_access'], $a['task_owner']);
    if ($canAccess) {
        $name = $a['task_name'];
        if ($locale_char_set == 'utf-8') {
            $name = utf8_decode($name);
        }
        $name = strlen($name) > 34 ? substr($name, 0, 33) . '.' : $name;
        $name = str_repeat(' ', $level) . $name;

        if ($caller == 'todo') {
            $pname = $a['project_name'];
            $pname = utf8_decode($pname);
            $pname = strlen($pname) > 14 ? substr($pname, 0, 5) . '...' . substr($pname, -5, 5) : $pname;
        }

        //using new jpGraph determines using Date object instead of string
        $start_date = new w2p_Utilities_Date($a['task_start_date']);
        $end_date = new w2p_Utilities_Date($a['task_end_date']);
        $start = $start_date->getDate();
		$end = $end_date->getDate();

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

        if ($showLabels == '1') {
            $q = new w2p_Database_Query;
            $q->addTable('user_tasks', 'ut');
            $q->innerJoin('users', 'u', 'u.user_id = ut.user_id');
            $q->innerJoin('contacts', 'c', 'c.contact_id = u.user_contact');
            $q->addQuery('ut.task_id, u.user_username, ut.perc_assignment');
            $q->addQuery('c.contact_first_name, c.contact_last_name');
            $q->addWhere('ut.task_id = ' . (int)$a['task_id']);
            $res = $q->loadList();
            foreach ($res as $rw) {
				$caption = '';
				switch ($rw['perc_assignment']) {
					case 100:
						$caption .= $rw['contact_first_name'] . ' ' . $rw['contact_last_name'] . ';';
						break;
					default:
						$caption .= $rw['contact_first_name'] . ' ' . $rw['contact_last_name'] . ' [' . $rw['perc_assignment'] . '%];';
						break;
				}
            }
            $q->clear();
            $caption = mb_substr($caption, 0, mb_strlen($caption) - 1);
        }

        if ($flags == 'm') {
            // if hide milestones is ticked this bit is not processed//////////////////////////////////////////
            if ($showNoMilestones != '1') {
                $start = new w2p_Utilities_Date($start_date);
                $start->addDays(0);
                $start_mile = $start->getDate();
                $s = $start_date->format($df);
                $today_date = date('m/d/Y');
                $today_date_stamp = strtotime($today_date);
                $mile_date = $start_date->format($df);
                $mile_date_stamp = strtotime($mile_date);
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
                    if (strtotime($mile_date) < strtotime($today_date)) {
                        $color = '#990000';
                    } else {
                        if ($a['task_percent_complete'] == 0)  {
                            $color = '#0000FF';
                        } else {
                            $color = '#FF9900';
                        }
                    }
                }
                $gantt->addMilestone($fieldArray, $a['task_start_date'], $color);
            }	//this closes the code that is not processed if hide milestones is checked ///////////////
        } else {
            $type = $a['task_duration_type'];
            $dur = $a['task_duration'];
            if ($type == 24) {
                $dur *= $w2Pconfig['daily_working_hours'];
            }

            if ($showWork == '1') {
                $work_hours = 0;
                $q = new w2p_Database_Query;
                $q->addTable('tasks', 't');
                $q->addJoin('user_tasks', 'u', 't.task_id = u.task_id', 'inner');
                $q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2) AS wh');
                $q->addWhere('t.task_duration_type = 24');
                $q->addWhere('t.task_id = ' . (int)$a['task_id']);

                $wh = $q->loadResult();
                $work_hours = $wh * $w2Pconfig['daily_working_hours'];
                $q->clear();

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
            $enddate = new w2p_Utilities_Date($end);
            $startdate = new w2p_Utilities_Date($start);
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
        $q->clear();
    }
}

$gantt->render();