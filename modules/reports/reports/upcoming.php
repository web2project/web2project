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
	$q->addWhere('project_id=' . (int)$project_id);
	$pname = 'Project: ' . $q->loadResult();
} else {
	$pname = $AppUI->_('All Projects');
}
if ($err = db_error()) {
	$AppUI->setMsg($err, UI_MSG_ERROR);
	$AppUI->redirect();
}

$font_dir = W2P_BASE_DIR . '/lib/ezpdf/fonts';

require ($AppUI->getLibraryClass('ezpdf/class.ezpdf'));

$pdf = new Cezpdf($paper = 'A4', $orientation = 'landscape');
$pdf->ezSetCmMargins(1, 2, 1.5, 1.5);
$pdf->selectFont($font_dir . '/Helvetica.afm');
$pdf->ezText(utf8_decode(w2PgetConfig('company_name')), 12);

$date = new w2p_Utilities_Date();
$pdf->ezText("\n" . $date->format($df), 8);
$next_week = new w2p_Utilities_Date($date);
$next_week->addSpan(new Date_Span(array(7, 0, 0, 0)));

$pdf->selectFont($font_dir . '/Helvetica-Bold.afm');
$pdf->ezText("\n" . $AppUI->_('Project Upcoming Task Report'), 12);
$pdf->ezText(utf8_decode($pname), 15);
$pdf->ezText($AppUI->_('Tasks Due to be Completed By') . ' ' . $next_week->format($df), 10);
$pdf->ezText("\n");
$pdf->selectFont($font_dir . '/Helvetica.afm');

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
$q->addWhere('task_end_date BETWEEN \'' . $date->format(FMT_DATETIME_MYSQL) . '\' AND \'' . $next_week->format(FMT_DATETIME_MYSQL) . '\'');
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
		$assigned_users[$row['task_id']][$row['user_id']] = $row[contact_first_name] . ' ' . $row[contact_last_name] . ' [' . $row[perc_assignment] . '%]';
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
	$row[] = implode("\n", $assigned_users[$task_id]);
	if ($hasResources)
		$row[] = implode("\n", $resources[$task_id]);
	$end_date = new w2p_Utilities_Date($detail['task_end_date']);
	$row[] = $end_date->format($df);
}

$pdf->ezTable($pdfdata, $columns, $title, $options);

$pdf->ezStream();