<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);
$forum_id = (int) w2PgetParam($_GET, 'forum_id', 0);

$message_parent = (int) w2PgetParam($_POST, 'message_parent', 0);
$message_id = (int) w2PgetParam($_POST, 'message_id', 0);

$successPath = ($delete && $message_id == $message_parent) || $message_parent == -1?
		'm=forums&a=viewer&forum_id='.$forum_id :
	'm=forums&a=viewer&forum_id='.$forum_id.'&message_id='.$message_parent;

$errorPath = 'm=forums&a=viewer&forum_id='.$forum_id.'&message_parent='.
        $message_parent.'&post_message=1';

$controller = new w2p_Controllers_Base(
                    new CForum_Message(), $delete, 'Message', $successPath, $errorPath
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
