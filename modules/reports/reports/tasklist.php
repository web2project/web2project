<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

/**
 * Generates a report of the task logs for given dates
 */
$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_all = w2PgetParam($_POST, 'log_all', 0);
$log_pdf = w2PgetParam($_POST, 'log_pdf', 0);
$log_ignore = w2PgetParam($_POST, 'log_ignore', 0);
$days = w2PgetParam($_POST, 'days', 30);

$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);

$period = w2PgetParam($_POST, 'period', 0);
$period_value = w2PgetParam($_POST, 'pvalue', 1);
if ($period) {
	$today = new w2p_Utilities_Date();
	$ts = $today->format(FMT_TIMESTAMP_DATE);
	if (strtok($period, ' ') == $AppUI->_('Next')) {
		$sign = + 1;
	} else {
		$sign = -1;
	}

	$day_word = strtok(' ');
	if ($day_word == $AppUI->_('Day')) {
		$days = $period_value;
	} elseif ($day_word == $AppUI->_('Week')) {
		$days = 7 * $period_value;
	} elseif ($day_word == $AppUI->_('Month')) {
		$days = 30 * $period_value;
	}

	$start_date = new w2p_Utilities_Date($ts);
	$end_date = new w2p_Utilities_Date($ts);

	if ($sign > 0) {
		$end_date->addSpan(new Date_Span(array($days,0,0,0)));
	} else {
		$start_date->subtractSpan(new Date_Span(array($days,0,0,0)));
	}

	$do_report = 1;

} else {
	// create Date objects from the datetime fields
	$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
	$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();
}

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

echo $AppUI->getTheme()->styleRenderBoxTop();

$df = $AppUI->getPref('SHDATEFORMAT');
?>
<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
    <input type="hidden" name="datePicker" value="log" />

    <table class="std">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Default Actions'); ?>:</td>
            <td nowrap="nowrap">
                <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Previous Month'); ?>" />
                <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Previous Week'); ?>" />
                <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Previous Day'); ?>" />
            </td>
            <td nowrap="nowrap">
                <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Next Day'); ?>" />
                <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Next Week'); ?>" />
                <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Next Month'); ?>" />
            </td>
            <td><input class="text" type="field" size="2" name="pvalue" value="1" /> - <?php echo $AppUI->_('value for the previous buttons'); ?></td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
                <?php echo $AppUI->_('to'); ?>
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <td nowrap="nowrap">
                <input type="checkbox" name="log_all" id="log_all" <?php if ($log_all) echo 'checked="checked"' ?> />
                <label for="log_all"><?php echo $AppUI->_('Log All'); ?></label>
                <input type="checkbox" name="log_pdf" id="log_pdf" <?php if ($log_pdf) echo 'checked="checked"' ?> />
                <label for="log_pdf"><?php echo $AppUI->_('Make PDF'); ?></label>
                <input class="button" style="float: right;" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
            </td>
        </tr>
    </table>
