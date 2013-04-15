<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $this_day, $first_time, $last_time, $company_id, $m, $a, $AppUI, $task_type;

$links = array();

$s = '';
$dayStamp = $this_day->format(FMT_TIMESTAMP_DATE);

$min_view = 1;
include W2P_BASE_DIR . '/modules/tasks/todo.php';

// The following code shows tasks that belong to projects of which
// the current user is owner and that are late as of the specified date or
// have a problem indication, regardless of lateness.

$user_id = $AppUI->user_id;
$showArcProjs = $AppUI->getState('TaskDayShowArc', 0);
$showLowTasks = $AppUI->getState('TaskDayShowLow', 1);
$showInProgress = $AppUI->getState('TaskDayShowInProgress', 0);
$showHoldProjs = $AppUI->getState('TaskDayShowHold', 0);
$showDynTasks = $AppUI->getState('TaskDayShowDyn', 0);
$showPinned = $AppUI->getState('TaskDayShowPin', 0);
$showEmptyDate = $AppUI->getState('TaskDayShowEmptyDate', 0);

// query my sub-tasks (ignoring task parents)

$q = new w2p_Database_Query;
$q->addQuery('distinct(ta.task_id), ta.*');
$q->addQuery('project_name, pr.project_id, project_color_identifier');
$q->addQuery('tp.task_pinned');
$q->addQuery('ut.user_task_priority');
$q->addQuery('DATEDIFF(ta.task_end_date, "' . date($date) . '") as task_due_in');
$q->addQuery('tlog.task_log_problem');

$q->addTable('projects', 'pr');
$q->addTable('tasks', 'ta');
$q->addTable('user_tasks', 'ut');
$q->leftJoin('user_task_pin', 'tp', 'tp.task_id = ta.task_id and tp.user_id = ' . (int)$user_id);
$q->leftJoin('project_departments', 'project_departments', 'pr.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = ta.task_id AND tlog.task_log_problem > 0');

if ($company_id) {
	$q->addWhere('pr.project_company = "' . (string)$company_id . '"');
}

$q->addWhere('ut.task_id = ta.task_id AND ut.user_id != ' . (int)$user_id);
$q->addWhere('pr.project_owner = ' . (int)$user_id);

$q->addWhere('ta.task_status = 0');
$q->addWhere('pr.project_id = ta.task_project');
if (!$showArcProjs) {
	$q->addWhere('project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('project_status <> ' . (int)$template_status);
	}
}
if (!$showLowTasks) {
	$q->addWhere('task_priority >= 0');
}
if ($showInProgress) {
	$q->addWhere('project_status = 3');
}
if (!$showHoldProjs) {
	if (($on_hold_status = w2PgetConfig('on_hold_projects_status_id')) != '') {
		$q->addWhere('project_status <> ' . (int)$on_hold_status);
	}
}
if (!$showDynTasks) {
	$q->addWhere('task_dynamic <> 1');
}
if ($showPinned) {
	$q->addWhere('task_pinned = 1');
}
if (!$showEmptyDate) {
	$q->addWhere('ta.task_start_date <> \'\' AND ta.task_start_date <> \'0000-00-00 00:00:00\'');
}
if ($task_type != '') {
	$q->addWhere('ta.task_type = ' . (int)$task_type);
}

$proj = new CProject;
$tobj = new CTask;

$allowedProjects = $proj->getAllowedSQL($user_id,'pr.project_id');
$allowedTasks = $tobj->getAllowedSQL($user_id, 'ta.task_id');

if (count($allowedTasks)) {
	$q->addWhere($allowedTasks);
}

if (count($allowedProjects)) {
	$q->addWhere($allowedProjects);
}

$q->addHaving('((ROUND(task_percent_complete) <> 100) AND (task_due_in < 0)) OR (task_log_problem > 0)');

$q->addOrder('task_end_date, task_start_date, task_priority');
$tasks = $q->loadList();

?>
<?php if (count($tasks)) { ?>
<br><br><h1><?php echo $AppUI->_('Tasks assigned to others') ?>:</h1>
<table class="tbl list">
        <tr>
            <th width="10">&nbsp;</th>
            <th width="10"><?php echo $AppUI->_('Pin'); ?></th>
            <th width="20" colspan="2"><?php echo $AppUI->_('Progress'); ?></th>
            <th width="15" align="center"><?php echo sort_by_item_title('P', 'task_priority', SORT_NUMERIC, '&amp;a=todo'); ?></th>
			<th width="15" align="center"><?php echo sort_by_item_title('U', 'user_task_priority', SORT_NUMERIC, '&amp;a=todo'); ?></th>
            <th colspan="2"><?php echo sort_by_item_title('Task / Project', 'task_name', SORT_STRING, '&amp;a=todo'); ?></th>
            <th nowrap="nowrap"><?php echo sort_by_item_title('Start Date', 'task_start_date', SORT_NUMERIC, '&amp;a=todo'); ?></th>
            <th nowrap="nowrap"><?php echo sort_by_item_title('Duration', 'task_duration', SORT_NUMERIC, '&amp;a=todo'); ?></th>
            <th nowrap="nowrap"><?php echo sort_by_item_title('Finish Date', 'task_end_date', SORT_NUMERIC, '&amp;a=todo'); ?></th>
            <th nowrap="nowrap"><?php echo sort_by_item_title('Due In', 'task_due_in', SORT_NUMERIC, '&amp;a=todo'); ?></th>
        </tr>
	<?php
        foreach ($tasks as $task) {
            echo showtask($task, 0, false, true);
        }
	?>
</table>
<?php } ?>