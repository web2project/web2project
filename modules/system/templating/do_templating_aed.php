<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);
$_POST['email_template_body'] = w2PgetParam($_POST, 'email_template_description', '');

$controller = new \Web2project\Actions\AddEdit(
    new CSystem_Template(), $delete, 'Email Templates', 'm=system&u=templating', 'm=system&u=templating'
);

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);