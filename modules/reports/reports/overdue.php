<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Output the PDF
// make the PDF file
if ($project_id != 0) {
    $project = new CProject();
    $project->load($project_id);
    $pname = 'Project: ' . $project->project_name;
} else {
	$pname = $AppUI->_('All Projects');
}
if ($err = db_error()) {
	$AppUI->setMsg($err, UI_MSG_ERROR);
    $AppUI->redirect('m=' . $m);
}

$font_dir = W2P_BASE_DIR . '/lib/ezpdf/fonts';

$output = new w2p_Output_PDFRenderer('A4', 'landscape');
$output->addTitle($AppUI->_('Project Overdue Task Report'));
$output->addDate($df);
$output->addSubtitle(w2PgetConfig('company_name'));
$output->addSubtitle($pname);

$next_week = new w2p_Utilities_Date($date);
$next_week->addSpan(new Date_Span(array(7, 0, 0, 0)));

$title = null;
$options = array('showLines' => 2, 'showHeadings' => 1, 'fontSize' => 9, 'rowGap' => 4, 'colGap' => 5, 'xPos' => 50, 'xOrientation' => 'right', 'width' => '750', 'shaded' => 0, 'cols' => array(0 => array('justification' => 'left', 'width' => 250), 1 => array('justification' => 'left', 'width' => 120), 2 => array('justification' => 'center', 'width' => 120), 3 => array('justification' => 'center', 'width' => 75), 4 => array('justification' => 'center', 'width' => 75)));

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
$q->addQuery('contact_display_name AS user_username');
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
$date = new w2p_Utilities_Date();
$q->addWhere('task_end_date < \'' . $date->format(FMT_DATETIME_MYSQL) . '\'');
$proj = new CProject();
$q = $proj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

$obj = new CTask();
$q = $obj->setAllowedSQL($AppUI->user_id, $q);
$tasks = $q->loadHashList('task_id');

if ($err = db_error()) {
	$AppUI->setMsg($err, UI_MSG_ERROR);
    $AppUI->redirect('m=' . $m);
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
    $q->addQuery('a.*, contact_display_name');
	$q->addTable('user_tasks', 'a');
	$q->addJoin('users', 'b', 'a.user_id = b.user_id', 'inner');
	$q->addJoin('contacts', 'c', 'b.user_contact = c.contact_id', 'inner');
	$q->addWhere('a.task_id IN (' . implode(',', $task_list) . ')');
	$res = $q->exec();
	if (!$res) {
		$AppUI->setMsg(db_error(), UI_MSG_ERROR);
		$q->clear();
        $AppUI->redirect('m=' . $m);
	}
	while ($row = db_fetch_assoc($res)) {
        $assigned_users[$row['task_id']][$row['user_id']] = $row['contact_display_name'] . ' [' . $row['perc_assignment'] . '%]';
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
        $AppUI->redirect('m=' . $m);
	}
	while ($row = db_fetch_assoc($res)) {
		$resources[$row['task_id']][$row['resource_id']] = $row['resource_name'] . ' [' . $row['percent_allocated'] . '%]';
	}
	$q->clear();
}

// Build the data columns
foreach ($tasks as $task_id => $detail) {
	$row = &$pdfdata[];
	$row[] = utf8_decode($detail['task_name']);
	$row[] = utf8_decode($detail['user_username']);
	$row[] = utf8_decode(implode("\n", $assigned_users[$task_id]));
	if ($hasResources) {
		$row[] = utf8_decode(implode("\n", $resources[$task_id]));
    }
	$end_date = new w2p_Utilities_Date($detail['task_end_date']);
	$row[] = $end_date->format($df);
}

$output->addTable($title, $columns, $pdfdata, $options);
$output->getStream();