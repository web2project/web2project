<?php
require_once $AppUI->getLibraryClass('xajax/xajax_core/xajax.inc');

$xajax = new xajax();
$xajax->configure('javascript URI',w2PgetConfig('base_url').'/lib/xajax/');

function calcFinish($start_date, $start_hour, $start_minute, $duration_type, $task_duration) {
	
	$year = substr($start_date,0,4);
	$month = substr($start_date,4,2);
	$day = substr($start_date,6,2);
	
	$date = new w2p_Utilities_Date($year.'-'.$month.'-'.$day);
	$date->setTime($start_hour,$start_minute);
	
	$finish = $date->calcFinish($task_duration,$duration_type);
	
	$response = new xajaxResponse();
	$response->assign('end_date','value',$finish->format('%d/%b/%Y'));
	$response->assign('task_end_date','value',$finish->format('%Y%m%d'));
	$response->assign('end_hour','value',$finish->getHour());
	$response->assign('end_minute','value',$finish->getMinute());
	
	if($finish->getHour()>11) {
		$response->assign('end_hour_ampm','value','pm');
	} else {
		$response->assign('end_hour_ampm','value','am');
	}
	
	return $response;
}

function calcDuration($start_date, $start_hour, $start_minute, $end_date, $end_hour, $end_minute, $duration_type) {
	
	$year = substr($start_date,0,4);
	$month = substr($start_date,4,2);
	$day = substr($start_date,6,2);
	
	$startDate = new w2p_Utilities_Date($year.'-'.$month.'-'.$day);
	$startDate->setTime($start_hour,$start_minute);
	
	$year = substr($end_date,0,4);
	$month = substr($end_date,4,2);
	$day = substr($end_date,6,2);
	
	$endDate = new w2p_Utilities_Date($year.'-'.$month.'-'.$day);
	$endDate->setTime($end_hour,$end_minute);
	
	$duration = $startDate->calcDuration($endDate);
	
	if(intval($duration_type) == 24) {
		$workHours = intval(w2PgetConfig('daily_working_hours'));
		$duration = $duration / $workHours;
	} 
	
	$response = new xajaxResponse();
	$response->assign('task_duration', 'value', $duration);
	
	return $response;
	
}

$xajax->register(XAJAX_FUNCTION,'calcDuration');
$xajax->register(XAJAX_FUNCTION,'calcFinish');
$xajax->processRequest();

?>
