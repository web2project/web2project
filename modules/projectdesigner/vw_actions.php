<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $task_access, $task_priority, $project_id;

$type = w2Pgetsysval('TaskType');
$stype = array('' => '('.$AppUI->_('Type').')') + $type;
$priority = w2Pgetsysval('TaskPriority');
$spriority = array('' => '('.$AppUI->_('Priority').')') + $priority;
$task_access = array(CTask::ACCESS_PUBLIC => $AppUI->_('Public'),
        CTask::ACCESS_PROTECTED => $AppUI->_('Protected'),
        CTask::ACCESS_PARTICIPANT => $AppUI->_('Participant'), CTask::ACCESS_PRIVATE => $AppUI->_('Private'));
$stask_access = array('' => '('.$AppUI->_('Access').')') + $task_access;
$durntype = w2PgetSysval('TaskDurationType');
$sdurntype = array('' => '('.$AppUI->_('Duration Type').')') + $durntype;
$sother = array('' => '('.$AppUI->_('Other Operations').')', '1' => $AppUI->_('Mark Tasks as Finished'),
        '8' => $AppUI->_('Mark Tasks as Active'), '9' => $AppUI->_('Mark Tasks as Inactive'),
        '2' => $AppUI->_('Mark Tasks as Milestones'), '3' => $AppUI->_('Mark Tasks as Non Milestone'),
        '4' => $AppUI->_('Mark Tasks as Dynamic'), '5' => $AppUI->_('Mark Tasks as Non Dynamic'),
        '6' => $AppUI->_('Add Task Reminder'), '7' => $AppUI->_('Remove Task Reminder'),
        '10' => $AppUI->_('Remove Tasks Description'), '99' => $AppUI->_('Delete Tasks'));

//Pull all users
$users = $perms->getPermittedUsers();
$sowners = array('' => '('.$AppUI->_('Task Owner').')') + $perms->getPermittedUsers('tasks');
$sassign = array('' => '('.$AppUI->_('Assign User').')') + $perms->getPermittedUsers('tasks');
$sunassign = array('' => '('.$AppUI->_('Unassign User').')') + $users;

$obj = new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id, 'tasks.task_id');

$obj->load($task_id);
$task_project = $project_id ? $project_id : ($obj->task_project ? $obj->task_project : 0);

$projTasks = array();

$parents = array();
$projTasksWithEndDates = array(0 => $AppUI->_('None')); //arrays contains task end date info for setting new task start date as maximum end date of dependenced tasks
$all_tasks = array();

$subtasks = $task->getNonRootTasks($task_project);
foreach ($subtasks as $sub_task) {
    // Build parent/child task list
    $parents[$sub_task['task_parent']][] = $sub_task['task_id'];
    $all_tasks[$sub_task['task_id']] = $sub_task;
    build_date_list($projTasksWithEndDates, $sub_task);
}

$task_parent_options = '';

$root_tasks = $obj->getRootTasks((int) $task_project);
foreach ($root_tasks as $root_task) {
    build_date_list($projTasksWithEndDates, $root_task);
	if ($root_task['task_id'] != $task_id) {
        $task_parent_options .= buildTaskTree($root_task, 0, array(), $all_tasks, $parents, 0, $task_id);
	}
}

$project = new CProject();
$sprojects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');

$idx_companies = __extract_from_vw_actions();

foreach ($sprojects as $prj_id => $prj_name) {
	$sprojects[$prj_id] = $idx_companies[$prj_id] . ': ' . $prj_name;
}
asort($sprojects);
$sprojects = arrayMerge(array('' => '(' . $AppUI->_('Move to Project', UI_OUTPUT_RAW) . ')'), $sprojects);

