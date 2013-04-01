<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $w2Pconfig, $task_parent_options, $loadFromTab;
global $can_edit_time_information, $task;
global $durnTypes, $task_project, $task_id, $tab;

//Time arrays for selects
$start = (int) w2PgetConfig('cal_day_start');
$end = (int) w2PgetConfig('cal_day_end');
$inc = (int) w2PgetConfig('cal_day_increment');
if ($start === null) {
	$start = 8;
}
if ($end === null) {
	$end = 17;
}
if ($inc === null) {
	$inc = 15;
}
$hours = array();
for ($current = $start; $current < $end + 1; $current++) {
	if ($current < 10) {
		$current_key = '0' . $current;
	} else {
		$current_key = $current;
	}

	if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
		//User time format in 12hr
		$hours[$current_key] = ($current > 12 ? $current - 12 : $current);
	} else {
		//User time format in 24hr
		$hours[$current_key] = $current;
	}
}

// Pull tasks dependencies
$deps = false;
if ($deps) {
	$q = new w2p_Database_Query;
	$q->addTable('tasks');
	$q->addQuery('task_id, task_name');
	$q->addWhere('task_id IN (' . $deps . ')');
} else {
	$q = new w2p_Database_Query;
	$q->addTable('tasks', 't');
	$q->addTable('task_dependencies', 'td');
	$q->addQuery('t.task_id, t.task_name');
	$q->addWhere('td.dependencies_task_id = ' . (int)$task_id);
	$q->addWhere('t.task_id = td.dependencies_req_task_id');
}
$taskDep = $q->loadHashList();
$q->clear();

?>

<script language="javascript" type="text/javascript">

function changeCtrls() {
	// Get the Dynamic setting from the fields
	var td_on = document.getElementById('td_on');
	var td_off = document.getElementById('td_off');
	var td_dyn = document.getElementById('task_dynamic');
	var setting = td_dyn.checked ? 1 : (td_on.checked ? 31 : 0);

	// If we're coming out of a dynamic type, make sure the settings are sane
	if (!td_on.checked && !td_off.checked) {
		td_on.checked = true;
		setting = 31;
	}

	// Apply the visibility
	var elm = document.getElementsByName('task_depend_nodyn');
	for (var i=0, i_cmp=elm.length; i < i_cmp; i++) {
		var el = elm[i];
		el.style.display = setting == 1 ? 'none' : 'block';
	}
	elm = document.getElementsByName('task_depend_nodyn_lists');
	for (var i=0, i_cmp=elm.length; i < i_cmp; i++) {
		var el = elm[i];
		el.style.display = setting < 20 ? 'none' : 'block';
	}

	ctl = document.getElementById('set_task_start_date');
	ctl.checked = setting > 20;
}

</script>

