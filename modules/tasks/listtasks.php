<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();
if (!$perms->checkModule('tasks', 'view')) {
	$AppUI->redirect('m=public&a=access_denied');
}
$proj = w2PgetParam($_GET, 'project', 0);
$userFilter = w2PgetParam($_GET, 'userFilter', false);

$q = new DBQuery();
$q->addQuery('t.task_id, t.task_name');
$q->addTable('tasks', 't');

if ($userFilter) {
	$q->addJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
	$q->addWhere('ut.user_id = ' . $AppUI->user_id);
}
if ($proj != 0) {
	$q->addWhere('task_project = ' . $proj);
}
$tasks = $q->loadList();
$q->clear();
?>

<script language="JavaScript">
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