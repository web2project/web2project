<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();

// check permissions
if (!$perms->checkModule('delegations', 'add')) {
    $AppUI->redirect(ACCESS_DENIED);
}

// get the delegation fields
$deleg_do_date = $_POST['deleg_do_date'] . $_POST['do_hour'] . $_POST['do_minute'];
$name = w2PgetParam($_POST, 'deleg_name', '');
$description = w2PgetParam($_POST, 'deleg_description', '');
$user_id = (int)w2PgetParam($_POST, 'user_id', 0);
	
// get the task list
$tasks_to_delegate = w2PgetParam($_POST, 'selected_task', array());
	
// delegate the indicated tasks
$result = true;
$errors = array();
foreach ($tasks_to_delegate as $ttd) {
	$deleg = new CDelegation();
	$deleg->delegating_user_id = $AppUI->user_id;
	$deleg->delegated_to_user_id = $user_id;
	$deleg->delegation_task = $ttd;
	$deleg->delegation_start_date = $deleg_do_date;
	$deleg->delegation_name = $name;
	$deleg->delegation_description = $description;
	$deleg->delegation_creator = $AppUI->user_id;

	$task = new CTask;
	$task->load($ttd);
	$deleg->delegation_project = $task->task_project;

	$result = $deleg->store() && $result;

	if (count($deleg->getError())) {
		$errors[] = $deleg->getError();
	}
}

if (!$result) {
	$AppUI->setMsg($errors, UI_MSG_ERROR, true);
} else {
	$AppUI->setMsg('Tasks delegated', UI_MSG_OK, true);
}
$AppUI->redirect('m=delegations');
