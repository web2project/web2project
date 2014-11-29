<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$object_id = (int) w2PgetParam($_GET, 'link_id', 0);

$object = new CLink();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

header("Location: " . $object->link_url);
