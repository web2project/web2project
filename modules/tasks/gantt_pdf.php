<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

global $gantt_arr, $w2Pconfig, $gtask_sliced, $printpdfhr, $showNoMilestones;


w2PsetExecutionConditions($w2Pconfig);

$project_id = (int) w2PgetParam($_REQUEST, 'project_id', 0);
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

$task = new CTask();

if ($caller == 'todo') {
	$user_id = w2PgetParam($_REQUEST, 'user_id', $AppUI->user_id);

	$projects[$project_id]['project_name'] = $AppUI->_('Todo for') . ' ' . CContact::getContactByUserid($user_id);
	$projects[$project_id]['project_color_identifier'] = 'ff6000';

    $proTasks = __extract_from_gantt_pdf3($user_id, $showArcProjs, $showLowTasks, $showHoldProjs, $showDynTasks, $showPinned, $task, $AppUI);
} else {
    $proTasks = __extract_from_gantt_pdf4($project_id, $f, $AppUI, $task);

}

// get any specifically denied tasks

$orrarr[] = array('task_id' => 0, 'order_up' => 0, 'order' => '');

$end_max = '0000-00-00 00:00:00';
$start_min = date('Y-m-d H:i:s');

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

unset($proTasks);
foreach ($projects as $p) {
    global $parents, $task_id;
    $parents = array();
    $tnums = count($p['tasks']);

    for ($i=0; $i < $tnums; $i++) {
        $t = $p['tasks'][$i];
        if (!(isset($parents[$t['task_parent']]))) {
            $parents[$t['task_parent']] = false;
        }
        if ($t['task_parent'] == $t['task_id']) {
            showgtask($t);
            findchild_gantt($p['tasks'], $t['task_id']);
        }
    }
}

$width = 1600;
$gantt_start_date = w2PgetParam($_GET, 'start_date', $start_min);
$gantt_end_date = w2PgetParam($_GET, 'end_date', $end_max);

$s1 = ($gantt_start_date) ? new w2p_Utilities_Date($gantt_start_date) : new w2p_Utilities_Date();
$e1 = ($gantt_end_date) ? new w2p_Utilities_Date($gantt_end_date) : new w2p_Utilities_Date();

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
$gtask_sliced = dumb_slice($gantt_arr, 30);// smart_slice( $gantt_arr, $showNoMilestones, $printpdfhr, $e1->dateDiff($s1) );
$page = 0 ;					// Numbering of output files
$outpfiles = array();		// array of output files to be returned to caller

foreach ($gtask_sliced as $gts) {
    if (!$gantt_start_date || !$gantt_end_date) {
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
            $columnSizes = array(250, 60, 90, 90);
        }
    }
    $gantt->setColumnHeaders($columnNames, $columnSizes);
    $gantt->setProperties(array('showhgrid' => true));
    $gantt->setDateRange($gantt_start_date, $gantt_end_date);

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
                findchild_gantt($p['tasks'], $t['task_id']);
            }
        }
    }
    $gantt->loadTaskArray($gantt_arr);

    $row = 0;
    $gts_count = count($gts);
    for($i = 0; $i < $gts_count; $i ++) {
        $a = $gts[$i][0];
        $level = $gts[$i][1];
        $name = $a['task_name'];
        $name = ((mb_strlen($name) > 34) ? (mb_substr($name, 0, 30) . '...') : $name);
        $name = str_repeat('  ', $level) . $name;

        if ($caller == 'todo') {
            $pname = $a['project_name'];
            $pname = ((mb_strlen($pname) > 20) ? (mb_substr($pname, 0, 14) . '...' . mb_substr($pname, -5, 5)) : $pname);
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
            } //this closes the code that is not processed if hide milestones is checked ///////////////
        } else {
            $type = $a['task_duration_type'];
            $dur = $a['task_duration'];
            if ($type == 24) {
                $dur *= $w2Pconfig['daily_working_hours'];
            }

            if ($showWork == '1') {
                $work_hours = 0;
                $wh = __extract_from_gantt_pdf($a);

                $work_hours = $wh * $w2Pconfig['daily_working_hours'];

                $wh2 = __extract_from_gantt_pdf2($a);

                $work_hours += $wh2;

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
    }
    unset($gts);

    $filename = W2P_BASE_DIR."/files/temp/GanttPNG_".md5(time())."_$page.png";

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

// Initialize PDF document 
$font_dir = W2P_BASE_DIR . '/lib/ezpdf/fonts';
$temp_dir = W2P_BASE_DIR . '/files/temp';

$output = new w2p_Output_PDFRenderer('A4', 'landscape');
$pdf = $output->getPDF();

/*
 * 		Define page header to be displayed on top of each page
 */
$pdf->saveState();
if ( $skip_page ) $pdf->ezNewPage();
$skip_page++;
$page_header = $pdf->openObject();
$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
$ypos= $pdf->ez['pageHeight'] - ( 30 + $pdf->getFontHeight(12) );
$doc_title = strEzPdf( $projects[$project_id]['project_name'], UI_OUTPUT_RAW);
$pwidth=$pdf->ez['pageWidth'];
$xpos= round( ($pwidth - $pdf->getTextWidth( 12, $doc_title ))/2, 2 );
$pdf->addText( $xpos, $ypos, 12, $doc_title) ;
$pdf->selectFont( "$font_dir/Helvetica.afm" );
$date = new w2p_Utilities_Date();
$xpos = round( $pwidth - $pdf->getTextWidth( 10, $date->format($df)) - $pdf->ez['rightMargin'] , 2);
$doc_date = strEzPdf($date->format( $df ));
$pdf->addText( $xpos, $ypos, 10, $doc_date );
$pdf->closeObject($page_header);
$pdf->addObject($page_header, 'all');
$gpdfkey = W2P_BASE_DIR. '/modules/tasks/images/ganttpdf_key.png';
$gpdfkeyNM = W2P_BASE_DIR. '/modules/tasks/images/ganttpdf_keyNM.png';

$pdf->ezStartPageNumbers( 802 , 30 , 10 ,'left','Page {PAGENUM} of {TOTALPAGENUM}') ;
$ganttfile_count = count($ganttfile);
for ($i=0; $i < $ganttfile_count; $i++) {
    $gf = $ganttfile[$i];
    $pdf->ezColumnsStart(array('num' =>1, 'gap' =>0));
    $pdf->ezImage( $gf, 0, 765, 'width', 'left'); // No pad, width = 800px, resize = 'none' (will go to next page if image height > remaining page space)
    if ($showNoMilestones == '1') {
        $pdf->ezImage( $gpdfkeyNM, 0, 500, 'width', 'center');
    } else {
        $pdf->ezImage( $gpdfkey, 0, 500, 'width', 'center');
    }
    $pdf->ezColumnsStop();
}
// End of project display
// Create document body and pdf temp file
$pdf->stopObject($page_header);
$gpdffile = $temp_dir . '/GanttChart_'.md5(time()).'.pdf';
if ($fp = fopen($gpdffile, 'wb')) {
    fwrite($fp, $pdf->ezOutput());
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