<form name="dependFrm" action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" accept-charset="utf-8">
    <input name="dosql" type="hidden" value="do_task_aed" />
    <input name="task_id" type="hidden" value="<?php echo $task_id; ?>" />
    <input type="hidden" name="hdependencies" />

    <table width="100%" border="0" cellpadding="4" cellspacing="0" class="std addedit">
        <?php if ($can_edit_time_information) { ?>
        <tr>
	    <td width="50%">&nbsp;</td>
	    <td nowrap>
		<div style="display: <?php echo $task->task_dynamic == '1' ? 'none' : 'block'; ?>" name="task_depend_nodyn">
		<table><tr><td rowspan="2">
		<?php echo $AppUI->_('Dependency Tracking'); ?>:
		</td><td>
		<input type="radio" name="task_dynamic" value="31" id="td_on" <?php if ($task_id == 0 || $task->task_dynamic > '20') { echo "checked"; } ?> onclick="changeCtrls();" />
		<label for="td_on"><?php echo $AppUI->_('On'); ?></label>
		</td></tr><tr><td>
		<input type="radio" name="task_dynamic" value="0" id="td_off" <?php if ($task_id && ($task->task_dynamic == '0' || $task->task_dynamic == '11')) { echo "checked"; } ?> onclick="changeCtrls();" />
		<label for="td_off"><?php echo $AppUI->_('Off'); ?></label>
		</td></tr></table>
		</div>
	    </td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    </td><td nowrap>
		<input type="checkbox" name="task_dynamic" id="task_dynamic" value="1" <?php if ($task->task_dynamic == "1") { echo 'checked="checked"'; } ?> onclick="changeCtrls();" />
		<label for="task_dynamic"><?php echo $AppUI->_('Dynamic task'); ?></label><br>
		<div style="display: <?php echo $task->task_dynamic == '1' ? 'none' : 'block'; ?>" name="task_depend_nodyn">
		<input type="checkbox" name="task_dynamic_nodelay" id="task_dynamic_nodelay" value="1" <?php if (($task->task_dynamic > '10') && ($task->task_dynamic < 30)) { echo 'checked="checked"'; } ?> />
		<label for="task_dynamic_nodelay"><?php echo $AppUI->_('Do not track this task'); ?></label><br>
		<input type="checkbox" name="set_task_start_date" id="set_task_start_date" <?php if ($task_id == 0 || $task->task_dynamic > '20') { echo "checked"; } ?> disabled="disabled" />
		<label for="set_task_start_date"><?php echo $AppUI->_('Set task start date based on dependency'); ?></label>
		</div>
	    </td>
	    <td width="50%">&nbsp;</td>
	</tr>
        <?php } else { ?>
	<tr>
	    <td colspan="5" align="center"><?php echo $AppUI->_('Only the task owner, project owner, or system administrator is able to edit time related information.'); ?></td>
	</tr>
        <?php } // end of can_edit_time_information ?>

	<tr>
	    <td width="50%">&nbsp;</td>
	    <td><div style="display: <?php echo (int)$task->task_dynamic < 20 ? 'none' : 'block'; ?>" name="task_depend_nodyn_lists"><?php echo $AppUI->_('All Tasks'); ?>:</div></td>
	    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	    <td><div style="display: <?php echo (int)$task->task_dynamic < 20 ? 'none' : 'block'; ?>" name="task_depend_nodyn_lists"><?php echo $AppUI->_('Task Dependencies'); ?>:</div></td>
	    <td width="50%">&nbsp;</td>
	</tr><tr>
	    <td width="50%">&nbsp;</td>
	    <td>
		<div style="display: <?php echo (int)$task->task_dynamic < 20 ? 'none' : 'block'; ?>" name="task_depend_nodyn_lists">
		<select name="all_tasks" class="text" style="width:220px" size="10" class="text" multiple="multiple">
		<?php echo str_replace('selected', '', $task_parent_options); // we need to remove selected added from task_parent options ?>
		</select>
		</div>
	    </td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    </td><td>
		<div style="display: <?php echo (int)$task->task_dynamic < 20 ? 'none' : 'block'; ?>" name="task_depend_nodyn_lists">
		<?php echo arraySelect($taskDep, 'task_dependencies', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
		</div>
	   </td>
	    <td width="50%">&nbsp;</td>
	</tr><tr>
	    <td width="50%">&nbsp;</td>
	    <td align="right"><div style="display: <?php echo (int)$task->task_dynamic < 20 ? 'none' : 'block'; ?>" name="task_depend_nodyn_lists"><input type="button" class="button" value="&gt;" onclick="addTaskDependency(document.dependFrm, document.datesFrm)" /></div></td>
	    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	    <td align="left"><div style="display: <?php echo (int)$task->task_dynamic < 20 ? 'none' : 'block'; ?>" name="task_depend_nodyn_lists"><input type="button" class="button" value="&lt;" onclick="removeTaskDependency(document.dependFrm, document.datesFrm)" /></div></td>
	    <td width="50%">&nbsp;</td>
	</tr>
    </table>
</form>
<script language="javascript" type="text/javascript">
	subForm.push( new FormDefinition(<?php echo $tab; ?>, document.dependFrm, checkDepend, saveDepend));
</script>
