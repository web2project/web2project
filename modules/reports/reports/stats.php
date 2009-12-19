<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$q = new DBQuery;
$q->addTable('projects');
$q->addQuery('*');
$q->addJoin('tasks', '', 'task_project = project_id');

if (!empty($project_id)) {
	$q->addWhere('project_id = ' . (int)$project_id);
}
$obj = new CTask();
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
if (count($allowedTasks)) {
	$obj->getAllowedSQL($AppUI->user_id, $q);
}
$all_tasks = $q->loadList();
$q->clear();

$q = new DBQuery();
$q->addTable('projects');
$q->addQuery('*, round(sum(task_log_hours),2) as work');
$q->addJoin('tasks', '', 'task_project = project_id');
$q->addJoin('user_tasks', '', 'user_tasks.task_id = tasks.task_id');
$q->addJoin('users', '', 'user_tasks.user_id = users.user_id');
$q->addJoin('contacts', '', 'users.user_contact = contact_id');
$q->addJoin('task_log', '', 'task_log_task = tasks.task_id AND task_log_creator = users.user_id');
$q->addWhere('project_active = 1');
if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
	$q->addWhere('project_status <> ' . (int)$template_status);
}
if (!empty($project_id)) {
	$q->addWhere('project_id = ' . (int)$project_id);
}
$q->addGroup('tasks.task_id');
$q->addGroup('users.user_id');
$obj = new CTask();
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
if (count($allowedTasks)) {
	$obj->getAllowedSQL($AppUI->user_id, $q);
}
$users_all = $q->loadList();
$q->clear();

foreach ($users_all as $user) {
	$users_per_task[$user['task_id']][] = $user['user_id'];
	$users[$user['user_id']]['all'][$user['task_id']] = $user;
	$users[$user['user_id']]['name'] = (!empty($user['contact_first_name'])) ? $user['contact_first_name'] . ' ' . $user['contact_last_name'] : ((!empty($user['user_username'])) ? $user['user_username'] : $user['user_id']);
	$users[$user['user_id']]['hours'] = 0;
	$users[$user['user_id']]['completed'] = array();
	$users[$user['user_id']]['inprogress'] = array();
	$users[$user['user_id']]['pending'] = array();
	$users[$user['user_id']]['overdue'] = array();
}

$tasks['hours'] = 0;
$tasks['inprogress'] = array();
$tasks['completed'] = array();
$tasks['pending'] = array();
$tasks['overdue'] = array();
foreach ($all_tasks as $task) {
	if ($task['task_percent_complete'] == 100) {
		$tasks['completed'][] = &$task;
	} else {
		if ($task['task_end_date'] < date('Y-m-d')) {
			$tasks['overdue'][] = &$task;
		}
		if ($task['task_percent_complete'] == 0) {
			$tasks['pending'][] = &$task;
		} else {
			$tasks['inprogress'][] = &$task;
		}
	}

	if (isset($users_per_task[$task['task_id']])) {
		foreach ($users_per_task[$task['task_id']] as $user) {
			if ($task['task_percent_complete'] == 100) {
				$users[$user]['completed'][] = &$task;
			} else {
				if ($task['task_end_date'] < date('Y-m-d')) {
					$users[$user]['overdue'][] = &$task;
				}
				if ($task['task_percent_complete'] == 0) {
					$users[$user]['pending'][] = &$task;
				} else {
					$users[$user]['inprogress'][] = &$task;
				}
			}

			$users[$user]['hours'] += $users[$user]['all'][$task['task_id']]['work'];
			$tasks['hours'] += $users[$user]['all'][$task['task_id']]['work'];
		}
	}
}

$q = new DBQuery;
$q->addTable('files');
$q->addQuery('sum(file_size)');
if ($project_id) {
	$q->addWhere('file_project = ' . (int)$project_id);
} else {
	$q->addWhere('file_project = 0');
}
$q->addGroup('file_project');
$files = $q->loadResult();
$q->clear();

$ontime = round(100 * (1 - (count($tasks['overdue']) / count($all_tasks)) - (count($tasks['completed']) / count($all_tasks))));
?>

<table width="100%" border="1" cellpadding="0" cellspacing="0" class="tbl">
<tr>
	<th colspan="3"><?php echo $AppUI->_('Progress Chart (completed/in progress/pending)'); ?></th>
</tr>
<tr>
	<td width="<?php echo round(count($tasks['completed']) / count($all_tasks) * 100); ?>%" style="background: springgreen; text-align: center;"><?php echo $AppUI->_('completed'); ?></td>
	<td width="<?php echo round(count($tasks['inprogress']) / count($all_tasks) * 100); ?>%" style="background: aquamarine; text-align: center;"><?php echo $AppUI->_('in progress'); ?></td>
	<td width="<?php echo round(count($tasks['pending']) / count($all_tasks) * 100); ?>%" style="background: gold; text-align: center;"><?php echo $AppUI->_('pending'); ?></td>
</tr>
</table>
<br />

<table width="100%" border="1" cellpadding="0" cellspacing="0" class="tbl">
<tr>
	<th colspan="3"><?php echo $AppUI->_('Time Chart (completed/on time/ocerdue)'); ?></td>
