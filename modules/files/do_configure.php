<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

unset($_POST['dosql']);
foreach($_POST as $key => $value) {
    $key = preg_replace("/[^A-Za-z0-9_]/", "", $key);
    $value = preg_replace("/[^A-Za-z0-9_\/]/", "", $value);

    $query = new w2p_Database_Query();
    $query->setDelete('config');
    $query->addWhere("config_name = '$key'");
    $query->exec();

    $obj = new w2p_System_Config();
    $obj->config_name  = $key;
    $obj->config_value = $value;
    $result = $obj->store();

    if (!$result) {
        break;
    }
}

$AppUI->setMsg('Files configuration updated', UI_MSG_OK, true);

$AppUI->redirect('m=files&a=configure');