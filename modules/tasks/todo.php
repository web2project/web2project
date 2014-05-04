<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

global $AppUI, $task_type, $min_view;


$tab = $AppUI->processIntState('ToDoTab', $_GET, 'tab', 0);

if (isset($_POST['task_type'])) {
	$AppUI->setState('ToDoTaskType', w2PgetParam($_POST, 'task_type', ''));
}

$task_type = $AppUI->getState('ToDoTaskType') !== null ? $AppUI->getState('ToDoTaskType') : '';

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);
$this_day = new w2p_Utilities_Date();
$date = ((int) w2PgetParam($_GET, 'date', '')) ? $this_day->format(FMT_TIMESTAMP_DATE) : '';

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

if (canView('users')) { // let's see if the user has sysadmin access
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
    __extract_from_tasks_todo($selected, $task_priority);
}

$proj = new CProject;
$tobj = new CTask;

$allowedProjects = $proj->getAllowedSQL($AppUI->user_id,'pr.project_id');
$allowedTasks = $tobj->getAllowedSQL($AppUI->user_id, 'ta.task_id');

$tasks = __extract_from_todo($user_id, $showArcProjs, $showLowTasks, $showInProgress, $showHoldProjs, $showDynTasks, $showPinned, $showEmptyDate, $task_type, $allowedTasks, $allowedProjects);

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

$priorities = array('1' => 'high', '0' => 'normal', '-1' => 'low');
$durnTypes = w2PgetSysVal('TaskDurationType');

if ('todo' == $a) {
	$titleBlock = new w2p_Theme_TitleBlock('My Tasks To Do', 'icon.png', $m);
	$titleBlock->addCrumb('?m=tasks', 'tasks list');
	$titleBlock->show();
}

// If we are called from anywhere but directly, we would end up with
// double rows of tabs that would not work correctly, and since we
// are called from the day view of calendar, we need to prevent this
if ($m == 'tasks' && $a == 'todo') {
    ?>
    <table class="std">
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