//lets addthe reference to percent
$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');
$spercent = arrayMerge(array('' => '('.$AppUI->_('Progress').')'), $percent);
?>
    <table id="tbl_bulk" width="100%" class="well">
        <tr>
            <th width="15%"><?php echo $AppUI->_('Start Date'); ?>&nbsp;</th>
            <td width="160" nowrap="nowrap">
                <input type='hidden' id='add_task_bulk_start_date' name='add_task_bulk_start_date' value='' />
                <input type='text' onchange="setDate('frm_bulk', 'bulk_start_date');" class='text' id='bulk_start_date' name='bulk_start_date' value='' />
                <a onclick="return showCalendar('bulk_start_date', '<?php echo $cf ?>', 'frm_bulk', '<?php echo (strpos($cf, '%p') !== false ? '12' : '24') ?>', true)" href="javascript: void(0);">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <th width="15%" nowrap="nowrap"><?php echo $AppUI->_('End Date'); ?>&nbsp;</th>
            <td width="160" nowrap="nowrap">
                <input type='hidden' id='add_task_bulk_end_date' name='add_task_bulk_end_date' value='' />
                <input type='text' onchange="setDate('frm_bulk', 'bulk_end_date');" class='text' id='bulk_end_date' name='bulk_end_date' value='' />
                <a onclick="return showCalendar('bulk_end_date', '<?php echo $cf ?>', 'frm_bulk', '<?php echo (strpos($cf, '%p') !== false ? '12' : '24') ?>', true)" href="javascript: void(0);">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <th width="15%" nowrap="nowrap"><?php echo $AppUI->_('Keep daytimes'); ?>&nbsp;</th>
            <td width="160" nowrap="nowrap">
                <input type='checkbox' id='add_task_bulk_time_keep' name='add_task_bulk_time_keep' value='1' checked />
            </td>
       </tr>
        <tr>
            <th nowrap="nowrap"><?php echo $AppUI->_('Duration'); ?>&nbsp;</th>
            <td nowrap="nowrap">
                <input type='text' class='text' id='bulk_task_duration' name='bulk_task_duration' value='' />&nbsp;
                <?php echo arraySelect($sdurntype, 'bulk_task_durntype', 'size="1" class="text"', '', true); ?>
            </td>
            <th><?php echo $AppUI->_('Assign') . '&nbsp;</th>'; ?>
            <td nowrap="nowrap"><a href="javascript: void(0);" style="display: block;" onclick="expand_selector('assign', 'frm_bulk')"><img src="<?php echo w2PfindImage('icons/expand.gif'); ?>" id="assign_expand" />&nbsp;<img src="<?php echo w2PfindImage('icons/collapse.gif'); ?>" id="assign_collapse" style="display:none">&nbsp;</a>
                <div>
                    <table>
                        <tr id="assign" style="visibility:collapse;display:none">
                            <td nowrap="nowrap">
                                <a href="javascript: void(0);" onclick="addUser(document.frm_bulk)">
                                    <img src="<?php echo w2PfindImage('add.png', $m); ?>" title="<?php echo $AppUI->_('Add Assignment'); ?>" alt="<?php echo $AppUI->_('Add Assignment'); ?>" />
                                </a>
                                <?php echo arraySelect($sassign, 'bulk_task_user', 'class="text"', ''); ?>
                                <select name="bulk_task_assign_perc" class="text">
                                    <?php
                                    for ($i = 5; $i <= 100; $i += 5) {
                                        echo '<option ' . (($i == 100) ? 'selected="true"' : '') . ' value="' . $i . '">' . $i . '%</option>';
                                    }
                                    ?>
                                </select><br /><br />
                                <a href="javascript: void(0);" onclick="removeUser(document.frm_bulk)">
                                    <img src="<?php echo w2PfindImage('remove.png', $m); ?>" title="<?php echo $AppUI->_('Remove Assignment'); ?>" alt="<?php echo $AppUI->_('Remove Assignment'); ?>" />
                                </a>
                                <select name="bulk_task_assign[]" id="bulk_task_assign" size="6" class="text" multiple="multiple">
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <th><?php echo $AppUI->_('Unassign'); ?>&nbsp;</th>
            <td><?php echo arraySelect($sunassign, 'bulk_task_unassign', 'class="text"', ''); ?></td>
        </tr>
        <tr>
            <th><?php echo $AppUI->_('Owner'); ?>&nbsp;</th>
            <td><?php echo arraySelect($sowners, 'bulk_task_owner', 'class="text"', ''); ?></td>
            <th><?php echo $AppUI->_('Priority'); ?>&nbsp;</th>
            <td><?php echo arraySelect($spriority, 'bulk_task_priority', 'class="text"', ''); ?></td>
            <th><?php echo $AppUI->_('User Priority for')." '".$AppUI->user_display_name."' (".$AppUI->_('if assigned').")"; ?>&nbsp;</th>
            <td><?php echo arraySelect($spriority, 'bulk_task_user_priority', 'class="text"', ''); ?></td>
            <td>&nbsp;</td>
        </tr>
