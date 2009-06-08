<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI, $m, $obj, $task_id;
if (!getDenyRead('links')) {
	if (!getDenyEdit('links')) {
		echo '<a href="./index.php?m=links&a=addedit&project_id=' . $obj->task_project . '&link_task=' . $task_id . '">' . $AppUI->_('Attach a link') . '</a>';

	}
	echo w2PshowImage('stock_attach-16.png', 16, 16, '', '', $m);
	$showProject = false;
	$project_id = $obj->task_project;
	include (W2P_BASE_DIR . '/modules/links/index_table.php');
}
?>