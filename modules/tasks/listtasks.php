<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

$perms = &$AppUI->acl();
if (!canView('tasks')) {
	$AppUI->redirect(ACCESS_DENIED);
}
$proj = (int) w2PgetParam($_GET, 'project', 0);
$userFilter = w2PgetParam($_GET, 'userFilter', false);

$tasks = __extract_from_listtasks($userFilter, $AppUI, $proj);

?>

<script language="javascript" type="text/javascript">
function loadTasks() {
	var tasks = new Array();
	var sel = parent.document.forms['form'].new_task;
	while (sel.options.length) {
		sel.options[0] = null;
	}
	sel.options[0] = new Option('[top task]', 0);
	
  <?php
$i = 0;
foreach ($tasks as $task) {
	++$i;
?>
  sel.options[<?php echo $i; ?>] = new Option("<?php echo $task['task_name']; ?>", <?php echo $task['task_id']; ?>);
    <?php
}
?>
}
  
loadTasks();
</script>