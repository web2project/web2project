<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CLink();
if (!$obj->bind($_POST)) {
  $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
  $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
  $AppUI->setMsg($result, UI_MSG_ERROR, true);
  $AppUI->redirect();
}
if ($result) {
  $AppUI->setMsg('Links '.$action, UI_MSG_OK, true);
  $AppUI->redirect('m=links');
} else {
  $AppUI->redirect('m=public&a=access_denied');
}