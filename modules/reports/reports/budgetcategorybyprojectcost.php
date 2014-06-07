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
$log_end_date   = w2PgetParam($_POST, 'log_end_date',   '2012-01-01');
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

    <?php echo $AppUI->getTheme()->styleRenderBoxTop(); ?>
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

<table width="100%" class="tbl" cellspacing="1" cellpadding="4" border="0">
    <tr>
        <th colspan="2">&nbsp;</th>
        <?php foreach ($billingCategory as $id => $category) { ?>
            <th colspan="2"><?php echo $AppUI->_($category); ?></th>
        <?php } ?>
        <th><?php echo $AppUI->_('Unidentified'); ?></th>
        <th colspan="3"><?php echo $AppUI->_('Totals'); ?></th>
    </tr>
	<tr>
        <th width="10px" nowrap="nowrap"><?php echo $AppUI->_('Work'); ?></th>
        <th><?php echo $AppUI->_('Project Name'); ?></th>
        <?php foreach ($billingCategory as $id => $category) { ?>
            <th><?php echo $AppUI->_('Budgetted'); ?></th>
            <th><?php echo $AppUI->_('Used'); ?></th>
        <?php } ?>
        <th><?php echo $AppUI->_('Costs'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Budgetted'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Used'); ?></th>
        <th width="10px" align="center"><?php echo $AppUI->_('Remaining'); ?></th>
    </tr>
    <?php
    //TODO: rotate the headers by 90 degrees?
    $activeOnly = ($active_projects) ? true : false;
    $symbol = $w2Pconfig['currency_symbol'];
    $projectList = CCompany::getProjects($AppUI, $company_id, $activeOnly);
    $bcode = new CSystem_Bcode();
    $project = new CProject();
    $totalBudget = array();
    $totalConsumed = array();

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
            $pdfRow = array();
            $pdfRow[] = sprintf('%.1f%%', $project->project_percent_complete);
            ?><tr>
                <td width="10" align="right" style="border: outset #eeeeee 1px;background-color:#<?php echo $project->project_color_identifier; ?>">
                    <font color="<?php echo bestColor($project->project_color_identifier); ?>"><?php echo sprintf('%.1f%%', $project->project_percent_complete); ?></font>
                </td>
                <td>
                    <a href="?m=projects&amp;a=view&amp;project_id=<?php echo $project->project_id; ?>">
                        <?php
                        $projectName = htmlentities($project->project_name);
                        $pdfRow[] = '  '.$projectName;
                        echo $projectName;
                        ?>
                    </a>
                </td>
                <?php
                    $projectBudget = 0;
                    foreach ($billingCategory as $id => $category) {
                        $budget = ($project->budget[$id]['budget_amount']) ? $project->budget[$id]['budget_amount'] : 0;
                        $consumed = (isset($costs[$id])) ? $costs[$id] : 0;

                        echo '<td align="center">'.$symbol.round($budget * $factor, 2).'</td>';
                        echo '<td align="center">'.$symbol.$consumed.'</td>';
                        $pdfRow[] = round($budget * $factor, 2);
                        $pdfRow[] = $consumed;

                        $projectBudget += $budget;
                        $totalBudget[$id] += $budget * $factor;
                        $totalConsumed[$id] += $consumed;
                    }
                    $consumed = (isset($costs['otherCosts'])) ? $costs['otherCosts'] : 0;
                    $totalConsumed['otherCosts'] += $consumed;
                    $pdfRow[] = $consumed;
                    echo '<td align="center">'.$symbol.$consumed.'</td>';
                ?>
                <td align="center">
                    <?php
                    echo $symbol.round($projectBudget*$factor, 2);
                    $pdfRow[] = round($projectBudget*$factor, 2);
                    ?>
                </td>
                <td align="center">
                    <?php
                        $projectCost = $costs['totalCosts'];
                        $actualCost = $symbol.((int) $projectCost);
                        echo $actualCost;
                        $pdfRow[] = (int) $projectCost;
                    ?>
                </td>
                <td align="center">
                    <?php
                    $projectDiff = (int) ($projectBudget*$factor - $projectCost);
                    echo ($projectDiff < 0) ? '<span style="color: red;">' : '';
                    echo $symbol.$projectDiff;
                    echo ($projectDiff < 0) ? '</span>' : '';
                    $pdfRow[] = $projectDiff;
                    ?>
                </td>
            </tr><?php
            $pdfdata[] = $pdfRow;
        }

        $pdfRow = array();
        echo '<tr>';
        echo '<td colspan="2" align="right">'.$AppUI->_('Totals').'</td>';
        $pdfRow[] = '';
        $pdfRow[] = $AppUI->_('Totals');
        foreach ($billingCategory as $id => $category) {
            $tmpBudget = (isset($totalBudget[$id]) ? $totalBudget[$id] : 0);
            $sumBudget += $tmpBudget;
            $tmpConsumed = (isset($totalConsumed[$id]) ? $totalConsumed[$id] : 0);
            $sumConsumed += $tmpConsumed;
            echo '<td align="center">'.$symbol.round($tmpBudget).'</td>';
            echo '<td align="center">'.$symbol.round($tmpConsumed).'</td>';
            $pdfRow[] = round($tmpBudget);
            $pdfRow[] = round($tmpConsumed);
        }
        $sumConsumed += $totalConsumed['otherCosts'];
        echo '<td align="center">'.$symbol.$totalConsumed['otherCosts'].'</td>';
        echo '<td align="center">'.$symbol.round($sumBudget).'</td>';
        echo '<td align="center">'.$symbol.round($sumConsumed).'</td>';
        $pdfRow[] = round($totalConsumed['otherCosts']);
        $pdfRow[] = round($sumBudget);
        $pdfRow[] = round($sumConsumed);
        echo '<td align="center">';
        $sumDiff = (int) ($sumBudget - $sumConsumed);
        echo ($sumDiff < 0) ? '<span style="color: red;">' : '';
        echo $symbol.$sumDiff;
        echo ($sumDiff < 0) ? '</span>' : '';
        $pdfRow[] = round($sumDiff);
        echo '</td>';
        echo '</tr>';
        $pdfdata[] = $pdfRow;

        if ($log_pdf) {
            // make the PDF file
            $temp_dir = W2P_BASE_DIR . '/files/temp';

            $output = new w2p_Output_PDFRenderer('A4', 'landscape');
            $output->addTitle($AppUI->_('Costs By Project and Billing Category'));
            $output->addDate($df);
            $output->addSubtitle($companies[$company_id]);

            $columns = array();
            $pdfheaders = array($AppUI->_('Work', UI_OUTPUT_JS),
                '  '.$AppUI->_('Project Name', UI_OUTPUT_JS));
            $columns[] = array('justification' => 'center', 'width' => 40);
            $columns[] = array('justification' => 'left'  , 'width' => 130);
            foreach ($billingCategory as $id => $category) {
                $pdfheaders[] = $AppUI->_($category."\n".$AppUI->_('Budgetted', UI_OUTPUT_JS), UI_OUTPUT_JS);
                $pdfheaders[] = "\n(".$AppUI->_('Used', UI_OUTPUT_JS).")";
                $columns[] = array('justification' => 'center', 'width' => 45);
                $columns[] = array('justification' => 'center', 'width' => 45);
            }
            $pdfheaders[] = $AppUI->_("Unidentified\n(Used)", UI_OUTPUT_JS);
            $pdfheaders[] = $AppUI->_('Budgetted', UI_OUTPUT_JS);
            $pdfheaders[] = $AppUI->_('Used', UI_OUTPUT_JS);
            $pdfheaders[] = $AppUI->_('Remaining', UI_OUTPUT_JS);
            $columns[] = array('justification' => 'center', 'width' => 50);
            $columns[] = array('justification' => 'center', 'width' => 45);
            $columns[] = array('justification' => 'center', 'width' => 45);
            $columns[] = array('justification' => 'center', 'width' => 50);

            $options = array('showLines' => 1, 'fontSize' => 9, 'rowGap' => 1,
                'colGap' => 1, 'xPos' => 25, 'xOrientation' => 'right', 'width' => '500',
                'cols' => $columns);

            $output->addTable($title, $pdfheaders, $pdfdata, $options);

            $w2pReport = new CReport();
            echo '<tr><td colspan="20" align="center">';
            if ($output->writeFile($w2pReport->getFilename())) {
                echo '<a href="' . W2P_BASE_URL . '/files/temp/' . $w2pReport->getFilename() . '.pdf" target="pdf">';
                echo $AppUI->_('View PDF File');
                echo '</a>';
            } else {
                echo 'Could not open file to save PDF.  ';
                if (!is_writable($temp_dir)) {
                    echo 'The files/temp directory is not writable.  Check your file system permissions.';
                }
            }
            echo '</td></tr>';
        }
    } else {
        echo '<tr><td colspan="20">'.$AppUI->_('There are no projects in this company').'</td></tr>';
    }
    ?>
</table>
