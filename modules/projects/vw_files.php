<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;

$showProject = false;
require (W2P_BASE_DIR . '/modules/files/index_table.php');