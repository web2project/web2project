<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $priorities;
global $m, $a, $date, $other_users, $user_id, $task_type;
global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;

$showEditCheckbox = w2PgetConfig('direct_edit_assignment');

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
$AppUI = is_object($AppUI) ? $AppUI : new w2p_Core_CAppUI();
$showArcProjs = $AppUI->getState('TaskDayShowArc', 0);
$showLowTasks = $AppUI->getState('TaskDayShowLow', 1);
$showHoldProjs = $AppUI->getState('TaskDayShowHold', 0);
$showDynTasks = $AppUI->getState('TaskDayShowDyn', 0);
$showPinned = $AppUI->getState('TaskDayShowPin', 0);
$showEmptyDate = $AppUI->getState('TaskDayShowEmptyDate', 0);
$showInProgress = $AppUI->getState('TaskDayShowInProgress', 0);

/*
 * TODO: This is a nasty, dirty hack because globals have stacked on top of
 *   globals and have made a mess of things.. we need a better option.
 */
if(!isset($tasks) || !count($tasks)) {
    global $tasks;
}
$perms = &$AppUI->acl();
$canDelete = $perms->checkModuleItem($m, 'delete');
?>
<form name="form_buttons" method="post" action="index.php?<?php echo 'm=' . $m . '&amp;a=' . $a . '&amp;date=' . $date; ?>" accept-charset="utf-8">
    <input type="hidden" name="show_form" value="1" />
    <table width="100%" border="0" cellpadding="1" cellspacing="0" class="my-tasks">
        <tr>
            <td width="50%">
                <?php
                    if ($other_users) {
                        $users = $perms->getPermittedUsers('tasks');
                        echo arraySelect($users, 'show_user_todo', 'class="text" onchange="document.form_buttons.submit()"', $user_id);
                    }
                ?>
            </td>
            <td align="right" width="50%">
                <?php echo $AppUI->_('Show'); ?>:
                <input type="checkbox" name="show_inprogress" id="show_inprogress" onclick="document.form_buttons.submit()" <?php echo $showInProgress ? 'checked="checked"' : ''; ?> />
            </td>
             <td nowrap="nowrap">
                <label for="show_inprogress"><?php echo $AppUI->_('In Progress Only'); ?></label>
            </td>
            <td>
                <input type="checkbox" name="show_pinned" id="show_pinned" onclick="document.form_buttons.submit()" <?php echo $showPinned ? 'checked="checked"' : ''; ?> />
            </td>
            <td nowrap="nowrap">
                <label for="show_pinned"><?php echo $AppUI->_('Pinned Only'); ?></label>
            </td>
            <td>
                <input type="checkbox" name="show_arc_proj" id="show_arc_proj" onclick="document.form_buttons.submit()" <?php echo $showArcProjs ? 'checked="checked"' : ''; ?> />
            </td>
            <td nowrap="nowrap">
                <label for="show_arc_proj"><?php echo $AppUI->_('Archived/Template Projects'); ?></label>
            </td>
            <td>
                <input type="checkbox" name="show_dyn_task" id="show_dyn_task" onclick="document.form_buttons.submit()" <?php echo $showDynTasks ? 'checked="checked"' : ''; ?> />
            </td>
            <td nowrap="nowrap">
                <label for="show_dyn_task"><?php echo $AppUI->_('Dynamic Tasks'); ?></label>
            </td>
            <td>
                <input type="checkbox" name="show_low_task" id="show_low_task" onclick="document.form_buttons.submit()" <?php echo $showLowTasks ? 'checked="checked"' : ''; ?> />
            </td>
            <td nowrap="nowrap">
                <label for="show_low_task"><?php echo $AppUI->_('Low Priority Tasks'); ?></label>
            </td>
            <td>
                <input type="checkbox" name="show_empty_date" id="show_empty_date" onclick="document.form_buttons.submit()" <?php echo $showEmptyDate ? 'checked="checked"' : ''; ?> />
            </td>
            <td nowrap="nowrap">
                <label for="show_empty_date"><?php echo $AppUI->_('Empty Dates'); ?></label>
            </td>
            <td>
            <?php
                $types = array('' => '(Task Type Filter)') + w2PgetSysVal('TaskType');
                echo arraySelect($types, 'task_type', 'class="text" onchange="document.form_buttons.submit()"', $task_type, true);
            ?>
            </td>
        </tr>
    </table>
</form>
<?php
$module = new w2p_System_Module();
$fields = $module->loadSettings('tasks', 'todo');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_percent_complete', 'task_priority', 'user_task_priority', 'task_name', 'task_project',
        'task_start_datetime', 'task_duration', 'task_end_datetime', 'task_due_in');
    $fieldNames = array('', 'P', 'U', 'Task Name', 'Project Name', 'Start Date', 'Duration', 'Finish Date', 'Due In');

    $module->storeSettings('tasks', 'todo', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}
$fieldNames = array_values($fields);

$listTable = new w2p_Output_HTML_TaskTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$listTable->addBefore('edit', 'task_id');
$listTable->addBefore('pin', 'task_id');
$listTable->addBefore('log', 'task_id');

echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($tasks);
echo $listTable->endTable();

include $AppUI->getTheme()->resolveTemplate('task_key');