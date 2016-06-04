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

global $locale_char_set;

// retrieve any state parameters
if (isset($_REQUEST['company_id'])) {
	$AppUI->setState('CalIdxCompany', (int) w2PgetParam($_REQUEST, 'company_id', 0));
}
$company_id = $AppUI->getState('CalIdxCompany') !== null ? $AppUI->getState('CalIdxCompany') : $AppUI->user_company;

// Using simplified set/get semantics. Doesn't need as much code in the module.
$event_filter = $AppUI->checkPrefState('CalIdxFilter', w2PgetParam($_REQUEST, 'event_filter', 'my'), 'EVENTFILTER', 'my');

// get the passed timestamp (today if none)
$date = w2PgetParam($_GET, 'date', null);

$today = new w2p_Utilities_Date();
$today->convertTZ($AppUI->getPref('TIMEZONE'));
$today = $today->format(FMT_TIMESTAMP_DATE);

// establish the focus 'date'
$this_week = new w2p_Utilities_Date($date);
$dd = $this_week->getDay();
$mm = $this_week->getMonth();
$yy = $this_week->getYear();

// prepare time period for 'events'
$first_time = new w2p_Utilities_Date(Date_Calc::beginOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$first_time->setTime(0, 0, 0);
$last_time = new w2p_Utilities_Date(Date_Calc::endOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$last_time->setTime(23, 59, 59);

$prev_week = new w2p_Utilities_Date(Date_Calc::beginOfPrevWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$next_week = new w2p_Utilities_Date(Date_Calc::beginOfNextWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));

$links = array();

// assemble the links for the tasks
$links = getTaskLinks($first_time, $last_time, $links, 50, $company_id);

// assemble the links for the events
$links += getEventLinks($first_time, $last_time, $links, 50);

$hooks = new w2p_System_HookHandler($AppUI);
$hooks->links = $links;
$links = $hooks->calendar_links();

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);
$event_filter_list = array('my' => 'My Events', 'own' => 'Events I Created', 'all' => 'All Events');

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Week View', 'icon.png', $m);
$titleBlock->addCrumb('?m=events&a=year_view&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'year view');
$titleBlock->addCrumb('?m=events&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'month view');
$titleBlock->addCrumb('?m=events&a=week_view&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'week view');
$titleBlock->addCrumb('?m=events&a=day_view&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'day view');
$titleBlock->addCell('<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany" accept-charset="utf-8">' . arraySelect($companies, 'company_id', 'onchange="document.pickCompany.submit()" class="text"', $company_id) . '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCell('<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="pickFilter" accept-charset="utf-8">' . arraySelect($event_filter_list, 'event_filter', 'onchange="document.pickFilter.submit()" class="text"', $event_filter, true) . '</form>');
$titleBlock->addCell($AppUI->_('Event Filter') . ':');
$titleBlock->addButton('New event', '?m=events&a=addedit&date=' . $today);
$titleBlock->show();
?>
<table border="0" cellspacing="0" cellpadding="2" width="100%" class="motitle">
<tr>
	<td>
		<a href="<?php echo '?m=events&a=week_view&date=' . $prev_week->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" alt="pre" /></a>
	</td>
	<th width="100%">
		<?php echo $AppUI->_('Week') . ' ' . $first_time->format('%U - %Y') . ' - ' . $AppUI->_($first_time->format('%B')); ?>
	</th>
	<td>
		<a href="<?php echo '?m=events&a=week_view&date=' . $next_week->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" alt="next" /></a>
	</td>
</tr>
</table>

<table border="0" cellspacing="1" cellpadding="2" width="100%" class="view week">
<?php

$workingDays = explode(',', w2PgetConfig('cal_working_days'));

$show_day = $this_week;

$s = '';
$s .= '<tr>';
for ($i = 0; $i < 7; $i++) {

    $class = (in_array($i, $workingDays)) ? 'workingDay' : 'otherDay';
    $s .= '<td class="'.$class.'">';

	$dayStamp = $show_day->format(FMT_TIMESTAMP_DATE);
	$href = '?m=events&a=day_view&date='.$dayStamp.'&tab=0';

	$s .= '		<table>';
	$s .= '		<tr><td align="left"><a href="' . $href . '">';

	$s .= $dayStamp == $today ? '<span style="color:red">' : '';
	$day_string = "<strong>" . $show_day->format('%d') . '</strong>';
	$day_name = $AppUI->_($show_day->format('%A'));
	$s .= $day_string . ' ' . $day_name;
	$s .= $dayStamp == $today ? '</span>' : '';
	$s .= '</a></td></tr>';

	$s .= '<tr><td>';

	if (isset($links[$dayStamp])) {
		foreach ($links[$dayStamp] as $e) {
            $href = isset($e['href']) ? $e['href'] : null;
            $alt = isset($e['alt']) ? $e['alt'] : null;

            $link  = $href ? '<a href="'.$href.'" title="'.$alt.'">' : '';
            $link .= $e['text'];
            $link .= $href ? '</a>' : '';

            $s .= '<br /><span class="cal-item">' . $link . '</span>';
		}
	}

	$s .= '</td></tr></table>';

	$s .= '</td>';

	// select next day
	$show_day->addSeconds(24 * 3600);
}
$s .= '</tr>';
echo $s;
?>
<tr>
	<td colspan="7" align="right" bgcolor="#efefe7">
		<a href="./index.php?m=events&a=day_view"><?php echo $AppUI->_('today'); ?></a>
	</td>
</tr>
</table>