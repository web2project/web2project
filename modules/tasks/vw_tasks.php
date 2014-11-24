<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $m, $a, $project_id, $task_id, $f, $task_status, $min_view, $query_string, $durnTypes, $tpl;
global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;
global $user_id, $w2Pconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox, $tab;
global $history_active;

if (empty($query_string)) {
    $query_string = '?m=' . $m . '&amp;a=' . $a;
}
$mods = $AppUI->getActiveModules();
$history_active = !empty($mods['history']) && canView('history');

/****
// Let's figure out which tasks are selected
 */
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);

$pinned_only = (int) w2PgetParam($_GET, 'pinned', 0);
__extract_from_tasks_pinning($AppUI, $task_id);

$task_sort_item1 = w2PgetParam($_GET, 'task_sort_item1', 'task_start_date');
$task_sort_type1 = w2PgetParam($_GET, 'task_sort_type1', '');
$task_sort_item2 = w2PgetParam($_GET, 'task_sort_item2', 'task_end_date');
$task_sort_type2 = w2PgetParam($_GET, 'task_sort_type2', '');
$task_sort_order1 = (int) w2PgetParam($_GET, 'task_sort_order1', 0);
$task_sort_order2 = (int) w2PgetParam($_GET, 'task_sort_order2', 0);
if (isset($_POST['show_task_options'])) {
    $AppUI->setState('TaskListShowIncomplete', w2PgetParam($_POST, 'show_incomplete', 0));
}
$showIncomplete = $AppUI->getState('TaskListShowIncomplete', 0);

$project = new CProject();
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'p.project_id');
$where_list = (count($allowedProjects)) ? implode(' AND ', $allowedProjects) : '';
$projects = __extract_from_tasks4($where_list, $project_id, $task_id);

global $expanded;
$expanded = $AppUI->getPref('TASKSEXPANDED');
$open_link = w2PtoolTip($m, 'click to expand/collapse all the tasks for this project.') . '<a href="javascript: void(0);"><img onclick="expand_collapse(\'task_proj_' . $project_id . '_\', \'tblProjects\',\'collapse\',0,2);" id="task_proj_' . $project_id . '__collapse" src="' . w2PfindImage('up22.png', $m) . '" class="center" ' . (!$expanded ? 'style="display:none"' : '') . ' /><img onclick="expand_collapse(\'task_proj_' . $project_id . '_\', \'tblProjects\',\'expand\',0,2);" id="task_proj_' . $project_id . '__expand" src="' . w2PfindImage('down22.png', $m) . '" class="center" ' . ($expanded ? 'style="display:none"' : '') . ' /></a>' . w2PendTip();

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

$tempTask = new CTask();
$listTable = new w2p_Output_HTML_TaskTable($AppUI, $tempTask);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$listTable->addBefore('edit', 'task_id');
$listTable->addBefore('pin', 'task_id');
$listTable->addBefore('log', 'task_id');
?>
<form name="frm_bulk" method="post" action="?m=projectdesigner" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_task_bulk_aed" />
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
    <input type="hidden" name="pd_option_view_project" value="<?php echo (isset($view_options[0]['pd_option_view_project']) ? $view_options[0]['pd_option_view_project'] : 1); ?>" />
    <input type="hidden" name="pd_option_view_gantt" value="<?php echo (isset($view_options[0]['pd_option_view_gantt']) ? $view_options[0]['pd_option_view_gantt'] : 1); ?>" />
    <input type="hidden" name="pd_option_view_tasks" value="<?php echo (isset($view_options[0]['pd_option_view_tasks']) ? $view_options[0]['pd_option_view_tasks'] : 1); ?>" />
    <input type="hidden" name="pd_option_view_actions" value="<?php echo (isset($view_options[0]['pd_option_view_actions']) ? $view_options[0]['pd_option_view_actions'] : 1); ?>" />
    <input type="hidden" name="pd_option_view_addtasks" value="<?php echo (isset($view_options[0]['pd_option_view_addtasks']) ? $view_options[0]['pd_option_view_addtasks'] : 1); ?>" />
    <input type="hidden" name="pd_option_view_files" value="<?php echo (isset($view_options[0]['pd_option_view_files']) ? $view_options[0]['pd_option_view_files'] : 1); ?>" />
    <input type="hidden" name="bulk_task_hperc_assign" value="" />

<?php
echo $listTable->startTable();

echo $listTable->buildHeader($fields, false, $m);

$status = w2PgetSysVal('TaskStatus');
$priority = w2PgetSysVal('TaskPriority');
$customLookups = array('task_status' => $status, 'task_priority' => $priority);

if ($task_id) {
    $task = new CTask();
    $task->load($task_id);
    $taskTree = $tempTask->getTaskTree($task->task_project, $task_id, $showIncomplete);
    echo $listTable->buildRows($taskTree, $customLookups);
} else {
    reset($projects);
    foreach ($projects as $k => $p) {
        $tnums = (isset($p['tasks'])) ? count($p['tasks']) : 0;
        if ($tnums && $m == 'tasks') {
            $width = ($p['project_percent_complete'] < 30) ? 30 : $p['project_percent_complete'];
            ?>
            <tr>
                <td colspan="<?php echo count($fieldList) + 3; ?>">
                    <div style="border: outset #eeeeee 1px;background-color:#<?php echo $p['project_color_identifier']; ?>; width: <?php echo $width; ?>%">
                        <a href="./index.php?m=projects&amp;a=view&amp;project_id=<?php echo $k; ?>">
                            <?php echo w2PshowImage('pencil.gif'); ?>
                        </a>
                        <span style="color:<?php echo bestColor($p['project_color_identifier']); ?>;text-decoration:none;">
                            <strong>
                                <?php echo $p['company_name'] . ' :: ' . $p['project_name']; ?>
                            </strong>
                            <span style="float: right;">
                                <?php echo (int) $p['project_percent_complete']; ?>%
                            </span>
                        </span>
                    </div>
                </td>
            </tr>
            <?php
            $taskTree = $tempTask->getTaskTree($k, 0, $showIncomplete);
            echo $listTable->buildRows($taskTree, $customLookups);
        }
        if ('projects' == $m || 'projectdesigner' == $m) {
            // TODO: fix this ugly bit of code :(
            if ($tab == 1) {
                $taskTree = $tempTask->loadAll('task_start_date, task_end_date', "task_project = $k AND task_status != 0");
            } else {
                $taskTree = $tempTask->getTaskTree($k);
            }
            echo $listTable->buildRows($taskTree, $customLookups);
        }
    }
}


echo $listTable->endTable();
?>
<?php
include $AppUI->getTheme()->resolveTemplate('task_key');
