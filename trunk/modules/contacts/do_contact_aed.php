<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$obj = new CContact();
$msg = '';

$notifyasked = w2PgetParam($_POST, 'contact_updateask', 0);
if ($notifyasked != 0) {
	$notifyasked = 1;
}

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

require_once ($AppUI->getSystemClass('CustomFields'));
$del = w2PgetParam($_POST, 'del', 0);

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Contact');
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect("m=contacts");
	}
} else {
	$isNotNew = @$_POST['contact_id'];

	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$custom_fields = new CustomFields($m, 'addedit', $obj->contact_id, 'edit');
		$custom_fields->bind($_POST);
		$sql = $custom_fields->store($obj->contact_id); // Store Custom Fields

		$updatekey = $obj->getUpdateKey();
		if ($notifyasked && !$updatekey) {
			$rnow = new CDate();
			$obj->contact_updatekey = MD5($rnow->format(FMT_DATEISO));
			$obj->contact_updateasked = $rnow->format(FMT_DATETIME_MYSQL);
			$obj->contact_lastupdate = '';
			$obj->updateNotify();
			//            } elseif (!$notifyasked && $obj->contact_updatekey) {
		} elseif ($notifyasked && $updatekey) {
		} else {
			$obj->contact_updatekey = '';
		}
		$obj->store();

		$AppUI->setMsg($isNotNew ? 'updated' : 'added', UI_MSG_OK, true);
	}
	$AppUI->redirect();
}
?>