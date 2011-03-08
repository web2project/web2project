<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $caller, $locale_char_set, $showWork, $sortByName, $showLabels, 
    $gantt_arr, $showPinned, $showArcProjs, $showHoldProjs, $showDynTasks,
    $showLowTasks, $user_id, $w2Pconfig, $project_id;
global $show_days, $start_date_min, $end_date_max, $day_diff;
global $gtask_sliced, $printpdfhr, $showNoMilestones;

w2PsetExecutionConditions($w2Pconfig);

$f = w2PgetParam($_REQUEST, 'f', 0);

$showLabels = (int) w2PgetParam($_REQUEST, 'showLabels', 0);
$showWork = (int) w2PgetParam($_REQUEST, 'showWork', 0);

$showPinned = (int) w2PgetParam( $_REQUEST, 'showPinned', false );
$showArcProjs = (int) w2PgetParam( $_REQUEST, 'showArcProjs', false );
$showHoldProjs = (int) w2PgetParam( $_REQUEST, 'showHoldProjs', false );
$showDynTasks = (int) w2PgetParam( $_REQUEST, 'showDynTasks', false );
$showLowTasks = (int) w2PgetParam( $_REQUEST, 'showLowTasks', true);

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
    $q->leftJoin('user_task_pin', 'tp', 'tp.task_id = t.task_id and tp.user_id = ' . (int) $user_id);
    $q->addWhere('(t.task_percent_complete < 100 OR t.task_percent_complete IS NULL)');
    $q->addWhere('t.task_status = 0');
    if (!$showArcProjs) {
        $q->addWhere('pr.project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('pr.project_status <> ' . (int) $template_status);
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
    $q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date,' .
            ' task_duration, task_duration_type, task_priority, task_percent_complete,' .
            ' task_order, task_project, task_milestone, project_name, project_color_identifier,' .
            ' task_dynamic');
    $q->addJoin('projects', 'p', 'project_id = t.task_project', 'inner');

    if ($project_id) {
        $q->addWhere('task_project = ' . (int) $project_id);
    }

    switch ($f) {
        case 'all':
            $q->addWhere('task_status > -1');
            break;
        case 'myproj':
            $q->addWhere('task_status > -1');
            $q->addWhere('project_owner = ' . (int) $AppUI->user_id);
            break;
        case 'mycomp':
            $q->addWhere('task_status > -1');
            $q->addWhere('project_company = ' . (int) $AppUI->user_company);
            break;
        case 'myinact':
            $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
            $q->addWhere('ut.user_id = ' . $AppUI->user_id);
            break;
        default:
            $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
            $q->addWhere('ut.user_id = ' . $AppUI->user_id);
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

unset($proTasks);
foreach ($projects as $p) {
    global $parents, $task_id;
    $parents = array();
    $tnums = count($p['tasks']);

    for ($i = 0; $i < $tnums; $i++) {
        $t = $p['tasks'][$i];
        if (!(isset($parents[$t['task_parent']]))) {
            $parents[$t['task_parent']] = false;
        }
        if ($t['task_parent'] == $t['task_id']) {
            showgtask($t);
            findgchild($p['tasks'], $t['task_id']);
        }
    }
}

$width = 1600;
$start_date = w2PgetParam($_GET, 'start_date', $start_min);
$end_date = w2PgetParam($_GET, 'end_date', $end_max);
$s1 = ($start_date) ? new w2p_Utilities_Date($start_date) : new w2p_Utilities_Date();
$e1 = ($end_date) ? new w2p_Utilities_Date($end_date) : new w2p_Utilities_Date();

//consider critical (concerning end date) tasks as well
if ($caller != 'todo') {
    $start_min = $projects[$project_id]['project_start_date'];
    $end_max = (($projects[$project_id]['project_end_date'] > $criticalTasks[0]['task_end_date'])
                            ? $projects[$project_id]['project_end_date'] : $criticalTasks[0]['task_end_date']);
}

$count = 0;

/*
* 	Prepare Gantt_chart loop
*/
$gtask_sliced = array() ;
$gtask_sliced = smart_slice( $gantt_arr, $showNoMilestones, $printpdfhr, $e1->dateDiff($s1) );
$page = 0 ;					// Numbering of output files
$outpfiles = array();		// array of output files to be returned to caller
$taskcount = 0 ;
// Create task_index array
$ctflag = false;
if (count($gtask_sliced) > 1) {
    for ($i = 0; $i < count($gantt_arr); $i++) {
        $task_index[$gantt_arr[$i][0]['task_id']] = $i + 1;
    }
    $ctflag = true;
}

foreach ($gtask_sliced as $gts) {
    $gantt = new w2p_Output_GanttRenderer($AppUI, $width);
    $gantt->localize();

    $field = ($showWork == '1') ? 'Work' : 'Dur';

    if ($showTaskNameOnly == '1') {
        $columnNames = array('Task name');
        $columnSizes = array(600);
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
    for ($i = 0; $i < count($gts); $i++) {
        $a = $gts[$i][0];
        $level = $gts[$i][1];
        $name = $a['task_name'];
        $name = ((mb_strlen($name) > 34) ? (mb_substr($name, 0, 30) . '...') : $name);
        $name = str_repeat(' ', $level) . $name;

        $pname = $a['project_name'];
        $pname = (mb_strlen($pname) > 25) ? (mb_substr($pname, 0, 20) . '...') : $pname;

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
            $q->addWhere('ut.task_id = ' . (int) $a['task_id']);
            $res = $q->loadList();
            foreach ($res as $rw) {
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
                if ($a['task_percent_complete'] == 100) {
                    $color = '#006600';
                } else {
                    if (strtotime($mile_date) < strtotime($today_date)) {
                        $color = '#990000';
                    } else {
                        if ($a['task_percent_complete'] == 0) {
                            $color = '#0000FF';
                        } else {
                            $color = '#FF9900';
                        }
                    }
                }
                $gantt->addMilestone($fieldArray, $a['task_start_date'], $color);
            } //this closes the code that is not processed if hide milestones is checked ///////////////
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
                $q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
                $q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2) AS wh');
                $q->addWhere('t.task_duration_type = 24');
                $q->addWhere('t.task_id = ' . (int) $a['task_id']);

                $wh = $q->loadResult();
                $work_hours = $wh * $w2Pconfig['daily_working_hours'];
                $q->clear();

                $q->addTable('tasks', 't');
                $q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
                $q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2) AS wh');
                $q->addWhere('t.task_duration_type = 1');
                $q->addWhere('t.task_id = ' . (int) $a['task_id']);

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
    unset($gts);

    $filename = W2P_BASE_DIR . "/files/temp/GanttPDF" . md5(time()) . ".png";
    // Prepare Gantt image and store in $filename
    $gantt->render(true, $filename);
    $outpfiles[] = $filename;
    $page++;
}

//Override of some variables, not very tidy but necessary when importing code from other sources...
$skip_page = 0;
$do_report = 1;
$show_task = 1;
$show_assignee = 1;
$show_gantt = 1;
$show_gantt_taskdetails = ($showTaskNameOnly == '1') ? 0 : 1;
$ganttfile = $outpfiles;

$temp_dir = W2P_BASE_DIR . '/files/temp';

$gpdfkey = W2P_BASE_DIR. '/modules/tasks/images/ganttpdf_key.png';
$gpdfkeyNM = W2P_BASE_DIR. '/modules/tasks/images/ganttpdf_keyNM.png';

$pdf = new w2p_Output_PDF_Gantt('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(14, 20, 14, true); // left, top, right
$pdf->setHeaderMargin(10);
$pdf->setFooterMargin(20);

$pdf->header_project_name = $projects[$project_id]['project_name'];
$date = new w2p_Utilities_Date();
$pdf->header_date = $date->format($df);

$pdf->SetFont('freeserif', '', 12);

$pdf->AddPage();

$next_image_y = 30;

for ($i=0; $i < count($ganttfile); $i++) {
    $gf = $ganttfile[0];
    $pdf->Image($gf, '', $next_image_y, 0, 0, '', '', ' ', true, 300, '', false, false, 0, false, false, true);
    
    $next_image_y = $pdf->getImageRBY();

    if ($showNoMilestones == '1') {
        $pdf->Image($gpdfkeyNM, '', $next_image_y, 0, 0, '', '', '', true, 300, '', false, false, 0, false, false, true);
    } else {
        $pdf->Image($gpdfkey, '', $next_image_y, 0, 0, '', '', '', true, 300, '', false, false, 0, false, false, true);
    }
    $next_image_y = $pdf->getImageRBY();
}

$gpdffile = $temp_dir . '/GanttChart_'.md5(time()).'.pdf';
if ($fp = fopen($gpdffile, 'wb')) {
    fwrite($fp, $pdf->Output('ganttchart.pdf', 'S'));
    fclose($fp);
} else {
    //TODO: create error handler for permission problems
    echo "Could not open file to save PDF.  ";
    if (!is_writable( $temp_dir ))
    echo "The files/temp directory is not writable.  Check your file system permissions.";
}

$_POST['printpdf'] = '0';
$printpdf = '0';
$_POST['printpdfhr']= 0;
$printpdfhr = 0;

// check that file exists and is readable
if (file_exists($gpdffile) && is_readable($gpdffile)) {
    // get the file size and send the http headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($gpdffile));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($gpdffile));
    header('Content-disposition: attachment; filename="GanttChart_'.$AppUI->user_id.$project_id.'.pdf"');
    flush();
    ob_end_clean();
    readfile($gpdffile);
    exit;
}