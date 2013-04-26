<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$f = (($f) ? $f : '');

$q = new w2p_Database_Query;
$q->addTable('user_delegations','ud');
$q->addTable('projects', 'pr');
$q->addQuery('ud.*, ta.task_end_date, ta.task_name, ta.task_description');
$q->addQuery('c2.contact_display_name AS delegating, c.contact_display_name AS delegated');
$q->addQuery('DATEDIFF(ta.task_end_date, NOW()) as task_due_in');
$q->leftJoin('tasks', 'ta', 'ta.task_id = ud.delegation_task');
$q->addJoin('users', 'u', 'u.user_id = ud.delegated_to_user_id', 'inner');
$q->addJoin('contacts', 'c', 'u.user_contact = c.contact_id', 'inner');
$q->addJoin('users', 'u2', 'u2.user_id = ud.delegating_user_id', 'inner');
$q->addJoin('contacts', 'c2', 'u2.user_contact = c2.contact_id', 'inner');

if ($task_id || $project_id) {
	if ($project_id) {
		$q->addWhere('pr.project_id = ' . (int)$project_id);
	}

	if ($task_id) {
		$q->addWhere('ta.task_id = ' . (int)$task_id);
	}

	$q->addWhere('ta.task_status = 0');
	$q->addWhere('pr.project_id = ta.task_project');
	
	$q->addWhere('project_active = 1');
	$q->addWhere('task_dynamic <> 1');

	$q->addOrder('task_end_date, task_start_date, task_priority');
} else {
	$project = new CProject;
	$allowedProjects = $project->getAllowedSQL($AppUI->user_id,'pr.project_id');

	$task = new CTask;
	$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'ta.task_id');

	if ((int)$f2) {
		$q->addWhere('pr.project_company = "' . (int)$f2 . '"');
	}

	$q->addWhere('ta.task_status = 0');
	$q->addWhere('pr.project_id = ta.task_project');
	
	$q->addWhere('project_active = 1');
	$q->addWhere('task_dynamic <> 1');

	if (count($allowedProjects)) {
		$q->addWhere($allowedProjects);
	}

	if (count($allowedTasks)) {
		$q->addWhere($allowedTasks);
	}

	if ($reject) {
		if ($showIncomplete) {
			$q->addWhere('delegation_rejection_validation_date IS NULL');
		}
	} else {
		if ($showIncomplete) {
			$q->addWhere('(delegation_percent_complete < 100 OR delegation_percent_complete IS NULL)');
		}
	}

	if ($reject) {
		$q->addWhere('delegation_rejection_date IS NOT NULL');
	}
	
	switch ($f) {
		case 'myfinished7days':
			if ($owner == 'mine') {
				$q->addWhere('ud.delegating_user_id = ' . (int)$user_id);
			} else if ($owner == 'other') {
				$q->addWhere('ud.delegated_to_user_id = ' . (int)$user_id);
			}
			$q->addWhere('delegation_percent_complete = 100');
			$fdate = new w2p_Utilities_Date();
			$fdate->addDays(-7);
			$q->addWhere('delegation_end_date >= \'' . $fdate->format(FMT_DATETIME_MYSQL) . '\'');
			break;
		case 'myunfinished':
			if ($owner == 'mine') {
				$q->addWhere('ud.delegating_user_id = ' . (int)$user_id);
			} else if ($owner == 'other') {
				$q->addWhere('ud.delegated_to_user_id = ' . (int)$user_id);
			}
			$q->addWhere('(delegation_percent_complete < 100 OR delegation_end_date = \'\')');
			break;
		case 'taskcreated':
			$q->addWhere('task_creator = ' . (int)$user_id);
			break;
		case 'taskowned':
			$q->addWhere('task_owner = ' . (int)$user_id);
			break;
		default:
			if ($owner == 'mine') {
				$q->addWhere('ud.delegating_user_id = ' . (int)$user_id);
			} else if ($owner == 'other') {
				$q->addWhere('ud.delegated_to_user_id = ' . (int)$user_id);
			}
			break;
	}
	
	if ($search_text != '') {
		$q->addWhere('( task_name LIKE (\'%' . $search_text . '%\') OR task_description LIKE (\'%' . $search_text . '%\') )');
	}

	if ($reject) {
		$q->addOrder('delegation_rejection_date');
	} else {
		$q->addOrder('task_end_date, task_start_date, task_priority');
	}
}
$delegations = $q->loadList();

