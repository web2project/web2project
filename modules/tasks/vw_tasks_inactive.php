<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $AppUI, $m, $project_id;

$task = new CTask();

$tasks = $task->loadAll('task_start_date, task_end_date', 'task_status = -1 AND task_project = '. $project_id);

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

$listTable = new w2p_Output_HTML_TaskTable($AppUI, $task);
$listTable->setFilters($f, $user_id);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$listTable->addBefore('edit', 'task_id');
$listTable->addBefore('pin', 'task_id');
$listTable->addBefore('log', 'task_id');

echo $listTable->startTable();
echo $listTable->buildHeader($fields, false, $m);
echo $listTable->buildRows($tasks, $customLookups);
echo $listTable->endTable();
?>
<?php
include $AppUI->getTheme()->resolveTemplate('task_key');