<!--        <tr>-->
<!--            <th>--><?php //echo $AppUI->_('Type'); ?><!--&nbsp;</th>-->
<!--            <td>--><?php //echo arraySelect($stype, 'bulk_task_type', 'class="text"', ''); ?><!--</td>-->
<!--            <td>&nbsp;</td>-->
<!--        </tr>-->
        <tr>
            <th><?php echo $AppUI->_('Access'); ?>&nbsp;</th>
            <td><?php echo arraySelect($stask_access, 'bulk_task_access', 'class="text"', ''); ?></td>
            <th><?php echo $AppUI->_('Progress'); ?>&nbsp;</th>
            <td><?php echo arraySelect($spercent, 'bulk_task_percent_complete', 'class="text"', ''); ?> %</td>
            <th><?php echo $AppUI->_('Dependency'); ?>&nbsp;</th>
            <td>
                <select name='bulk_task_dependency' class='text'>
                    <option value=''>(<?php echo $AppUI->_('Task Depend on Completion of...'); ?>)</option>
                    <option value='0'>(<?php echo $AppUI->_('Remove Dependencies'); ?>)</option>
                    <?php echo $task_parent_options; ?>
                </select>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th nowrap="nowrap"><?php echo $AppUI->_('Date Move (Days)'); ?>&nbsp;</th>
            <td>
                <input type='text' class='text' id='bulk_move_date' name='bulk_move_date' value='' />
            </td>
            <th><?php echo $AppUI->_('Other'); ?>&nbsp;</th>
            <td><?php echo arraySelect($sother, 'bulk_task_other', 'class="text"', ''); ?></td>
            <th><?php echo $AppUI->_('Project'); ?>&nbsp;</th>
            <td><?php echo arraySelect($sprojects, 'bulk_task_project', 'class="text bulk"', ''); ?></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th><?php echo $AppUI->_('Allow users to add task logs for others'); ?></th>
            <td>
                <select name="bulk_task_allow_other_user_tasklogs"class="text">
                    <option value=""></option>
                    <option value="1"><?php echo $AppUI->_('Yes'); ?></option>
                    <option value="0"><?php echo $AppUI->_('No'); ?></option>
                </select>
            </td>
            <th><?php echo $AppUI->_('Parent'); ?>&nbsp;</th>
            <td>
                <select name='bulk_task_parent' class='text'>
                    <option value=''>(<?php echo $AppUI->_('Task Parent'); ?>)</option>
                    <option value='-1'>(<?php echo $AppUI->_('Reset to Self Task'); ?>)</option>
                    <?php echo $task_parent_options; ?>
                </select>
            </td>
            <td colspan="18" align="right"><input type="button" class="button btn btn-primary btn-small" value="<?php echo $AppUI->_('update'); ?>" onclick="if (confirm('Are you sure you wish to apply the update(s) to the selected task(s)?')) document.frm_bulk.submit();" /></td>
        </tr>
    </table>
</form>