<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
  
$obj = new CProject();
if (!$obj->bind($_POST)) {
  $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
  $AppUI->redirect();
}
if (!w2PgetParam($_POST, 'project_departments', 0)) {
  $obj->project_departments = implode(',', w2PgetParam($_POST, 'dept_ids', array()));
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

$notify_owner = w2PgetParam($_POST, 'email_project_owner_box', 'off');
$notify_contacts = w2PgetParam($_POST, 'email_project_contacts_box', 'off');

$notfiyTrigger = ($del) ? 1 : $obj->project_id;
$importTask_projectId = (int) w2PgetParam($_POST, 'import_tasks_from', '0');

if (is_array($result)) {
  $AppUI->setMsg($result, UI_MSG_ERROR);
  $AppUI->holdObject($obj);
  $AppUI->redirect('m=projects&a=addedit');
}
if ($result) {
  if ($importTask_projectId) {
    $obj->importTasks($importTask_projectId);
  }
  if ('on' == $notify_owner) {
    if ($msg = $obj->notifyOwner($notfiyTrigger)) {
      $AppUI->setMsg($msg, UI_MSG_ERROR);
    }
  }
  if ('on' == $notify_contacts) {
    if ($msg = $obj->notifyContacts($notfiyTrigger)) {
      $AppUI->setMsg($msg, UI_MSG_ERROR);
    }
  }

  $AppUI->setMsg('Project '.$action, UI_MSG_OK, true);
  $AppUI->redirect('m=projects');
} else {
  $AppUI->redirect('m=public&a=access_denied');
}