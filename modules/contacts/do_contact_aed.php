<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CContact();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete() : $obj->store();
$redirect = ($del) ? 'm=contacts' : 'm=contacts&a=view&contact_id='.$obj->contact_id;

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=contacts&a=addedit');
}
if ($result) {
    if (!$del) {
        $updatekey = $obj->getUpdateKey();
        $notifyasked = w2PgetParam($_POST, 'contact_updateask', 0);
		if ($notifyasked && !strlen($updatekey)) {
			$rnow = new w2p_Utilities_Date();
			$obj->contact_updatekey = MD5($rnow->format(FMT_DATEISO));
			$obj->contact_updateasked = $rnow->format(FMT_DATETIME_MYSQL);
			$obj->contact_lastupdate = '';
            $obj->store();
			$obj->notify();
            $action .= '. Update request sent';
		}
    }

    $AppUI->setMsg('Contact '.$action, UI_MSG_OK, true);
} else {
    $redirect = 'm=public&a=access_denied';
}
$AppUI->redirect($redirect);
