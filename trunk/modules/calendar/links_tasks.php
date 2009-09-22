<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * Sub-function to collect tasks within a period
 *
 * @param Date the starting date of the period
 * @param Date the ending date of the period
 * @param array by-ref an array of links to append new items to
 * @param int the length to truncate entries by
 * @param int the company id to filter by
 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
 */
function getTaskLinks($startPeriod, $endPeriod, &$links, $strMaxLen, $company_id = 0, $minical = false) {
	global $a, $AppUI, $w2Pconfig;
	$tasks = CTask::getTasksForPeriod($startPeriod, $endPeriod, $company_id, 0);
	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');
	//subtract one second so we don't have to compare the start dates for exact matches with the startPeriod which is 00:00 of a given day.
	$startPeriod->subtractSeconds(1);

	$link = array();
	$sid = 3600 * 24;
	
	// assemble the links for the tasks
	foreach ($tasks as $row) {
		// the link
		$link['task'] = true;

		if (!$minical) {
			$link['href'] = '?m=tasks&a=view&task_id=' . $row['task_id'];
			// the link text
			if (mb_strlen($row['task_name']) > $strMaxLen) {
				$row['short_name'] = mb_substr($row['task_name'], 0, $strMaxLen) . '...';
			} else {
				$row['short_name'] = $row['task_name'];
			}
	
			$link['text'] = '<span style="color:' . bestColor($row['color']) . ';background-color:#' . $row['color'] . '">' . $row['short_name'] . ($row['task_milestone'] ? '&nbsp;' . w2PshowImage('icons/milestone.gif') : '') . '</span>';
		}

		// determine which day(s) to display the task
		$start = new CDate($row['task_start_date']);
		$end = $row['task_end_date'] ? new CDate($row['task_end_date']) : null;

		// First we test if the Tasks Starts and Ends are on the same day, if so we don't need to go any further.
		if (($start->after($startPeriod)) && ($end && $end->after($startPeriod) && $end->before($endPeriod) && !($start->dateDiff($end)))) {
			if ($minical) {
				$temp = array('task' => true);
			} else {
				$temp = $link;
				if ($a != 'day_view') {
					$temp['text'] = w2PtoolTip($row['task_name'], getTaskTooltip($row['task_id'], true, true, $tasks), true) . w2PshowImage('block-start-16.png') . $start->format($tf) . ' ' . $temp['text'] . ' ' . $end->format($tf) . w2PshowImage('block-end-16.png') . w2PendTip();
				}
			}
			$links[$end->format(FMT_TIMESTAMP_DATE)][] = $temp;
		} else {
			// If they aren't, we will now need to see if the Tasks Start date is between the requested period
			if ($start->after($startPeriod) && $start->before($endPeriod)) {
				if ($minical) {
					$temp = array('task' => true);
				} else {
					$temp = $link;
					if ($a != 'day_view') {
						$temp['text'] = w2PtoolTip($row['task_name'], getTaskTooltip($row['task_id'], true, false, $tasks), true) . w2PshowImage('block-start-16.png') . $start->format($tf) . ' ' . $temp['text'] . w2PendTip();
					}
				}
				$links[$start->format(FMT_TIMESTAMP_DATE)][] = $temp;
			}
			// And now the Tasks End date is checked if it is between the requested period too.
			if ($end && $end->after($startPeriod) && $end->before($endPeriod) && $start->before($end)) {
				if ($minical) {
					$temp = array('task' => true);
				} else {
					$temp = $link;
					if ($a != 'day_view') {
						$temp['text'] = w2PtoolTip($row['task_name'], getTaskTooltip($row['task_id'], false, true, $tasks), true) . ' ' . $temp['text'] . ' ' . $end->format($tf) . w2PshowImage('block-end-16.png') . w2PendTip();
					}
				}
				$links[$end->format(FMT_TIMESTAMP_DATE)][] = $temp;	
			}
		}
	}
}

function getTaskTooltip($task_id, $starts = false, $ends = false, $tasks_tips ) {
	global $AppUI;

	if (!$task_id) {
		return '';	
	}

	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');

	$task = new CTask();

	$hash = $tasks_tips[$task_id];
	// load the record data
	$task->bind($hash, null, false, true);

	// load the event types
	$types = w2PgetSysVal('TaskType');

	$assigned = $task->getAssigned();

	$start_date = (int)$task->task_start_date ? new CDate($task->task_start_date) : null;
	$end_date = (int)$task->task_end_date ? new CDate($task->task_end_date) : null;
	// load the record data
	$task_project = $task->project_name;
	$task_company = $task->company_name;

	$tt = '<table border="0" cellpadding="0" cellspacing="0" width="96%">';
	$tt .= '<tr>';
	$tt .= '	<td valign="top" width="50%">';
	$tt .= '		<strong>' . $AppUI->_('Details') . '</strong>';
	$tt .= '		<table cellspacing="3" cellpadding="2" width="100%">';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Company') . '</td>';
	$tt .= '			<td width="100%">' . $task_company . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Project') . '</td>';
	$tt .= '			<td width="100%">' . $task_project . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Type') . '</td>';
	$tt .= '			<td width="100%" nowrap="nowrap">' . $AppUI->_($types[$task->task_type]) . '</td>';
	$tt .= '		</tr>	';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Progress') . '</td>';
	$tt .= '			<td width="100%" nowrap="nowrap"><strong>' . sprintf("%.1f%%", $task->task_percent_complete) . '</strong></td>';
	$tt .= '		</tr>	';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Starts') . '</td>';
	$tt .= '			<td nowrap="nowrap">' . ($starts ? '<strong>' : '') . ($start_date ? $start_date->format($df . ' ' . $tf) : '-') . ($starts ? '</strong>' : '') . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Ends') . '</td>';
	$tt .= '			<td nowrap="nowrap">' . ($ends ? '<strong>' : '') . ($end_date ? $end_date->format($df . ' ' . $tf) : '-') . ($ends ? '</strong>' : '') . '</td>';
	$tt .= '		</tr>';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;" align="right" nowrap="nowrap">' . $AppUI->_('Assignees') . '</td>';
	$tt .= '			<td nowrap="nowrap">';
	if (is_array($assigned)) {
		$start = false;
		foreach ($assigned as $user) {
			if ($start) {
				$tt .= '<br/>';
			} else {
				$start = true;
			}
			$tt .= $user['user_name'] . ' ' . $user['perc_assignment'] . '%';
		}
	}
	$tt .= '		</tr>';
	$tt .= '		</table>';
	$tt .= '	</td>';
	$tt .= '	<td width="50%" valign="top">';
	$tt .= '		<strong>' . $AppUI->_('Description') . '</strong>';
	$tt .= '		<table cellspacing="0" cellpadding="2" border="0" width="100%">';
	$tt .= '		<tr>';
	$tt .= '			<td style="border: 1px solid white;-moz-border-radius:3.5px;-webkit-border-radius:3.5px;">';
	$tt .= '				' . $task->task_description;
	$tt .= '			</td>';
	$tt .= '		</tr>';
	$tt .= '		</table>';
	$tt .= '	</td>';
	$tt .= '</tr>';
	$tt .= '</table>';
	return $tt;
}