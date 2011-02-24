<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $m, $a, $project_id, $f, $task_status, $min_view, $query_string, $durnTypes, $tpl;
global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;
global $user_id, $w2Pconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;

if (empty($query_string)) {
	$query_string = '?m=' . $m . '&amp;a=' . $a;
}

// Number of columns (used to calculate how many columns to span things through)
$cols = 13;

/****
// Let's figure out which tasks are selected
*/
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);

$q = new w2p_Database_Query;
$pinned_only = (int) w2PgetParam($_GET, 'pinned', 0);
if (isset($_GET['pin'])) {
	$pin = (int) w2PgetParam($_GET, 'pin', 0);
	$msg = '';

	// load the record data
	if ($pin) {
		$q->addTable('user_task_pin');
		$q->addInsert('user_id', $AppUI->user_id);
		$q->addInsert('task_id', $task_id);
	} else {
		$q->setDelete('user_task_pin');
		$q->addWhere('user_id = ' . (int)$AppUI->user_id);
		$q->addWhere('task_id = ' . (int)$task_id);
	}

	if (!$q->exec()) {
		$AppUI->setMsg('ins/del err', UI_MSG_ERROR, true);
	} else {
		$q->clear();
	}

	$AppUI->redirect('', -1);
}

$AppUI->savePlace();

$durnTypes = w2PgetSysVal('TaskDurationType');
$taskPriority = w2PgetSysVal('TaskPriority');

$task_project = $project_id;

$task_sort_item1 = w2PgetParam($_GET, 'task_sort_item1', '');
$task_sort_type1 = w2PgetParam($_GET, 'task_sort_type1', '');
$task_sort_item2 = w2PgetParam($_GET, 'task_sort_item2', '');
$task_sort_type2 = w2PgetParam($_GET, 'task_sort_type2', '');
$task_sort_order1 = (int) w2PgetParam($_GET, 'task_sort_order1', 0);
$task_sort_order2 = (int) w2PgetParam($_GET, 'task_sort_order2', 0);
if (isset($_POST['show_task_options'])) {
	$AppUI->setState('TaskListShowIncomplete', w2PgetParam($_POST, 'show_incomplete', 0));
}
$showIncomplete = $AppUI->getState('TaskListShowIncomplete', 0);

$project = new CProject;
// $allowedProjects = $project->getAllowedRecords($AppUI->user_id, 'project_id, project_name');
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'projects.project_id');
$working_hours = ($w2Pconfig['daily_working_hours'] ? $w2Pconfig['daily_working_hours'] : 8);

