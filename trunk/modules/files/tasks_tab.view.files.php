<?php // check access to files module
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $m, $obj, $task_id, $w2Pconfig;
if (!getDenyRead('files')) {
	if (!getDenyEdit('files')) {
		echo '<a href="./index.php?m=files&a=addedit&project_id=' . $obj->task_project . '&file_task=' . $task_id . '">' . $AppUI->_('Attach a file') . '</a>';
	}
	echo w2PshowImage('stock_attach-16.png', 16, 16, '');
	$showProject = false;
	$project_id = $obj->task_project;
	include (W2P_BASE_DIR . '/modules/files/index_table.php');
}
?>