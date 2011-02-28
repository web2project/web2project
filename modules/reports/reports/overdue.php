<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

// Output the PDF
// make the PDF file
if ($project_id != 0) {
    $q = new w2p_Database_Query;
    $q->addTable('projects');
    $q->addQuery('project_name');
    $q->addWhere('project_id=' . $project_id);
    $pname = 'Project: ' . $q->loadResult();
} else {
    $pname = $AppUI->_('All Projects');
}
if ($err = db_error()) {
    $AppUI->setMsg($err, UI_MSG_ERROR);
    $AppUI->redirect();
}

$pdf = new w2p_Output_PDF_Reports('L', 'mm', 'A4', true, 'UTF-8');
$pdf->SetMargins(15, 25, 15, true); // left, top, right
$pdf->setHeaderMargin(10);
$pdf->setFooterMargin(20);

$pdf->header_company_name = w2PgetConfig('company_name');
$date = new w2p_Utilities_Date();
$pdf->header_date = $date->format($df);

$pdf->SetFont('freeserif', '', 12);

$pdf->AddPage();

$pdf->Cell(0, 0, $AppUI->_('Project Overdue Task Report'), 0, 1);

$pdf->SetFont('freeserif', 'B', 15);

$pdf->Cell(0, 0, $pname, 0, 1);

$pdf->SetFont('freeserif', '', 10);

$hasResources = $AppUI->isActiveModule('resources');
$perms = &$AppUI->acl();
if ($hasResources) {
    $hasResources = canView('resources');
}
// Build the data to go into the table.
$pdfdata = array();
$columns = array();
$columns[] = '<b>' . $AppUI->_('Task Name') . '</b>';
$columns[] = '<b>' . $AppUI->_('Owner') . '</b>';
$columns[] = '<b>' . $AppUI->_('Assigned Users') . '</b>';
if ($hasResources) {
    $columns[] = '<b>' . $AppUI->_('Assigned Resources') . '</b>';
}
$columns[] = '<b>' . $AppUI->_('Finish Date') . '</b>';

// Grab the completed items in the last week
$q = new w2p_Database_Query();
$q->addQuery('a.*');
$q->addQuery('CONCAT(contact_first_name, \' \', contact_last_name) AS user_username');
$q->addTable('tasks', 'a');
$q->addTable('projects', 'pr');
$q->addWhere('a.task_project = pr.project_id');
$q->addJoin('users', 'b', 'a.task_owner = b.user_id', 'inner');
$q->addJoin('contacts', 'ct', 'ct.contact_id = b.user_contact', 'inner');
$q->addWhere('task_percent_complete < 100');
$q->addWhere('pr.project_active = 1');
if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
    $q->addWhere('pr.project_status <> ' . (int)$template_status);
}
if ($project_id != 0) {
    $q->addWhere('task_project = ' . (int)$project_id);
}
$q->addWhere('task_end_date < \'' . $date->format(FMT_DATETIME_MYSQL) . '\'');
$proj = new CProject();
$proj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

$obj = new CTask();
$obj->setAllowedSQL($AppUI->user_id, $q);
$tasks = $q->loadHashList('task_id');

if ($err = db_error()) {
    $AppUI->setMsg($err, UI_MSG_ERROR);
    $AppUI->redirect();
}
// Now grab the resources allocated to the tasks.
$task_list = array_keys($tasks);
$assigned_users = array();
// Build the array
foreach ($task_list as $tid) {
    $assigned_users[$tid] = array();
}

if (count($tasks)) {
    $q->clear();
    $q->addQuery('a.task_id, a.perc_assignment, b.*, c.*');
    $q->addTable('user_tasks', 'a');
    $q->addJoin('users', 'b', 'a.user_id = b.user_id', 'inner');
    $q->addJoin('contacts', 'c', 'b.user_contact = c.contact_id', 'inner');
    $q->addWhere('a.task_id IN (' . implode(',', $task_list) . ')');
    $res = $q->exec();
    if (!$res) {
        $AppUI->setMsg(db_error(), UI_MSG_ERROR);
        $q->clear();
        $AppUI->redirect();
    }
    while ($row = db_fetch_assoc($res)) {
        $assigned_users[$row['task_id']][$row['user_id']] = $row['contact_first_name'] . ' ' . $row['contact_last_name'] . ' [' . $row['perc_assignment'] . '%]';
    }
    $q->clear();
}

$resources = array();
if ($hasResources && count($tasks)) {
    foreach ($task_list as $tid) {
        $resources[$tid] = array();
    }
    $q->clear();
    $q->addQuery('a.*, b.resource_name');
    $q->addTable('resource_tasks', 'a');
    $q->addJoin('resources', 'b', 'a.resource_id = b.resource_id', 'inner');
    $q->addWhere('a.task_id IN (' . implode(',', $task_list) . ')');
    $res = $q->exec();
    if (!$res) {
        $AppUI->setMsg(db_error(), UI_MSG_ERROR);
        $q->clear();
        $AppUI->redirect();
    }
    while ($row = db_fetch_assoc($res)) {
        $resources[$row['task_id']][$row['resource_id']] = $row['resource_name'] . ' [' . $row['percent_allocated'] . '%]';
    }
    $q->clear();
}

// Build the data columns
foreach ($tasks as $task_id => $detail) {
    $row = &$pdfdata[];
    $row[] = $detail['task_name'];
    $row[] = $detail['user_username'];
    $row[] = implode("<br>", $assigned_users[$task_id]);
    if ($hasResources) {
        $row[] = implode("<br>", $resources[$task_id]);
    }
    $end_date = new w2p_Utilities_Date($detail['task_end_date']);
    $row[] = $end_date->format($df);
}

$pdf->addHtmlTable($pdfdata, $columns);

$pdf->Output('file.pdf', 'D');