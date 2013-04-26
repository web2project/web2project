<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check for existence of a 'delete' delegation ID 
$to_delete = (int)w2PgetParam($_POST, 'delegation_to_delete', 0);

// get the operation to execute
$op = (int)w2PgetParam($_POST, 'op_to_do', 0);

if ($to_delete) {
	$deleg = new CDelegation();
	$deleg->load($to_delete);

	// check permissions
	if (!$deleg->canDelete()) {
	    $AppUI->redirect(ACCESS_DENIED);
	}

	if ($deleg->delete()) {
		$AppUI->setMsg('Delegation deleted', UI_MSG_OK, true);
	} else {
		$AppUI->setMsg($deleg->getError(), UI_MSG_ERROR, true);
	}
} else if ($op == 1) {
	// Do a re-delegate

	// check permissions
	$deleg = new CDelegation();
	if (!$deleg->canCreate()) {
	    $AppUI->redirect(ACCESS_DENIED);
	}

	// get the delegation fields
	$deleg_do_date = $_POST['deleg_do_date'] . $_POST['do_hour'] . $_POST['do_minute'];
	$name = w2PgetParam($_POST, 'deleg_name', '');
	$description = w2PgetParam($_POST, 'deleg_description', '');
	$user_id = (int)w2PgetParam($_POST, 'user_id', 0);

	// get the delegation list
	$delegs_to_delegate = w2PgetParam($_POST, 'selected_deleg', array());

	// delegate the indicated tasks
	$result = true;
	$errors = array();
	foreach ($delegs_to_delegate as $ttd) {
		// get the original delegation so its data can be used
		$old = new CDelegation();
		$old->load($ttd);

		// setup the new delegation
		$deleg = new CDelegation();
		$deleg->delegating_user_id = $old->delegated_to_user_id;
		$deleg->delegated_to_user_id = $user_id;
		$deleg->delegation_task = $old->delegation_task;
		$deleg->delegation_start_date = $deleg_do_date;
		$deleg->delegation_name = $name;
		$deleg->delegation_description = $description;
		$deleg->delegation_creator = $AppUI->user_id;
	
		$deleg->delegation_project = $old->delegation_project;

		// create a task log for this
		$tlog = new CTask_Log();
		$tlog->task_log_task = $deleg->delegation_task;
		$tlog->task_log_name = $AppUI->_('Delegation');
		$tlog->task_log_description = $AppUI->_('Delegation from user') . ' \'' . CContact::getContactByUserid($old->delegated_to_user_id) . '\' ' . $AppUI->_('to user') . ' \'' . CContact::getContactByUserid($user_id) . '\'';
		$tlog->task_log_creator = $AppUI->user_id;
		$tlog->task_log_date = $deleg_do_date;
		$tlog->task_log_record_creator = $AppUI->user_id;
		$tlog->task_log_related_to_delegation_op = $op;

		// get the task object for the end date
		$task = new CTask();
		$task->load($deleg->delegation_task);
 		$tlog->task_log_task_end_date = $task->task_end_date;		

		$parres = $deleg->store();
		$result = $parres && $result;
	
		if (count($deleg->getError())) {
			$errors[] = $deleg->getError();
		}

		if ($parres) {
			// delete any other related task log, pertaining to a delegation
			$old->deleteRelatedTaskLogs($op);

			// Only here is the new delegation_id available
			$tlog->task_log_related_to_delegation_id = $deleg->delegation_id;

			// try to store the task log
			$result = $tlog->store() && $result;
	
			if (count($tlog->getError())) {
				$errors[] = $tlog->getError();
			}
		}
	}

	if (!$result) {
		$AppUI->setMsg($errors, UI_MSG_ERROR, true);
	} else {
		$AppUI->setMsg('Delegations re-delegated', UI_MSG_OK, true);
	}
} else if ($op == 2) {
	// do a rejection

	// check permissions
	$deleg = new CDelegation();
	if (!$deleg->canEdit()) {
	    $AppUI->redirect(ACCESS_DENIED);
	}

	// get the delegation fields
	$reject_date = $_POST['deleg_reject_date'] . $_POST['reject_hour'] . $_POST['reject_minute'];
	$reason = w2PgetParam($_POST, 'reject_reason', '');

	// get the delegation list
	$delegs_to_reject = w2PgetParam($_POST, 'selected_deleg', array());

	// reject the indicated tasks
	$result = true;
	$errors = array();
	foreach ($delegs_to_reject as $ttd) {
		// get the original delegation so it can be changed
		$deleg = new CDelegation();
		$deleg->load($ttd);

		// fill in the rejection data
		$deleg->delegation_rejection_date = $reject_date;
		$deleg->delegation_rejection_reason = $reason;
		$deleg->delegation_rejection_updator = $AppUI->user_id;
		$deleg->delegation_percent_complete = 0;
		$deleg->delegation_end_date = null;

		// create a task log for this
		$tlog = new CTask_Log();
		$tlog->task_log_task = $deleg->delegation_task;
		$tlog->task_log_name = $AppUI->_('Delegation rejected');
		$tlog->task_log_description = $AppUI->_('Delegation was rejected by user') . ' \'' . CContact::getContactByUserid($deleg->delegated_to_user_id) . '\'';
		$tlog->task_log_creator = $AppUI->user_id;
		$tlog->task_log_date = $reject_date;
		$tlog->task_log_related_to_delegation_id = $ttd;
		$tlog->task_log_record_creator = $AppUI->user_id;
		$tlog->task_log_related_to_delegation_op = $op;
		$tlog->task_log_problem = true;

		// get the task object for the end date
		$task = new CTask();
		$task->load($deleg->delegation_task);
 		$tlog->task_log_task_end_date = $task->task_end_date;		

		// Since the end_date can be null, call store() with updateNulls
		$parres = $deleg->store(true);
		$result = $parres && $result;
	
		if (count($deleg->getError())) {
			$errors[] = $deleg->getError();
		}

		if ($parres) {
			// delete any other related task log, pertaining to a rejection
			$deleg->deleteRelatedTaskLogs($op);

			// try to store the task log
			$result = $tlog->store() && $result;
	
			if (count($tlog->getError())) {
				$errors[] = $tlog->getError();
			}
		}
	}

	if (!$result) {
		$AppUI->setMsg($errors, UI_MSG_ERROR, true);
	} else {
		$AppUI->setMsg('Delegations rejected', UI_MSG_OK, true);
	}
} else if (($op == 3) || ($op == 4)) {
	// do a completion status change. Only one per delegation is allowed

	// check permissions
	$deleg = new CDelegation();
	if (!$deleg->canEdit()) {
	    $AppUI->redirect(ACCESS_DENIED);
	}
	$tlog = new CTask_Log();
	if (!$tlog->canCreate()) {
	    $AppUI->redirect(ACCESS_DENIED);
	}

	// get the delegation fields
	$completion_date = $_POST['deleg_completion_date'] . $_POST['completion_hour'] . $_POST['completion_minute'];
	$name = w2PgetParam($_POST, 'completion_tl_name', '');
	$description = w2PgetParam($_POST, 'completion_description', '');

	// get the delegation list
	$delegs_to_complete = w2PgetParam($_POST, 'selected_deleg', array());

	// change the completion status of the delegations
	$result = true;
	$errors = array();
	foreach ($delegs_to_complete as $ttd) {
		// get the original delegation so it can be changed
		$deleg = new CDelegation();
		$deleg->load($ttd);

		// create a task log for this
		$tlog = new CTask_Log();
		$tlog->task_log_task = $deleg->delegation_task;
		$tlog->task_log_name = $name;
		$tlog->task_log_description = $description;
		$tlog->task_log_creator = $AppUI->user_id;
		$tlog->task_log_date = $completion_date;
		$tlog->task_log_related_to_delegation_id = $ttd;
		$tlog->task_log_record_creator = $AppUI->user_id;
		$tlog->task_log_related_to_delegation_op = $op;

		// get the task object for the end date
		$task = new CTask();
		$task->load($deleg->delegation_task);
 		$tlog->task_log_task_end_date = $task->task_end_date;		

		// calculate the worked time
		$start = new w2p_Utilities_Date($deleg->delegation_start_date);
		$tlog->task_log_hours = $start->calcDuration(new w2p_Utilities_Date($completion_date));

		// this code only handles 100% done or 0% done. If a 0% done record is stored a problem is assumed
		if ($op == 3) {
			$tlog->task_log_percent_complete = 100;
			$deleg->delegation_percent_complete = 100;
			$deleg->delegation_end_date = $completion_date;
		} else {
			$tlog->task_log_percent_complete = 0;
			$tlog->task_log_problem = true;
			$deleg->delegation_percent_complete = 0;
			$deleg->delegation_end_date = null;
		}
		$deleg->delegation_completion_updator = $AppUI->user_id;

		// try to store the delegation info
		// use the 'updateNulls param to make sure the end_date is cleared on not done
		$parres = $deleg->store(true);
		$result = $parres && $result;
	
		if (count($deleg->getError())) {
			$errors[] = $deleg->getError();
		}

		if ($parres) {
			// delete any other related task log, pertaining to a completion change
			$deleg->deleteRelatedTaskLogs(3);
			$deleg->deleteRelatedTaskLogs(4);

			// try to store the task log
			$result = $tlog->store() && $result;
	
			if (count($tlog->getError())) {
				$errors[] = $tlog->getError();
			}
		}
	}

	if (!$result) {
		$AppUI->setMsg($errors, UI_MSG_ERROR, true);
	} else {
		$AppUI->setMsg('Delegations completion status changed', UI_MSG_OK, true);
	}
} else if (($op == 5) || ($op == 6)) {
	// do a rejection validation. Once the rejection is validated (accepted or not) it's task log is deleted.

	// check permissions
	$deleg = new CDelegation();
	if (!$deleg->canEdit()) {
	    $AppUI->redirect(ACCESS_DENIED);
	}
	$tlog = new CTask_Log();
	if (!$tlog->canCreate()) {
	    $AppUI->redirect(ACCESS_DENIED);
	}

	// get the delegation fields
	$validation_date = $_POST['deleg_validation_date'] . $_POST['validation_hour'] . $_POST['validation_minute'];

	// get the delegation list
	$delegs_to_complete = w2PgetParam($_POST, 'selected_deleg', array());

	// change the completion status of the delegations
	$result = true;
	$errors = array();
	foreach ($delegs_to_complete as $ttd) {
		// get the original delegation so it can be changed
		$deleg = new CDelegation();
		$deleg->load($ttd);

		if ($op == 5) {
			$deleg->delegation_rejection_date = null;
			$deleg->delegation_rejection_reason = null;
		} else {
			$deleg->delegation_rejection_validation_date = $validation_date;
		}
		$deleg->delegation_rejection_updator = $AppUI->user_id;

		// try to store the delegation info
		// use the 'updateNulls param to make sure the rejection data is cleared on not accepted
		$parres = $deleg->store(true);
		$result = $parres && $result;
	
		if (count($deleg->getError())) {
			$errors[] = $deleg->getError();
		}

		if ($parres) {
			// delete any other related task log, pertaining to rejection
			$deleg->deleteRelatedTaskLogs(2);
		}
	}

	if (!$result) {
		$AppUI->setMsg($errors, UI_MSG_ERROR, true);
	} else {
		$AppUI->setMsg('Delegations completion status changed', UI_MSG_OK, true);
	}
}
$AppUI->redirect('m=delegations');
