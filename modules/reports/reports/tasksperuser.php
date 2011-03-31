<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$log_all = w2PgetParam($_POST['log_all'], 0);
$use_period = w2PgetParam($_POST, 'use_period', 0);
$display_week_hours = w2PgetParam($_POST, 'display_week_hours', 0);
$max_levels = w2PgetParam($_POST, 'max_levels', 'max');
$log_userfilter = w2PgetParam($_POST, 'log_userfilter', '');
$log_open = w2PgetParam($_POST, 'log_open', 0);
$pdf_output = w2PgetParam($_POST, 'pdf_output', 0);

$table_header = '';
$table_rows = '';

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);
?>

<script language="javascript">
function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'log_' + f_date );
	if (fld_date.value.length>0) {
      if ((parseDate(fld_date.value))==null) {
            alert('The Date/Time you typed does not match your prefered format, please retype.');
            fld_real_date.value = '';
            fld_date.style.backgroundColor = 'red';
        } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
        	fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
            fld_date.style.backgroundColor = '';
  		}
	} else {
      	fld_real_date.value = '';
	}
}
</script>

<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td nowrap="nowrap">
        <select name="log_userfilter" class="text" style="width: 200px">
  
	   	 	<?php
if ($log_userfilter == 0)
	echo '<option value="0" selected="selected">' . $AppUI->_('All users');
else
	echo '<option value="0">All users';

if (($log_userfilter_users = w2PgetUsersList())) {
	foreach ($log_userfilter_users as $row) {
		$selected = '';
		if ($log_userfilter == $row['user_id']) {
			$selected = ' selected="selected"';
		}
		echo '<option value="' . $row['user_id'] . '"' . $selected . '>' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
	}
}

?>
	
   	     </select>

	</td>

	<td nowrap="nowrap" rowspan="2">
		<table>
		<tr><td>
			<input type="checkbox" name="use_period" id="use_period" <?php if ($use_period)
	echo 'checked="checked"' ?> />
			<label for="use_period"><?php echo $AppUI->_('Use the period'); ?></label>
		</td></tr>
		<tr><td>
			<input type="checkbox" name="display_week_hours" id="display_week_hours" <?php if ($display_week_hours)
	echo 'checked="checked"' ?> />
			<label for="display_week_hours"><?php echo $AppUI->_('Display allocated hours/week'); ?></label>
		</td></tr> 
		</table>
	</td> 
	
	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to:'); ?></td>
	<td>
		<input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td>
		<?php echo $AppUI->_('Levels to display'); ?>
		<input type="text" name="max_levels" size="10" maxlength="3" <?php $max_levels ?> />
	</td>

</tr>

