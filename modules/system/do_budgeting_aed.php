<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$controller = new \Web2project\Actions\AddEdit(
                    new CSystem_Budget(), $delete, 'Budgets', 'm=system&a=budgeting', 'm=system&a=budgeting'
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
