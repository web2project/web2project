<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

/**
 * Generates a report of the task logs for given dates
 */
$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_all = w2PgetParam($_POST, 'log_all', 0);
$log_pdf = w2PgetParam($_POST, 'log_pdf', 0);
$log_ignore = w2PgetParam($_POST, 'log_ignore', 0);
$days = w2PgetParam($_POST, 'days', 30);

$list_start_date = w2PgetParam($_POST, 'list_start_date', 0);
$list_end_date = w2PgetParam($_POST, 'list_end_date', 0);

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
        $end_date->addSpan(new Date_Span("$days,0,0,0"));
    } else {
        $start_date->subtractSpan(new Date_Span("$days,0,0,0"));
    }

    $do_report = 1;

} else {
    // create Date objects from the datetime fields
    $start_date = intval($list_start_date) ? new w2p_Utilities_Date($list_start_date) : new w2p_Utilities_Date();
    $end_date = intval($list_end_date) ? new w2p_Utilities_Date($list_end_date) : new w2p_Utilities_Date();
}

if (!$list_start_date) {
    $start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

?>
<script language="javascript">
    function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'list_' + f_date );
	if (fld_date.value.length>0) {
            if ((parseDate(fld_date.value))==null) {
                alert('The Date/Time you typed does not match your prefered format, please retype.');
                fld_real_date.value = '';
                fld_date.style.backgroundColor = 'red';
            } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
        	fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
                fld_date.style.backgroundColor = '';
            }
	} else {
            fld_real_date.value = '';
	}
    }
</script>

<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />

<?php
if (function_exists('styleRenderBoxTop')) {
    echo styleRenderBoxTop();
}
?>
<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
        <td align="right"><?php echo $AppUI->_('Default Actions'); ?>:</td>
        <td nowrap="nowrap" colspan="2">
          <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Previous Month'); ?>" />
          <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Previous Week'); ?>" />
          <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Previous Day'); ?>" />
        </td>
        <td nowrap="nowrap">
          <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Next Day'); ?>" />
          <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Next Week'); ?>" />
          <input class="button" type="submit" name="period" value="<?php echo $AppUI->_('Next Month'); ?>" />
          </td>
        <td colspan="3"><input class="text" type="field" size="2" name="pvalue" value="1" /> - value for the previous buttons</td>
</tr>
<tr>

	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="list_start_date" id="list_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="list_end_date" id="list_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" id="log_all" <?php if ($log_all)
	echo 'checked="checked"' ?> />
		<label for="log_all"><?php echo $AppUI->_('Log All'); ?></label>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" id="log_pdf" <?php if ($log_pdf)
	echo 'checked="checked"' ?> />
		<label for="log_pdf"><?php echo $AppUI->_('Make PDF'); ?></label>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
	</td>
