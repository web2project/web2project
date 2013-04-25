<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $this_day, $first_time, $last_time, $company_id, $m, $a, $AppUI, $task_type;

$links = array();

$s = '';
$dayStamp = $this_day->format(FMT_TIMESTAMP_DATE);

$min_view = 1;
include W2P_BASE_DIR . '/modules/tasks/todo.php';
?>