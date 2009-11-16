<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI, $m, $obj, $task_id;
$project_id = $obj->task_project;
$showProject = false;
include W2P_BASE_DIR . '/modules/links/index_table.php';