<?php
require_once $AppUI->getLibraryClass('xajax/xajax_core/xajax.inc');

$xajax = new xajax();
$xajax->configure('javascript URI',w2PgetConfig('base_url').'/lib/xajax/');

function calcFinish($start_date, $start_hour, $start_minute, $duration_type, $task_duration) {
    global $AppUI;

    $df = $AppUI->getPref('SHDATEFORMAT');

    $year = substr($start_date,0,4);
    $month = substr($start_date,4,2);
    $day = substr($start_date,6,2);

    $date = new w2p_Utilities_Date($year.'-'.$month.'-'.$day);
    $date->setTime($start_hour, $start_minute);
    $finish = $date->calcFinish($task_duration, $duration_type);

    $response = new xajaxResponse();
    $response->assign('end_date','value',$finish->format($df));
    $response->assign('task_end_date','value',$finish->format(FMT_TIMESTAMP_DATE));
    $response->assign('end_hour','value',$finish->getHour());
    $response->assign('end_minute','value',$finish->getMinute());

    if($finish->getHour()>11) {
        $response->assign('end_hour_ampm','value','pm');
    } else {
        $response->assign('end_hour_ampm','value','am');
    }

    return $response;
}

function calcDuration($start_date, $start_hour, $start_minute,
        $end_date, $end_hour, $end_minute, $duration_type,
        $duration_output_field = 'task_duration',
        $duration_type_output_field = 'task_duration_type') {
	
    $year = substr($start_date,0,4);
    $month = substr($start_date,4,2);
    $day = substr($start_date,6,2);

    $startDate = new w2p_Utilities_Date($year.'-'.$month.'-'.$day);
    $startDate->setTime($start_hour, $start_minute);

    $year = substr($end_date,0,4);
    $month = substr($end_date,4,2);
    $day = substr($end_date,6,2);

    $endDate = new w2p_Utilities_Date($year.'-'.$month.'-'.$day);
    $endDate->setTime($end_hour, $end_minute);

    $duration = $startDate->calcDuration($endDate);

    $workHours = intval(w2PgetConfig('daily_working_hours'));

    // If the duration is larger than a working day and is a number of full working days
    // (without leftover hours) then force the unit to 'days'. Otherwise force it to 'hours'.
    if ((abs($duration) >= $workHours) && ($duration % $workHours) == 0) {
        $duration = $duration / $workHours;
	$duration_type = 24;
    } else {
        $duration_type = 1;
    }

    $response = new xajaxResponse();
    $response->assign($duration_output_field, 'value', $duration);
    $response->assign($duration_type_output_field, 'value', $duration_type);

    return $response;
}
        
function getDepartment($department_id, $fieldname)
{
    $department = new CDepartment();
    $department->load((int) $department_id);

    $response = new xajaxResponse();
    $response->assign($fieldname,'value',$department->dept_name);

    return $response;
}

$xajax->register(XAJAX_FUNCTION,'calcDuration');
$xajax->register(XAJAX_FUNCTION,'calcFinish');
$xajax->register(XAJAX_FUNCTION,'getDepartment');
$xajax->processRequest();