</form>
<?php
if ($do_report) {

    echo $AppUI->getTheme()->styleRenderBoxBottom();
	echo '<br />';
    echo $AppUI->getTheme()->styleRenderBoxTop();
	echo '<table class="std">
<tr>
	<td>';

	echo '<table cellspacing="1" cellpadding="4" border="0" class="tbl"><tr>';
	if ($project_id == 0) {
		echo '<th>Project Name</th>';
	}
    echo '<th>Task Name</th>';
	echo '<th width=400>Task Description</th>';
	echo '<th>Assigned To</th>';
	echo '<th>Task Start Date</th>';
	echo '<th>Task End Date</th>';
	echo '<th>Completion</th></tr>';

	$pdfdata = array();
	$columns = array('<b>' . $AppUI->_('Task Name') . '</b>', '<b>' . $AppUI->_('Task Description') . '</b>', '<b>' . $AppUI->_('Assigned To') . '</b>', '<b>' . $AppUI->_('Task Start Date') . '</b>', '<b>' . $AppUI->_('Task End Date') . '</b>', '<b>' . $AppUI->_('Completion') . '</b>');
	if ($project_id == 0) {
		array_unshift($columns, '<b>' . $AppUI->_('Project Name') . '</b>');
	}

    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    if ($project_id == 0) {
        $myProject = new CProject();
        $projects = $myProject->getAllowedProjects($AppUI->user_id);
        $project_ids = array_keys($projects);
    } else {
        $project_ids = array($project_id);
    }

    $obj = new CTask();

    foreach ($project_ids as $project_id) {
        $taskTree = $obj->getTaskTree($project_id, 0);
        foreach($taskTree as $task) {
            $str = '<tr>';
            if (count($project_ids) > 1) {
                $str .= '<td>' . $task['project_name'] . '</td>';
            }
            $str .= '<td>';

            $indent_count = substr_count($task['task_path_enumeration'], '/') * 3;
            $str .= ($task['task_id'] == $task['task_parent']) ? '' : str_repeat('&nbsp;', $indent_count) . '<img src="' . w2PfindImage('corner-dots.gif') . '" />';
            $str .= '&nbsp;<a href="?m=tasks&a=view&task_id=' . $task['task_id'] . '">' . $task['task_name'] . '</a></td>';
            $str .= '<td>' . nl2br($task['task_description']) . '</td>';

            $users = array();
            $assignees = $obj->assignees($task['task_id']);
            foreach($assignees as $assignee) {
                $users[] = $assignee['contact_name'];
            }
            $str .= '<td>' . implode($users, ', ') . '</td>';

            $str .= $htmlHelper->createCell('task_start_date', $task['task_start_date']);
            $str .= $htmlHelper->createCell('task_end_date', $task['task_end_date']);
            $str .= $htmlHelper->createCell('task_percent_complete', $task['task_percent_complete']);
            $str .= '</tr>';
            echo $str;

            if ($project_id == 0) {
                $pdfdata[] = array($task['project_name'], $task['task_name'], $task['task_description'], $users, (($start_date != ' ') ? $start_date->format($df) : ' '), (($end_date != ' ') ? $end_date->format($df) : ' '), $task['task_percent_complete'] . '%', );
            } else {
                $start_date = new w2p_Utilities_Date($task['task_start_date']);
                $end_date = new w2p_Utilities_Date($task['task_end_date']);
                $spacer = str_repeat('  ', $task['depth']);
                $pdfdata[] = array($spacer . $task['task_name'], $task['task_description'],
                    implode($users, ', '),
                    (($start_date != ' ') ? $start_date->format($df) : ' '),
                    (($end_date != ' ') ? $end_date->format($df) : ' '),
                    $task['task_percent_complete'] . '%', );
            }
        }
    }

	echo '</table>';
	if ($log_pdf) {
		// make the PDF file
        $project = new CProject();
        $project->load((int)$project_id);
		$pname = $project->project_name;

		$temp_dir = W2P_BASE_DIR . '/files/temp';

        $output = new w2p_Output_PDFRenderer('A4', 'landscape');
        $output->addTitle($AppUI->_('Project Task Report'));
        $output->addDate($df);
        $output->addSubtitle(w2PgetConfig('company_name'));
        if ($project_id != 0) {
            $output->addSubtitle($pname);
        }

        $subhead = '';
		if ($log_all) {
            $title = $AppUI->_('All task entries');
		} else {
			if ($end_date != ' ') {
                $title = $AppUI->_('Task entries from') . ' ' . $start_date->format($df) .
                    $AppUI->_('to') . ' ' . $end_date->format($df);
			} else {
                $title = $AppUI->_('Task entries from') . ' ' . $start_date->format($df);
			}
		}

		$options = array('showLines' => 2, 'showHeadings' => 1, 'fontSize' => 9,
            'rowGap' => 4, 'colGap' => 5, 'xPos' => 50, 'xOrientation' => 'right',
            'width' => '750', 'shaded' => 0,
            'cols' => array(array('justification' => 'left', 'width' => 225),
                            array('justification' => 'left', 'width' => 225),
                            array('justification' => 'left', 'width' => 80),
                            array('justification' => 'center', 'width' => 80),
                            array('justification' => 'center', 'width' => 80),
                            array('justification' => 'center', 'width' => 70)));

        $output->addTable($title, $columns, $pdfdata, $options);

        $w2pReport = new CReport();
        if ($output->writeFile($w2pReport->getFilename())) {
            echo '<a href="' . W2P_BASE_URL . '/files/temp/' . $w2pReport->getFilename() . '.pdf" target="pdf">';
            echo $AppUI->_('View PDF File');
            echo '</a>';
        } else {
            echo 'Could not open file to save PDF.  ';
            if (!is_writable($temp_dir)) {
                'The files/temp directory is not writable.  Check your file system permissions.';
            }
        }
	}
	echo '</td>
</tr>
</table>';
}