<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $project, $project_id, $canEdit, $m, $tab;

$company_id = $project->company();

$task_log_costcodes =  array(0 => '(all)') + CProject::getBillingCodes($company_id, true);

$users = w2PgetUsers();

$cost_code = w2PgetParam($_GET, 'cost_code', 0);

if (isset($_GET['user_id'])) {
	$AppUI->setState('ProjectsTaskLogsUserFilter', w2PgetParam($_GET, 'user_id', 0));
}
$user_id = $AppUI->getState('ProjectsTaskLogsUserFilter') ? $AppUI->getState('ProjectsTaskLogsUserFilter') : 0;

if (isset($_GET['hide_inactive'])) {
	$AppUI->setState('ProjectsTaskLogsHideArchived', true);
} else {
	$AppUI->setState('ProjectsTaskLogsHideArchived', false);
}
$hide_inactive = $AppUI->getState('ProjectsTaskLogsHideArchived');

if (isset($_GET['hide_complete'])) {
	$AppUI->setState('ProjectsTaskLogsHideComplete', true);
} else {
	$AppUI->setState('ProjectsTaskLogsHideComplete', false);
}
$hide_complete = $AppUI->getState('ProjectsTaskLogsHideComplete');

?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt2(id) {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Task Log', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
<?php } ?>
</script>
<form name="frmFilter" action="./index.php" method="get" accept-charset="utf-8">
    <input type="hidden" name="m" value="projects"/>
    <input type="hidden" name="a" value="view"/>
    <input type="hidden" name="project_id" value="<?php echo $project_id ?>"/>
    <input type="hidden" name="tab" value="<?php echo $tab ?>"/>

    <table class="std">
        <tr>
            <td width="98%">&nbsp;</td>
            <td width="1%" nowrap="nowrap"><input type="checkbox" name="hide_inactive" id="hide_inactive" <?php echo $hide_inactive ? 'checked="checked"' : '' ?> onchange="document.frmFilter.submit()" /></td><td width="1%" nowrap="nowrap"><label for="hide_inactive"><?php echo $AppUI->_('Hide Inactive') ?></label></td>
            <td width="1%" nowrap="nowrap"><input type="checkbox" name="hide_complete" id="hide_complete" <?php echo $hide_complete ? 'checked="checked"' : '' ?> onchange="document.frmFilter.submit()" /></td><td width="1%" nowrap="nowrap"><label for="hide_complete"><?php echo $AppUI->_('Hide 100% Complete') ?></label></td>
            <!--
        TODO: disabled this filter for now... something is wrong with the userId portion...
            <td width="1%" nowrap="nowrap"><?php echo $AppUI->_('User Filter') ?></td>
            <td width="1%"><?php echo arraySelect($users, 'user_id', 'size="1" class="text" id="medium" onchange="document.frmFilter.submit()"', $user_id) ?></td>
            -->
            <td width="1%" nowrap="nowrap"><?php echo $AppUI->_('Cost Code Filter') ?></td>
            <!-- TODO: add in optgroups to display company groupings for cost codes -->
            <td width="1%"><?php echo arraySelect($task_log_costcodes, 'cost_code', 'size="1" class="text" onchange="document.frmFilter.submit()"', $cost_code) ?></td>
        </tr>
    </table>
</form>
<form name="frmDelete2" action="./index.php?m=tasks" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>
<?php
$fieldList = array();
$fieldNames = array();

$module = new w2p_System_Module();
$fields = $module->loadSettings('tasks', 'task_logs_projects_view');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_log_date', 'task_log_name',
        'task_log_creator', 'task_log_hours', 'task_log_costcode', 'task_log_description');
    $fieldNames = array('Date', 'Summary', 'User', 'Hours',
        'Cost Code', 'Comments', '');

    $module->storeSettings('tasks', 'task_logs_projects_view', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}
$fieldList = array_keys($fields);
$fieldNames = array_values($fields);

$i = 0;
$hours_index = 0;
?>
<a name="task_logs-projects_view"> </a>
<table class="tbl list">
    <tr>
        <th></th>
        <?php
        foreach ($fieldNames as $index => $name) {
            ?><th><?php echo $AppUI->_($fieldNames[$index]); ?></th><?php
            if ('task_log_hours' == $fieldList[$index]) {
                $hours_index = $i;
            }
            $i++;
        }
        ?>
        <th></th>
    </tr>
<?php
// Winnow out the tasks we are not allowed to view.
$perms = &$AppUI->acl();
$canDelete = canDelete('task_log');

// Pull the task comments
$project = new CProject;
//TODO: this method should be moved to CTaskLog
$logs = $project->getTaskLogs(null, $project_id, $user_id, $hide_inactive, $hide_complete, $cost_code);

$s = '';
$hrs = 0;
$canEdit = canEdit('task_log');

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$billingCategory = w2PgetSysVal('BudgetCategory');
$durnTypes = w2PgetSysVal('TaskDurationType');
$status = w2PgetSysVal('TaskStatus');
$task_types = w2PgetSysVal('TaskType');

$customLookups = array('budget_category' => $billingCategory, 'task_duration_type' => $durnTypes,
        'task_status' => $status, 'task_type' => $task_types);

$task = new CTask();

if (count($logs)) {
    foreach ($logs as $row) {
        $task->task_id = $row['task_id'];
        if (!$task->canAccess()) {
            /** If the user isn't allowed to see the task, don't show the logs. */
            continue;
        }
        
        $s .= '<tr bgcolor="white" valign="top"><td class="data _edit">';
        if ($canEdit) {
            $s .= '<a href="?m=tasks&a=view&task_id=' . $row['task_id'] . '&tab=1&task_log_id=' . $row['task_log_id'] . '">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . "\n\t\t</a>";
        }
        $s .= '</td>';
        $htmlHelper->stageRowData($row);
        foreach ($fieldList as $index => $column) {
            $s .= $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
        }

        $s .= '<td class="data _delete">';
        if ($canDelete) {
            $s .= '<a href="javascript:delIt2(' . $row['task_log_id'] . ');" title="' . $AppUI->_('delete log') . '">' . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
        }
        $s .= '</td></tr>';
        
        $hrs += (float)$row['task_log_hours'];
    }
}
$s .= '<tr bgcolor="white" valign="top">';
$s .= '<td colspan="'.($hours_index + 1).'" align="right">' . $AppUI->_('Total Hours') . ' =</td>';
$s .= $htmlHelper->createCell('task_log_hours', sprintf('%.2f', $hrs));
$s .= str_repeat('<td></td>', count($fieldList) - $hours_index); ;
$s .= '</tr>';
echo $s;
?>
</table>