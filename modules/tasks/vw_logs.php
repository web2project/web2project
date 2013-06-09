<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $task_id, $sf, $df, $canEdit, $m;

$perms = &$AppUI->acl();
if (!canView('task_log')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$problem = (int) w2PgetParam($_GET, 'problem', null);

?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
$canDelete = canDelete('task_log');
if ($canDelete) {
?>
function delIt2(id) {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Task Log', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
<?php } ?>
</script>
<form name="frmDelete2" action="./index.php?m=tasks" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>
<?php
$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('tasks', 'task_logs_tasks_view');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_log_date', 'task_log_reference',
        'task_log_name', 'task_log_related_url', 'task_log_creator',
        'task_log_hours', 'task_log_costcode', 'task_log_description');
    $fieldNames = array('Date', 'Ref', 'Summary', 'URL', 'User',
        'Hours', 'Cost Code', 'Comments');

    $module->storeSettings('tasks', 'task_logs_tasks_view', $fieldList, $fieldNames);
}
?>
<a name="task_logs-tasks_view"> </a>
<table class="tbl list">
    <tr>
        <th></th>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
        <th></th>
    </tr>
<?php
// Pull the task comments
$task= new CTask();
//TODO: this method should be moved to CTaskLog
$logs = $task->getTaskLogs($task_id, $problem);

$s = '';
$hrs = 0;
$canEdit = canEdit('task_log');

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$billingCategory = w2PgetSysVal('BudgetCategory');
$durnTypes = w2PgetSysVal('TaskDurationType');
$taskLogReference = w2PgetSysVal('TaskLogReference');
$status = w2PgetSysVal('TaskStatus');
$task_types = w2PgetSysVal('TaskType');

$customLookups = array('budget_category' => $billingCategory, 'task_duration_type' => $durnTypes,
        'task_log_reference' => $taskLogReference, 'task_status' => $status, 'task_type' => $task_types);

if (count($logs)) {
    foreach ($logs as $row) {
        $s .= '<tr>';

        $s .= '<td class="data _edit">';
        if ($canEdit) {
            $s .= '<a href="?m=tasks&a=view&task_id=' . $task_id . '&tab=';
            $s .= ($tab == -1) ? $AppUI->getState('TaskLogVwTab') : '1';
            $s .= '&task_log_id=' . $row['task_log_id'] . '">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '</a>';
        }
        $s .= '<a name="tasklog' . $row['task_log_id'] . '"></a>';
        $s .= '</td>';

        $htmlHelper->stageRowData($row);
        foreach ($fieldList as $index => $column) {
            $s .= $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
        }

        $s .= '<td class="data _delete">';
        if ($canDelete) {
            $s .= '<a href="javascript:delIt2(' . $row['task_log_id'] . ');" title="' . $AppUI->_('delete log') . '">' . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
        }
        $s .= '</td>';

        $s .= '</tr>';
        $hrs += (float)$row['task_log_hours'];
    }
}

$s .= '<tr>';
$s .= '<td colspan="6" align="right">' . $AppUI->_('Total Hours') . ' =</td>';
$s .= $htmlHelper->createCell('task_log_hours', sprintf('%.2f', $hrs));
$s .= '<td align="right" colspan="3">';
if ($perms->checkModuleItem('tasks', 'edit', $task_id)) {
	$s .= '<form action="?m=tasks&a=view&tab=1&task_id=' . $task_id . '" method="post" accept-charset="utf-8">';
    $s .= '<input type="submit" class="button btn btn-primary btn-mini" value="' . $AppUI->_('new log') . '"></form>';
}
$s .= '</td></tr>';

echo $s;
?>
</table>
<table>
<tr>
	<td><?php echo $AppUI->_('Key'); ?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffffff">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Normal Log'); ?></td>
	<td bgcolor="#CC6666">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Problem Report'); ?></td>
</tr>
</table>
