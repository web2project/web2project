<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$edit = (int) w2PgetParam($_POST, 'edit', 0);
$company_id = (int) w2PgetParam($_POST, 'company_id', 0);

if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$obj = new bcode();
$obj->_billingcode_id = (int) w2PgetParam($_POST, 'billingcode_id', 0);

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Billing Codes');
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
	}
} else {
	if ($edit) {
		$obj->_billingcode_id = $edit;
	}

	$obj->billingcode_value = w2PgetParam($_POST, 'billingcode_value', 0);
	$obj->billingcode_name = w2PgetParam($_POST, 'billingcode_name', '');
	$obj->billingcode_desc = w2PgetParam($_POST, 'billingcode_desc', '');
	$obj->company_id = $company_id;

	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('updated', UI_MSG_OK, true);
	}
}

$AppUI->redirect('m=system&a=billingcode&company_id=' . $company_id);
