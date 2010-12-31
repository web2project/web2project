<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $showEditCheckbox, $tasks, $priorities;
global $m, $a, $date, $other_users, $showPinned, $showArcProjs, $showHoldProjs, $showDynTasks, $showLowTasks, $showEmptyDate, $user_id, $task_type;
global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;
$perms = &$AppUI->acl();
$canDelete = $perms->checkModuleItem($m, 'delete');
?>
<table width="100%" border="0" cellpadding="1" cellspacing="0">
<form name="form_buttons" method="post" action="index.php?<?php echo 'm=' . $m . '&amp;a=' . $a . '&amp;date=' . $date; ?>" accept-charset="utf-8">
<input type="hidden" name="show_form" value="1" />

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
</tr>
<tr>
	<td colspan = "12" align="right">
<?php
	$types = array('' => '(Task Type Filter)') + w2PgetSysVal('TaskType');
	echo arraySelect($types, 'task_type', 'class="text" onchange="document.form_buttons.submit()"', $task_type, true);
?>
	</td>
</tr>
</form>
</table>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<form name="form" method="post" action="index.php?<?php echo "m=$m&amp;a=$a&amp;date=$date"; ?>" accept-charset="utf-8">
<tr>
	<th width="10">&nbsp;</th>
	<th width="10"><?php echo $AppUI->_('Pin'); ?></th>
	<th width="20" colspan="2"><?php echo $AppUI->_('Progress'); ?></th>
	<th width="15" align="center"><?php sort_by_item_title('P', 'task_priority', SORT_NUMERIC, '&amp;a=todo'); ?></th>
	<th colspan="2"><?php sort_by_item_title('Task / Project', 'task_name', SORT_STRING, '&amp;a=todo'); ?></th>
	<th nowrap="nowrap"><?php sort_by_item_title('Start Date', 'task_start_date', SORT_NUMERIC, '&amp;a=todo'); ?></th>
	<th nowrap="nowrap"><?php sort_by_item_title('Duration', 'task_duration', SORT_NUMERIC, '&amp;a=todo'); ?></th>
	<th nowrap="nowrap"><?php sort_by_item_title('Finish Date', 'task_end_date', SORT_NUMERIC, '&amp;a=todo'); ?></th>
	<th nowrap="nowrap"><?php sort_by_item_title('Due In', 'task_due_in', SORT_NUMERIC, '&amp;a=todo'); ?></th>
	<?php if (w2PgetConfig('direct_edit_assignment')) { ?><th width="0">&nbsp;</th><?php } ?>
</tr>
<?php

// sorting tasks
if ($task_sort_item1 != '') {
	if ($task_sort_item2 != '' && $task_sort_item1 != $task_sort_item2) {
		$tasks = array_csort($tasks, $task_sort_item1, $task_sort_order1, $task_sort_type1, $task_sort_item2, $task_sort_order2, $task_sort_type2);
	} else {
		$tasks = array_csort($tasks, $task_sort_item1, $task_sort_order1, $task_sort_type1);
	}
} else { // All this appears to already be handled in todo.php ... should consider deleting this else block
	/* we have to calculate the end_date via start_date+duration for
	** end='0000-00-00 00:00:00' if array_csort function is not used
	** as it is normally done in array_csort function in order to economise
	** cpu time as we have to go through the array there anyway
	*/
	for ($j = 0, $j_cmp = count($tasks); $j < $j_cmp; $j++) {
		if ($tasks[$j]['task_end_date'] == '0000-00-00 00:00:00' || $tasks[$j]['task_end_date'] == '') {
			if ($tasks[$j]['task_start_date'] == '0000-00-00 00:00:00' || $tasks[$j]['task_start_date'] == '') {
				$tasks[$j]['task_start_date'] = '0000-00-00 00:00:00'; //just to be sure start date is "zeroed"
				$tasks[$j]['task_end_date'] = '0000-00-00 00:00:00';
			} else {
				$tasks[$j]['task_end_date'] = calcEndByStartAndDuration($tasks[$j]);
			}
		}
	}
}

$history_active = false;
// showing tasks
foreach ($tasks as $task) {
	showtask($task, 0, false, true);
}
if (w2PgetConfig('direct_edit_assignment')) {
?>
<tr>
	<td colspan="9" align="right" height="30">
		<input type="submit" class="button" value="<?php echo $AppUI->_('update task'); ?>" />
	</td>
	<td colspan="3" align="center">
<?php
	foreach ($priorities as $k => $v) {
		$options[$k] = $AppUI->_('set priority to ' . $v, UI_OUTPUT_RAW);
	}
	$options['c'] = $AppUI->_('mark as finished', UI_OUTPUT_RAW);
	if ($canDelete) {
		$options['d'] = $AppUI->_('delete', UI_OUTPUT_RAW);
	}
	echo arraySelect($options, 'task_priority', 'size="1" class="text"', '0');
}
?>
	</td>
</form>
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