$q->addQuery('projects.project_id, project_color_identifier, project_name');
$q->addQuery('SUM(task_duration * task_percent_complete * IF(task_duration_type = 24, ' . $working_hours . ', task_duration_type)) / SUM(task_duration * IF(task_duration_type = 24, ' . $working_hours . ', task_duration_type)) AS project_percent_complete');
$q->addQuery('company_name');
$q->addTable('projects');
$q->leftJoin('tasks', 't1', 'projects.project_id = t1.task_project');
$q->leftJoin('companies', 'c', 'company_id = project_company');
$q->leftJoin('project_departments', 'project_departments', 'projects.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q->addWhere('t1.task_id = t1.task_parent');
$q->addWhere('projects.project_id=' . $project_id);
if (count($allowedProjects)) {
	$q->addWhere($allowedProjects);
}
$q->addGroup('projects.project_id');
$q2 = new w2p_Database_Query;
$q2 = $q;
$q2->addQuery('projects.project_id, COUNT(t1.task_id) as total_tasks');

$perms = &$AppUI->acl();
$projects = array();
if ($canViewTasks) {
	$prc = $q->exec();
	echo db_error();
	while ($row = $q->fetchRow()) {
		$projects[$row['project_id']] = $row;
	}

	$prc2 = $q2->exec();
	echo db_error();
	while ($row2 = $q2->fetchRow()) {
		$projects[$row2['project_id']] = ((!($projects[$row2['project_id']])) ? array() : $projects[$row2['project_id']]);
		array_push($projects[$row2['project_id']], $row2);
	}
}
$q->clear();
$q2->clear();

$q->addQuery('tasks.task_id, task_parent, task_name');
$q->addQuery('task_start_date, task_end_date, task_dynamic');
$q->addQuery('count(tasks.task_parent) as children');
$q->addQuery('task_pinned, pin.user_id as pin_user');
$q->addQuery('ut.user_task_priority');
$q->addQuery('task_priority, task_percent_complete');
$q->addQuery('task_duration, task_duration_type');
$q->addQuery('task_project, task_represents_project');
$q->addQuery('task_access, task_type');
$q->addQuery('task_description, task_owner, task_status');
$q->addQuery('usernames.user_username, usernames.user_id');
$q->addQuery('assignees.user_username as assignee_username');
$q->addQuery('count(distinct assignees.user_id) as assignee_count');
$q->addQuery('co.contact_first_name, co.contact_last_name');
$q->addQuery('task_milestone');
$q->addQuery('count(distinct f.file_task) as file_count');
$q->addQuery('tlog.task_log_problem');
$q->addQuery('evtq.queue_id');

$q->addTable('tasks');
$mods = $AppUI->getActiveModules();
if (!empty($mods['history']) && canView('history')) {
	$q->addQuery('MAX(history_date) as last_update');
	$q->leftJoin('history', 'h', 'history_item = tasks.task_id AND history_table=\'tasks\'');
}
$q->leftJoin('projects', 'projects', 'projects.project_id = task_project');
$q->leftJoin('users', 'usernames', 'task_owner = usernames.user_id');
$q->leftJoin('user_tasks', 'ut', 'ut.task_id = tasks.task_id');
$q->leftJoin('users', 'assignees', 'assignees.user_id = ut.user_id');
$q->leftJoin('contacts', 'co', 'co.contact_id = usernames.user_contact');
$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = tasks.task_id AND tlog.task_log_problem > 0');
$q->leftJoin('files', 'f', 'tasks.task_id = f.file_task');
$q->leftJoin('user_task_pin', 'pin', 'tasks.task_id = pin.task_id AND pin.user_id = ' . (int)$AppUI->user_id);
$q->leftJoin('event_queue', 'evtq', 'tasks.task_id = evtq.queue_origin_id AND evtq.queue_module = "tasks"');
$q->leftJoin('project_departments', 'project_departments', 'projects.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');

$q->addWhere('task_project = ' . (int)$project_id);

$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'task_project');
if (count($allowedProjects)) {
	$q->addWhere($allowedProjects);
}
$obj = new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id, 'tasks.task_id');
if (count($allowedTasks)) {
	$q->addWhere($allowedTasks);
}
$q->addGroup('tasks.task_id');
$q->addOrder('task_start_date');
if ($canViewTasks) {
	$tasks = $q->loadList();
}
// POST PROCESSING TASKS
foreach ($tasks as $row) {
	//add information about assigned users into the page output
	$q->clear();
	$q->addQuery('ut.user_id,	u.user_username');
	$q->addQuery('ut.perc_assignment, SUM(ut.perc_assignment) AS assign_extent');
	$q->addQuery('contact_first_name, contact_last_name');
	$q->addTable('user_tasks', 'ut');
	$q->leftJoin('users', 'u', 'u.user_id = ut.user_id');
	$q->leftJoin('contacts', 'c', 'u.user_contact = c.contact_id');
	$q->addWhere('ut.task_id = ' . (int)$row['task_id']);
	$q->addGroup('ut.user_id');
	$q->addOrder('perc_assignment desc, user_username');

	$assigned_users = array();
	$row['task_assigned_users'] = $q->loadList();
	$q->addQuery('count(task_id) as children');
	$q->addTable('tasks');
	$q->addWhere('task_parent = ' . (int)$row['task_id']);
	$q->addWhere('task_id <> task_parent');
	$row['children'] = $q->loadResult();
	$row['style'] = taskstyle_pd($row);
	$i = count($projects[$row['task_project']]['tasks']) + 1;
	$row['task_number'] = $i;
	$row['node_id'] = 'node_' . $i . '-' . $row['task_id'];
	if (strpos($row['task_duration'], '.') && $row['task_duration_type'] == 1) {
		$row['task_duration'] = floor($row['task_duration']) . ':' . round(60 * ($row['task_duration'] - floor($row['task_duration'])));
	}
	//pull the final task row into array
	$projects[$row['task_project']]['tasks'][] = $row;
}

$showEditCheckbox = isset($canEditTasks) && $canEditTasks || canView('admin');

$durnTypes = w2PgetSysVal('TaskDurationType');
$tempoTask = new CTask();
$userAlloc = $tempoTask->getAllocation('user_id');
global $expanded;
$expanded = $AppUI->getPref('TASKSEXPANDED');
$open_link = w2PtoolTip($m, 'click to expand/collapse all the tasks for this project.') . '<a href="javascript: void(0);"><img onclick="expand_collapse(\'task_proj_' . $project_id . '_\', \'tblProjects\',\'collapse\',0,2);" id="task_proj_' . $project_id . '__collapse" src="' . w2PfindImage('up22.png', $m) . '" border="0" width="22" height="22" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' /><img onclick="expand_collapse(\'task_proj_' . $project_id . '_\', \'tblProjects\',\'expand\',0,2);" id="task_proj_' . $project_id . '__expand" src="' . w2PfindImage('down22.png', $m) . '" border="0" width="22" height="22" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' alt="" /></a>' . w2PendTip();
?>
<form name="frm_tasks" accept-charset="utf-8"">
<table id="tblTasks" width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
  <td colspan="16" align='left'>
		<?php echo $open_link; ?>
  </td>
