<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$sub_form = (int) w2PgetParam($_POST, 'sub_form', 0);
$dept_id = (int) w2PgetParam($_POST, 'dept_id', 0);
$isNotNew = $dept_id;

$perms = &$AppUI->acl();

$obj = new CDepartment();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=departments&a=addedit');
}
if ($result) {
    $AppUI->setMsg('Department '.$action, UI_MSG_OK, true);
    $AppUI->redirect('m=companies&a=view&company_id='.$obj->dept_company);
} else {
    $AppUI->redirect('m=public&a=access_denied');
}