if ($reject) {
	$ops = array(0=>$AppUI->_('None'), 5=>$AppUI->_('Rejection not accepted'), 6=>$AppUI->_('Rejection accepted'));
} else {
	$ops = array(0=>$AppUI->_('None'), 1=>$AppUI->_('Re-delegate'), 2=>$AppUI->_('Reject'), 3=>$AppUI->_('Mark as done'), 4=>$AppUI->_('Mark as not done'));
}
$cur_op = 0;

?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
$canDelete = canDelete('delegations');
if ($canDelete) {
?>
function delIt(id) {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Delegation', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.delegateFrm.delegation_to_delete.value = id;
		document.delegateFrm.submit();
	}
}
<?php } ?>

function clickedDeleg(id) {
	var div = document.getElementById('delegate_block');
	var boxes = document.getElementsByName('selected_deleg[]');
	for (var i=0, l=boxes.length; i < l; i++) {
		if (boxes[i].checked) {
			div.style.display = '';
			return;
		}
	}
	div.style.display = 'none';
}

function changeOp() {
	var select = document.getElementById('op_to_do');
	var delegate_div = document.getElementById('delegate_div');
	var reject_div = document.getElementById('reject_div');
	var completion_div = document.getElementById('completion_div');
	var validation_div = document.getElementById('validation_div');
	if (select.value == '1') {
		delegate_div.style.display = 'block';
		reject_div.style.display = 'none';
		completion_div.style.display = 'none';
		validation_div.style.display = 'none';
	} else if (select.value == '2') {
		delegate_div.style.display = 'none';
		reject_div.style.display = 'block';
		completion_div.style.display = 'none';
		validation_div.style.display = 'none';
	} else if ((select.value == '3') || (select.value == '4')) {
		delegate_div.style.display = 'none';
		reject_div.style.display = 'none';
		completion_div.style.display = 'block';
		validation_div.style.display = 'none';
	} else if ((select.value == '5') || (select.value == '6')) {
		delegate_div.style.display = 'none';
		reject_div.style.display = 'none';
		completion_div.style.display = 'none';
		validation_div.style.display = 'block';
	} else {
		delegate_div.style.display = 'none';
		reject_div.style.display = 'none';
		completion_div.style.display = 'none';
		validation_div.style.display = 'none';
	}
}

</script>