</tr>
<tr>
        <th width="10">&nbsp;</th>
        <th width="20"><?php echo $AppUI->_('Work'); ?></th>
        <th align="center"><?php echo $AppUI->_('P'); ?></th>
		<th align="center"><?php echo $AppUI->_('U'); ?></th>
        <th align="center"><?php echo $AppUI->_('A'); ?></th>
        <th align="center"><?php echo $AppUI->_('T'); ?></th>
        <th align="center"><?php echo $AppUI->_('R'); ?></th>
        <th align="center"><?php echo $AppUI->_('I'); ?></th>
        <th align="center"><?php echo $AppUI->_('Log'); ?></th>
        <th width="40%"><?php echo $AppUI->_('Task Name'); ?></th>
<?php if ($PROJDESIGN_CONFIG['show_task_descriptions']) { ?>
        <th width="200"><?php echo $AppUI->_('Task Description'); ?></th>
<?php } ?>
        <th nowrap="nowrap"><?php echo $AppUI->_('Task Owner'); ?></th>
        <th nowrap="nowrap"><?php echo $AppUI->_('Start'); ?></th>
        <th nowrap="nowrap"><?php echo $AppUI->_('Duration'); ?>&nbsp;&nbsp;</th>
        <th nowrap="nowrap"><?php echo $AppUI->_('Finish'); ?></th>
        <th nowrap="nowrap"><?php echo $AppUI->_('Assigned Users') ?></th>
        <?php if ($showEditCheckbox) {
	echo '<th width="1"><input type="checkbox" onclick="mult_sel(this, \'selected_task_\', \'frm_tasks\')" name="multi_check"/></th>';
} ?>
</tr>
<?php
reset($projects);

foreach ($projects as $k => $p) {
	$tnums = count($p['tasks']);
	if ($tnums > 0 || $project_id == $p['project_id']) {
		if ($task_sort_item1 != '') {
			if ($task_sort_item2 != '' && $task_sort_item1 != $task_sort_item2) {
				$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1, $task_sort_item2, $task_sort_order2, $task_sort_type2);
			} else {
				$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1);
			}
		}

		for ($i = 0; $i < $tnums; $i++) {
			$t = $p['tasks'][$i];
			if ($t['task_parent'] == $t['task_id']) {
				showtask_pd($t, 0);
				findchild_pd($p['tasks'], $t['task_id']);
			}
		}
	}
}
?>
</table>
</form>
<table>
<tr>
        <td><?php echo $AppUI->_('Key'); ?>:</td>
        <th>&nbsp;P&nbsp;</th>
        <td>=<?php echo $AppUI->_('Overall Priority'); ?></td>
        <th>&nbsp;U&nbsp;</th>
        <td>=<?php echo $AppUI->_('User Priority'); ?></td>
        <th>&nbsp;A&nbsp;</th>
        <td>=<?php echo $AppUI->_('Access'); ?></td>
        <th>&nbsp;T&nbsp;</th>
        <td>=<?php echo $AppUI->_('Type'); ?></td>
        <th>&nbsp;R&nbsp;</th>
        <td>=<?php echo $AppUI->_('Reminder'); ?></td>
        <th>&nbsp;I&nbsp;</th>
        <td>=<?php echo $AppUI->_('Inactive'); ?></td>
        <td>&nbsp; &nbsp;</td>
        <td style="border-style:solid;border-width:1px" bgcolor="#ffffff">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Future Task'); ?></td>
        <td>&nbsp; &nbsp;</td>
        <td style="border-style:solid;border-width:1px" bgcolor="#e6eedd">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Started and on time'); ?></td>
        <td style="border-style:solid;border-width:1px" bgcolor="#ffeebb">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Should have started'); ?></td>
        <td>&nbsp; &nbsp;</td>
        <td style="border-style:solid;border-width:1px" bgcolor="#CC6666">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Overdue'); ?></td>
        <td>&nbsp; &nbsp;</td>
        <td style="border-style:solid;border-width:1px" bgcolor="#aaddaa">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Done'); ?></td>
</tr>
</table>