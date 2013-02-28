<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = canView($m);

if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

w2PsetMicroTime();

// retrieve any state parameters
if (isset($_REQUEST['company_id'])) {
	$AppUI->setState('CalIdxCompany', intval(w2PgetParam($_REQUEST, 'company_id', 0)));
}
$company_id = $AppUI->getState('CalIdxCompany', 0);

// Using simplified set/get semantics. Doesn't need as much code in the module.
$event_filter = $AppUI->checkPrefState('CalIdxFilter', w2PgetParam($_REQUEST, 'event_filter', 'my'), 'EVENTFILTER', 'my');

// get the passed timestamp (today if none)
$ctoday = new w2p_Utilities_Date();
$today = $ctoday->format(FMT_TIMESTAMP_DATE);
$date = w2PgetParam($_GET, 'date', $today);

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Monthly Calendar', 'myevo-appointments.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=calendar&a=year_view&date=' . $date, 'year view');
$titleBlock->addCrumb('?m=calendar&date=' . $date, 'month view');
$titleBlock->addCrumb('?m=calendar&a=week_view&date=' . $date, 'week view');
$titleBlock->addCrumb('?m=calendar&a=day_view&date=' . $date, 'day view');
$titleBlock->addCell('<form action="?m=calendar" method="post" name="pickCompany" accept-charset="utf-8">' . arraySelect($companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id) . '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCell( '<form action="?m=calendar" method="post" name="pickFilter" accept-charset="utf-8">' . arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"', $event_filter, true) . '</form>');
$titleBlock->addCell($AppUI->_('Event Filter') . ':');
$titleBlock->addCell('<form action="?m=calendar&a=addedit&date=' . $today . '" method="post" accept-charset="utf-8"><input type="submit" class="button" value="' . $AppUI->_('New event') . '">' . '</form>');
$titleBlock->show();
?>

<script language="javascript" type="text/javascript">
function clickDay( uts, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+uts+'&tab=0';
}
function clickWeek( uts, fdate ) {
	window.location = './index.php?m=calendar&a=week_view&date='+uts;
}
</script>

<table cellspacing="0" cellpadding="0" border="0" width="100%" id="month-view"><tr><td>
<?php
// establish the focus 'date'
$date = new w2p_Utilities_Date($date);

// prepare time period for 'events'
// "go back" to the first day shown on the calendar
// and "go forward" to the last day shown on the calendar
$first_time = new w2p_Utilities_Date($date);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);

// if Sunday is the 1st, we don't need to go back
// as that's the first day shown on the calendar
if($first_time->getDayOfWeek() != 0) {
    $last_day_of_previous_month = $first_time->getPrevDay();
    $day_of_previous_month = $last_day_of_previous_month->getDayOfWeek();
    $seconds_to_sub_in_previous_month = 86400 * $day_of_previous_month;
    // need to cast it to int because Pear::Date_Span::set down the line
    // fails to set the seconds correctly
    $last_day_of_previous_month->subtractSeconds((int)$seconds_to_sub_in_previous_month);

    $first_time->setDay($last_day_of_previous_month->getDay());
    $first_time->setMonth($last_day_of_previous_month->getMonth());
    $first_time->setYear($last_day_of_previous_month->getYear());
}

$last_time = new w2p_Utilities_Date($date);
$last_time->setDay($date->getDaysInMonth());
$last_time->setTime(23, 59, 59);

// if Saturday is the last day of month, we don't need to go forward
// as that's the last day shown on the calendar
if($last_time->getDayOfWeek() != 6) {
    $first_day_of_next_month = $last_time->getNextDay();
    $day_of_next_month = $first_day_of_next_month->getDayOfWeek();
    $seconds_to_add_in_next_month = 86400 * $day_of_next_month;
    // need to cast it to int because Pear::Date_Span::set down the line
    // fails to set the seconds correctly
    $first_day_of_next_month->addSeconds((int)$seconds_to_add_in_next_month);
    $last_time->setDay($first_day_of_next_month->getDay());
    $last_time->setMonth($first_day_of_next_month->getMonth());
    $last_time->setYear($first_day_of_next_month->getYear());
}

$links = array();

// assemble the links for the tasks
getTaskLinks($first_time, $last_time, $links, 20, $company_id);

// assemble the links for the events
getEventLinks($first_time, $last_time, $links, 20);

$hooks = new w2p_Core_HookHandler($AppUI);
$hooks->links = $links;
$links = $hooks->calendar_links();

// create the main calendar
$cal = new w2p_Output_MonthCalendar($date);
$cal->setStyles('motitle', 'mocal');
$cal->setLinkFunctions('clickDay', 'clickWeek');
$cal->setEvents($links);

echo $cal->show();
//echo '<pre>';print_r($cal);echo '</pre>';

// create the mini previous and next month calendars under
$minical = new w2p_Output_MonthCalendar($cal->prev_month);
$minical->setStyles('minititle', 'minical');
$minical->showArrows = false;
$minical->showWeek = false;
$minical->clickMonth = true;
$minical->setLinkFunctions('clickDay');

$first_time = new w2p_Utilities_Date($cal->prev_month);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new w2p_Utilities_Date($cal->prev_month);
$last_time->setDay($cal->prev_month->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20);
$minical->setEvents($links);

echo '<table class="std" cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td valign="top" align="center" width="220">' . $minical->show() . '</td>';
echo '<td valign="top" align="center" width="75%">&nbsp;</td>';

$minical->setDate($cal->next_month);
$first_time = new w2p_Utilities_Date($cal->next_month);
$first_time->setDay(1);
$first_time->setTime(0, 0, 0);
$last_time = new w2p_Utilities_Date($cal->next_month);
$last_time->setDay($cal->next_month->getDaysInMonth());
$last_time->setTime(23, 59, 59);
$links = array();
getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
getEventLinks($first_time, $last_time, $links, 20, true);
$minical->setEvents($links);

echo '<td valign="top" align="center" width="220">' . $minical->show() . '</td>';
echo '</tr></table>';
?>
</td></tr></table>