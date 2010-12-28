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
$perms = &$AppUI->acl();

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=contacts&a=addedit');
}
if ($result) {
    $AppUI->setMsg('Contact '.$action, UI_MSG_OK, true);

    if (!$del) {
        $updatekey = $obj->getUpdateKey();
        $notifyasked = w2PgetParam($_POST, 'contact_updateask', 0);
		if ($notifyasked && !$updatekey) {
			$rnow = new w2p_Utilities_Date();
			$obj->contact_updatekey = MD5($rnow->format(FMT_DATEISO));
			$obj->contact_updateasked = $rnow->format(FMT_DATETIME_MYSQL);
			$obj->contact_lastupdate = '';
			$obj->updateNotify();
		} elseif (!($notifyasked && $updatekey)) {
			$obj->contact_updatekey = '';
		}
    }

    $AppUI->redirect('m=contacts');
} else {
    $AppUI->redirect('m=public&a=access_denied');
}
