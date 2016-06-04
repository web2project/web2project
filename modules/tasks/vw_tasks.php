<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $m, $a, $project_id, $task_id, $f, $task_status, $min_view, $query_string, $durnTypes, $tpl;
global $user_id, $w2Pconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox, $tab;

$tab = (int) w2PgetParam($_GET, 'tab', 0);

if (empty($query_string)) {
    $query_string = '?m=' . $m . '&amp;a=' . $a;
}
$mods = $AppUI->getActiveModules();

/****
// Let's figure out which tasks are selected
 */
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);

$pinned_only = (int) w2PgetParam($_GET, 'pinned', 0);
__extract_from_tasks_pinning($AppUI, $task_id);

$project = new CProject;
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'p.project_id');

$where_list = (count($allowedProjects)) ? implode(' AND ', $allowedProjects) : '';

$working_hours = ($w2Pconfig['daily_working_hours'] ? $w2Pconfig['daily_working_hours'] : 8);

$projects = __extract_from_tasks4($where_list, $project_id, $task_id);

$subquery = __extract_from_tasks1();
$task_status = __extract_from_tasks($min_view, $currentTabId, $project_id, $currentTabName, $AppUI);

$q = new w2p_Database_Query;
$q = __extract_from_tasks5($q, $subquery);

$q->addJoin('projects', 'p', 'p.project_id = task_project', 'inner');
$q->leftJoin('users', 'usernames', 'task_owner = usernames.user_id');
$q->leftJoin('user_tasks', 'ut', 'ut.task_id = tasks.task_id');
$q->leftJoin('users', 'assignees', 'assignees.user_id = ut.user_id');
$q->leftJoin('contacts', 'co', 'co.contact_id = usernames.user_contact');
$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = tasks.task_id AND tlog.task_log_problem > 0');
$q->leftJoin('files', 'f', 'tasks.task_id = f.file_task');
$q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q->leftJoin('user_task_pin', 'pin', 'tasks.task_id = pin.task_id AND pin.user_id = ' . (int)$AppUI->user_id);

$f2 = isset($f2) ? $f2 : 0;
if ((int) $f2) {
    $q->addWhere('project_company = ' . (int) $f2);
}
if ($project_id) {
    $q->addWhere('task_project = ' . (int)$project_id);
    //if we are on a project context make sure we show all tasks
    $f = 'all';
} else {
    $q->addWhere('project_active = 1');
    if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
        $q->addWhere('project_status <> ' . $template_status);
    }
}

if ($pinned_only) {
    $q->addWhere('task_pinned = 1');
}

$q = __extract_from_tasks3($f, $q, $user_id, $task_id, $AppUI);

if ($showIncomplete) {
    $q->addWhere('task_percent_complete <> 100');
}

//When in task view context show all the tasks, active and inactive. (by not limiting the query by task status)
//When in a project view or in the tasks list, show the active or the inactive tasks depending on the selected tab or button.
if (!$task_id) {
    if ($tab == 1) {
        $task_status = -1;
    } else {
        $task_status = 0;
    }
    $q->addWhere('task_status = ' . (int)$task_status);
}
if (isset($task_type) && (int) $task_type > 0) {
    $q->addWhere('task_type = ' . (int)$task_type);
}
if (isset($task_owner) && (int) $task_owner > 0) {
    $q->addWhere('task_owner = ' . (int)$task_owner);
}

if (($project_id || !$task_id) && !$min_view) {
    if ($search_text = $AppUI->getState('tasks_search_string')) {
        $q->addWhere('( task_name LIKE (\'%' . $search_text . '%\') OR task_description LIKE (\'%' . $search_text . '%\') )');
    }
}

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';

// TODO: Enable tasks filtering
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
if (!$project_id && !$task_id) {
    $q->addOrder('p.project_id, task_start_date, task_end_date');
} else {
    $q->addOrder('task_start_date, task_end_date, task_name');
}

$tasks = $q->loadList();

// POST PROCESSING TASKS
if (count($tasks) > 0) {
    foreach ($tasks as $row) {
        //add information about assigned users into the page output
        $assigned_users = __extract_from_tasks2($row);

        $row['task_assigned_users'] = $assigned_users;

        //pull the final task row into array
        $projects[$row['task_project']]['tasks'][] = $row;
    }
}

$showEditCheckbox = ((isset($canEdit) && $canEdit && w2PgetConfig('direct_edit_assignment')) ? true : false);

$durnTypes = w2PgetSysVal('TaskDurationType');
$tempTask = new CTask();
$userAlloc = $tempTask->getAllocation('user_id');

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
$listTable->setFilters($f, $user_id);
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
    $taskTree = $tempTask->getTaskTree($task->task_project, $task_id);
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
            $taskTree = $tempTask->getTaskTree($k, 0);
            echo $listTable->buildRows($taskTree, $customLookups);
        }
        if ('projects' == $m || 'projectdesigner' == $m) {
            $taskTree = $tempTask->getTaskTree($k, 0);
            echo $listTable->buildRows($taskTree, $customLookups);
        }
    }
}


echo $listTable->endTable();
?>
<?php
include $AppUI->getTheme()->resolveTemplate('task_key');