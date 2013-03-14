<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $w2Pconfig, $task_parent_options, $loadFromTab;
global $can_edit_time_information, $locale_char_set, $task;
global $durnTypes, $task_project, $task_id, $tab;
global $cal_sdf;
$AppUI->loadCalendarJS();

//Time arrays for selects
$start = (int) w2PgetConfig('cal_day_start', 8);
$end = (int) w2PgetConfig('cal_day_end', 17);
$inc = (int) w2PgetConfig('cal_day_increment', 15);

$ampm = stristr($AppUI->getPref('TIMEFORMAT'), '%p');
$hours = array();
for ($current = $start; $current < $end + 1; $current++) {
    $current_key = ($current < 10) ? '0' . $current : $current;

    if ($ampm) {
		//User time format in 12hr
		$hours[$current_key] = ($current > 12 ? $current - 12 : $current);
	} else {
		//User time format in 24hr
		$hours[$current_key] = $current;
	}
}

$minutes = array();
$minutes['00'] = '00';
for ($current = 0 + $inc; $current < 60; $current += $inc) {
	$minutes[$current] = $current;
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$defaultDate = new w2p_Utilities_Date();
$start_date = intval($task->task_start_date) ?
    new w2p_Utilities_Date($AppUI->formatTZAwareTime($task->task_start_date, '%Y-%m-%d %T')) :
        $defaultDate->calcFinish(1, $task->task_duration_type);

$task->task_duration = isset($task->task_duration) ? $task->task_duration : 1;
$end_date = intval($task->task_end_date) ?
    new w2p_Utilities_Date($AppUI->formatTZAwareTime($task->task_end_date, '%Y-%m-%d %T')) :
        $defaultDate->calcFinish($task->task_duration + 1, $task->task_duration_type);

// convert the numeric calendar_working_days config array value to a human readable output format
$cwd = explode(',', $w2Pconfig['cal_working_days']);

$cwd_conv = array_map('cal_work_day_conv', $cwd);
$cwd_hr = implode(', ', $cwd_conv);

?>

<form name="datesFrm" action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" accept-charset="utf-8">
    <input name="dosql" type="hidden" value="do_task_aed" />
    <input name="task_id" type="hidden" value="<?php echo $task_id; ?>" />
    <input type="hidden" name="datePicker" value="task" />

    <table width="100%" border="0" cellpadding="4" cellspacing="0" class="std addedit">
        <?php if ($can_edit_time_information) { ?>
            <tr>
                <td align="right" nowrap="nowrap"><div id="start_date_div"><?php echo $AppUI->_('Start Date'); ?>:</div></td>
                <td nowrap="nowrap">
                    <input type='hidden' id='task_start_date' name='task_start_date' value='<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
                    <input type='text' onchange="setDate_new('datesFrm', 'start_date');" class='text' style='width:120px;' id='start_date' name='start_date' value='<?php echo $start_date ? $start_date->format($df) : ''; ?>' />
                    <a onclick="return showCalendar('start_date', '<?php echo $df ?>', 'datesFrm', null, true, true)" href="javascript: void(0);">
                        <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                    </a>
                </td>
                <td>
                    <table><tr>
                    <?php
                        echo '<td>' . $AppUI->_('Start Time'). ':&nbsp;' . arraySelect($hours, 'start_hour', 'size="1" onchange="setAMPM(this)" class="text"', $start_date ? $start_date->getHour() : $start) . '</td><td> : </td>';
                        echo '<td>' . arraySelect($minutes, 'start_minute', 'size="1" class="text"', $start_date ? $start_date->getMinute() : '00') . '</td>';
                        if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
                            echo '<td><input type="text" name="start_hour_ampm" id="start_hour_ampm" value="' . ($start_date ? $start_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
                        }
                    ?>
                    </tr></table>
                </td>
            </tr>
            <tr class="end_date_ctrls" style="visibility: <?php echo $task->task_milestone ? 'hidden' : 'visible'; ?>">
                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date'); ?>:</td>
                <td nowrap="nowrap">
                    <input type='hidden' id='task_end_date' name='task_end_date' value='<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
                    <input type='text' onchange="setDate_new('datesFrm', 'end_date');" class='text' style='width:120px;' id='end_date' name='end_date' value='<?php echo $end_date ? $end_date->format($df) : ''; ?>' />
                    <a onclick="return showCalendar('end_date', '<?php echo $df ?>', 'datesFrm', null, true, true)" href="javascript: void(0);">
                        <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                    </a>
                </td>
                    <td>
                        <table><tr>
                        <?php
                            echo '<td>' . $AppUI->_('End Time'). ':&nbsp;' .  arraySelect($hours, 'end_hour', 'size="1" onchange="setAMPM(this)" class="text"', $end_date ? $end_date->getHour() : $end) . '</td><td> : </td>';
                            echo '<td>' . arraySelect($minutes, 'end_minute', 'size="1" class="text"', $end_date ? $end_date->getMinute() : '00') . '</td>';
                            if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
                                echo '<td><input type="text" name="end_hour_ampm" id="end_hour_ampm" value="' . ($end_date ? $end_date->getAMPM() : ($end > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
                            }
                        ?>
                        </tr></table>
                    </td>
            </tr>
            <tr class="end_date_ctrls" style="visibility: <?php echo $task->task_milestone ? 'hidden' : 'visible'; ?>">
                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Expected Duration'); ?>:</td>
                <td nowrap="nowrap">
					<input type="text" class="text" name="task_duration" id="task_duration" maxlength="8" size="6" value="<?php echo $task->task_duration; ?>" />
                    <?php
                        echo arraySelect($durnTypes, 'task_duration_type', 'class="text"', $task->task_duration_type, true);
                    ?>
                </td>
                <td><?php echo $AppUI->_('Daily Working Hours') . ': ' . $w2Pconfig['daily_working_hours']; ?></td>
            </tr>
            <tr class="end_date_ctrls" style="visibility: <?php echo $task->task_milestone ? 'hidden' : 'visible'; ?>">
                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Calculate'); ?>:</td>
                <td nowrap="nowrap">
                    <input type="button" value="<?php echo $AppUI->_('Duration'); ?>" onclick="xajax_calcDuration(document.datesFrm.task_start_date.value,document.datesFrm.start_hour.value,document.datesFrm.start_minute.value,document.datesFrm.task_end_date.value,document.datesFrm.end_hour.value,document.datesFrm.end_minute.value,document.datesFrm.task_duration_type.value);" class="button" />
                    <input type="button" value="<?php echo $AppUI->_('Finish Date'); ?>" onclick="xajax_calcFinish(document.datesFrm.task_start_date.value,document.datesFrm.start_hour.value,document.datesFrm.start_minute.value,document.datesFrm.task_duration_type.value,document.datesFrm.task_duration.value)" class="button" />
                </td>
                <td><?php echo $AppUI->_('Working Days') . ': ' . $cwd_hr; ?></td>
            </tr>
        <?php } else { ?>
            <tr>
                <td colspan='2'>
                    <?php echo $AppUI->_('Only the task owner, project owner, or system administrator is able to edit time related information.'); ?>
                </td>
            </tr>
        <?php } // end of can_edit_time_information ?>
    </table>
</form>
<script language="javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.datesFrm, checkDates, saveDates));
</script>
