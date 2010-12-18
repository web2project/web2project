<?php /* $Id: do_forum_aed.php 1483 2010-10-26 17:11:59Z pedroix $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/forums/do_forum_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$isNotNew = (int) w2PgetParam($_POST, 'forum_id', 0);
$del = (int)w2PgetParam($_POST, 'del', 0) && $isNotNew;

$obj = new CForum();
if (!$obj->bind($_POST)) {
  $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
  $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
  $AppUI->setMsg($result, UI_MSG_ERROR, true);
  $AppUI->holdObject($obj);
  $AppUI->redirect('m=forums&a=addedit');
}
if ($result) {
  $AppUI->setMsg('Forums '.$action, UI_MSG_OK, true);
  $AppUI->redirect('m=forums');
} else {
  $AppUI->redirect('m=public&a=access_denied');
}