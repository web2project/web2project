<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$sub_form = (int) w2PgetParam($_POST, 'sub_form', 0);

$isNotNew = $_POST['dept_id'];
$dept_id = (int) w2PgetParam($_POST, 'dept_id', 0);
$perms = &$AppUI->acl();

if ($del) {
	if (!$perms->checkModuleItem('departments', 'delete', $dept_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} elseif ($isNotNew) {
	if (!$perms->checkModuleItem('departments', 'edit', $dept_id)) {
		$AppUI->redirect('m=public&a=access_denied');
	}
} else {
	if (!canAdd('departments')) {
		$AppUI->redirect('m=public&a=access_denied');
	}
}

$obj = new CDepartment();
if (($msg = $obj->bind($_POST))) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
if ($del) {
	$dep = new CDepartment();
	$msg = $dep->load($obj->dept_id);
	if (($msg = $obj->delete($AppUI))) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect('m=companies&a=view&company_id=' . $dep->dept_company);
	}
} else {
	if (($result = $obj->store($AppUI))) {
    if (is_array($result)) {
      $AppUI->setMsg($result, UI_MSG_ERROR, true);
      $AppUI->holdObject($obj);
      $AppUI->redirect('m=departments&a=addedit');
    }
	} else {
		$AppUI->setMsg($isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true);
	}
	$AppUI->redirect();
}