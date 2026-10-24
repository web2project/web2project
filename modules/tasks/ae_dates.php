<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $w2Pconfig, $task_parent_options, $loadFromTab;
global $can_edit_time_information, $locale_char_set, $object;
global $durnTypes, $task_project, $object_id, $tab;
global $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$defaultDate = new w2p_Utilities_Date();
$start_date = intval($object->task_start_date) ?
    new w2p_Utilities_Date($AppUI->formatTZAwareTime($object->task_start_date, '%Y-%m-%d %T')) :
        $defaultDate->calcFinish(1, $object->task_duration_type);

$object->task_duration = isset($object->task_duration) ? $object->task_duration : 1;

$end_date = intval($object->task_end_date) ?
    new w2p_Utilities_Date($AppUI->formatTZAwareTime($object->task_end_date, '%Y-%m-%d %T')) :
        $defaultDate->calcFinish($object->task_duration + 1, $object->task_duration_type);

// The start and finish time selects: HHMM => the time in the user's format,
// for the working day's hours (cal_day_start to cal_day_end) in steps of
// cal_day_increment, like the hour and minute selects they replace.
$start = (int) w2PgetConfig('cal_day_start', 8);
$end = (int) w2PgetConfig('cal_day_end', 17);
$inc = (int) w2PgetConfig('cal_day_increment', 15);
$inc = ($inc > 0 && $inc <= 60) ? $inc : 15;

$times = array();
$time = new w2p_Utilities_Date();
for ($hour = $start; $hour <= $end; $hour++) {
    for ($minute = 0; $minute < 60; $minute += $inc) {
        $time->setTime($hour, $minute, 0);
        $times[sprintf('%02d%02d', $hour, $minute)] = $time->format($tf);
    }
}
// The selected times. A saved task's own time is always offered, so saving
// the form doesn't move it. A new task's default (based on the current time)
// falls back to the start and end of the working day when it isn't listed.
$selected_times = array();
foreach (array('start' => array($start_date, $object->task_start_date, $start),
               'end' => array($end_date, $object->task_end_date, $end)) as $which => $info) {
    list($date, $saved, $fallback_hour) = $info;
    $key = $date ? $date->format('%H%M') : '';
    if (!isset($times[$key])) {
        if ($date && intval($saved)) {
            $times[$key] = $date->format($tf);
        } else {
            $key = sprintf('%02d00', $fallback_hour);
        }
    }
    $selected_times[$which] = $key;
}
ksort($times, SORT_STRING);

// convert the numeric calendar_working_days config array value to a human readable output format
$cwd = explode(',', $w2Pconfig['cal_working_days']);

$cwd_conv = array_map('cal_work_day_conv', $cwd);
$cwd_hr = implode(', ', $cwd_conv);

include $AppUI->getTheme()->resolveTemplate('tasks/addedit_dates');
?>
<script language="javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.datesFrm, checkDates, saveDates));
</script>
