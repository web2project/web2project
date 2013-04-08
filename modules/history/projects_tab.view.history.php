<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $filter_param;

$filter_param = 'project_id';
require W2P_BASE_DIR . '/modules/history/index_table.php';