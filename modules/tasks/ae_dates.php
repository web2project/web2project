<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $w2Pconfig, $task_parent_options, $loadFromTab;
global $can_edit_time_information, $locale_char_set, $object;
global $durnTypes, $task_project, $object_id, $tab;
global $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

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
for ($current = $inc; $current < 60; $current += $inc) {
	$minutes[$current] = $current;
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$defaultDate = new w2p_Utilities_Date();
$start_date = intval($object->task_start_date) ?
    new w2p_Utilities_Date($AppUI->formatTZAwareTime($object->task_start_date, '%Y-%m-%d %T')) :
        $defaultDate->calcFinish(1, $object->task_duration_type);

$object->task_duration = isset($object->task_duration) ? $object->task_duration : 1;

$end_date = intval($object->task_end_date) ?
    new w2p_Utilities_Date($AppUI->formatTZAwareTime($object->task_end_date, '%Y-%m-%d %T')) :
        $defaultDate->calcFinish($object->task_duration + 1, $object->task_duration_type);

// convert the numeric calendar_working_days config array value to a human readable output format
$cwd = explode(',', $w2Pconfig['cal_working_days']);

$cwd_conv = array_map('cal_work_day_conv', $cwd);
$cwd_hr = implode(', ', $cwd_conv);

include $AppUI->getTheme()->resolveTemplate('tasks/addedit_dates');
?>
<script language="javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.datesFrm, checkDates, saveDates));
</script>
