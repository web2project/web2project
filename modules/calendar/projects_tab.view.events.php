<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig, $start_date, $end_date, $this_day, $event_filter, $event_filter_list;

//TODO: This is a hack until we can refactor getEventTooltip() somewhere else..
include 'links_events.php';

$perms = &$AppUI->acl();
$user_id = $AppUI->user_id;
$other_users = false;
$no_modify = false;

$start_date =  new w2p_Utilities_Date('2001-01-01 00:00:00');
$end_date =  new w2p_Utilities_Date('2100-12-31 23:59:59');

// assemble the links for the events
$events = CEvent::getEventsForPeriod($start_date, $end_date, 'all', 0, $project_id);

$start_hour = w2PgetConfig('cal_day_start');
$end_hour = w2PgetConfig('cal_day_end');

$tf = $AppUI->getPref('TIMEFORMAT');
$df = $AppUI->getPref('SHDATEFORMAT');
$types = w2PgetSysVal('EventType');

$html = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
$html .= '<tr><th>' . $AppUI->_('Date') . '</th><th>' . $AppUI->_('Type') . '</th><th>' . $AppUI->_('Event') . '</th></tr>';
foreach ($events as $row) {
	$html .= '<tr>';
	$start = new w2p_Utilities_Date($row['event_start_date']);
	$end = new w2p_Utilities_Date($row['event_end_date']);
	$html .= '<td width="25%" nowrap="nowrap">' . $start->format($df . ' ' . $tf) . '&nbsp;-&nbsp;';
	$html .= $end->format($df . ' ' . $tf) . '</td>';

	$href = '?m=calendar&a=view&event_id=' . $row['event_id'];
	$alt = $row['event_description'];

	$html .= '<td width="10%" nowrap="nowrap">';
	$html .= w2PshowImage('event' . $row['event_type'] . '.png', 16, 16, '', '', 'calendar');
	$html .= '&nbsp;<b>' . $AppUI->_($types[$row['event_type']]) . '</b><td>';

    $html .= w2PtoolTip($row['event_title'], getEventTooltip($row['event_id']), true);
	$html .= '<a href="' . $href . '" class="event">';
	$html .= $row['event_title'];
	$html .= '</a>';
    $html .= w2PendTip();

	$html .= '</td></tr>';
}
$html .= '</table>';
echo $html;