<form name="delegateFrm" action="?m=delegations" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_bulkops_aed" />
    <input type="hidden" name="datePicker" value="deleg" />
    <input type="hidden" name="delegation_to_delete" value="0" />
    <table class="tbl" cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td colspan="20">
	    <table id="tblDelegAssignTasks" class="tbl list">
		<tr>
		    <?php 
		    foreach ($fieldNames as $index => $name) {
		        echo '<th nowrap="nowrap">' . $AppUI->_($fieldNames[$index]) . '</th>';
		    }
		    ?>
		</tr>

		<?php
		$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
		$htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');
		foreach ($delegations as $deleg) {
		
			// Check for Delegation Access
			$tmpDeleg = new CDelegation();
			$tmpDeleg->load($deleg['delegation_id']);
			$canAccess = $tmpDeleg->canAccess();
			if (!$canAccess) {
				continue;
			}

			// prepare coloured highlight of task time information
			$class = w2pFindTaskComplete($deleg['delegation_start_date'], $deleg['task_end_date'], $deleg['delegation_percent_complete']);
			echo '<tr class="' . $class . '">';
			// loop to process the columns
			foreach ($fieldList as $column) {
				if ($column == '__edit') {
					// edit icon
					echo '<td align="center">';
					if ($tmpDeleg->canEdit()) {
						echo '<a href="?m=delegations&a=addedit&delegation_id=' . $deleg['delegation_id'] . '">' . w2PtoolTip('edit delegation', 'click to edit this delegation') . w2PshowImage('icons/pencil.gif', 12, 12) . w2PendTip() . '</a>' ;
					}
					echo '</td>';
				} else if ($column == 'delegation_percent_complete') {
					echo $htmlHelper->createCell('delegation_percent_complete', $deleg['delegation_percent_complete']);
				} else if ($column == 'delegating_user_id') { 
					if ($deleg['delegated_to_user_id'] != (int)$user_id) {
						echo '<td><a href="?m=admin&amp;a=viewuser&amp;user_id=' . $deleg['delegating_user_id'] . '">' . $deleg['delegating'] . '</a>-><a href="?m=admin&amp;a=viewuser&amp;user_id=' . $deleg['delegated_to_user_id'] . '">' . $deleg['delegated'] . '</a></td>';
					} else {
						echo '<td><a href="?m=admin&amp;a=viewuser&amp;user_id=' . $deleg['delegating_user_id'] . '">' . $deleg['delegating'] . '</a></td>';
					}
				} else if ($column == 'delegated_to_user_id') { 
					if ($deleg['delegating_user_id'] != (int)$user_id) {
						echo '<td><a href="?m=admin&amp;a=viewuser&amp;user_id=' . $deleg['delegating_user_id'] . '">' . $deleg['delegating'] . '</a>-><a href="?m=admin&amp;a=viewuser&amp;user_id=' . $deleg['delegated_to_user_id'] . '">' . $deleg['delegated'] . '</a></td>';
					} else {
						echo '<td><a href="?m=admin&amp;a=viewuser&amp;user_id=' . $deleg['delegated_to_user_id'] . '">' . $deleg['delegated'] . '</a></td>';
					}
				} else if ($column == 'delegation_start_date') { 
					echo $htmlHelper->createCell('delegation_start_date+time', $deleg['delegation_start_date']);
				} else if ($column == 'task_name') { 
					echo '<td style="width: 50%" class="data _name">';
					if ($deleg['task_description']) {
						echo w2PtoolTip('Task Description', $deleg['task_description'], true);
					}
					echo '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $deleg['delegation_task'] . '" >' . $deleg['task_name'] . '</a>';
					if ($deleg['task_description']) {
						$s .= w2PendTip();
					}
					echo '</td>';
				} else if ($column == 'delegation_description') { 
					$full_description = $deleg['delegation_description'];
					$lines = explode("\n", $full_description);
					$chars = 0;
					$parts = array();
					foreach($lines as $line) {
						$chars += strlen($line);
						if ($chars > 300) {
							break;
						}
						$parts[] = $line;
					}
					$part_description = implode("\n", $parts);
					echo '<td width="50%">';
					echo '<div id="short_task_desc_' . $deleg['delegation_id'] . '" style="display: block;">' . nl2br($part_description) . ' <a href="javascript: void(0);" onclick="setLongTaskDescription(' . $deleg['delegation_id'] . ')">' . ($part_description != $full_description ? '(+)' : '') . '</a></div>';
					echo '<div id="long_task_desc_' . $deleg['delegation_id'] . '" style="display: none;">' . nl2br($full_description) . ' <a href="javascript: void(0);" onclick="setShortTaskDescription(' . $deleg['delegation_id'] . ')">' . ($part_description != $full_description ? '(-)' : '') . '</a></div>';
					echo '</td>';
				} else if ($column == 'delegation_name_description') { 
					$full_description = $deleg['delegation_description'];
					$lines = explode("\n", $full_description);
					$chars = 0;
					$parts = array();
					foreach($lines as $line) {
						$chars += strlen($line);
						if ($chars > 300) {
							break;
						}
						$parts[] = $line;
					}
					$part_description = implode("\n", $parts);
					echo '<td width="50%">';
					echo '<a href="./index.php?m=delegations&amp;a=view&amp;delegation_id=' . $deleg['delegation_id'] . '"><strong>' . $deleg['delegation_name'] . '</strong></a><br />';
					echo '<div id="short_task_desc_' . $deleg['delegation_id'] . '" style="display: block;">' . nl2br($part_description) . ' <a href="javascript: void(0);" onclick="setLongTaskDescription(' . $deleg['delegation_id'] . ')">' . ($part_description != $full_description ? '(+)' : '') . '</a></div>';
					echo '<div id="long_task_desc_' . $deleg['delegation_id'] . '" style="display: none;">' . nl2br($full_description) . ' <a href="javascript: void(0);" onclick="setShortTaskDescription(' . $deleg['delegation_id'] . ')">' . ($part_description != $full_description ? '(-)' : '') . '</a></div>';
					echo '</td>';
				} else if ($column == 'task_end_date') { 
					echo $htmlHelper->createCell('task_end_datetime', $deleg['task_end_date']);
				} else if ($column == 'delegation_end_date') { 
					echo $htmlHelper->createCell('task_end_datetime', $deleg['delegation_end_date']);
				} else if ($column == 'delegation_rejection_date') { 
					echo $htmlHelper->createCell('delegation_rejection_date+time', $deleg['delegation_rejection_date']);
				} else if ($column == 'delegation_rejection_reason') { 
					echo $htmlHelper->createCell('delegation_rejection_reason', $deleg['delegation_rejection_reason']);
				} else if ($column == 'delegation_rejection_validation_date') { 
					echo $htmlHelper->createCell('delegation_rejection_validation_date+time', $deleg['delegation_rejection_validation_date']);
				} else if ($column == 'task_selection') {
					echo '<td align="center">' . '<input type="checkbox" name="selected_deleg[]" value="' . $deleg['delegation_id'] . '" onclick="clickedDeleg(' . $deleg['delegation_id'] . ')"/></td>';
				} else if ($column == '__delete') {
					// delete icon
					echo '<td align="center">';
					if ($tmpDeleg->canDelete()) {
						echo '<a href="javascript:delIt(' . $deleg['delegation_id'] . ')">' . w2PtoolTip('delete delegation', 'click to delete this delegation') . w2PshowImage('icons/stock_delete-16.png') . w2PendTip() . '</a>' ;
					}
					echo '</td>';
				}
			}
			echo '</tr>';
		}
		if ($owner == 'other') {
		?>
		<tr id="delegate_block" style="display: none">
		    <td colspan="4">&nbsp;</td>
		    <td colspan="8" align="right">
			<?php echo $AppUI->_('Operation'); ?>:&nbsp;<?php echo arraySelect($ops, 'op_to_do', 'onchange="changeOp()"', $cur_op); ?>
			<div id="delegate_div" style="display: none; padding: 10px;"><hr>
			    <table cellspacing="0" cellpadding="0" border="0">
			    	<tr>
		                    <td align="right" nowrap="nowrap"><div id="do_date_div"><?php echo $AppUI->_('Date'); ?>:&nbsp;</div></td>
		                    <td nowrap="nowrap" width="1%">
		                    	<input type='hidden' id='deleg_do_date' name='deleg_do_date' value='<?php echo $deleg_date ? $deleg_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
		                    	<input type='text' onchange="setDate_new('delegateFrm', 'do_date');" class='text' style='width:120px;' id='do_date' name='do_date' value='<?php echo $deleg_date ? $deleg_date->format($df) : ''; ?>' />
		                    	<a onclick="return showCalendar('do_date', '<?php echo $df ?>', 'delegateFrm', null, true, true)" href="javascript: void(0);">
		                       	    <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		                    	</a>
		                    </td>
		                    <?php
		                    	echo '<td nowrap="nowrap" width="1%">&nbsp;/&nbsp;' . arraySelect($hours, 'do_hour', 'size="1" onchange="setAMPM(this)" class="text"', $deleg_date ? $deleg_date->getHour() : $start) . '</td>';
				    	echo '<td nowrap="nowrap" width="1%">&nbsp;:&nbsp;</td>';
		                    	echo '<td nowrap="nowrap" width="1%">' . arraySelect($minutes, 'do_minute', 'size="1" class="text"', $deleg_date ? $deleg_date->getMinute() : '00') . '</td>';
		                    	if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
		                            echo '<td nowrap="nowrap" width="1%"><input type="text" name="do_hour_ampm" id="do_hour_ampm" value="' . ($deleg_date ? $deleg_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
				    	} else {
					    echo '<td nowrap="nowrap" width="1%">&nbsp;</td>';
		                    	}
		                    ?>
				    <td width="100%"></td>
			    	</tr>
				<tr>
			            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Name'); ?>:&nbsp;</div></td>
			            <td colspan="6"><input type="text" name="deleg_name" class="text" size="60" /></td>
	                	    <td align="right"></td>
			    	</tr>
			    	<tr>
		                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:&nbsp;</div></td>
		                    <td colspan="6"><textarea name="deleg_description" class="textarea" cols="60" rows="5"></textarea></td>
	                            <td align="right"><input type="submit" class="button" value="<?php echo $AppUI->_('Re-delegate tasks'); ?>" /></td>
			    	</tr>
			    	<tr>
		                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegate To'); ?>:&nbsp;</div></td>
		                    <td><?php echo arraySelect($user_list, 'user_id', 'size="1" class="text"', 1, false); ?></td>
				    <td width="100%" colspan="5"></td>
			    	</tr>
			    </table>
		    	</div>
			<div id="reject_div" style="display: none; padding: 10px;"><hr>
			    <table cellspacing="0" cellpadding="0" border="0">
			    	<tr>
		                    <td align="right" nowrap="nowrap"><div id="reject_date_div"><?php echo $AppUI->_('Date'); ?>:&nbsp;</div></td>
		                    <td nowrap="nowrap" width="1%">
		                    	<input type='hidden' id='deleg_reject_date' name='deleg_reject_date' value='<?php echo $deleg_date ? $deleg_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
		                    	<input type='text' onchange="setDate_new('delegateFrm', 'reject_date');" class='text' style='width:120px;' id='reject_date' name='reject_date' value='<?php echo $deleg_date ? $deleg_date->format($df) : ''; ?>' />
		                    	<a onclick="return showCalendar('reject_date', '<?php echo $df ?>', 'delegateFrm', null, true, true)" href="javascript: void(0);">
		                       	    <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		                    	</a>
		                    </td>
		                    <?php
		                    	echo '<td nowrap="nowrap" width="1%">&nbsp;/&nbsp;' . arraySelect($hours, 'reject_hour', 'size="1" onchange="setAMPM(this)" class="text"', $deleg_date ? $deleg_date->getHour() : $start) . '</td>';
				    	echo '<td nowrap="nowrap" width="1%">&nbsp;:&nbsp;</td>';
		                    	echo '<td nowrap="nowrap" width="1%">' . arraySelect($minutes, 'reject_minute', 'size="1" class="text"', $deleg_date ? $deleg_date->getMinute() : '00') . '</td>';
		                    	if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
		                            echo '<td nowrap="nowrap" width="1%"><input type="text" name="reject_hour_ampm" id="reject_hour_ampm" value="' . ($deleg_date ? $deleg_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
				    	} else {
					    echo '<td nowrap="nowrap" width="1%">&nbsp;</td>';
		                    	}
		                    ?>
				    <td width="100%"></td>
			    	</tr>
			    	<tr>
		                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Reason'); ?>:&nbsp;</div></td>
		                    <td colspan="6"><textarea name="reject_reason" class="textarea" cols="60" rows="5"></textarea></td>
	                            <td align="right"><input type="submit" class="button" value="<?php echo $AppUI->_('Reject delegation'); ?>" /></td>
			    	</tr>
			    </table>
		    	</div>
			<div id="completion_div" style="display: none; padding: 10px;"><hr>
			    <table cellspacing="0" cellpadding="0" border="0">
			    	<tr>
		                    <td align="right" nowrap="nowrap"><div id="status_date_div"><?php echo $AppUI->_('Date'); ?>:&nbsp;</div></td>
		                    <td nowrap="nowrap" width="1%">
		                    	<input type='hidden' id='deleg_completion_date' name='deleg_completion_date' value='<?php echo $deleg_date ? $deleg_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
		                    	<input type='text' onchange="setDate_new('delegateFrm', 'completion_date');" class='text' style='width:120px;' id='completion_date' name='completion_date' value='<?php echo $deleg_date ? $deleg_date->format($df) : ''; ?>' />
		                    	<a onclick="return showCalendar('completion_date', '<?php echo $df ?>', 'delegateFrm', null, true, true)" href="javascript: void(0);">
		                       	    <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		                    	</a>
		                    </td>
		                    <?php
		                    	echo '<td nowrap="nowrap" width="1%">&nbsp;/&nbsp;' . arraySelect($hours, 'completion_hour', 'size="1" onchange="setAMPM(this)" class="text"', $deleg_date ? $deleg_date->getHour() : $start) . '</td>';
				    	echo '<td nowrap="nowrap" width="1%">&nbsp;:&nbsp;</td>';
		                    	echo '<td nowrap="nowrap" width="1%">' . arraySelect($minutes, 'completion_minute', 'size="1" class="text"', $deleg_date ? $deleg_date->getMinute() : '00') . '</td>';
		                    	if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
		                            echo '<td nowrap="nowrap" width="1%"><input type="text" name="completion_hour_ampm" id="completion_hour_ampm" value="' . ($deleg_date ? $deleg_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
				    	} else {
					    echo '<td nowrap="nowrap" width="1%">&nbsp;</td>';
		                    	}
		                    ?>
				    <td width="100%" colspan="2"></td>
			    	</tr>
			    	<tr>
		                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Name'); ?>:&nbsp;</div></td>
		                    <td colspan="6"><input type="text" name="completion_tl_name" class="text" size="70" /></td>
				    <td width="100%"></td>
			    	</tr>
			    	<tr>
		                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:&nbsp;</div></td>
		                    <td colspan="6"><textarea name="completion_description" class="textarea" cols="60" rows="5"></textarea></td>
	                            <td align="right"><input type="submit" class="button" value="<?php echo $AppUI->_('Update completion status'); ?>" /></td>
			    	</tr>
			    </table>
		    	</div>
			<div id="validation_div" style="display: none; padding: 10px;"><hr>
			    <table cellspacing="0" cellpadding="0" border="0">
			    	<tr>
		                    <td align="right" nowrap="nowrap"><div id="valid_date_div"><?php echo $AppUI->_('Date'); ?>:&nbsp;</div></td>
		                    <td nowrap="nowrap" width="1%">
		                    	<input type='hidden' id='deleg_validation_date' name='deleg_validation_date' value='<?php echo $deleg_date ? $deleg_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
		                    	<input type='text' onchange="setDate_new('delegateFrm', 'validation_date');" class='text' style='width:120px;' id='validation_date' name='validation_date' value='<?php echo $deleg_date ? $deleg_date->format($df) : ''; ?>' />
		                    	<a onclick="return showCalendar('validation_date', '<?php echo $df ?>', 'delegateFrm', null, true, true)" href="javascript: void(0);">
		                       	    <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		                    	</a>
		                    </td>
		                    <?php
		                    	echo '<td nowrap="nowrap" width="1%">&nbsp;/&nbsp;' . arraySelect($hours, 'validation_hour', 'size="1" onchange="setAMPM(this)" class="text"', $deleg_date ? $deleg_date->getHour() : $start) . '</td>';
				    	echo '<td nowrap="nowrap" width="1%">&nbsp;:&nbsp;</td>';
		                    	echo '<td nowrap="nowrap" width="1%">' . arraySelect($minutes, 'validation_minute', 'size="1" class="text"', $deleg_date ? $deleg_date->getMinute() : '00') . '</td>';
		                    	if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
		                            echo '<td nowrap="nowrap" width="1%"><input type="text" name="validation_hour_ampm" id="completion_hour_ampm" value="' . ($deleg_date ? $deleg_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
				    	} else {
					    echo '<td nowrap="nowrap" width="1%">&nbsp;</td>';
		                    	}
		                    ?>
				    <td width="100%"></td>
	                            <td align="right"><input type="submit" class="button" value="<?php echo $AppUI->_('Validate rejection'); ?>" /></td>
			    	</tr>
			    </table>
		    	</div>
		    </td>	
	    	</tr>
		<?php } ?>
	    </table>
	</td></tr>
    </table>
    <table class="std" cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="tabox" colspan="20">
	    <table width="100%"><tr>
		<td nowrap="nowrap"><?php echo $AppUI->_('Key'); ?>:</td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#ffffff">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Future Delegation'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#e6eedd">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Started and on time'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#ffeebb">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Should have started'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#CC6666">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Overdue'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#aaddaa">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Done'); ?></td>
		<td width="40%">&nbsp;</td>
	    </tr></table>
    	</td></tr>
    </table>
</form>
