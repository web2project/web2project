<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CEvent();
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

if ($obj->event_start_date) {
    $start_date = new w2p_Utilities_Date($obj->event_start_date . $_POST['start_time']);
    $obj->event_start_date = $start_date->format(FMT_DATETIME_MYSQL);
}
if ($obj->event_end_date) {
    $end_date = new w2p_Utilities_Date($obj->event_end_date . $_POST['end_time']);
    $obj->event_end_date = $end_date->format(FMT_DATETIME_MYSQL);
}

$action = ($del) ? 'deleted' : 'stored';
$clashRedirect = false;

if ($del) {
    $result = $obj->delete();
} else {
    if ($_POST['event_assigned'] > '' && ($clash = $obj->checkClash($_POST['event_assigned']))) {
		$last_a = $a;
		$GLOBALS['a'] = "clash";
        $clashRedirect = true;
	} else {
        $result = $obj->store();
        if (isset($_POST['event_assigned'])) {
            $obj->updateAssigned(explode(',', $_POST['event_assigned']));
        }
        if (isset($_POST['mail_invited'])) {
            $obj->notify($_POST['event_assigned'], false);
        }
    }
}

//TODO: I hate this clash param.. there should be a better way.
if (!$clashRedirect) {
    if (is_array($result)) {
        $AppUI->setMsg($result, UI_MSG_ERROR, true);
        $AppUI->holdObject($obj);
        $AppUI->redirect('m=calendar&a=addedit');
    }

    if ($result) {
        $AppUI->setMsg('Event '.$action, UI_MSG_OK, true);
        $redirect = 'm=calendar';
    } else {
        $redirect = ACCESS_DENIED;
    }
    $AppUI->redirect($redirect);
}
