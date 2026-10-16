<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$project_ids = w2PgetParam($_POST, 'project_ids', array()); // Note: This is an array

$status_id = (int) w2PgetParam($_POST, 'project_status', 0);

foreach ($project_ids as $project_id) {
    if (0 == (int) $project_id) {
        continue;
    }

    $project = new CProject();
    $project->load($project_id);
    $project->project_status = $status_id;
    foreach ($project->getContactList() as $contact_data){
        $project->project_contacts[]=$contact_data['contact_id'];
    }
    foreach ($project->getDepartmentList() as $department_data){
        $project->project_departments[]=$department_data['dept_id'];
    }
    $project->store();
}

// Show the tab of the status the projects were moved to (tabs 0 and 1 are
// All / All Active, so status N is tab N + 2); the remembered tab no longer
// lists them.
$AppUI->redirect('m=projects&tab=' . ($status_id + 2));