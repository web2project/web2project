<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $AppUI, $m, $project_id, $f, $user_id;

$task = new CTask();

// Same permission filters as the active task list (vw_tasks.php): the
// project and task allow/deny lists for the current user.
$q = new w2p_Database_Query();
$q->addTable('tasks');
$q->addJoin('projects', 'p', 'p.project_id = task_project', 'inner');
$q->addQuery('tasks.*');
$q->addWhere('task_status = -1');
$q->addWhere('task_project = ' . (int) $project_id);

$project = new CProject();
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'task_project');
if (count($allowedProjects)) {
    $q->addWhere($allowedProjects);
}
$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'tasks.task_id');
if (count($allowedTasks)) {
    $q->addWhere($allowedTasks);
}
$q->addOrder('task_start_date, task_end_date');
$tasks = $q->loadHashList('task_id');

$module = new w2p_System_Module();
$fields = $module->loadSettings($m, 'tasklist');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_percent_complete', 'task_priority', 'user_task_priority', 'task_name', 'task_owner',
        'task_assignees', 'task_start_date', 'task_duration', 'task_end_date');
    $fieldNames = array('Percent', 'P', 'U', 'Task Name', 'Owner', 'Assignees', 'Start Date', 'Duration', 'Finish Date');

    $module->storeSettings($m, 'tasklist', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}
$fieldList = array_keys($fields);
$fieldNames = array_values($fields);

// same lookups as vw_tasks.php
$customLookups = array('task_status' => w2PgetSysVal('TaskStatus'), 'task_priority' => w2PgetSysVal('TaskPriority'));

$listTable = new w2p_Output_HTML_TaskTable($AppUI, $task);
$listTable->setFilters($f, $user_id);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$listTable->addBefore('edit', 'task_id');
$listTable->addBefore('pin', 'task_id');
$listTable->addBefore('log', 'task_id');

echo $listTable->startTable('tasks-tasklist');
echo $listTable->buildHeader($fields, false, $m);
echo $listTable->buildRows($tasks, $customLookups);
echo $listTable->endTable();
?>
<?php
include $AppUI->getTheme()->resolveTemplate('task_key');