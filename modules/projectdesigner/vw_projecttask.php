<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

$project_statuses = w2PgetSysVal('ProjectStatus');
$project_types = w2PgetSysVal('ProjectType');
$customLookups = array('project_status' => $pstatus, 'project_type' => $ptype);

$params = get_object_vars($obj);

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData($params);
?>	
<table width="100%" border="0" cellpadding="1" cellspacing="3" class="prjprint">
<tr>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Details'); ?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Project Name'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_name', $obj->project_name); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
            <?php echo $htmlHelper->createCell('company_name', $obj->company_name); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_short_name', $obj->project_short_name); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_start_date', $obj->project_start_date); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Target End Date'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_start_date', $obj->project_start_date); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Status'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_status', $obj->project_status, $customLookups); ?>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><strong><?php echo $AppUI->_('Progress'); ?>:&nbsp;</strong></td>
            <?php echo $htmlHelper->createCell('project_percent_complete', $obj->project_percent_complete); ?>
		</tr>
<?php
global $m, $a, $project_id, $f, $task_status, $min_view, $query_string, $durnTypes, $tpl;
global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;
global $user_id, $w2Pconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;
global $history_active;

if (empty($query_string)) {
	$query_string = '?m=' . $m . '&amp;a=' . $a;
}
$mods = $AppUI->getActiveModules();
$history_active = !empty($mods['history']) && canView('history');

// Number of columns (used to calculate how many columns to span things through)
$cols = 13;

/****
// Let's figure out which tasks are selected
*/
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

$project = new CProject();
$allowedProjects = $project->getAllowedSQL($AppUI->user_id);
$working_hours = ($w2Pconfig['daily_working_hours'] ? $w2Pconfig['daily_working_hours'] : 8);

$q->addQuery('projects.project_id, project_color_identifier, project_name, project_percent_complete');
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
$q->addQuery('task_priority, task_percent_complete');
$q->addQuery('task_duration, task_duration_type');
$q->addQuery('task_project');
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
if ($history_active) {
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
	$q->addQuery('contact_first_name, contact_last_name, contact_email');
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


$fieldList = array();
$fieldNames = array();

$module = new w2p_System_Module();
$fields = $module->loadSettings('projectdesigner', 'task_list_print');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe 
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_name', 'task_percent_complete',
        'task_start_date', 'task_end_date', 'task_updated');
    $fieldNames = array('Task Name', 'Work', 'Start', 'Finish', 'Last Update');

    $module->storeSettings('projectdesigner', 'task_list_print', $fieldList, $fieldNames);
}

echo '<table class="tbl list prjprint"><tr class="prjprint">';
foreach ($fieldNames as $index => $name) {
    ?><th nowrap="nowrap">
        <?php echo $AppUI->_($fieldNames[$index]); ?>
    </th><?php
}
echo '</tr>';

reset($projects);

foreach ($projects as $p) {
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
				echo showtask_new($t, 0);
				findchild_pd($p['tasks'], $t['task_id']);
			}
		}
	}
}
?>
</table >
<?php
global $project_id, $m;
global $st_projects_arr;

$df = $AppUI->getPref('SHDATEFORMAT');
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectStatus = w2PgetSysVal('ProjectStatus');
?>
<table class="tbl" cellspacing="1" cellpadding="2" border="0" width="100%">
<td align="center">
<?php echo '<strong>Gantt Chart</strong>' ?>
</td>
<tr>
    <td align="center" colspan="20">
<?php
$src = "?m=projectdesigner&a=gantt&suppressHeaders=1&showLabels=1&proFilter=&showInactive=1showAllGantt=1&project_id=$project_id&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.90) + '";
echo "<script language=\"javascript\" type=\"text/javascript\">document.write('<img src=\"$src\">')</script>";
?>
</td>
</table>