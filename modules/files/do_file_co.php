<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$file_id = intval(w2PgetParam($_GET, 'file_id', 0));

$AppUI->setMsg('File Check In/Out has been removed from web2project.', UI_MSG_WARNING, true);

$AppUI->redirect('m=files&a=addedit&file_id=' . $file_id);