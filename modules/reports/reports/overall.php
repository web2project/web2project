<?php
/** @var w2p_Core_CAppUI $AppUI */
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

$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$log_all = w2PgetParam($_POST, 'log_all', 0);

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

$fullaccess = ($AppUI->user_type == 1);

echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
    <input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
    <input type="hidden" name="datePicker" value="log" />

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 std w2p-report-filter">
        <span class="text-nowrap"><?php echo $AppUI->_('For period'); ?>:</span>
        <span class="text-nowrap">
            <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
            <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
            <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
            </a>
        </span>
        <span class="text-nowrap"><?php echo $AppUI->_('to'); ?></span>
        <span class="text-nowrap">
            <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
            <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
            <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
            </a>
        </span>

        <span class="text-nowrap">
            <input type="checkbox" name="log_all" id="log_all" <?php if ($log_all) echo 'checked="checked"' ?> />
            <label for="log_all"><?php echo $AppUI->_('Log All'); ?></label>
        </span>

        <span class="text-nowrap">
            <input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
        </span>
    </div>
</form>
<?php

if ($do_report) {
    echo $AppUI->getTheme()->styleRenderBoxBottom();
	echo '<br />';
    echo $AppUI->getTheme()->styleRenderBoxTop();
	echo '<div class="std w2p-report">';

	$total = 0;

	$q = new w2p_Database_Query;
	if ($fullaccess) {
		$q->addTable('companies');
		$q->addQuery('company_id');
	} else {
		$q->addTable('companies');
		$q->addQuery('company_id');
		$q->addWhere('company_owner = ' . (int)$AppUI->user_id);
	}

	$companies = $q->loadColumn();
	$q->clear();

	if (!empty($companies)) {
		foreach ($companies as $company) {
			$total += showcompany($company);
		}
	} else {
		$q->addTable('companies');
		$q->addQuery('company_id');
		foreach ($q->loadColumn() as $company) {
			$total += showcompany($company, true);
		}
	}

	echo '<h2>' . $AppUI->_('Total Hours') . ': ';
	printf("%.2f", $total);
	echo '</h2>';
	echo '</div>';
}