</table>
</form>
<?php
if ($do_report) {

	if (function_exists('styleRenderBoxBottom')) {
		echo styleRenderBoxBottom();
	}
	echo '<br />';
	if (function_exists('styleRenderBoxTop')) {
		echo styleRenderBoxTop();
	}
	echo '<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
	<tr>
		<td align="center">';

	// Let's figure out which users we have

	$user_list = w2PgetUsersHashList();
	if ($log_userfilter != 0) {
		$user_list = array($log_userfilter => $user_list[$log_userfilter]);
	}

	$ss = "'" . $start_date->format(FMT_DATETIME_MYSQL) . "'";
	$se = "'" . $end_date->format(FMT_DATETIME_MYSQL) . "'";

	$and = false;
	$where = false;

	$q = new w2p_Database_Query;
	$q->addTable('tasks', 't');
	$q->addQuery('t.*');
	$q->addJoin('projects', '', 'projects.project_id = task_project', 'inner');
	$q->addJoin('project_departments', '', 'project_departments.project_id = projects.project_id');
	$q->addJoin('departments', '', 'department_id = dept_id');
	$q->addWhere('project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('project_status <> ' . (int)$template_status);
	}

	if ($use_period) {
		$q->addWhere('( (task_start_date >= ' . $ss . ' AND task_start_date <= ' . $se . ') OR ' . '(task_end_date <= ' . $se . ' AND task_end_date >= ' . $ss . ') )');
	}

	if ($project_id != 0) {
		$q->addWhere('task_project=' . $project_id);
	}

	$proj = new CProject();
	$obj = new CTask();
	$allowedProjects = $proj->getAllowedSQL($AppUI->user_id, 'task_project');
	$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);

	if (count($allowedProjects)) {
		$q->addWhere(implode(' AND ', $allowedProjects));
	}

	if (count($allowedTasks)) {
		$q->addWhere(implode(' AND ', $allowedTasks));
	}

	$q->addOrder('task_end_date');

	$task_list_hash = $q->loadHashList('task_id');
	$q->clear();
	$task_list = array();
	$task_assigned_users = array();
	$i = 0;
	foreach ($task_list_hash as $task_id => $task_data) {
		$task = new CTask();
		$task->bind($task_data);
		$task_list[$i] = $task;
		$task_assigned_users[$i] = $task->getAssignedUsers($task_id);
		$i += 1;
	}
	$Ntasks = $i;

	$user_usage = array();
	$task_dates = array();

	$actual_date = $start_date;
	$days_header = ""; // we will save days title here

	if (strtolower($max_levels) == 'max') {
		$max_levels = -1;
	} elseif ($max_levels == '') {
		$max_levels = -1;
	} else {
		$max_levels = atoi($max_levels);
	}
	if ($max_levels == 0) {
		$max_levels = 1;
	}
	if ($max_levels < 0) {
		$max_levels = -1;
	}

	if (count($task_list) == 0) {
		echo '<p>' . $AppUI->_('No data available') . '</p>';
	} else {

		$sss = $ss;
		$sse = $se;
		if (!$use_period) {
			$sss = -1;
			$sse = -1;
		}
		if ($display_week_hours and !$use_period) {
			foreach ($task_list as $t) {
				if ($sss == -1) {
					$sss = $t->task_start_date;
					$sse = $t->task_end_date;
				} else {
					if ($t->task_start_date < $sss) {
						$sss = $t->task_start_date;
					}
					if ($t->task_end_date > $sse) {
						$sse = $t->task_end_date;
					}
				}
			}
		}

		$table_header = '
			<tr>
				<td nowrap="nowrap" bgcolor="#A0A0A0">
				<font color="black"><b>' . $AppUI->_('Task') . '</b></font> </td>' . ($project_id == 0 ? '<td nowrap="nowrap" bgcolor="#A0A0A0"><font color="black"><b>' . $AppUI->_('Project') . '</b></font></td>' : '') . '
				<td nowrap="nowrap" bgcolor="#A0A0A0"><font color="black"><b>' . $AppUI->_('Start Date') . '</b></font></td>
				<td nowrap="nowrap" bgcolor="#A0A0A0"><font color="black"><b>' . $AppUI->_('End Date') . '</b></font></td>' . weekDates($display_week_hours, $sss, $sse) . '
			</tr>';
		$table_rows = '';

		foreach ($user_list as $user_id => $user_data) {

			$tmpuser = "<tr><td align='left' nowrap='nowrap' bgcolor='#D0D0D0'><font color='black'><B>" . $user_data["contact_first_name"] . ' ' . $user_data['contact_last_name'] . '</b></font></td>';
			for ($w = 0, $w_cmp = (1 + ($project_id == 0 ? 1 : 0) + weekCells($display_week_hours, $sss, $sse)); $w <= $w_cmp; $w++) {
				$tmpuser .= '<td bgcolor="#D0D0D0">&nbsp;</td>';
			}
			$tmpuser .= '</tr>';

			$tmptasks = '';
			$actual_date = $start_date;
			foreach ($task_list as $task) {
				if (!isChildTask($task)) {
					if (isMemberOfTask($task_list, $task_assigned_users, $Ntasks, $user_id, $task)) {
						$tmptasks .= displayTask($task_list, $task, 0, $display_week_hours, $sss, $sse, !$project_id, $user_id);
						// Get children
						$tmptasks .= doChildren($task_list, $task_assigned_users, $Ntasks, $task->task_id, $user_id, 1, $max_levels, $display_week_hours, $sss, $sse, !$project_id);
					}
				}
			}
			if ($tmptasks != '') {
				$table_rows .= $tmpuser;
				$table_rows .= $tmptasks;
			}
		}
	}
	echo '
	<table class="std">
		' . $table_header . $table_rows . '
	</table>
