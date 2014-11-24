<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$start_date = new w2p_Utilities_Date($_POST['event_start_date'] . $_POST['start_time']);
$_POST['event_start_date'] = $start_date->format(FMT_DATETIME_MYSQL);

$end_date = new w2p_Utilities_Date($_POST['event_end_date'] . $_POST['end_time']);
$_POST['event_end_date'] = $end_date->format(FMT_DATETIME_MYSQL);

$controller = new \Web2project\Actions\AddEdit(
    new CEvent(), $delete, 'Event', 'm=events', 'm=events&a=addedit'
);

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
