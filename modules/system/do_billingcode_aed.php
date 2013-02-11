<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CSystem_Bcode();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete() : $obj->store();

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=system&a=billingcode');
}
if ($result) {
    $AppUI->setMsg('Billing Codes '.$action, UI_MSG_OK, true);
    $AppUI->redirect('m=system&a=billingcode');
} else {
    $AppUI->redirect(ACCESS_DENIED);
}