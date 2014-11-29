<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'task_id', 0);
$task_log_id = (int) w2PgetParam($_GET, 'task_log_id', 0);

$tab = $AppUI->processIntState('TaskLogVwTab', $_GET, 'tab', 0);

$object = new CTask();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $object->canEdit();
$canDelete = $object->canDelete();

/**
 * Clear any reminders
 * @todo THIS SHOULD NOT HAPPEN HERE.. VIEWING SHOULD BE IDEMPOTENT
 */
$reminded = (int) w2PgetParam($_GET, 'reminded', 0);
if ($reminded) {
    $object->clearReminder();
}

//check permissions for the associated project
$canReadProject = canView('projects', $object->task_project);

$users = $object->assignees($object_id);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Task', 'icon.png', $m);
$titleBlock->addCell();
if ($canReadProject) {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $object->task_project, 'view this project');
}

if ($canEdit) {
    $titleBlock->addButton('new log',  '?m=tasks&a=view&task_id=' . $object_id . '&tab=1');
    $titleBlock->addButton('new link', '?m=links&a=addedit&task_id=' . $object_id . '&project_id=' . $object->task_project);
    $titleBlock->addButton('new file', '?m=files&a=addedit&project_id=' . $object->task_project . '&file_task=' . $object->task_id);
    $titleBlock->addButton('new task', '?m=tasks&a=addedit&task_project=' . $object->task_project . '&task_parent=' . $object_id);

    if (!$object->task_represents_project) {
        $titleBlock->addCrumb('?m=tasks&a=addedit&task_id=' . $object_id, 'edit this task');
    }
}
if ($object->task_represents_project) {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $object->task_represents_project, 'view subproject');
}
if ($canDelete) {
    $titleBlock->addCrumbDelete('delete task', $canDelete, $msg);
}
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $object, 'Task');
echo $view->renderDelete();
?>
<script language="javascript" type="text/javascript">
function updateTask()
{
	var f = document.editFrm;

	f.submit();
}
</script>
<?php
$durnTypes = w2PgetSysVal('TaskDurationType');
$task_types = w2PgetSysVal('TaskType');
$billingCategory = w2PgetSysVal('BudgetCategory');

include $AppUI->getTheme()->resolveTemplate($m . '/' . $a);

$query_string = '?m=tasks&a=view&task_id=' . $object_id;
$tabBox = new CTabBox('?m=tasks&a=view&task_id=' . $object_id, '', $tab);

$tabBox_show = 0;
if ($object->task_dynamic != 1 && 0 == $object->task_represents_project) {
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

if (count($object->getChildren()) > 0) {
    // Has children
    // settings for tasks
    $f = 'children';
    $min_view = true;
    $tabBox_show = 1;
    // in the tasks file there is an if that checks
    // $_GET[task_status]; this patch is to be able to see
    // child tasks withing an inactive task
    $_GET['task_status'] = $object->task_status;
    $tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Child Tasks');
}

if (count($tabBox->tabs)) {
    $tabBox_show = 1;
}

if ($tabBox_show == 1) {
    $tabBox->show();
}
