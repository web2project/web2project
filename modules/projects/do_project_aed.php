<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$notfiyTrigger = (int) w2PgetParam($_POST, 'project_id', 0);

$obj = new CProject();
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}
if (!w2PgetParam($_POST, 'project_departments', 0)) {
	$obj->project_departments = implode(',', w2PgetParam($_POST, 'dept_ids', array()));
}

$action   = ($del) ? 'deleted' : 'stored';
$result   = ($del) ? $obj->delete() : $obj->store();
$redirect = ($del) ? 'm=projects' : 'm=projects&a=view&project_id='.$obj->project_id;

$notify_owner = w2PgetParam($_POST, 'email_project_owner_box', 'off');
$notify_contacts = w2PgetParam($_POST, 'email_project_contacts_box', 'off');

$importTask_projectId = (int) w2PgetParam($_POST, 'import_tasks_from', '0');

if ($result) {
    if (!$del) {
        $billingCategory = w2PgetSysVal('BudgetCategory');
        $budgets = array();
        foreach ($billingCategory as $id => $category) {
            $budgets[$id] = w2PgetParam($_POST, 'budget_'.$id, 0);
        }
        $obj->storeBudget($budgets);

        if ($importTask_projectId) {
            $import_result = $obj->importTasks($importTask_projectId);

            if (is_array($import_result) && count($import_result)) {
                $AppUI->setMsg($import_result, UI_MSG_ERROR, true);
                $AppUI->holdObject($obj);
                $AppUI->redirect('m=projects&a=addedit&project_id=' . $obj->project_id);
            }
        }

        if ('on' == $notify_owner) {
            $obj->notifyOwner($notfiyTrigger);
        }
        if ('on' == $notify_contacts) {
            $obj->notifyContacts($notfiyTrigger);
        }
    }

    $AppUI->setMsg('Project '.$action, UI_MSG_OK, true);
    $redirect = 'm=projects';
} else {
    $AppUI->setMsg($result, UI_MSG_ERROR);
    $AppUI->holdObject($obj);
    $redirect = 'm=projects&a=addedit&project_id=' . $obj->project_id;
}

$AppUI->redirect($redirect);