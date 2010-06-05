<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$isNotNew = (int) w2PgetParam($_POST, 'message_id', 0);
$message_forum = (int) w2PgetParam($_POST, 'message_forum', 0);
$message_parent = (int) w2PgetParam($_POST, 'message_parent', 0);

$obj = new CForumMessage();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=forums&a=viewer&forum_id='.$message_forum.'&message_parent='.$message_parent.'&post_message=1');
}
if ($result) {
    $AppUI->setMsg('Message '.$action, UI_MSG_OK, true);
    $redirect = ($del) ? 'm=forums' : 'm=forums&a=viewer&forum_id='.$message_forum.'&message_parent='.$message_parent;
    $AppUI->redirect($redirect);
} else {
    $AppUI->redirect('m=public&a=access_denied');
}