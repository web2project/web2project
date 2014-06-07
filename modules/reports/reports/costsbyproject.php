<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

$company_id = (int) w2PgetParam($_POST, 'company_id', 0);
$active_projects = w2PgetParam($_POST, 'active_projects', 0);
$active_projects = (isset($_POST['company_id'])) ? $active_projects : 1;

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

$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => 'All Companies'), $companies);
?>

<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
    <input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
    <input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
    <input type="hidden" name="datePicker" value="log" />

    <?php
    echo $AppUI->getTheme()->styleRenderBoxTop();
    ?>
    <table class="std">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('For Company'); ?>:</td>
            <td nowrap="nowrap">
                <?php echo arraySelect($companies, 'company_id', 'class="text" size="1"', $company_id); ?> *
            </td>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <td nowrap="nowrap">
                <input type="checkbox" name="log_pdf" id="log_pdf" <?php if ($log_pdf)
            echo 'checked="checked"' ?> />
                <label for="log_pdf"><?php echo $AppUI->_('Make PDF'); ?></label>
            </td>
            <td nowrap="nowrap">
                <input type="checkbox" name="active_projects" id="active_projects" <?php if ($active_projects)
            echo 'checked="checked"' ?> />
                <label for="active_projects"><?php echo $AppUI->_('Active Projects Only'); ?></label>
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
        <th><?php echo $AppUI->_('Project Name'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Project Owner'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Start Date'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Finish Date'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Target Budget'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Actual Cost'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Difference'); ?></th>
    </tr>
    <?php
    //TODO: rotate the headers by 90 degrees?
    $activeOnly = ($active_projects) ? true : false;
    $projectList = CCompany::getProjects($AppUI, $company_id, $activeOnly);
    $bcode = new CSystem_Bcode();
    $project = new CProject();

    if (count($projectList)) {
        foreach ($projectList as $projectItem) {
            $project->loadFull(null, $projectItem['project_id']);
            $criticalTasks = $project->getCriticalTasks($projectItem['project_id']);

            $costs = $bcode->calculateProjectCost($projectItem['project_id'],
                    $start_date->format(FMT_DATETIME_MYSQL),
                    $end_date->format(FMT_DATETIME_MYSQL));
            $pstart = new w2p_Utilities_Date($project->project_start_date);
            $pend = intval($criticalTasks[0]['task_end_date']) ? new w2p_Utilities_Date($criticalTasks[0]['task_end_date']) : new w2p_Utilities_Date();
            $filterStart = $start_date;
            $filterEnd = $end_date;
            $workingDaysInSpans = $filterStart->findDaysInRangeOverlap($pstart, $pend, $filterStart, $filterEnd);
            $workingDaysForProj = $pstart->workingDaysInSpan($pend);
            $factor = $workingDaysInSpans/$workingDaysForProj;
            $factor = ($factor > 1) ? 1 : $factor;
            ?><tr>
                <td width="10" align="right" style="border: outset #eeeeee 1px;background-color:#<?php echo $project->project_color_identifier; ?>">
                    <font color="<?php echo bestColor($project->project_color_identifier); ?>"><?php echo sprintf('%.1f%%', $project->project_percent_complete); ?></font>
                </td>
                <td>
                    <a href="?m=projects&amp;a=view&amp;project_id=<?php echo $project->project_id; ?>">
                        <?php
                        $projectName = htmlentities($project->project_name);
                        echo $projectName;
                        ?>
                    </a>
                </td>
                <td align="center" nowrap="nowrap">
                    <?php
                        $contactName = htmlentities(CContact::getContactByUserid($project->project_owner));
                        echo $contactName;
                    ?>
                </td>
                <td><?php echo $AppUI->formatTZAwareTime($project->project_start_date, $df); ?></td>
                <td><?php echo $AppUI->formatTZAwareTime($criticalTasks[0]['task_end_date'], $df); ?></td>
                <td align="center">
                    <?php
                        $totalBudget = 0;
                        foreach ($billingCategory as $id => $category) {
                            $totalBudget += $project->budget[$id]['budget_amount'];
                        }

                        $targetBudget = $w2Pconfig['currency_symbol'].(int) ($totalBudget*$factor);
                        echo $targetBudget;
                    ?>
                </td>
                <td align="center">
                    <?php
                        $totalCost = $costs['totalCosts'];
                        $actualCost = $w2Pconfig['currency_symbol'].((int) $totalCost);
                        echo $actualCost;
                    ?>
                </td>
                <td align="center">
                    <?php
                    $diff_total = (int) ($totalBudget*$factor - $totalCost);
                    echo ($diff_total < 0) ? '<span style="color: red;">' : '';
                    echo $w2Pconfig['currency_symbol'].$diff_total;
                    echo ($diff_total < 0) ? '</span>' : '';
                    ?>
                </td>
            </tr><?php
            $pdfdata[] = array(sprintf('%.1f%%', $project->project_percent_complete), 
                '  '.$projectName, $contactName,
                $AppUI->formatTZAwareTime($project->project_start_date, $df),
                $AppUI->formatTZAwareTime($criticalTasks[0]['task_end_date'], $df),
                $targetBudget, $actualCost,
                $w2Pconfig['currency_symbol'].$diff_total);
        }

        if ($log_pdf) {
            // make the PDF file
            $temp_dir = W2P_BASE_DIR . '/files/temp';

            $output = new w2p_Output_PDFRenderer('A4', 'landscape');
            $output->addTitle($AppUI->_('Costs By Project'));
            $output->addDate($df);
            $output->addSubtitle($companies[$company_id]);

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

            $output->addTable($title, $pdfheaders, $pdfdata, $options);

            $w2pReport = new CReport();
            if ($output->writeFile($w2pReport->getFilename())) {
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
        echo '<tr><td colspan="13">'.$AppUI->_('There are no projects in this company').'</td></tr>';
    }
    ?>
</table>