</tr>
</table>
</form>
<?php
if ($do_report) {

    if ($project_id == 0) {
        $q = new w2p_Database_Query;
        $q->addTable('tasks', 'a');
        $q->addTable('projects', 'b');
        $q->addQuery('a.*, b.project_name');
        $q->addWhere('a.task_project = b.project_id');
        $q->addWhere('b.project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('b.project_status <> ' . (int)$template_status);
        }
    } else {
        $q = new w2p_Database_Query;
        $q->addTable('tasks', 'a');
        $q->addWhere('task_project =' . $project_id);
    }
    if (!$log_all) {
        $q->addWhere('task_start_date >= \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\'');
        $q->addWhere('task_start_date <= \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\'');
    }

    $obj = new CTask();
    $allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
    if (count($allowedTasks)) {
        $obj->getAllowedSQL($AppUI->user_id, $q);
    }
    $q->addOrder('task_project', 'task_parent', 'task_start_date');
    $Task_List = $q->exec();

    if (function_exists('styleRenderBoxBottom')) {
        echo styleRenderBoxBottom();
    }
    echo '<br />';
    if (function_exists('styleRenderBoxTop')) {
        echo styleRenderBoxTop();
    }
    echo '<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td>';

    echo '<table cellspacing="1" cellpadding="4" border="0" class="tbl">';
    if ($project_id == 0) {
        echo '<tr><th>Project Name</th><th>Task Name</th>';
    } else {
        echo '<tr><th>Task Name</th>';
    }
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
    while ($Tasks = db_fetch_assoc($Task_List)) {
        $start_date = intval($Tasks['task_start_date']) ? new w2p_Utilities_Date($Tasks['task_start_date']) : ' ';
        $end_date = intval($Tasks['task_end_date']) ? new w2p_Utilities_Date($Tasks['task_end_date']) : ' ';
        $task_id = $Tasks['task_id'];

        $q = new w2p_Database_Query;
        $q->addTable('user_tasks');
        $q->addWhere('task_id = ' . (int)$task_id);
        $sql_user = $q->exec();

        $users = null;
        while ($Task_User = db_fetch_assoc($sql_user)) {
            if ($users != null) {
                $users .= ', ';
            }

            $q->clear();
            $q = new w2p_Database_Query;
            $q->addTable('users', 'u');
            $q->addTable('contacts', 'c');
            $q->addQuery('contact_first_name, contact_last_name');
            $q->addWhere('u.user_contact = c.contact_id');
            $q->addWhere('user_id = ' . (int)$Task_User['user_id']);

            $sql_user_array = $q->exec();
            $q->clear();
            $user_list = db_fetch_assoc($sql_user_array);
            $users .= $user_list['contact_first_name'] . ' ' . $user_list['contact_last_name'];
        }
        $str = '<tr>';
        if ($project_id == 0) {
            $str .= '<td>' . $Tasks['project_name'] . '</td>';
        }
        $str .= '<td>';
        $str .= ($Tasks['task_id'] == $Tasks['task_parent']) ? '' : '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" border="0" alt="" />';
        $str .= '&nbsp;<a href="?m=tasks&a=view&task_id=' . $Tasks['task_id'] . '">' . $Tasks['task_name'] . '</a></td>';
        $str .= '<td>' . nl2br($Tasks['task_description']) . '</td>';
        $str .= '<td>' . $users . '</td>';
        $str .= '<td align="center">';
        ($start_date != ' ') ? $str .= $start_date->format($df) . '</td>' : $str .= ' </td>';
        $str .= '<td align="center">';
        ($end_date != ' ') ? $str .= $end_date->format($df) . '</td>' : $str .= ' ' . '</td>';
        $str .= '<td align="right">' . $Tasks['task_percent_complete'] . '%</td>';
        $str .= '</tr>';
        echo $str;
        if ($project_id == 0) {
            $pdfdata[] = array($Tasks['project_name'], $Tasks['task_name'], $Tasks['task_description'], $users, (($start_date != ' ') ? $start_date->format($df) : ' '), (($end_date != ' ') ? $end_date->format($df) : ' '), $Tasks['task_percent_complete'] . '%', );
        } else {
            $pdfdata[] = array($Tasks['task_name'], $Tasks['task_description'], $users, (($start_date != ' ') ? $start_date->format($df) : ' '), (($end_date != ' ') ? $end_date->format($df) : ' '), $Tasks['task_percent_complete'] . '%', );
        }
    }
    echo '</table>';
    if ($log_pdf) {
        // make the PDF file
        $q = new w2p_Database_Query;
        $q->addTable('projects');
        $q->addQuery('project_name');
        $q->addWhere('project_id=' . (int)$project_id);
        $pname = $q->loadResult();

        $pdf = new w2p_Output_PDF_Reports('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetMargins(15, 25, 15, true); // left, top, right
        $pdf->setHeaderMargin(10);
        $pdf->setFooterMargin(20);

        $pdf->header_company_name = w2PgetConfig('company_name');
        $date = new w2p_Utilities_Date();
        $pdf->header_date = $date->format($df);

        $pdf->SetFont('freeserif', 'B', 12);

        $pdf->AddPage();

        $pdf->Cell(0, 0, $AppUI->_('Project Task Report'), 0, 1);

        if ($project_id != 0) {
            $pdf->SetFont('freeserif', 'B', 15);
            $pdf->Cell(0, 0, $pname, 0, 1);
        }

        $pdf->SetFont('freeserif', 'B', 10);
        if ($log_all) {
            $pdf->Cell(0, 0, 'All task entries', 0, 1);
        } else {
            if ($end_date != ' ') {
                $pdf->Cell(0, 0, 'Task entries from ' . $start_date->format($df) . ' to ' . $end_date->format($df), 0, 1);
            } else {
                $pdf->Cell(0, 0, 'Task entries from ' . $start_date->format($df), 0, 1);
            }
        }

        $temp_dir = W2P_BASE_DIR . '/files/temp';

        $pdf->SetFont('freeserif', '', 10);
        $pdf->addHtmlTable($pdfdata, $columns);

        $w2pReport = new CReport();
        if ($fp = fopen($temp_dir . '/'.$w2pReport->getFilename().'.pdf', 'wb')) {
            fwrite($fp, $pdf->Output('tasklist.pdf', 'S'));
            fclose($fp);
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