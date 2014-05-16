<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $users, $object_id, $task_project, $object, $projTasksWithEndDates, $tab, $loadFromTab;
global $form;

// Make sure that we can see users that are allocated to the task.

if ($object_id == 0) {
	// Add task creator to assigned users by default
	$assignedUsers = array($AppUI->user_id => array('contact_name' => $users[$AppUI->user_id], 'perc_assignment' => '100'));
} else {
	// Pull users on this task
	$assignedUsers = $object->assignees($object_id);
}

$initPercAsignment = '';
$assigned = array();
foreach ($assignedUsers as $user_id => $data) {
        $displayName = $data['contact_name'];
        if (isset($data['contact_display_name'])) {
            $displayName = $data['contact_display_name'];
        }
	$assigned[$user_id] = $displayName . ' [' . $data['perc_assignment'] . '%]';
	$initPercAsignment .= "$user_id={$data['perc_assignment']};";
}

?>
<script language="javascript" type="text/javascript">
<?php
echo "var projTasksWithEndDates=new Array();\n";
$keys = array_keys($projTasksWithEndDates);
for ($i = 1, $i_cmp = sizeof($keys); $i < $i_cmp; $i++) {
	//array[task_is] = end_date, end_hour, end_minutes
	echo 'projTasksWithEndDates[' . $keys[$i] . "]=new Array(\"" . $projTasksWithEndDates[$keys[$i]][1] . "\", \"" . $projTasksWithEndDates[$keys[$i]][2] . "\", \"" . $projTasksWithEndDates[$keys[$i]][3] . "\");\n";
}
?>
</script>
<?php
include $AppUI->getTheme()->resolveTemplate('tasks/addedit_resource');
?>
<script language="javascript" type="text/javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.resourceFrm, checkResource, saveResource));
</script>
