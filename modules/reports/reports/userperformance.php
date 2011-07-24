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
$group_by_unit = w2PgetParam($_POST['group_by_unit'], 'day');

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

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
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" id="log_all" <?php if ($log_all)
	echo "checked" ?> />
		<label for="log_all"><?php echo $AppUI->_('Log All'); ?></label>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
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
		<td>';

	// Let's figure out which users we have
	$user_list = w2PgetUsersHashList();

	// Now which tasks will we need and the real allocated hours (estimated time / number of users)
	// Also we will use tasks with duration_type = 1 (hours) and those that are not marked
	// as milstones
	// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours
	$working_hours = $w2Pconfig['daily_working_hours'];

	$q = new w2p_Database_Query;
	$q->addTable('tasks', 't');
	$q->addTable('user_tasks', 'ut');
	$q->addJoin('projects', '', 'project_id = task_project', 'inner');
	$q->addQuery('t.task_id, round(t.task_duration * IF(t.task_duration_type = 24, ' . $working_hours . ', t.task_duration_type)/count(ut.task_id),2) as hours_allocated');
	$q->addWhere('t.task_id = ut.task_id');
	$q->addWhere('t.task_milestone = 0');
	$q->addWhere('project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('project_status <> ' . (int)$template_status);
	}

	if ($project_id != 0) {
		$q->addWhere('t.task_project = ' . (int)$project_id);
	}

	if (!$log_all) {
		$q->addWhere('t.task_start_date >= \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\'');
		$q->addWhere('t.task_start_date <= \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\'');
	}
	$q->addGroup('t.task_id');

	$task_list = $q->loadHashList('task_id');
	$q->clear();
?>

<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th colspan='2'><?php echo $AppUI->_('User'); ?></th>
		<th><?php echo $AppUI->_('Hours allocated'); ?></th>
		<th><?php echo $AppUI->_('Hours worked'); ?></th>
		<th><?php echo $AppUI->_('% of work done (based on duration)'); ?></th>
		<th><?php echo $AppUI->_('User Efficiency (based on completed tasks)'); ?></th>
	</tr>

<?php
	if (count($user_list)) {
		$percentage_sum = $hours_allocated_sum = $hours_worked_sum = 0;
		$sum_total_hours_allocated = $sum_total_hours_worked = 0;
		$sum_hours_allocated_complete = $sum_hours_worked_complete = 0;

		//TODO: Split times for which more than one users were working...
		foreach ($user_list as $user_id => $user) {
			$q->addTable('user_tasks', 'ut');
			$q->addQuery('task_id');
			$q->addWhere('user_id = ' . (int)$user_id);
			$tasks_id = $q->loadColumn();
			$q->clear();

			$total_hours_allocated = $total_hours_worked = 0;
			$hours_allocated_complete = $hours_worked_complete = 0;

			foreach ($tasks_id as $task_id) {
				if (isset($task_list[$task_id])) {
					// Now let's figure out how many time did the user spent in this task
					$q->addTable('task_log');
					$q->addQuery('SUM(task_log_hours)');
					$q->addWhere('task_log_task =' . (int)$task_id);
					$q->addWhere('task_log_creator =' . (int)$user_id);
					$hours_worked = round($q->loadResult(), 2);
					$q->clear();

					$q->addTable('tasks');
					$q->addQuery('task_percent_complete');
					$q->addWhere('task_id =' . (int)$task_id);
					$percent = $q->loadColumn();
					$q->clear();
					$complete = ($percent[0] == 100);

					if ($complete) {
						$hours_allocated_complete += $task_list[$task_id]['hours_allocated'];
						$hours_worked_complete += $hours_worked;
					}

					$total_hours_allocated += $task_list[$task_id]['hours_allocated'];
					$total_hours_worked += $hours_worked;
				}
			}

			$sum_total_hours_allocated += $total_hours_allocated;
			$sum_total_hours_worked += $total_hours_worked;

			$sum_hours_allocated_complete += $hours_allocated_complete;
			$sum_hours_worked_complete += $hours_worked_complete;

			if ($total_hours_allocated > 0 || $total_hours_worked > 0) {
				$percentage = 0;
				$percentage_e = 0;
				if ($total_hours_worked > 0) {
					$percentage = ($total_hours_worked / $total_hours_allocated) * 100;
					if ($hours_worked_complete > 0) {
						$percentage_e = ($hours_allocated_complete / $hours_worked_complete) * 100;
					}
				}
?>
				<tr>
					<td><?php echo '(' . $user['user_username'] . ') </td><td> ' . $user['contact_first_name'] . ' ' . $user['contact_last_name']; ?></td>
					<td align='right'><?php echo $total_hours_allocated; ?> </td>
					<td align='right'><?php echo $total_hours_worked; ?> </td>
					<td align='right'><?php echo number_format($percentage, 0); ?>% </td>
					<td align='right'><?php echo number_format($percentage_e, 0); ?>% </td>
				</tr>
				<?php
			}
		}
		$sum_percentage = 0;
		$sum_efficiency = 0;
		if ($sum_total_hours_worked > 0) {
			$sum_percentage = ($sum_total_hours_worked / $sum_total_hours_allocated) * 100;
			if ($sum_hours_worked_complete > 0)
				$sum_efficiency = ($sum_hours_allocated_complete / $sum_hours_worked_complete) * 100;
		}
?>
			<tr>
				<td colspan='2'><?php echo $AppUI->_('Total'); ?></td>
				<td align='right'><?php echo $sum_total_hours_allocated; ?></td>
				<td align='right'><?php echo $sum_total_hours_worked; ?></td>
				<td align='right'><?php echo number_format($sum_percentage, 0); ?>%</td>
				<td align='right'><?php echo number_format($sum_efficiency, 0); ?>%</td>
			</tr>
		<?php
	} else {
?>
		<tr>
		    <td><p><?php echo $AppUI->_('There are no tasks that fulfill selected filters'); ?></p></td>
		</tr>
		<?php
	}
	echo '</table>';
	echo '</td>
</tr>
</table>';
}