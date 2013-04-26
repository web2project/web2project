<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id;

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('delegations', 'tasks_delegated_others');
$fieldList = array_keys($fields);
$fieldNames = array_values($fields);

// just to avoid a warning
$hours = array();
$minutes = array();

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$owner = 'mine';
include W2P_BASE_DIR . '/modules/delegations/index_table.php';
