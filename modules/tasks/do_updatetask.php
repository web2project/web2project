<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//There is an issue with international UTF characters, when stored in the database an accented letter
//actually takes up two letters per say in the field length, this is a problem with costcodes since
//they are limited in size so saving a costcode as REDACI�N would actually save REDACI� since the accent takes
//two characters, so lets unaccent them, other languages should add to the replacements array too...
function cleanText($text) {
	//This text file is not utf, its iso so we have to decode/encode
	$text = utf8_decode($text);
	$trade = array('�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'N', '�' => 'n');
	$text = strtr($text, $trade);
	$text = utf8_encode($text);

	return $text;
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$notify_owner = w2PgetParam($_POST, 'task_log_notify_owner', 'off');
$isNotNew = (int) w2PgetParam($_POST, 'task_log_id', 0);

$obj = new CTaskLog();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=tasks&a=view&task_id='.$obj->task_log_task);
}
if ($result) {
    $AppUI->setMsg('Task Log '.$action, UI_MSG_OK, true);

    $task = new CTask();
    $task->load($obj->task_log_task);

    $canEditTask = $perms->checkModuleItem('tasks', 'edit', $task_id);
    if ($canEditTask) {
        $task->htmlDecode();
        $task->check();
        $task_end_date = new CDate($task->task_end_date);
        $task->task_percent_complete = w2PgetParam($_POST, 'task_percent_complete', null);

        if (w2PgetParam($_POST, 'task_end_date', '') != '') {
            $new_date = new CDate($_POST['task_end_date']);
            $new_date->setTime($task_end_date->hour, $task_end_date->minute, $task_end_date->second);
            $task->task_end_date = $new_date->format(FMT_DATETIME_MYSQL);
        }

        if ($task->task_percent_complete >= 100 && (!$task->task_end_date || $task->task_end_date == '0000-00-00 00:00:00')) {
            $task->task_end_date = $obj->task_log_date;
        }

        $msg = $task->store($AppUI);
        if (is_array($msg)) {
            $AppUI->setMsg($msg, UI_MSG_ERROR, true);
        }

        $new_task_end = new CDate($task->task_end_date);
        if ($new_task_end->dateDiff($task_end_date)) {
            $task->addReminder();
        }

        $task->pushDependencies($task->task_id, $task->task_end_date);
    }

    if ($notify_owner) {
        if ($msg = $task->notifyOwner()) {
            $AppUI->setMsg($msg, UI_MSG_ERROR);
        }
    }

    // Check if we need to email the task log to anyone.
    $email_assignees = w2PgetParam($_POST, 'email_assignees', null);
    $email_task_contacts = w2PgetParam($_POST, 'email_task_contacts', null);
    $email_project_contacts = w2PgetParam($_POST, 'email_project_contacts', null);
    $email_others = w2PgetParam($_POST, 'email_others', '');
    $email_extras = w2PgetParam($_POST, 'email_extras', null);

    if ($task->email_log($obj, $email_assignees, $email_task_contacts, $email_project_contacts, $email_others, $email_extras)) {
        $obj->store(); // Save the updated message. It is not an error if this fails.
    }

    $AppUI->redirect('m=tasks&a=view&task_id=' . $obj->task_log_task . '&tab=0#tasklog' . $obj->task_log_id);

} else {
    $AppUI->redirect('m=public&a=access_denied');
}