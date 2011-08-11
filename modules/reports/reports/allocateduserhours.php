<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

$coarseness = w2PgetParam($_POST, 'coarseness', 1);
$do_report = w2PgetParam($_POST, 'do_report', 0);
$hideNonWd = w2PgetParam($_POST, 'hideNonWd', 0);
$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$use_assigned_percentage = w2PgetParam($_POST, 'use_assigned_percentage', 0);
$user_id = w2PgetParam($_POST, 'user_id', $AppUI->user_id);

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date(date('Y-m-01'));
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

$end_date->setTime(23, 59, 59);

if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
<input type="hidden" name="report_category" value="<?php echo $report_category; ?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">

<tr>
	<td nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:
		<input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td nowrap="nowrap"><?php echo $AppUI->_('to'); ?>
		<input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td nowrap='nowrap'>
	   <input type="radio" name="coarseness" value="1" <?php if ($coarseness == 1)
	echo "checked" ?> />
	   <?php echo $AppUI->_('Days'); ?>
	   <input type="radio" name="coarseness" value="7" <?php if ($coarseness == 7)
	echo "checked" ?> />
	   <?php echo $AppUI->_('Weeks'); ?>
</td>
	<td nowrap='nowrap'>
	   <?php
echo $AppUI->_('Tasks created by');
echo ' ';
echo getUsersCombo($user_id);
?>
	</td>