';
	echo '</td>
</tr>
</table>';
}

function doChildren($list, $Lusers, $N, $id, $uid, $level, $maxlevels, $display_week_hours, $ss, $se, $log_all_projects = false) {
	$tmp = "";
	if ($maxlevels == -1 || $level < $maxlevels) {
		for ($c = 0; $c < $N; $c++) {
			$task = $list[$c];
			if (($task->task_parent == $id) and isChildTask($task)) {
				// we have a child, do we have the user as a member?
				if (isMemberOfTask($list, $Lusers, $N, $uid, $task)) {
					$tmp .= displayTask($list, $task, $level, $display_week_hours, $ss, $se, $log_all_projects, $uid);
					$tmp .= doChildren($list, $Lusers, $N, $task->task_id, $uid, $level + 1, $maxlevels, $display_week_hours, $ss, $se, $log_all_projects);
				}
			}
		}
	}
	return $tmp;
}

function isMemberOfTask($list, $Lusers, $N, $user_id, $task) {

	for ($i = 0; $i < $N && $list[$i]->task_id != $task->task_id; $i++)
		;
	$users = $Lusers[$i];

	foreach ($users as $task_user_id => $user_data) {
		if ($task_user_id == $user_id) {
			return true;
		}
	}

	// check child tasks if any

	for ($c = 0; $c < $N; $c++) {
		$ntask = $list[$c];
		if (($ntask->task_parent == $task->task_id) and isChildTask($ntask)) {
			// we have a child task
			if (isMemberOfTask($list, $Lusers, $N, $user_id, $ntask)) {
				return true;
			}
		}
	}
	return false;
}

function displayTask($list, $task, $level, $display_week_hours, $fromPeriod, $toPeriod, $log_all_projects = false, $user_id = 0) {
	global $AppUI;

	$tmp = '';
	$tmp .= '<tr><td align="left" nowrap="nowrap">&#160&#160&#160';
	for ($i = 0; $i < $level; $i++) {
		$tmp .= '&#160&#160&#160';
	}
	if ($level == 0) {
		$tmp .= '<b>';
	} elseif ($level == 1) {
		$tmp .= '<i>';
	}
	$tmp .= $task->task_name;
	if ($level == 0) {
		$tmp .= '</b>';
	} elseif ($level == 1) {
		$tmp .= '</i>';
	}
	$tmp .= '&#160&#160&#160</td>';
	if ($log_all_projects) {
		//Show project name when we are logging all projects
		$project = $task->getProject();
		$tmp .= '<td nowrap="nowrap">';
		if (!isChildTask($task)) {
			//However only show the name on parent tasks and not the children to make it a bit cleaner
			$tmp .= $project['project_name'];
		}
		$tmp .= '</td>';
	}
	$df = $AppUI->getPref('SHDATEFORMAT');

	$tmp .= '<td nowrap="nowrap">';
	$dt = new w2p_Utilities_Date($task->task_start_date);
	$tmp .= $dt->format($df);
	$tmp .= '&#160&#160&#160</td>';
	$tmp .= '<td nowrap="nowrap">';
	$dt = new w2p_Utilities_Date($task->task_end_date);
	$tmp .= $dt->format($df);
	$tmp .= '</td>';
	if ($display_week_hours) {
		$tmp .= displayWeeks($list, $task, $level, $fromPeriod, $toPeriod, $user_id);
	}
	$tmp .= "</tr>\n";
	return $tmp;
}

