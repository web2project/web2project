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

$AppUI->savePlace();
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
$today = $today->format(FMT_TIMESTAMP_DATE);

// establish the focus 'date'
$this_week = new w2p_Utilities_Date($date);
$dd = $this_week->getDay();
$mm = $this_week->getMonth();
$yy = $this_week->getYear();

// prepare time period for 'events'
$first_time = new w2p_Utilities_Date(Date_calc::beginOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$first_time->setTime(0, 0, 0);
$last_time = new w2p_Utilities_Date(Date_calc::endOfWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$last_time->setTime(23, 59, 59);

$prev_week = new w2p_Utilities_Date(Date_calc::beginOfPrevWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));
$next_week = new w2p_Utilities_Date(Date_calc::beginOfNextWeek($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY));

$links = array();

// assemble the links for the tasks
getTaskLinks($first_time, $last_time, $links, 50, $company_id);

// assemble the links for the events
getEventLinks($first_time, $last_time, $links, 50);

$hooks = new w2p_Core_HookHandler($AppUI);
$hooks->links = $links;
$links = $hooks->calendar_links();

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('All')), $companies);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Week View', 'myevo-appointments.png', $m, "$m.$a");
$titleBlock->addCrumb('?m=calendar&a=year_view&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'year view');
$titleBlock->addCrumb('?m=calendar&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'month view');
$titleBlock->addCrumb('?m=calendar&a=week_view&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'week view');
$titleBlock->addCrumb('?m=calendar&a=day_view&date=' . $this_week->format(FMT_TIMESTAMP_DATE), 'day view');
$titleBlock->addCell('<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany" accept-charset="utf-8">' . arraySelect($companies, 'company_id', 'onchange="document.pickCompany.submit()" class="text"', $company_id) . '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCell('<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="pickFilter" accept-charset="utf-8">' . arraySelect($event_filter_list, 'event_filter', 'onchange="document.pickFilter.submit()" class="text"', $event_filter, true) . '</form>');
$titleBlock->addCell($AppUI->_('Event Filter') . ':');
$titleBlock->addCell('<form action="?m=calendar&a=addedit&date=' . $today . '" method="post" accept-charset="utf-8">' . '<input type="submit" class="button" value="' . $AppUI->_('new event') . '">' . '</form>');
$titleBlock->show();
?>
<table border="0" cellspacing="0" cellpadding="2" width="100%" class="motitle">
<tr>
	<td>
		<a href="<?php echo '?m=calendar&a=week_view&date=' . $prev_week->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" width="16" height="16" alt="pre" border="0"></a>
	</td>
	<th width="100%">
		<span style="font-size:12pt"><?php echo $AppUI->_('Week') . ' ' . $first_time->format('%U - %Y') . ' - ' . $AppUI->_($first_time->format('%B')); ?></span>
	</th>
	<td>
		<a href="<?php echo '?m=calendar&a=week_view&date=' . $next_week->format(FMT_TIMESTAMP_DATE); ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" width="16" height="16" alt="next" border="0"></a>
	</td>
</tr>
</table>

<table border="0" cellspacing="1" cellpadding="2" width="100%" style="margin-width:4px;background-color:white" class="view">
<?php
$show_day = $this_week;

$s = '';
$s .= '<tr>';
for ($i = 0; $i < 7; $i++) {
	$dayStamp = $show_day->format(FMT_TIMESTAMP_DATE);

	$day = $show_day->getDay();
	$href = '?m=calendar&a=day_view&date='.$dayStamp.'&tab=0';

	$dow = (int) $show_day->format('%w');
	if ($dow == 0 || $dow == 6) {
		$s .= '<td class="weekendDay" style="width:14.29%;">';
	} else {
		$s .= '<td class="weekDay" style="width:14.29%;">';
	}
	
	$s .= '		<table style="width:100%;border-spacing:0;">';
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

			$s .= '<br />';
			$s .= $href ? '<a href="'.$href.'" class="event" title="'.$alt.'">' : '';
			$s .= $e['text'];
			$s .= $href ? '</a>' : '';
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
		<a href="./index.php?m=calendar&a=day_view"><?php echo $AppUI->_('today'); ?></a>
	</td>
</tr>
</table>