</tr>
<tr>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_all_projects" id="log_all_projects" <?php if ($log_all_projects)
	echo 'checked="checked"' ?> />
		<label for="log_all_projects"><?php echo $AppUI->_('Log All Projects'); ?></label>
	</td>	
	<td nowrap="nowrap">
	   <input type="checkbox" name="use_assigned_percentage" id="use_assigned_percentage" <?php if ($use_assigned_percentage)
	echo 'checked="checked"' ?> />
	   <label for="use_assigned_percentage"><?php echo $AppUI->_('Use assigned percentage'); ?></label>
	</td>	
	<td nowrap="nowrap">
	   <input type="checkbox" name="hideNonWd" id="hideNonWd" <?php if ($hideNonWd)
	echo 'checked="checked"' ?> />
	   <label for="hideNonWd"><?php echo $AppUI->_('Hide non-working days'); ?></label>
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
	$q = new w2p_Database_Query;
	$q->addTable('users', 'u');
	$q->addQuery('u.user_id, u.user_username, contact_first_name, contact_last_name');
	$q->addJoin('contacts', 'c', 'u.user_contact = contact_id', 'inner');
	$user_list = $q->loadHashList('user_id');
	$q->clear();

	$q = new w2p_Database_Query;
	$q->addTable('tasks', 't');
	$q->addTable('user_tasks', 'ut');
	$q->addTable('projects', 'pr');
	$q->addQuery('t.*, ut.*, pr.project_name');
	$q->addWhere('( task_start_date
			   BETWEEN \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\' 
	                AND \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\' 
	           OR task_end_date	BETWEEN \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\' 
	                AND \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\' 
		   OR ( task_start_date <= \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\'
	                AND task_end_date >= \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\') )');
	$q->addWhere('task_end_date IS NOT NULL');
	$q->addWhere('task_end_date <> \'0000-00-00 00:00:00\'');
	$q->addWhere('task_start_date IS NOT NULL');
	$q->addWhere('task_start_date <> \'0000-00-00 00:00:00\'');
	$q->addWhere('task_dynamic <> 1');
	$q->addWhere('task_milestone = 0');
	$q->addWhere('task_duration  > 0');
	$q->addWhere('t.task_project = pr.project_id');
	$q->addWhere('t.task_id = ut.task_id');
	$q->addWhere('pr.project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('pr.project_status <> ' . (int)$template_status);
	}

	if ($user_id) {
		$q->addWhere('t.task_owner = ' . (int)$user_id);
	}
	if ($project_id != 0) {
		$q->addWhere('t.task_project = ' . (int)$project_id);
	}

	$proj = new CProject();
	$proj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

	$obj = new CTask();
	$obj->setAllowedSQL($AppUI->user_id, $q);

	$task_list_hash = $q->loadHashList('task_id');

	$q->clear();

	$task_list = array();
	$fetched_projects = array();
	foreach ($task_list_hash as $task_id => $task_data) {
		$task = new CTask();
		$task->bind($task_data);
		$task_list[] = $task;
		$fetched_projects[$task->task_project] = $task_data['project_name'];
	}

	$user_usage = array();
	$task_dates = array();

	$actual_date = $start_date;
	$days_header = ''; // we will save days title here

	$user_tasks_counted_in = array();
	$user_names = array();

	if (count($task_list) == 0) {
		echo '<p>' . $AppUI->_('No data available') . '</p>';
	} else {
		foreach ($task_list as $task) {
			$task_start_date = new w2p_Utilities_Date($task->task_start_date);
			$task_end_date = new w2p_Utilities_Date($task->task_end_date);

			$day_difference = $task_end_date->dateDiff($task_start_date);
			$actual_date = $task_start_date;

			$users = $task->getAssignedUsers($task->task_id);

			if ($coarseness == 1) {
				userUsageDays();
			} elseif ($coarseness == 7) {
				userUsageWeeks();
			}

		}

		if ($coarseness == 1) {
			showDays();
		} elseif ($coarseness == 7) {
			showWeeks();
		}
?>
			<center><table class="std">
			<?php echo $table_header . $table_rows; ?>
			</table>
			<table width="100%"><tr><td align="center">
		<?php


		echo '<h4>' . $AppUI->_('Total capacity for shown users') . '</h4>';
		echo $AppUI->_('Allocated hours') . ': ' . number_format($allocated_hours_sum, 2) . '<br />';
		echo $AppUI->_('Total capacity') . ': ' . number_format($total_hours_capacity, 2) . '<br />';
		echo $AppUI->_('Percentage used') . ': ' . (($total_hours_capacity > 0) ? number_format($allocated_hours_sum / $total_hours_capacity, 2) * 100 : 0) . '%<br />';
?>
			</td>
			<td align="center">
		<?php


		echo '<h4>' . $AppUI->_('Total capacity for all users') . '</h4>';
		echo $AppUI->_('Allocated hours') . ': ' . number_format($allocated_hours_sum, 2) . '<br />';
		echo $AppUI->_('Total capacity') . ': ' . number_format($total_hours_capacity_all, 2) . '<br />';
		echo $AppUI->_('Percentage used') . ': ' . (($total_hours_capacity_all > 0) ? number_format($allocated_hours_sum / $total_hours_capacity_all, 2) * 100 : 0) . '%<br />';
	}
?>
	   </td></tr>
	   </table>
	   </center>
<?php
	foreach ($user_tasks_counted_in as $user_id => $project_information) {
		echo '<b>' . $user_names[$user_id] . '</b><br /><blockquote>';
		echo '<table width="50%" border="1" class="std">';
		foreach ($project_information as $project_id => $task_information) {
			echo '<tr><th colspan="3"><span style="font-weight:bold; font-size:110%">' . $fetched_projects[$project_id] . '</span></th></tr>';

			$project_total = 0;
			foreach ($task_information as $task_id => $hours_assigned) {
				echo '<tr><td>&nbsp;</td><td>' . $task_list_hash[$task_id]['task_name'] . '</td><td style="text-align:right;">' . number_format(round($hours_assigned, 2), 2) . ' hrs</td></tr>';
				$project_total += round($hours_assigned, 2);
			}
			echo '<tr><td colspan="2" align="right"><b>' . $AppUI->_('Total assigned') . '</b></td><td style="text-align:right;"><b>' . number_format($project_total, 2) . ' hrs</b></td></tr>';

		}
		echo '</table></blockquote>';
	}
	echo '</td>
</tr>
</table>';
}
