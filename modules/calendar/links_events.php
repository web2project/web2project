<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * Sub-function to collect events within a period
 * @param Date the starting date of the period
 * @param Date the ending date of the period
 * @param array by-ref an array of links to append new items to
 * @param int the length to truncate entries by
 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
 */
function getEventLinks($startPeriod, $endPeriod, &$links, $strMaxLen, $minical = false) {
	global $event_filter;
	$events = CEvent::getEventsForPeriod($startPeriod, $endPeriod, $event_filter);
	$cwd = explode(',', w2PgetConfig('cal_working_days'));

	// assemble the links for the events
	foreach ($events as $row) {
		$start = new w2p_Utilities_Date($row['event_start_date']);
		$end = new w2p_Utilities_Date($row['event_end_date']);
		$date = $start;

		for ($i = 0, $i_cmp = $start->dateDiff($end); $i <= $i_cmp; $i++) {
			// the link
			// optionally do not show events on non-working days
			if (($row['event_cwd'] && in_array($date->getDayOfWeek(), $cwd)) || !$row['event_cwd']) {
				if ($minical) {
					$link = array();
				} else {
					$url = '?m=calendar&a=view&event_id=' . $row['event_id'];
					$link['href'] = '';
					$link['alt'] = '';
					$link['text'] = w2PtoolTip($row['event_title'], getEventTooltip($row['event_id']), true) . w2PshowImage('event' . $row['event_type'] . '.png', 16, 16, '', '', 'calendar') . '</a>&nbsp;' . '<a href="' . $url . '"><span class="event">' . $row['event_title'] . '</span></a>' . w2PendTip();
				}	
				$links[$date->format(FMT_TIMESTAMP_DATE)][] = $link;
			}
			$date = $date->getNextDay();
		}
	}
}

function getEventTooltip($event_id) {
	global $AppUI;

	if (!$event_id) {
		return '';	
	}

	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');

	// load the record data

	$event = new CEvent();
	$event->loadFull($event_id);

	// load the event types
	$types = w2PgetSysVal('EventType');

	// load the event recurs types
	$recurs = array('Never', 'Hourly', 'Daily', 'Weekly', 'Bi-Weekly', 'Every Month', 'Quarterly', 'Every 6 months', 'Every Year');

	$obj = new CEvent();
	$obj->event_id = $event_id;
	$assigned = $obj->getAssigned();

	$start_date = $event->event_start_date ? new w2p_Utilities_Date($event->event_start_date) : null;
	$end_date = $event->event_end_date ? new w2p_Utilities_Date($event->event_end_date) : null;
	if ($event->event_project) {
		$event_project = $event->project_name;
		$event_company = $event->company_name;
	}

	$tt = '<table border="0" cellpadding="0" cellspacing="0" width="96%">';
	$tt .= '<tr>';
	$tt .= '	<td valign="top" width="50%">';
	$tt .= '		<strong>' . $AppUI->_('Details') . '</strong>';
	$tt .= '		<table cellspacing="3" cellpadding="2" width="100%">';
	$tt .= '		<tr>';
	$tt .= '			<td style="text-color:white;border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Type') . '</td>';
	$tt .= '			<td width="100%" nowrap="nowrap">' . $AppUI->_($types[$event->event_type]) . '</td>';
	$tt .= '		</tr>	';
	if ($event->event_project) {
		$tt .= '		<tr>';
		$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Company') . '</td>';
		$tt .= '			<td width="100%">' . $event_company . '</td>';
		$tt .= '		</tr>';
		$tt .= '		<tr>';
		$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Project') . '</td>';
		$tt .= '			<td width="100%">' . $event_project . '</td>';
		$tt .= '		</tr>';
	}
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Starts') . '</td>';
    $tt .= '			<td nowrap="nowrap">' . $AppUI->formatTZAwareTime($event->event_start_date, $df . ' ' . $tf) . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Ends') . '</td>';
    $tt .= '			<td nowrap="nowrap">' . $AppUI->formatTZAwareTime($event->event_end_date, $df . ' ' . $tf) . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Recurs') . '</td>';
	$tt .= '			<td nowrap="nowrap">' . $AppUI->_($recurs[$event->event_recurs]) . ($event->event_recurs ? ' (' . $event->event_times_recuring . '&nbsp;' . $AppUI->_('times') . ')' : '') . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Attendees') . '</td>';
	$tt .= '			<td nowrap="nowrap">';
	if (is_array($assigned)) {
		$start = false;
		foreach ($assigned as $user) {
			if ($start) {
				$tt .= '<br />';
			} else {
				$start = true;
			}
			$tt .= $user;
		}
	}
	$tt .= '		</tr>';
	$tt .= '		</table>';
	$tt .= '	</td>';
	$tt .= '	<td width="50%" valign="top">';
	$tt .= '		<strong>' . $AppUI->_('Note') . '</strong>';
	$tt .= '		<table cellspacing="0" cellpadding="2" border="0" width="100%">';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;">';
	$tt .= '				' . mb_str_replace(chr(10), "<br />", $event->event_description) . '&nbsp;';
	$tt .= '			</td>';
	$tt .= '		</tr>';
	$tt .= '		</table>';
	$tt .= '	</td>';
	$tt .= '</tr>';
	$tt .= '</table>';
	return $tt;
}