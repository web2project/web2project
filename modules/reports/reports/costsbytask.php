<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();
//TODO: block execution unless I tell it to
/**
 * Generates a report of the task logs for given dates
 */
$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_pdf = w2PgetParam($_POST, 'log_pdf', 0);

$log_start_date = w2PgetParam($_POST, 'log_start_date', '2008-01-01');
$log_end_date   = w2PgetParam($_POST, 'log_end_date',   '2014-01-01');
// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);
$billingCategory = w2PgetSysVal('BudgetCategory');
?>

<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
    <input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
    <input type="hidden" name="datePicker" value="log" />

    <?php
    if (function_exists('styleRenderBoxTop')) {
        echo styleRenderBoxTop();
    }
    ?>
    <table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
            </td>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
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

<table width="100%" class="tbl" cellspacing="1" cellpadding="3" border="0">
	<tr>
        <th width="10px" nowrap="nowrap"><?php echo $AppUI->_('Work'); ?></th>
        <th><?php echo $AppUI->_('Task Name'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Task Owner'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Start Date'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Finish Date'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Target Budget'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Actual Cost'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Difference'); ?></th>
    </tr>
    <?php
    //TODO: rotate the headers by 90 degrees?
    $task = new CTask();
    $taskList = $task->getAllowedTaskList(null, $project_id);
    $bcode = new CSystem_Bcode();

    if (count($taskList)) {
        foreach ($taskList as $taskItem) {
            $task->loadFull(null, $taskItem['task_id']);
            $costs = $bcode->calculateTaskCost($taskItem['task_id'],
                    $start_date->format(FMT_DATETIME_MYSQL),
                    $end_date->format(FMT_DATETIME_MYSQL));
            $tstart = new w2p_Utilities_Date($task->task_start_date);
            $tend   = new w2p_Utilities_Date($task->task_end_date);
            $filterStart = $start_date;
            $filterEnd = $end_date;
            $workingDaysInSpans = $filterStart->findDaysInRangeOverlap($tstart, $tend, $filterStart, $filterEnd);
            $workingDaysForTask = $tstart->workingDaysInSpan($tend);
            $factor = $workingDaysInSpans/$workingDaysForTask;
            $factor = ($factor > 1) ? 1 : $factor;
            ?><tr>
                <td align="center"><?php echo sprintf('%.0f%%', $task->task_percent_complete); ?></td>
                <td>
                    <?php if ($task->task_id != $task->task_parent) { ?>
                        <img src="<?php echo w2PfindImage('corner-dots.gif'); ?>" width="16" height="12" border="0" alt="">
                    <?php } ?>
                    <a href="?m=tasks&amp;a=view&amp;task_id=<?php echo $task->task_id; ?>">
                        <?php
                        $taskName = htmlentities($task->task_name);
                        echo $taskName;
                        if ($task->task_id != $task->task_parent) {
                            $taskName = '--'.$taskName;
                        }
                        ?>
                    </a>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php
                        $contactName = htmlentities(CContact::getContactByUserid($task->task_owner));
                        echo $contactName;
                    ?>
                </td>
                <td><?php echo $AppUI->formatTZAwareTime($task->task_start_date, $df); ?></td>
                <td><?php echo $AppUI->formatTZAwareTime($task->task_end_date, $df); ?></td>
                <td align="center">
                    <?php
                        $targetBudget = $w2Pconfig['currency_symbol'].(int) ($task->task_target_budget*$factor);
                        echo $targetBudget;
                    ?>
                </td>
                <td align="center">
                    <?php
                        $actualCost = $w2Pconfig['currency_symbol'].((int) $costs['totalCosts']);
                        echo $actualCost;
                    ?>
                </td>
                <td align="center">
                    <?php
                    $diff_total = (int) ($task->task_target_budget*$factor - $costs['totalCosts']);
                    echo ($diff_total < 0) ? '<span style="color: red;">' : '';
                    echo $w2Pconfig['currency_symbol'].$diff_total;
                    echo ($diff_total < 0) ? '</span>' : '';
                    ?>
                </td>
            </tr><?php
            $pdfdata[] = array(sprintf('%.1f%%', $task->task_percent_complete),
                '  '.$taskName, $contactName,
                $AppUI->formatTZAwareTime($task->task_start_date, $df),
                $AppUI->formatTZAwareTime($task->task_end_date, $df),
                $targetBudget, $actualCost, $w2Pconfig['currency_symbol'].$diff_total);
        }

        if ($log_pdf) {
            // make the PDF file
            $font_dir = W2P_BASE_DIR . '/lib/ezpdf/fonts';
            $temp_dir = W2P_BASE_DIR . '/files/temp';

            $pdf = new Cezpdf('A4', 'landscape');
            $pdf->ezSetCmMargins(1, 1, 1, 1);
            $pdf->selectFont($font_dir . '/Helvetica-Bold.afm');
            $pdf->ezText($projectList[$project_id]['project_name'], 14);

            $pdf->selectFont($font_dir . '/Helvetica.afm');
            $pdf->ezText($AppUI->_('Costs By Task') . "\n", 12);

            $pdfheaders = array($AppUI->_('Work', UI_OUTPUT_JS),
                '  '.$AppUI->_('Project Name', UI_OUTPUT_JS), $AppUI->_('Project Owner', UI_OUTPUT_JS),
                $AppUI->_('Start Date', UI_OUTPUT_JS), $AppUI->_('Finish Date', UI_OUTPUT_JS),
                $AppUI->_('Target Budget', UI_OUTPUT_JS), $AppUI->_('Actual Cost', UI_OUTPUT_JS),
                $AppUI->_('Difference', UI_OUTPUT_JS));

            $options = array('showLines' => 1, 'fontSize' => 9, 'rowGap' => 1,
                'colGap' => 1, 'xPos' => 50, 'xOrientation' => 'right', 'width' => '500',
                'cols' => array(
                            0 => array('justification' => 'center', 'width' => 45),
                            1 => array('justification' => 'left', 'width' => 175),
                            2 => array('justification' => 'center', 'width' => 75),
                            3 => array('justification' => 'center', 'width' => 65),
                            4 => array('justification' => 'center', 'width' => 65),
                            5 => array('justification' => 'center', 'width' => 65),
                            6 => array('justification' => 'center', 'width' => 65),
                            7 => array('justification' => 'center', 'width' => 65),
                    ));

            $pdf->ezTable($pdfdata, $pdfheaders, $title, $options);

            $w2pReport = new CReport();
            if ($fp = fopen($temp_dir . '/'.$w2pReport->getFilename().'.pdf', 'wb')) {
                fwrite($fp, $pdf->ezOutput());
                fclose($fp);
                echo '<tr><td colspan="13">';
                echo '<a href="' . W2P_BASE_URL . '/files/temp/' . $w2pReport->getFilename() . '.pdf" target="pdf">';
                echo $AppUI->_('View PDF File');
                echo '</a>';
                echo '</td></tr>';
            } else {
                echo '<tr><td colspan="13">';
                echo 'Could not open file to save PDF.  ';
                if (!is_writable($temp_dir)) {
                    echo 'The files/temp directory is not writable.  Check your file system permissions.';
                }
                echo '</td></tr>';
            }
        }
    } else {
        echo '<tr><td colspan="13">'.$AppUI->_('There are no tasks on this project').'</td></tr>';
    }
    ?>
</table>