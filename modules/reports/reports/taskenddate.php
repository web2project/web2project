<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$user_id = w2PgetParam($_POST, 'user_id', $AppUI->user_id);

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new CDate($log_start_date) : new CDate();
$end_date = intval($log_end_date) ? new CDate($log_end_date) : new CDate();

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

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ""; ?>" class="text" />
		<a href="#" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		<a href="#" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>

	<td nowrap='nowrap'>
	   <?php
$users = w2PgetUsers();
echo arraySelect($users, 'user_id', 'class="text"', $user_id);
?>
	</td>
	
	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
	</td>
</tr>
</table>
</form>
<?php
if ($do_report) {

	$q = new DBQuery;
	$q->addTable('tasks', 't');
	$q->addTable('users', 'u');
	$q->addTable('projects', 'p');
	$q->addQuery('t.*, p.project_name, u.user_username');
	$q->addQuery('CONCAT(contact_first_name, " ", contact_last_name) AS user_username');
	$q->leftJoin('contacts', 'ct', 'ct.contact_id = u.user_contact');
	$q->addWhere('p.project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('p.project_status <> ' . $template_status);
	}

	if ($user_id > 0) {
		$q->addTable('user_tasks', 'ut');
		$q->addWhere('ut.user_id =' . $user_id);
		$q->addWhere('ut.task_id = t.task_id');
	}

	if ($project_id != 0) {
		$q->addWhere('task_project =' . $project_id);
	}

	$q->addWhere('p.project_id   = t.task_project');
	$q->addWhere('t.task_dynamic = "0"');
	$q->addWhere('t.task_owner = u.user_id');
	$q->addWhere('task_end_date >= \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\'');
	$q->addWhere('task_end_date <= \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\'');

	$q->addOrder('project_name ASC');
	$q->addOrder('task_end_date ASC');

	$tasks = $q->loadHashList('task_id');
	$q->clear();
	$first_task = current($tasks);
	$actual_project_id = 0;
	$first_task = true;
	$task_log = array();

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

	echo '<table class="std" width="100%">';
	echo '<tr><th>' . $AppUI->_('Task name') . '</th><th>' . $AppUI->_('T.Owner') . '</th><th>' . $AppUI->_('H.Alloc.') . '</th><th>' . $AppUI->_('Task end date') . '</th><th>' . $AppUI->_('Last activity date') . '</th><th>' . $AppUI->_('Done') . '?</th></tr>';
	$hrs = $AppUI->_('hrs'); // To avoid calling $AppUI each row
	foreach ($tasks as $task) {
		if ($actual_project_id != $task['task_project']) {
			echo '<tr><td colspan="6"><b>' . $task['project_name'] . '</b></td>';
			$actual_project_id = $task['task_project'];
		}
		$q->addTable('task_log');
		$q->addQuery('*');
		$q->addWhere('task_log_task = ' . (int)$task['task_id']);
		$q->addOrder('task_log_date DESC');
		$q->setLimit(1);
		$task_log = $q->loadHash();
		$q->clear();

		$done_img = $task['task_percent_complete'] == 100 ? 'Yes' : 'No';
		echo '<tr><td>&nbsp;&nbsp;&nbsp;' . $task['task_name'] . '</td><td>' . $task['user_username'] . '</td><td align="right">' . ($task['task_duration'] * $task['task_duration_type']) . ' ' . $hrs . '</td><td align="center">' . $task['task_end_date'] . '</td><td align="center">' . $task_log['task_log_date'] . '</td><td align="center">' . $done_img . '</td></tr>';
	}
	echo '</table>';
	echo '</td>
</tr>
</table>';
}
?>