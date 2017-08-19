<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$project_ids = w2PgetParam($_POST, 'project_ids', array()); // Note: This is an array

$status_id = (int) w2PgetParam($_POST, 'project_status', 0);

foreach ($project_ids as $project_id) {
    if (0 == (int) $project_id) {
        continue;
    }

    CProject::updateStatus(null, $project_id, $status_id);
}

$tab = $status_id + 2;

$AppUI->redirect('m=projects&tab=' . $tab);