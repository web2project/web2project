<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);
$task_log_id = (int) w2PgetParam($_GET, 'task_log_id', 0);

$tab = $AppUI->processIntState('TaskLogVwTab', $_GET, 'tab', 0);

$obj = new CTask();

if (!$obj->load($task_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $obj->canEdit();
$canDelete = $obj->canDelete();

/**
 * Clear any reminders
 * @todo THIS SHOULD NOT HAPPEN HERE.. VIEWING SHOULD BE IDEMPOTENT
 */
$reminded = (int) w2PgetParam($_GET, 'reminded', 0);
if ($reminded) {
	$obj->clearReminder();
}

//check permissions for the associated project
$canReadProject = canView('projects', $obj->task_project);

$users = $obj->assignees($task_id);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Task', 'icon.png', $m);
$titleBlock->addCell();
if ($canReadProject) {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->task_project, 'view this project');
}

if ($canEdit) {
    $titleBlock->addButton('new log',  '?m=tasks&a=view&task_id=' . $task_id . '&tab=1');
    $titleBlock->addButton('new link', '?m=links&a=addedit&task_id=' . $task_id . '&project_id=' . $obj->task_project);
    $titleBlock->addButton('new file', '?m=files&a=addedit&project_id=' . $obj->task_project . '&file_task=' . $obj->task_id);
    $titleBlock->addButton('new task', '?m=tasks&a=addedit&task_project=' . $obj->task_project . '&task_parent=' . $task_id);

    if (!$obj->task_represents_project) {
	    $titleBlock->addCrumb('?m=tasks&a=addedit&task_id=' . $task_id, 'edit this task');
    }
}
if ($obj->task_represents_project) {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->task_represents_project, 'view subproject');
}
if ($canDelete) {
	$titleBlock->addCrumbDelete('delete task', $canDelete, $msg);
}
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $obj, 'Task');
echo $view->renderDelete();
?>
<script language="javascript" type="text/javascript">
function updateTask() {
	var f = document.editFrm;

	f.submit();
}
</script>
<?php
$durnTypes = w2PgetSysVal('TaskDurationType');
$task_types = w2PgetSysVal('TaskType');
$billingCategory = w2PgetSysVal('BudgetCategory');

include $AppUI->getTheme()->resolveTemplate('tasks/view');

$query_string = '?m=tasks&a=view&task_id=' . $task_id;
$tabBox = new CTabBox('?m=tasks&a=view&task_id=' . $task_id, '', $tab);

$tabBox_show = 0;
if ($obj->task_dynamic != 1 && 0 == $obj->task_represents_project) {
	// tabbed information boxes
	$tabBox_show = 1;
	if (canView('task_log')) {
		$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_logs', 'Task Logs');
	}
	if ($task_log_id == 0) {
		if (canAdd('task_log')) {
			$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_log_update', 'Log');
		}
	} elseif (canEdit('task_log')) {
		$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_log_update', 'Edit Log');
	} elseif (canAdd('task_log')) {
		$tabBox_show = 1;
		$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_log_update', 'Log');
	}
}

if (count($obj->getChildren()) > 0) {
	// Has children
	// settings for tasks
	$f = 'children';
	$min_view = true;
	$tabBox_show = 1;
	// in the tasks file there is an if that checks
	// $_GET[task_status]; this patch is to be able to see
	// child tasks withing an inactive task
	$_GET['task_status'] = $obj->task_status;
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_tasks', 'Child Tasks');
}

if (count($tabBox->tabs)) {
	$tabBox_show = 1;
}

if ($tabBox_show == 1) {
	$tabBox->show();
}
