<?php /* $Id$ $URL$ */
global $showEditCheckbox, $this_day, $other_users, $w2Pconfig, $user_id;

if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$showEditCheckbox = w2PgetConfig('direct_edit_assignment');
$perms = &$AppUI->acl();

$tab = $AppUI->processIntState('ToDoTab', $_GET, 'tab', 0);

if (isset($_POST['task_type'])) {
	$AppUI->setState('ToDoTaskType', w2PgetParam($_POST, 'task_type', ''));
}
global $task_type;
$task_type = $AppUI->getState('ToDoTaskType') !== null ? $AppUI->getState('ToDoTaskType') : '';

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);
$this_day = new w2p_Utilities_Date();
$date = ((int) w2PgetParam($_GET, 'date', '')) ? $this_day->format(FMT_TIMESTAMP_DATE) : '';

$user_id = $AppUI->user_id;
$no_modify = false;
$other_users = false;

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

// retrieve any state parameters
if (isset($_POST['show_form'])) {
	$AppUI->setState('TaskDayShowArc', w2PgetParam($_POST, 'show_arc_proj', 0));
	$AppUI->setState('TaskDayShowLow', w2PgetParam($_POST, 'show_low_task', 0));
	$AppUI->setState('TaskDayShowHold', w2PgetParam($_POST, 'show_hold_proj', 0));
	$AppUI->setState('TaskDayShowDyn', w2PgetParam($_POST, 'show_dyn_task', 0));
	$AppUI->setState('TaskDayShowPin', w2PgetParam($_POST, 'show_pinned', 0));
	$AppUI->setState('TaskDayShowEmptyDate', w2PgetParam($_POST, 'show_empty_date', 0));

}
// Required for today view.
global $showArcProjs, $showLowTasks, $showHoldProjs, $showDynTasks, $showPinned, $showEmptyDate;

$showArcProjs = $AppUI->getState('TaskDayShowArc', 0);
$showLowTasks = $AppUI->getState('TaskDayShowLow', 1);
$showHoldProjs = $AppUI->getState('TaskDayShowHold', 0);
$showDynTasks = $AppUI->getState('TaskDayShowDyn', 0);
$showPinned = $AppUI->getState('TaskDayShowPin', 0);
$showEmptyDate = $AppUI->getState('TaskDayShowEmptyDate', 0);

global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;
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
		} else
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

$q = new w2p_Database_Query;
$q->addQuery('ta.*');
$q->addQuery('project_name, pr.project_id, project_color_identifier');
$q->addQuery('tp.task_pinned');
$q->addQuery('ut.user_task_priority');
$dateDiffString = $q->dbfnDateDiff('ta.task_end_date', $q->dbfnNow()) . ' AS task_due_in';
$q->addQuery($dateDiffString);

$q->addTable('projects', 'pr');
$q->addTable('tasks', 'ta');
$q->addTable('user_tasks', 'ut');
$q->leftJoin('user_task_pin', 'tp', 'tp.task_id = ta.task_id and tp.user_id = ' . (int)$user_id);
$q->leftJoin('project_departments', 'project_departments', 'pr.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');

$q->addWhere('ut.task_id = ta.task_id');
$q->addWhere('ut.user_id = ' . (int)$user_id);
$q->addWhere('( ta.task_percent_complete < 100 or ta.task_percent_complete is null)');

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

if (count($allowedTasks)) {
	$q->addWhere($allowedTasks);
}

if (count($allowedProjects)) {
	$q->addWhere($allowedProjects);
}

$q->addGroup('ta.task_id');
$q->addOrder('ta.task_end_date');
$q->addOrder('task_priority DESC');

global $tasks;
$tasks = $q->loadList();
$q->clear();

/* we have to calculate the end_date via start_date+duration for
** end='0000-00-00 00:00:00' 
*/
for ($j = 0, $j_cmp = count($tasks); $j < $j_cmp; $j++) {

	if ($tasks[$j]['task_end_date'] == '0000-00-00 00:00:00' || $tasks[$j]['task_end_date'] == '') {
		if ($tasks[$j]['task_start_date'] == '0000-00-00 00:00:00' || $tasks[$j]['task_start_date'] == '') {
			$tasks[$j]['task_start_date'] = '0000-00-00 00:00:00'; //just to be sure start date is "zeroed"
			$tasks[$j]['task_end_date'] = '0000-00-00 00:00:00';
		} else {
			$tasks[$j]['task_end_date'] = calcEndByStartAndDuration($tasks[$j]);
		}
	}
}

global $priorities;
$priorities = array('1' => 'high', '0' => 'normal', '-1' => 'low');

global $durnTypes;
$durnTypes = w2PgetSysVal('TaskDurationType');

if (!$min_view) {
	$titleBlock = new CTitleBlock('My Tasks To Do', 'applet-48.png', $m, $m . '.' . $a);
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