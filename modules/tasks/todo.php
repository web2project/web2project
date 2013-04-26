<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$showEditCheckbox = w2PgetConfig('direct_edit_assignment');

$tab = $AppUI->processIntState('ToDoTab', $_GET, 'tab', 0);

if (isset($_POST['task_type'])) {
	$AppUI->setState('ToDoTaskType', w2PgetParam($_POST, 'task_type', ''));
}
global $task_type, $min_view, $company_id;
$task_type = $AppUI->getState('ToDoTaskType') !== null ? $AppUI->getState('ToDoTaskType') : '';

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);
$this_day = new w2p_Utilities_Date();
$date = (int) w2PgetParam($_GET, 'date', $this_day->format(FMT_TIMESTAMP_DATE));

$user_id = $AppUI->user_id;
$no_modify = false;
$other_users = false;

// retrieve any state parameters
if (isset($_POST['show_form'])) {
	$AppUI->setState('TaskDayShowArc', w2PgetParam($_POST, 'show_arc_proj', 0));
	$AppUI->setState('TaskDayShowLow', w2PgetParam($_POST, 'show_low_task', 0));
	$AppUI->setState('TaskDayShowHold', w2PgetParam($_POST, 'show_hold_proj', 0));
	$AppUI->setState('TaskDayShowDyn', w2PgetParam($_POST, 'show_dyn_task', 0));
	$AppUI->setState('TaskDayShowPin', w2PgetParam($_POST, 'show_pinned', 0));
	$AppUI->setState('TaskDayShowEmptyDate', w2PgetParam($_POST, 'show_empty_date', 0));
	$AppUI->setState('TaskDayShowInProgress', w2PgetParam($_POST, 'show_inprogress', 0));
}

// Required for today view.
$showArcProjs = $AppUI->getState('TaskDayShowArc', 0);
$showLowTasks = $AppUI->getState('TaskDayShowLow', 1);
$showHoldProjs = $AppUI->getState('TaskDayShowHold', 0);
$showDynTasks = $AppUI->getState('TaskDayShowDyn', 0);
$showPinned = $AppUI->getState('TaskDayShowPin', 0);
$showEmptyDate = $AppUI->getState('TaskDayShowEmptyDate', 0);
$showInProgress = $AppUI->getState('TaskDayShowInProgress', 0);

if (canView('admin')) { // let's see if the user has sysadmin access
	$other_users = true;
	if (($show_uid = w2PgetParam($_REQUEST, 'show_user_todo', 0)) != 0) { // lets see if the user wants to see anothers user mytodo
		$user_id = $show_uid;
		$no_modify = true;
		$AppUI->setState('tasks_todo_user_id', $user_id);
	} elseif ($AppUI->getState('tasks_todo_user_id')) {
		$user_id = $AppUI->getState('tasks_todo_user_id');
	}
}

// check permissions
$canEdit = canEdit($m);

$task_sort_item1 = w2PgetParam($_GET, 'task_sort_item1', '');
$task_sort_type1 = w2PgetParam($_GET, 'task_sort_type1', '');
$task_sort_item2 = w2PgetParam($_GET, 'task_sort_item2', '');
$task_sort_type2 = w2PgetParam($_GET, 'task_sort_type2', '');
$task_sort_order1 = (int) w2PgetParam($_GET, 'task_sort_order1', 0);
$task_sort_order2 = (int) w2PgetParam($_GET, 'task_sort_order2', 0);

// if task priority set and items selected, do some work
$task_priority = w2PgetParam($_POST, 'task_priority', 99);
$selected = w2PgetParam($_POST, 'selected_task', 0);

if (is_array($selected) && count($selected)) {
	foreach ($selected as $key => $val) {
		if ($task_priority == 'c') {
			// mark task as completed
			$q = new w2p_Database_Query;
			$q->addTable('tasks');
			$q->addUpdate('task_percent_complete', '100');
			$q->addWhere('task_id=' . (int)$val);
		} else {
			if ($task_priority == 'd') {
				// delete task
				$q = new w2p_Database_Query;
				$q->setDelete('tasks');
				$q->addWhere('task_id=' . (int)$val);
			} else
				if ($task_priority > -2 && $task_priority < 2) {
					// set priority
					$q = new w2p_Database_Query;
					$q->addTable('tasks');
					$q->addUpdate('task_priority', $task_priority);
					$q->addWhere('task_id=' . (int)$val);
				}
        }
		$q->exec();
		echo db_error();
		$q->clear();
	}
}