function isChildTask($task) {
	return $task->task_id != $task->task_parent;
}

function weekDates($display_allocated_hours, $fromPeriod, $toPeriod) {
  global $AppUI;

	if ($fromPeriod == -1) {
		return '';
	}
	if (!$display_allocated_hours) {
		return '';
	}

	$s = new w2p_Utilities_Date($fromPeriod);
	$e = new w2p_Utilities_Date($toPeriod);
	$sw = getBeginWeek($s);
	$ew = getEndWeek($e); //intval($e->Format('%U'));

	$row = '';
	for ($i = $sw; $i <= $ew; $i++) {
		$sdf = substr($AppUI->getPref('SHDATEFORMAT'), 3);
		$row .= '<td nowrap="nowrap" bgcolor="#A0A0A0"><font color="black"><b>' . $s->format($sdf) . '</b></font></td>';
		$s->addSeconds(168 * 3600); // + one week
	}
	return $row;
}

function weekCells($display_allocated_hours, $fromPeriod, $toPeriod) {

	if ($fromPeriod == -1) {
		return 0;
	}
	if (!$display_allocated_hours) {
		return 0;
	}

	$s = new w2p_Utilities_Date($fromPeriod);
	$e = new w2p_Utilities_Date($toPeriod);
	$sw = getBeginWeek($s);
	$ew = getEndWeek($e);

	return $ew - $sw + 1;
}

// Look for a user when he/she has been allocated
// to this task and when. Report this in weeks
// This function is called within 'displayTask()'
function displayWeeks($list, $task, $level, $fromPeriod, $toPeriod, $user_id = 0) {

	if ($fromPeriod == -1) {
		return '';
	}
	$s = new w2p_Utilities_Date($fromPeriod);
	$e = new w2p_Utilities_Date($toPeriod);
	$sw = getBeginWeek($s);
	$ew = getEndWeek($e);

	$st = new w2p_Utilities_Date($task->task_start_date);
	$et = new w2p_Utilities_Date($task->task_end_date);
	$stw = getBeginWeek($st);
	$etw = getEndWeek($et);

	$row = '';
	for ($i = $sw; $i <= $ew; $i++) {
		$assignment = '';

		if ($i >= $stw and $i < $etw) {
			$color = '#0000FF';
			if ($level == 0 and hasChildren($list, $task)) {
				$color = '#C0C0FF';
			} else {
				if ($level == 1 and hasChildren($list, $task)) {
					$color = '#9090FF';
				}
			}

			if ($user_id) {
				$users = $task->getAssignedUsers($task->task_id);
				$assignment = ($users[$user_id]['perc_assignment']) ? $users[$user_id]['perc_assignment'].'%' : '';
			}
		} else {
			$color = '#FFFFFF';
		}
		$row .= '<td bgcolor="' . $color . '" class="center">';
		$row .= '<font color="'.bestColor($color).'">';
		$row .= $assignment;
		$row .= '</font>';
		$row .= '</td>';
	}

	return $row;
}

function getBeginWeek($d) {
	$dn = intval($d->Format('%w'));
	$dd = new w2p_Utilities_Date($d);
	$dd->subtractSeconds($dn * 24 * 3600);
	return intval($dd->Format('%U'));
}

function getEndWeek($d) {
	$dn = intval($d->Format('%w'));
	if ($dn > 0) {
		$dn = 7 - $dn;
	}
	$dd = new w2p_Utilities_Date($d);
	$dd->addSeconds($dn * 24 * 3600);
	return intval($dd->Format('%U'));
}

function hasChildren($list, $task) {
	foreach ($list as $t) {
		if ($t->task_parent == $task->task_id) {
			return true;
		}
	}
	return false;
}