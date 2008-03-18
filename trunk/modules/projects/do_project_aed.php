<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$obj = new CProject();
$msg = '';

$notify_owner = ($_POST['email_project_owner_box']) ? 1 : 0;
$notify_contacts = ($_POST['email_project_contacts_box']) ? 1 : 0;

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

require_once ($AppUI->getSystemClass('CustomFields'));
// convert dates to SQL format first
if ($obj->project_start_date) {
	$date = new CDate($obj->project_start_date);
	$obj->project_start_date = $date->format(FMT_DATETIME_MYSQL);
}
if ($obj->project_end_date) {
	$date = new CDate($obj->project_end_date);
	$date->setTime(23, 59, 59);
	$obj->project_end_date = $date->format(FMT_DATETIME_MYSQL);
}
if ($obj->project_actual_end_date) {
	$date = new CDate($obj->project_actual_end_date);
	$obj->project_actual_end_date = $date->format(FMT_DATETIME_MYSQL);
}

// let's check if there are some assigned departments to project
if (!w2PgetParam($_POST, 'project_departments', 0)) {
	$obj->project_departments = implode(',', w2PgetParam($_POST, 'dept_ids', array()));
}

$del = w2PgetParam($_POST, 'del', 0);

// prepare (and translate) the module name ready for the suffix
if ($del) {
	$project_id = w2PgetParam($_POST, 'project_id', 0);
	$canDelete = $obj->canDelete($msg, $project_id);
	if (!$canDelete) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		if ($notify_owner) {
			if ($msg = $obj->notifyOwner(1)) {
				$AppUI->setMsg($msg, UI_MSG_ERROR);
			}
		}
		if ($notify_contacts) {
			if ($msg = $obj->notifyContacts(1)) {
				$AppUI->setMsg($msg, UI_MSG_ERROR);
			}
		}
		$AppUI->setMsg('Project deleted', UI_MSG_ALERT);
		$AppUI->redirect('m=projects');
	}
} else {
	/*      $owner_id = $obj->project_owner;
	if ($obj->project_id) {
	$old_obj = new CProject();
	$old_obj->load($obj->project_id);
	}
	if (($owner_id != $old_obj->project_owner) || !$obj->project_id) {
	$q  = new DBQuery;
	$q->addTable('users');
	$q->addQuery('user_id, contact_company as user_company, contact_department as user_department');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
	$q->addWhere("user_id = $owner_id");
	$broker = $q->loadList();
	//print_r($broker);die;
	$q->clear();
	$obj->project_company = $broker[0]['user_company'];
	$obj->project_departments = $broker[0]['user_department'];
	}*/

	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$isNotNew = $_POST['project_id'];
		// check project parents and reset them to self if they do not exist
		if (!$obj->project_parent) {
			$obj->project_parent = $obj->project_id;
			$obj->project_original_parent = $obj->project_id;
		} else {
			$parent_project = new CProject();
			$parent_project->load($obj->project_parent);
			$obj->project_original_parent = $parent_project->project_original_parent;
		}

		if (!$obj->project_original_parent) {
			$obj->project_original_parent = $obj->project_id;
		}

		$obj->store();

		if ($importTask_projectId = w2PgetParam($_POST, 'import_tasks_from', '0')) {
			$obj->importTasks($importTask_projectId);
		}

		$custom_fields = new CustomFields($m, 'addedit', $obj->project_id, 'edit');
		$custom_fields->bind($_POST);
		$sql = $custom_fields->store($obj->project_id); // Store Custom Fields
		if ($notify_owner) {
			if ($msg = $obj->notifyOwner($isNotNew)) {
				$AppUI->setMsg($msg, UI_MSG_ERROR);
			}
		}
		if ($notify_contacts) {
			if ($msg = $obj->notifyContacts($isNotNew)) {
				$AppUI->setMsg($msg, UI_MSG_ERROR);
			}
		}
		$AppUI->setMsg($isNotNew ? 'Project updated' : 'Project inserted', UI_MSG_OK);
	}
	$AppUI->redirect();
}
?>