<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);
$role_id = (int) w2PgetParam($_POST, 'role_id', 0);

$controller = new \Web2project\Actions\AddEditRolePermissions($AppUI->acl(), $delete, 'Role Permission',
    'm=system&u=roles&a=viewrole&role_id=' . $role_id, 'm=system&u=roles&a=viewrole&role_id=' . $role_id);

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);