<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not call this file directly.');
}

$message_id = w2PgetParam($_REQUEST, 'message_id', 0);

$AppUI->setMsg('PDF Export has been removed from web2project. Use Print > Save to PDF in your browser instead', UI_MSG_WARNING, true);

$AppUI->redirect('m=forums&a=view&message_id=' . $message_id);