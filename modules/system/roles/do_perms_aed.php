<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$controller = new \Web2project\Actions\AddEditRolePermissions($AppUI->acl(), $delete, 'Role Permission',
    'm=system&u=roles', 'm=system&u=roles');

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
