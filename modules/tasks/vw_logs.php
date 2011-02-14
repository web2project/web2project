<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $task_id, $sf, $df, $canEdit, $m;

$perms = &$AppUI->acl();
if (!canView('task_log')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$problem = intval(w2PgetParam($_GET, 'problem', null));
// get sysvals
$taskLogReference = w2PgetSysVal('TaskLogReference');
$taskLogReferenceImage = w2PgetSysVal('TaskLogReferenceImage');
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

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<form name="frmDelete2" action="./index.php?m=tasks" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>

<tr>
	<th></th>
	<th><?php echo $AppUI->_('Date'); ?></th>
        <th title="<?php echo $AppUI->_('Reference'); ?>"><?php echo $AppUI->_('Ref'); ?></th>
	<th width="100"><?php echo $AppUI->_('Summary'); ?></th>
    <th><?php echo $AppUI->_('URL'); ?></th>
	<th width="100"><?php echo $AppUI->_('User'); ?></th>
	<th width="100"><?php echo $AppUI->_('Hours'); ?></th>
	<th width="100" nowrap="nowrap"><?php echo $AppUI->_('Cost Code'); ?></th>
	<th width="100%"><?php echo $AppUI->_('Comments'); ?></th>
	<th></th>
</tr>
<?php
// Pull the task comments
 $task= new CTask();
$logs = $task->getTaskLogs($task_id, $problem);

$s = '';
$hrs = 0;
$canEdit = canEdit('task_log');
foreach ($logs as $row) {
	$task_log_date = intval($row['task_log_date']) ? new w2p_Utilities_Date($row['task_log_date']) : null;
	$style = $row['task_log_problem'] ? 'background-color:#cc6666;color:#ffffff' : '';

	$s .= '<tr bgcolor="white" valign="top"><td>';
	if ($canEdit) {
		if ($tab == -1) {
			$s .= '<a href="?m=tasks&a=view&task_id=' . $task_id . '&tab=' . $AppUI->getState('TaskLogVwTab');
		} else {
			$s .= '<a href="?m=tasks&a=view&task_id=' . $task_id . '&tab=1';

		}
		$s .= '&task_log_id=' . $row['task_log_id'] . '#log">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '</a>';
	}
	$s .= '</td>';
	$s .= '<td nowrap="nowrap">' . ($task_log_date ? $task_log_date->format($sf) : '-') . '<br /><br />';
    $task_log_updated = intval($row['task_log_updated']) ? $row['task_log_updated'] : null;
    $s .= '(' . $AppUI->_('Logged').': ' . ($task_log_updated ? $AppUI->formatTZAwareTime($task_log_updated, $df) : '-') . ')';
    $s .= '</td>';
	$reference_image = '-';
	if ($row['task_log_reference'] > 0) {
		if (isset($taskLogReferenceImage[$row['task_log_reference']])) {
			$reference_image = w2PshowImage($taskLogReferenceImage[$row['task_log_reference']], 16, 16, $taskLogReference[$row['task_log_reference']], $taskLogReference[$row['task_log_reference']]);
		} elseif (isset($taskLogReference[$row['task_log_reference']])) {
			$reference_image = $taskLogReference[$row['task_log_reference']];
		}
	}
	$s .= '<td align="center" valign="middle">' . $reference_image . '</td>';
	$s .= '<td width="30%" style="' . $style . '">' . $row['task_log_name'] . '</td>';
	$s .= !empty($row['task_log_related_url']) ? '<td><a href="' . $row['task_log_related_url'] . '" title="' . $row['task_log_related_url'] . '">' . $AppUI->_('URL') . '</a></td>' : '<td></td>';
	$s .= '<td width="100">' . $row['real_name'] . '</td>';
	$s .= '<td width="100" align="right">' . sprintf('%.2f', $row['task_log_hours']) . '<br />(';

	$minutes = ($row['task_log_hours'] - floor($row['task_log_hours'])) * 60;
	$minutes = round($minutes, 0, PHP_ROUND_HALF_UP);
	$minutes = (($minutes < 10) ? ('0' . $minutes) : $minutes);
	$s .= (int)$row['task_log_hours'] . ':' . $minutes . ')</td>';
	$s .= '<td width="100">' . $row['task_log_costcode'] . '</td><td>' . '<a name="tasklog' . $row['task_log_id'] . '"></a>';

	// dylan_cuthbert: auto-transation system in-progress, leave these lines
	$transbrk = "\n[translation]\n";
  $descrip = w2p_textarea($row['task_log_description']);
	$tranpos = mb_strpos($descrip, mb_str_replace("\n", '<br />', $transbrk));
	if ($tranpos === false) {
		$s .= $descrip;
	} else {
		$descrip = mb_substr($descrip, 0, $tranpos);
		$tranpos = mb_strpos($row['task_log_description'], $transbrk);
		$transla = mb_substr($row['task_log_description'], $tranpos + mb_strlen($transbrk));
		$transla = mb_trim(mb_str_replace("'", '"', $transla));
		$s .= $descrip.'gsd' . '<div style="font-weight: bold; text-align: right"><a title="' . $transla . '" class="hilite">[' . $AppUI->_('translation') . ']</a></div>';
	}
	// end auto-translation code

	$s .= '</td><td>';
	if ($canDelete) {
		$s .= '<a href="javascript:delIt2(' . $row['task_log_id'] . ');" title="' . $AppUI->_('delete log') . '">' . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
	}
	$s .= '</td></tr>';
	$hrs += (float)$row['task_log_hours'];
}
$s .= '<tr bgcolor="white" valign="top">';
$s .= '<td colspan="6" align="right">' . $AppUI->_('Total Hours') . ' =</td>';
$s .= '<td align="right">' . sprintf('%.2f', $hrs) . '</td>';
$s .= '<td align="right" colspan="3"><form action="?m=tasks&a=view&tab=1&task_id=' . $task_id . '" method="post" accept-charset="utf-8">';
if ($perms->checkModuleItem('tasks', 'edit', $task_id)) {
	$s .= '<input type="submit" class="button" value="' . $AppUI->_('new log') . '"></form></td>';
}
$s .= '</tr>';
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