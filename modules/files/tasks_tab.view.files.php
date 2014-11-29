<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $AppUI, $m, $object, $task_id, $w2Pconfig;
if (canView('files')) {
    if (canAdd('files')) {
        echo '<a href="./index.php?m=files&a=addedit&project_id=' . $object->task_project . '&file_task=' . $task_id . '">' . $AppUI->_('Attach a file') . '</a>';
    }
    echo w2PshowImage('stock_attach-16.png', 16, 16, '');
    $showProject = false;
    $project_id = $object->task_project;
    $task_id = $object->getId();
    include W2P_BASE_DIR . '/modules/files/index_table.php';
}
