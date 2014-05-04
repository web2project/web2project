<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = canView($m);

if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

global $tab, $locale_char_set, $date;

$company_id = $AppUI->processIntState('CalIdxCompany', $_REQUEST, 'company_id', $AppUI->user_company);

$event_filter = $AppUI->checkPrefState('CalIdxFilter', w2PgetParam($_REQUEST, 'event_filter', ''), 'EVENTFILTER', 'my');

$tab = $AppUI->processIntState('CalDayViewTab', $_GET, 'tab', (isset($tab) ? $tab : 0));

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// get the passed timestamp (today if none)
$ctoday = new w2p_Utilities_Date();
$today = $ctoday->format(FMT_TIMESTAMP_DATE);
$date = (int) w2PgetParam($_GET, 'date', $today);
// establish the focus 'date'
$this_day = new w2p_Utilities_Date($date);
$dd = $this_day->getDay();
$mm = $this_day->getMonth();
$yy = $this_day->getYear();

// get current week
$this_week = Date_Calc::beginOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY);

// prepare time period for 'events'
$first_time =  clone $this_day;
$first_time->setTime(0, 0, 0);

$last_time = clone $this_day;
$last_time->setTime(23, 59, 59);

$prev_day = new w2p_Utilities_Date(Date_Calc::prevDay($dd, $mm, $yy, FMT_TIMESTAMP_DATE));
$next_day = new w2p_Utilities_Date(Date_Calc::nextDay($dd, $mm, $yy, FMT_TIMESTAMP_DATE));

// get the list of visible companies
$company = new CCompany();
global $companies;
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Day View', 'icon.png', $m);
$titleBlock->addCrumb('?m=events&a=year_view&date=' . $this_day->format(FMT_TIMESTAMP_DATE), 'year view');
$titleBlock->addCrumb('?m=events&date=' . $this_day->format(FMT_TIMESTAMP_DATE), 'month view');
$titleBlock->addCrumb('?m=events&a=week_view&date=' . $this_week, 'week view');
$titleBlock->addCrumb('?m=events&a=day_view&date=' . $this_day->format(FMT_TIMESTAMP_DATE), 'day view');
$titleBlock->addCell(arraySelect($companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id), '', '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany" accept-charset="utf-8">', '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addButton('New event', '?m=events&a=addedit&date=' . $today);
$titleBlock->show();
?>
<script language="javascript">
function clickDay( idate, fdate ) {
        window.location = './index.php?m=events&a=day_view&date='+idate+'&tab=0';
}
</script>

<table class="std">
    <tr>
        <td valign="top">
            <table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
                <tr>
                    <td>
                        <a href="<?php echo '?m=events&a=day_view&date=' . $prev_day->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" alt="pre" /></a>
                    </td>
                    <th width="100%">
                        <?php echo $AppUI->_(htmlspecialchars($this_day->format('%A'), ENT_COMPAT, $locale_char_set)) . ', ' . $this_day->format($df); ?>
                    </th>
                    <td>
                        <a href="<?php echo '?m=events&a=day_view&date=' . $next_day->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" alt="next" /></a>
                    </td>
                </tr>
            </table>

            <?php
                // tabbed information boxes
                $tabBox = new CTabBox('?m=events&a=day_view&date=' . $this_day->format(FMT_TIMESTAMP_DATE), W2P_BASE_DIR . '/modules/events/', $tab);
                $tabBox->add('vw_day_events', 'Events');
                $tabBox->add('vw_day_tasks', 'Tasks');
                $tabBox->show();
            ?>
        </td>
<?php if ($w2Pconfig['cal_day_view_show_minical']) { ?>
        <td valign="top" width="175">
<?php
	$minical = new w2p_Output_MonthCalendar($this_day);
	$minical->setStyles('minititle', 'minical');
	$minical->showArrows = false;
	$minical->showWeek = false;
	$minical->clickMonth = true;
	$minical->setLinkFunctions('clickDay');

	$first_time = new w2p_Utilities_Date($minical->prev_month);
	$first_time->setDay(1);
	$first_time->setTime(0, 0, 0);
	$last_time = new w2p_Utilities_Date($minical->prev_month);
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

	$first_time = new w2p_Utilities_Date($minical->next_month);
	$first_time->setDay(1);
	$first_time->setTime(0, 0, 0);
	$last_time = new w2p_Utilities_Date($minical->next_month);
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

	$first_time = new w2p_Utilities_Date($minical->next_month);
	$first_time->setDay(1);
	$first_time->setTime(0, 0, 0);
	$last_time = new w2p_Utilities_Date($minical->next_month);
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