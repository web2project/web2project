<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

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
$taskDep = __extract_from_ae_depend2($task_id);

?>
<script>
    function toggleDependencies()
    {
        if(document.getElementById('task_dynamic').checked) {
            document.getElementById('dep-row-1').style.display = "none";
            document.getElementById('dep-row-2').style.display = "none";
            document.getElementById('dep-row-3').style.display = "none";
            //TODO: clear dependencies
        } else {
            document.getElementById('dep-row-1').style.display = "";
            document.getElementById('dep-row-2').style.display = "";
            document.getElementById('dep-row-3').style.display = "";
            //TODO: reset dependencies?
        }
    }
</script>
<form name="dependFrm" action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" accept-charset="utf-8">
    <input name="dosql" type="hidden" value="do_task_aed" />
    <input name="task_id" type="hidden" value="<?php echo $task_id; ?>" />
    <input type="hidden" name="hdependencies" />

    <div class="std addedit tasks-depends">
        <div class="column left">
            <p>
                <label><?php echo $AppUI->_('Dependency Tracking'); ?></label>
                <?php echo $AppUI->_('On'); ?><input type="radio" name="task_dynamic" value="31" <?php if ($task_id == 0 || $task->task_dynamic > '20') { echo "checked"; } ?> />
                <?php echo $AppUI->_('Off'); ?><input type="radio" name="task_dynamic" value="0" <?php if ($task_id && ($task->task_dynamic == '0' || $task->task_dynamic == '11')) { echo "checked"; } ?> />
            </p>
            <p>
                <label><?php echo $AppUI->_('Set task start date based on dependency'); ?></label>
                <input type="checkbox" name="set_task_start_date" id="set_task_start_date" <?php if ($task_id == 0 || $task->task_dynamic > '20') { echo "checked"; } ?>  />
            </p>
            <p>
                <label><?php echo $AppUI->_('All Tasks'); ?>:</label>
                <select name="all_tasks" class="text" style="width:220px" size="10" class="text" multiple="multiple">
                    <?php echo str_replace('selected', '', $task_parent_options); // we need to remove selected added from task_parent options ?>
                </select>
            </p>
            <p><input type="button" class="button btn btn-primary btn-mini" value="&gt;" onclick="addTaskDependency(document.dependFrm, document.datesFrm)" /></p>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Dynamic Task'); ?></label>
                <input type="checkbox" name="task_dynamic" id="task_dynamic" value="1" <?php if ($task->task_dynamic == "1") { echo 'checked="checked"'; } ?> />
            </p>
            <p>
                <label><?php echo $AppUI->_('Do not track this task'); ?></label>
                <input type="checkbox" name="task_dynamic_nodelay" id="task_dynamic_nodelay" value="1" <?php if (($task->task_dynamic > '10') && ($task->task_dynamic < 30)) { echo 'checked="checked"'; } ?> />
            </p>
            <p>
                <label><?php echo $AppUI->_('Task Dependencies'); ?>:</label>
                <?php echo arraySelect($taskDep, 'task_dependencies', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
            </p>
            <p><input type="button" class="button btn btn-primary btn-mini" value="&lt;" onclick="removeTaskDependency(document.dependFrm, document.datesFrm)" /></p>
        </div>
    </div>
</form>
<script language="javascript" type="text/javascript">
	subForm.push( new FormDefinition(<?php echo $tab; ?>, document.dependFrm, checkDepend, saveDepend));
</script>
