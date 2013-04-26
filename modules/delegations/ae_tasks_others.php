<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $f, $f2, $user_id, $showIncomplete, $search_text, $user_list, $owner;

$AppUI->loadCalendarJS();

// remove the current user from the selection list
unset($user_list[$user_id]);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('delegations', 'tasks_delegated_others');
$fieldList = array_keys($fields);
$fieldNames = array_values($fields);

$start = (int) w2PgetConfig('cal_day_start', 8);
$end = (int) w2PgetConfig('cal_day_end', 17);
$inc = (int) w2PgetConfig('cal_day_increment', 15);

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

$deleg_date = new w2p_Utilities_Date();

$fieldList = array_merge(array('__edit'), $fieldList, array('__delete'));
$fieldNames = array_merge(array(''), $fieldNames, array(''));

$owner = 'mine';
$project_id = 0;
$task_id = 0;
include W2P_BASE_DIR . '/modules/delegations/index_table.php';

?>
