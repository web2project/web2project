<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $this_day, $first_time, $last_time, $company_id, $event_filter, $event_filter_list, $AppUI;

// load the event types
$types = w2PgetSysVal('EventType');
$links = array();

$perms = &$AppUI->acl();
$user_id = $AppUI->user_id;
$other_users = false;
$no_modify = false;

if ($perms->checkModule('admin', 'view')) {
	$other_users = true;
	if (($show_uid = w2PgetParam($_REQUEST, 'show_user_events', 0)) != 0) {
		$user_id = $show_uid;
		$no_modify = true;
		$AppUI->setState('event_user_id', $user_id);
	}
}

// assemble the links for the events
$events = CEvent::getEventsForPeriod($first_time, $last_time, $event_filter, $user_id);
$events2 = array();

$start_hour = w2PgetConfig('cal_day_start');
$end_hour = w2PgetConfig('cal_day_end');

foreach ($events as $row) {
	$start = new CDate($row['event_start_date']);
	$end = new CDate($row['event_end_date']);

	$events2[$start->format('%H%M%S')][] = $row;

	if ($start_hour > $start->format('%H')) {
		$start_hour = $start->format('%H');
	}
	if ($end_hour < $end->format('%H')) {
		$end_hour = $end->format('%H');
	}
}

$tf = $AppUI->getPref('TIMEFORMAT');

$dayStamp = $this_day->format(FMT_TIMESTAMP_DATE);

$start = $start_hour;
$end = $end_hour;
$inc = w2PgetConfig('cal_day_increment');

if ($start === null)
	$start = 8;
if ($end === null)
	$end = 17;
if ($inc === null)
	$inc = 15;

$this_day->setTime($start, 0, 0);

$html = '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="pickFilter">';
$html .= $AppUI->_('Event Filter') . ':' . arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"', $event_filter, true);
if ($other_users) {
	$html .= $AppUI->_('Show Events for') . ':' . '<select name="show_user_events" onchange="document.pickFilter.submit()" class="text">';

	if (($rows = w2PgetUsersList())) {
		foreach ($rows as $row) {
			if ($user_id == $row['user_id'])
				$html .= '<option value="' . $row['user_id'] . '" selected="selected">' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
			else
				$html .= '<option value="' . $row['user_id'] . '">' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
		}
	}
	$html .= '</select>';

}

require_once (W2P_BASE_DIR . '/modules/calendar/links_events.php');

$html .= '</form>';
$html .= '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
$rows = 0;
for ($i = 0, $n = ($end - $start) * 60 / $inc; $i < $n; $i++) {
	$html .= '<tr>';

	$tm = $this_day->format($tf);
	$html .= '<td width="1%" align="right" nowrap="nowrap">' . ($this_day->getMinute() ? $tm : '<b>' . $tm . '</b>') . '</td>';

	$timeStamp = $this_day->format('%H%M%S');
	if ($events2[$timeStamp]) {
		$count = count($events2[$timeStamp]);
		for ($j = 0; $j < $count; $j++) {
			$row = $events2[$timeStamp][$j];

			$et = new CDate($row['event_end_date']);
			$rows = (($et->getHour() * 60 + $et->getMinute()) - ($this_day->getHour() * 60 + $this_day->getMinute())) / $inc;

			$href = '?m=calendar&a=view&event_id=' . $row['event_id'];
			$alt = $row['event_description'];

			$html .= '<td class="event" rowspan="' . $rows . '" valign="top">';

			$html .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
			$html .= '<td>' . w2PshowImage('event' . $row['event_type'] . '.png', 16, 16, '', '', 'calendar');
			$html .= '</td><td>&nbsp;<b>' . $AppUI->_($types[$row['event_type']]) . '</b></td></tr></table>';
			$html .= w2PtoolTip($row['event_title'], getEventTooltip($row['event_id']), true);
			$html .= $href ? '<a href="' . $href . '" class="event">' : '';
			$html .= $row['event_title'];
			$html .= $href ? '</a>' : '';
			$html .= w2PendTip();
			$html .= '</td>';
		}
	} else {
		if (--$rows <= 0) {
			$html .= '<td></td>';
		}
	}

	$html .= '</tr>';

	$this_day->addSeconds(60 * $inc);
}

$html .= '</table>';
echo $html;
?>