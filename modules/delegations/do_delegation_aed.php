<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

// to join the time to the start date
$start_date = new w2p_Utilities_Date($_POST['delegation_start_date'] . $_POST['start_hour'] . $_POST['start_minute']);
$_POST['delegation_start_date'] = $start_date->format(FMT_DATETIME_MYSQL);

$controller = new w2p_Controllers_Base(new CDelegation(), $delete, 'Delegation', 'm=delegations', 'm=delegations&a=addedit');

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