$AppUI->savePlace();

$proj = new CProject;
$tobj = new CTask;

$allowedProjects = $proj->getAllowedSQL($AppUI->user_id,'pr.project_id');
$allowedTasks = $tobj->getAllowedSQL($AppUI->user_id, 'ta.task_id');

// query my sub-tasks (ignoring task parents)

$q_tasks = new w2p_Database_Query;
$q_tasks->addQuery('distinct(ta.task_id), ta.task_percent_complete, ta.task_priority, ta.task_name, ta.task_owner');
$q_tasks->addQuery('ta.task_start_date, ta.task_duration, ta.task_duration_type, ta.task_end_date');
$q_tasks->addQuery('pr.project_name, pr.project_id, pr.project_color_identifier');
$q_tasks->addQuery('tp.task_pinned, ut.user_task_priority, ta.task_description');
$q_tasks->addQuery('DATEDIFF(ta.task_end_date, "' . date($date) . '") as task_due_in');
$q_tasks->addQuery('tlog.task_log_problem, "task" as row_type, null as delegation_name');

$q_tasks->addTable('projects', 'pr');
$q_tasks->addTable('tasks', 'ta');
$q_tasks->addTable('user_tasks', 'ut');
$q_tasks->leftJoin('user_task_pin', 'tp', 'tp.task_id = ta.task_id and tp.user_id = ' . (int)$user_id);
$q_tasks->leftJoin('project_departments', 'project_departments', 'pr.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q_tasks->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q_tasks->leftJoin('task_log', 'tlog', 'tlog.task_log_task = ta.task_id AND tlog.task_log_problem > 0');

if ($company_id) {
	$q_tasks->addWhere('pr.project_company = "' . (string)$company_id . '"');
}

$q_tasks->addWhere('ut.task_id = ta.task_id');
$q_tasks->addWhere('ut.user_id = ' . (int)$user_id);

$q_tasks->addWhere('ta.task_status = 0');
$q_tasks->addWhere('pr.project_id = ta.task_project');
if (!$showArcProjs) {
	$q_tasks->addWhere('project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q_tasks->addWhere('project_status <> ' . (int)$template_status);
	}
}
if (!$showLowTasks) {
	$q_tasks->addWhere('task_priority >= 0');
}
if ($showInProgress) {
	$q_tasks->addWhere('project_status = 3');
}
if (!$showHoldProjs) {
	if (($on_hold_status = w2PgetConfig('on_hold_projects_status_id')) != '') {
		$q_tasks->addWhere('project_status <> ' . (int)$on_hold_status);
	}
}
if (!$showDynTasks) {
	$q_tasks->addWhere('task_dynamic <> 1');
}
if ($showPinned) {
	$q_tasks->addWhere('task_pinned = 1');
}
if (!$showEmptyDate) {
	$q_tasks->addWhere('ta.task_start_date <> \'\' AND ta.task_start_date <> \'0000-00-00 00:00:00\'');
}
if ($task_type != '') {
	$q_tasks->addWhere('ta.task_type = ' . (int)$task_type);
}

if (count($allowedTasks)) {
	$q_tasks->addWhere($allowedTasks);
}

if (count($allowedProjects)) {
	$q_tasks->addWhere($allowedProjects);
}

$q_tasks->addHaving('(ROUND(task_percent_complete) <> 100) OR (task_due_in >= 0)');


// Query for delegations, with the same filters as the tasks query
$q_deleg = new w2p_Database_Query;
$q_deleg->addTable('user_delegations','ud');
$q_deleg->addTable('projects', 'pr');

$q_deleg->addQuery('ud.delegation_id as task_id, ud.delegation_percent_complete as task_percent_complete');
$q_deleg->addQuery('ta.task_priority, ta.task_name, ud.delegating_user_id as task_owner');
$q_deleg->addQuery('ud.delegation_start_date as task_start_date, null as task_duration, null as task_duration_type');
$q_deleg->addQuery('ta.task_end_date, pr.project_name, pr.project_id, pr.project_color_identifier, tp.task_pinned');
$q_deleg->addQuery('ut.user_task_priority, ud.delegation_description as task_description');
$q_deleg->addQuery('DATEDIFF(ta.task_end_date, "' . date($date) . '") as task_due_in');
$q_deleg->addQuery('IF(ud.delegation_rejection_date IS NOT NULL,true,false) as task_log_problem, "delegation" as row_type');
$q_deleg->addQuery('ud.delegation_name');

$q_deleg->leftJoin('tasks', 'ta', 'ta.task_id = ud.delegation_task');
$q_deleg->leftJoin('project_departments', 'project_departments', 'pr.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q_deleg->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q_deleg->leftJoin('user_task_pin', 'tp', 'tp.task_id = ta.task_id and tp.user_id = ' . (int)$user_id);
$q_deleg->leftJoin('user_tasks', 'ut', 'ut.task_id = ta.task_id');

$q_deleg->addWhere('ud.delegated_to_user_id = ' . (int)$user_id);

if ($company_id) {
	$q_deleg->addWhere('pr.project_company = "' . (string)$company_id . '"');
}

$q_deleg->addWhere('ta.task_status = 0');
$q_deleg->addWhere('pr.project_id = ta.task_project');
if (!$showArcProjs) {
	$q_deleg->addWhere('project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q_deleg->addWhere('project_status <> ' . (int)$template_status);
	}
}
if (!$showLowTasks) {
	$q_deleg->addWhere('task_priority >= 0');
}
if ($showInProgress) {
	$q_deleg->addWhere('project_status = 3');
}
if (!$showHoldProjs) {
	if (($on_hold_status = w2PgetConfig('on_hold_projects_status_id')) != '') {
		$q_deleg->addWhere('project_status <> ' . (int)$on_hold_status);
	}
}
if (!$showDynTasks) {
	$q_deleg->addWhere('task_dynamic <> 1');
}
if ($showPinned) {
	$q_deleg->addWhere('task_pinned = 1');
}
if (!$showEmptyDate) {
	$q_deleg->addWhere('ta.task_start_date <> \'\' AND ta.task_start_date <> \'0000-00-00 00:00:00\'');
}
if ($task_type != '') {
	$q_deleg->addWhere('ta.task_type = ' . (int)$task_type);
}

if (count($allowedTasks)) {
	$q_deleg->addWhere($allowedTasks);
}

if (count($allowedProjects)) {
	$q_deleg->addWhere($allowedProjects);
}

$q_deleg->addHaving('(ROUND(task_percent_complete) <> 100) OR (task_due_in >= 0)');


// UNION query for fusing both resuls together and sorting them
$q = new w2p_Database_Query;
$q->unionQuery('ALL');
$q->addSelectQuery($q_tasks);
$q->addSelectQuery($q_deleg);
$q->addOrder('task_end_date, task_start_date, task_priority');
$tasks = $q->loadList();


/* There used to be some code here to calculate a task's
   end date dynamically if it had no end date. The same
   was done at todo.php and todo_tasks_sub.php.
   Apparently this was a fix to DotProject's issue #1509.
   But now it is not possible to create a task without
   start and end date, even if it depends on another.
   So I'm taking the code out to simplify things and allow
   task due in date and completion status to be computed
   with a SQL query.
*/

$priorities = array('1' => 'high', '0' => 'normal', '-1' => 'low');
$durnTypes = w2PgetSysVal('TaskDurationType');

if ('todo' == $a) {
	$titleBlock = new w2p_Theme_TitleBlock('My Tasks To Do', 'applet-48.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=tasks', 'tasks list');
	$titleBlock->show();
}

// If we are called from anywhere but directly, we would end up with
// double rows of tabs that would not work correctly, and since we
// are called from the day view of calendar, we need to prevent this
if ($m == 'tasks' && $a == 'todo') {
    ?>
    <table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
        <tr>
            <td width="80%" valign="top">
                <?php
                    // Tabbed information boxes
                    $tabBox = new CTabBox('?m=tasks&amp;a=todo','', $tab);
                    $tabBox->add(W2P_BASE_DIR . '/modules/tasks/todo_tasks_sub', 'My Tasks');
                    $tabBox->add(W2P_BASE_DIR . '/modules/tasks/todo_gantt_sub', 'My Gantt');
                    $tabBox->show();
                ?>
            </td>
        </tr>
    </table>
    <?php
} else {
	include W2P_BASE_DIR . '/modules/tasks/todo_tasks_sub.php';
}