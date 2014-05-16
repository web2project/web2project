<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $w2Pconfig, $task_parent_options, $loadFromTab;
global $can_edit_time_information, $object;
global $durnTypes, $task_project, $object_id, $tab;
global $form;

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
$taskDep = __extract_from_ae_depend2($object_id);

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
<?php
include $AppUI->getTheme()->resolveTemplate('tasks/addedit_depend');
?>
<script language="javascript" type="text/javascript">
	subForm.push( new FormDefinition(<?php echo $tab; ?>, document.dependFrm, checkDepend, saveDepend));
</script>