</tr>
<tr>
	<td width="<?php echo round(count($tasks['completed']) / count($all_tasks) * 100); ?>%" style="background: springgreen; text-align: center;"><?php echo $AppUI->_('completed'); ?></td>
	<td width="<?php echo $ontime; ?>%" style="background: aquamarine; text-align: center;"><?php echo $AppUI->_('on time'); ?></td>
	<td width="<?php echo round(count($tasks['overdue']) / count($all_tasks) * 100); ?>%" style="background: tomato; text-align: center;"><?php echo $AppUI->_('overdue'); ?></td>
</tr>
</table>
<br />

<table class="tbl">
<tr>
	<td>
<table width="100%" cellspacing="1" cellpadding="4" border="0" class="tbl">
<tr>
	<th colspan="3"><?php echo $AppUI->_('Current Project Status'); ?></th>
</tr>
<tr>
	<th><?php echo $AppUI->_('Status'); ?></th>
	<th><?php echo $AppUI->_('Task Details'); ?></th>
	<th>%</th>
</tr>
<tr>
	<td nowrap="nowrap"><?php echo $AppUI->_('Complete'); ?>:</td>
	<td align="right"><?php echo count($tasks['completed']); ?></td>
	<td align="right"><?php echo round(count($tasks['completed']) / count($all_tasks) * 100); ?>%</td>
</tr>
<tr>
	<td nowrap="nowrap"><?php echo $AppUI->_('In Progress'); ?>:</td>
	<td align="right"><?php echo count($tasks['inprogress']); ?></td>
	<td align="right"><?php echo round(count($tasks['inprogress']) / count($all_tasks) * 100); ?>%</td>
</tr>
<tr>
	<td nowrap="nowrap"><?php echo $AppUI->_('Not Started'); ?>:</td>
	<td align="right"><?php echo count($tasks['pending']); ?></td>
	<td align="right"><?php echo round(count($tasks['pending']) / count($all_tasks) * 100); ?>%</td>
</tr>
<tr>
	<td nowrap="nowrap"><?php echo $AppUI->_('Past Due'); ?>:</td>
	<td align="right"><?php echo count($tasks['overdue']); ?></td>
	<td align="right"><?php echo round(count($tasks['overdue']) / count($all_tasks) * 100); ?>%</td>
</tr>
<tr>
	<td><?php echo $AppUI->_('Total'); ?>:</td>
	<td align="right"><?php echo count($all_tasks); ?></td>
	<td align="right">100%</td>
</tr>
</table>
<br />

<table width="100%" cellspacing="1" cellpadding="4" border="0" class="tbl">
<tr>
	<th colspan="2"><?php echo $AppUI->_('Project Assignee Details'); ?></th>
</tr>
<tr>
	<td><?php echo $AppUI->_('Team Size'); ?>:</td>
	<td align="right"><?php echo count($users); ?> <?php echo $AppUI->_('users'); ?></td>
</tr>
</table>
<br />

<table width="100%" cellspacing="1" cellpadding="4" border="0" class="tbl">
<tr>
	<th colspan="2"><?php echo $AppUI->_('Document Space Utilized'); ?></th>
</tr>
<tr>
	<td><?php echo $AppUI->_('Space Utilized'); ?>:</td>
	<td align="right" nowrap="nowrap"><?php echo file_size($files); ?></td>
</tr>
</table>
	</td>
	<td width="100%" valign="top">
<table width="100%" cellspacing="1" cellpadding="4" border="0" class="tbl">
<tr>
	<th><?php echo $AppUI->_('Task Assignee'); ?></th>
	<th><?php echo $AppUI->_('Pending Tasks'); ?></th>
	<th><?php echo $AppUI->_('Overdue Tasks'); ?></th>
	<th><?php echo $AppUI->_('In progress'); ?></th>
	<th><?php echo $AppUI->_('Completed Tasks'); ?></th>
	<th><?php echo $AppUI->_('Total Tasks'); ?></th>
	<th><?php echo $AppUI->_('Hours worked'); ?></th>
</tr>
<?php foreach ($users as $user => $stats) { ?>
<tr>
	<td><?php echo $stats['name']; ?></td>
	<td align="right"><?php echo count($stats['pending']); ?></td>
	<td align="right"><?php echo count($stats['overdue']); ?></td>
	<td align="right"><?php echo count($stats['inprogress']); ?></td>
	<td align="right"><?php echo count($stats['completed']); ?></td>
	<td align="right"><?php echo count($stats['all']); ?></td>
	<td align="right"><?php echo $stats['hours']; ?> <?php echo $AppUI->_('hours'); ?></td>
</tr>
<?php } ?>
<tr>
	<td class="highlight"><?php echo $AppUI->_('Total'); ?>:</td>
	<td align="right" class="highlight"><?php echo count($tasks['pending']); ?></td>
	<td align="right" class="highlight"><?php echo count($tasks['pending']); ?></td>
	<td align="right" class="highlight"><?php echo count($tasks['inprogress']); ?></td>
	<td align="right" class="highlight"><?php echo count($tasks['completed']); ?></td>
	<td align="right" class="highlight"><?php echo count($all_tasks); ?></td>
	<td align="right" class="highlight"><?php echo $tasks['hours']; ?> <?php echo $AppUI->_('hours'); ?></td>
</tr>
</table>
	</td>
</tr>
</table>