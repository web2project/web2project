<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $date, $company_id, $AppUI, $task_type;

// The following code shows tasks that belong to projects of which
// the current user is owner and that are late as of the specified date or
// have a problem indication, regardless of lateness.

$user_id = $AppUI->user_id;

// get the tab _GET, when embedded in a Day View
$tab = w2PgetParam($_GET, 'tab', '');

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

if (isset($_POST['task_type'])) {
	$AppUI->setState('ToDoTaskType', w2PgetParam($_POST, 'task_type', ''));
}
$task_type = $AppUI->getState('ToDoTaskType') !== null ? $AppUI->getState('ToDoTaskType') : '';

// Required for today view.
$showArcProjs = $AppUI->getState('TaskDayShowArc', 0);
$showLowTasks = $AppUI->getState('TaskDayShowLow', 1);
$showHoldProjs = $AppUI->getState('TaskDayShowHold', 0);
$showDynTasks = $AppUI->getState('TaskDayShowDyn', 0);
$showPinned = $AppUI->getState('TaskDayShowPin', 0);
$showEmptyDate = $AppUI->getState('TaskDayShowEmptyDate', 0);
$showInProgress = $AppUI->getState('TaskDayShowInProgress', 0);

?>
<form name="form_buttons" method="post" action="index.php?<?php echo 'm=' . $m . '&amp;a=' . $a . '&amp;date=' . $date . (!empty($tab) ? '&tab=' . $tab : ''); ?>" accept-charset="utf-8">
    <input type="hidden" name="show_form" value="1" />
    <table width="100%" border="0" cellpadding="4" cellspacing="0">
        <tr>
            <td align="left" width="30%">
                <?php echo $AppUI->_('Show Tasks') . ':'; ?>
		 </td>
            <td valign="bottom">
                <?php
                    if ($other_users) {
                        $users = $perms->getPermittedUsers('tasks');
                        echo $AppUI->_('Assigned to') . ': ' . arraySelect($users, 'show_user_todo', 'class="text" onchange="document.form_buttons.submit()"', $user_id);
                    }
                ?>
            </td>
            <td valign="bottom">
			<?php echo $AppUI->_('From Projects In Progress Only'). ':'; ?><br>
                <input type="checkbox" name="show_inprogress" id="show_inprogress" onclick="document.form_buttons.submit()" <?php echo $showInProgress ? 'checked="checked"' : ''; ?> />
            </td>
            <td valign="bottom">
			<?php echo $AppUI->_('Pinned Only') . ':'; ?><br>
                <input type="checkbox" name="show_pinned" id="show_pinned" onclick="document.form_buttons.submit()" <?php echo $showPinned ? 'checked="checked"' : ''; ?> />
            </td>
            <td valign="bottom">
			<?php echo $AppUI->_('From Archived/Template Projects') . ':'; ?><br>
                <input type="checkbox" name="show_arc_proj" id="show_arc_proj" onclick="document.form_buttons.submit()" <?php echo $showArcProjs ? 'checked="checked"' : ''; ?> />
            </td>
            <td valign="bottom">
			<?php echo $AppUI->_('Dynamic Tasks') . ':'; ?><br>
                <input type="checkbox" name="show_dyn_task" id="show_dyn_task" onclick="document.form_buttons.submit()" <?php echo $showDynTasks ? 'checked="checked"' : ''; ?> />
            </td>
            <td valign="bottom">
			<?php echo $AppUI->_('Low Priority Tasks') . ':'; ?><br>
                <input type="checkbox" name="show_low_task" id="show_low_task" onclick="document.form_buttons.submit()" <?php echo $showLowTasks ? 'checked="checked"' : ''; ?> />
            </td>
            <td valign="bottom">
			<?php echo $AppUI->_('Empty Dates') . ':'; ?><br>
                <input type="checkbox" name="show_empty_date" id="show_empty_date" onclick="document.form_buttons.submit()" <?php echo $showEmptyDate ? 'checked="checked"' : ''; ?> />
            </td>
            <td valign="bottom">
			<?php echo $AppUI->_('Type') . ':<br>';
                $types = array('' => $AppUI->_('All types')) + w2PgetSysVal('TaskType');
                echo arraySelect($types, 'task_type', 'class="text" onchange="document.form_buttons.submit()"', $task_type, true);
            ?>
            </td>
        </tr>
    </table>
</form>

<?php

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
<table>
    <tr>
        <td>&nbsp; &nbsp;</td>
        <td style="border-style:solid;border-width:1px" bgcolor="#ffffff">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Future Task'); ?></td>
        <td>&nbsp; &nbsp;</td>
        <td style="border-style:solid;border-width:1px" bgcolor="#e6eedd">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Started and on time'); ?></td>
        <td style="border-style:solid;border-width:1px" bgcolor="#ffeebb">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Should have started'); ?></td>
        <td>&nbsp; &nbsp;</td>
        <td style="border-style:solid;border-width:1px" bgcolor="#CC6666">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Overdue'); ?></td>
    </tr>
</table>