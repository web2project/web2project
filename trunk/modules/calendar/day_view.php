<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = $perms->checkModule($m, 'view');

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

global $tab, $locale_char_set, $date;
$AppUI->savePlace();

require_once ($AppUI->getModuleClass('tasks'));

/* Kludge: Backward compatible function to address php5 and php4 date issue */
function php4_clone($object) {
	if (version_compare(phpversion(), '5.0') < 0) {
		return $object;
	} else {
		return @clone ($object);
	}
}

// retrieve any state parameters
if (isset($_REQUEST['company_id'])) {
	$AppUI->setState('CalIdxCompany', intval(w2PgetParam($_REQUEST, 'company_id', 0)));
}
$company_id = $AppUI->getState('CalIdxCompany', $AppUI->user_company);

$event_filter = $AppUI->checkPrefState('CalIdxFilter', w2PgetParam($_REQUEST, 'event_filter', ''), 'EVENTFILTER', 'my');

$AppUI->setState('CalDayViewTab', w2PgetParam($_GET, 'tab', $tab));
$tab = $AppUI->getState('CalDayViewTab', '0');

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// get the passed timestamp (today if none)
$ctoday = new CDate();
$today = $ctoday->format(FMT_TIMESTAMP_DATE);
$date = w2PgetParam($_GET, 'date', $today);
// establish the focus 'date'
$this_day = new CDate($date);
$dd = $this_day->getDay();
$mm = $this_day->getMonth();
$yy = $this_day->getYear();

// get current week
$this_week = Date_calc::beginOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY);

// prepare time period for 'events'
$first_time = php4_clone($this_day);
$first_time->setTime(0, 0, 0);
$first_time->subtractSeconds(1);

$last_time = php4_clone($this_day);
$last_time->setTime(23, 59, 59);

$prev_day = new CDate(Date_calc::prevDay($dd, $mm, $yy, FMT_TIMESTAMP_DATE));
$next_day = new CDate(Date_calc::nextDay($dd, $mm, $yy, FMT_TIMESTAMP_DATE));

// get the list of visible companies
$company = new CCompany();
global $companies;
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);

// setup the title block
$titleBlock = new CTitleBlock('Day View', 'myevo-appointments.png', $m, $m.'.'.$a);
$titleBlock->addCrumb('?m=calendar&a=year_view&date=' . $this_day->format(FMT_TIMESTAMP_DATE), 'year view');
$titleBlock->addCrumb('?m=calendar&date=' . $this_day->format(FMT_TIMESTAMP_DATE), 'month view');
$titleBlock->addCrumb('?m=calendar&a=week_view&date=' . $this_week, 'week view');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCell(arraySelect($companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id), '', '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany" accept-charset="utf-8">', '</form>');
$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new event') . '">', '', '<form action="?m=calendar&a=addedit&date=' . $this_day->format(FMT_TIMESTAMP_DATE) . '" method="post" accept-charset="utf-8">', '</form>');
$titleBlock->show();
?>
<script language="javascript">
function clickDay( idate, fdate ) {
        window.location = './index.php?m=calendar&a=day_view&date='+idate+'&tab=0';
}
</script>

<table class="std" width="100%" cellspacing="0" cellpadding="4">
<tr>
        <td valign="top">
                <table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
                <tr>
                        <td>
                                <a href="<?php echo '?m=calendar&a=day_view&date=' . $prev_day->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" width="16" height="16" alt="pre" border="0"></a>
                        </td>
                        <th width="100%">
                                <?php echo $AppUI->_(htmlspecialchars($this_day->format('%A'), ENT_COMPAT, $locale_char_set)) . ', ' . $this_day->format($df); ?>
                        </th>
                        <td>
                                <a href="<?php echo '?m=calendar&a=day_view&date=' . $next_day->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" width="16" height="16" alt="next" border="0"></a>
                        </td>
                </tr>
                </table>

<?php
// tabbed information boxes
$tabBox = new CTabBox('?m=calendar&a=day_view&date=' . $this_day->format(FMT_TIMESTAMP_DATE), W2P_BASE_DIR . '/modules/calendar/', $tab);
$tabBox->add('vw_day_events', 'Events');
$tabBox->add('vw_day_tasks', 'Tasks');
$tabBox->show();
?>
        </td>
<?php if ($w2Pconfig['cal_day_view_show_minical']) { ?>
        <td valign="top" width="175">
<?php
	require_once (W2P_BASE_DIR . '/modules/calendar/links_tasks.php');
	require_once (W2P_BASE_DIR . '/modules/calendar/links_events.php');

	$minical = new CMonthCalendar($this_day);
	$minical->setStyles('minititle', 'minical');
	$minical->showArrows = false;
	$minical->showWeek = false;
	$minical->clickMonth = true;
	$minical->setLinkFunctions('clickDay');

	$first_time = new CDate($minical->prev_month);
	$first_time->setDay(1);
	$first_time->setTime(0, 0, 0);
	$last_time = new CDate($minical->prev_month);
	$last_time->setDay($minical->prev_month->getDaysInMonth());
	$last_time->setTime(23, 59, 59);
	$links = array();
	getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
	getEventLinks($first_time, $last_time, $links, 20, true);
	$minical->setEvents($links);

	$minical->setDate($minical->prev_month);

	echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
	echo '<td align="center" >' . $minical->show() . '</td>';
	echo '</tr></table><hr noshade size="1">';

	$first_time = new CDate($minical->next_month);
	$first_time->setDay(1);
	$first_time->setTime(0, 0, 0);
	$last_time = new CDate($minical->next_month);
	$last_time->setDay($minical->next_month->getDaysInMonth());
	$last_time->setTime(23, 59, 59);
	$links = array();
	getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
	getEventLinks($first_time, $last_time, $links, 20, true);
	$minical->setEvents($links);

	$minical->setDate($minical->next_month);

	echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
	echo '<td align="center" >' . $minical->show() . '</td>';
	echo '</tr></table><hr noshade size="1">';

	$first_time = new CDate($minical->next_month);
	$first_time->setDay(1);
	$first_time->setTime(0, 0, 0);
	$last_time = new CDate($minical->next_month);
	$last_time->setDay($minical->next_month->getDaysInMonth());
	$last_time->setTime(23, 59, 59);
	$links = array();
	getTaskLinks($first_time, $last_time, $links, 20, $company_id, true);
	getEventLinks($first_time, $last_time, $links, 20, true);
	$minical->setEvents($links);

	$minical->setDate($minical->next_month);

	echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
	echo '<td align="center" >' . $minical->show() . '</td>';
	echo '</tr></table>';
?>
        </td>
 <?php } ?>
</tr>
</table>