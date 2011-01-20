<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
//echo '<pre>'; print_r($_POST); die();
$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CEvent();
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

if (!$obj->event_recurs) {
	$obj->event_times_recuring = 0;
}

$action = ($del) ? 'deleted' : 'stored';

if ($del) {
    $result = $obj->delete($AppUI);
} else {
	if ($_POST['event_assigned'] > '' && ($clash = $obj->checkClash($_POST['event_assigned']))) {
		$last_a = $a;
		$GLOBALS['a'] = "clash";
		$do_redirect = false;
	} else {
        $result = $obj->store($AppUI);
        if (isset($_POST['event_assigned'])) {
            $obj->updateAssigned(explode(',', $_POST['event_assigned']));
        }
        if (isset($_POST['mail_invited'])) {
            $obj->notify($_POST['event_assigned'], false);
        }
    }
}

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=calendar&a=addedit');
}

if ($result) {
    $AppUI->setMsg('Event '.$action, UI_MSG_OK, true);
    $AppUI->redirect('m=calendar');
} else {
    $AppUI->redirect('m=public&a=access_denied');
}