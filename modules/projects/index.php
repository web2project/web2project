<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

$structprojs = getProjects();

// Let's update project status!
if (isset($_GET['update_project_status']) && isset($_GET['project_status']) && isset($_GET['project_id'])) {
	$projects_id = w2PgetParam($_GET, 'project_id', array()); // This must be an array
	$statusId = w2PgetParam($_GET, 'project_status', 0);

	foreach ($projects_id as $project_id) {
		CProject::updateStatus($AppUI, $project_id, $statusId);
	}
}

if (isset($_POST['projsearchtext'])) {
	$AppUI->setState('projsearchtext', w2PformSafe($_POST['projsearchtext'], true));
}
$search_text = $AppUI->getState('projsearchtext') !== null ? $AppUI->getState('projsearchtext') : '';

$projectDesigner = $AppUI->getState('ProjIdxProjectDesigner') !== null ? $AppUI->getState('ProjIdxProjectDesigner') : 0;

$tab = $AppUI->processIntState('ProjIdxTab', $_GET, 'tab', 1);

$currentTabId = $tab;
$active = intval(!$AppUI->getState('ProjIdxTab'));

$oCompany = new CCompany;
$allowedCompanies = $oCompany->getAllowedRecords($AppUI->user_id, 'company_id,company_name');

if (isset($_POST['company_id'])) {
	$AppUI->setState('ProjIdxCompany', intval($_POST['company_id']));
}
$company_id = $AppUI->getState('ProjIdxCompany') !== null ? $AppUI->getState('ProjIdxCompany') : ((isset($allowedCompanies[$AppUI->user_company])) ? $AppUI->user_company : 0);

$company_prefix = 'company_';

if (isset($_POST['department'])) {
	$AppUI->setState('ProjIdxDepartment', $_POST['department']);

	//if department is set, ignore the company_id field
	unset($company_id);
}
$department = $AppUI->getState('ProjIdxDepartment') !== null ? $AppUI->getState('ProjIdxDepartment') : ((isset($allowedCompanies[$AppUI->user_company])) ? $company_prefix . $AppUI->user_company : $company_prefix . '0');

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the
// $department variable, and populate the $company_id variable.
if (!(strpos($department, $company_prefix) === false)) {
	$company_id = substr($department, strlen($company_prefix));
	$AppUI->setState('ProjIdxCompany', $company_id);
	unset($department);
}

$orderdir = $AppUI->getState('ProjIdxOrderDir') ? $AppUI->getState('ProjIdxOrderDir') : 'asc';
if (isset($_GET['orderby'])) {
	if ($AppUI->getState('ProjIdxOrderDir') == 'asc') {
		$orderdir = 'desc';
	} else {
		$orderdir = 'asc';
	}
	$AppUI->setState('ProjIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
}
$orderby = $AppUI->getState('ProjIdxOrderBy') ? $AppUI->getState('ProjIdxOrderBy') : 'project_end_date';
$AppUI->setState('ProjIdxOrderDir', $orderdir);

// prepare the type filter
if (isset($_POST['project_type'])) {
	$AppUI->setState('ProjIdxType', intval($_POST['project_type']));
}
$project_type = $AppUI->getState('ProjIdxType') !== null ? $AppUI->getState('ProjIdxType') : -1;

// prepare the users filter
if (isset($_POST['project_owner'])) {
	$AppUI->setState('ProjIdxowner', intval($_POST['project_owner']));
}
$owner = $AppUI->getState('ProjIdxowner') !== null ? $AppUI->getState('ProjIdxowner') : 0;

$user_list = array(0 => '(all)') + CProject::getOwners();

// collect the full projects list data via function in projects.class.php
projects_list_data();

$project_types = array(-1 => '(all)') + w2PgetSysVal('ProjectType');

$bufferSearch = '<input type="text" class="text" size="20" name="projsearchtext" onChange="document.searchfilter.submit();" value=' . "'$search_text'" . 'title="' . $AppUI->_('Search in name and description fields') . '"/>';

// setup the title block
$titleBlock = new CTitleBlock('Projects', 'applet3-48.png', $m, $m . '.' . $a);
$titleBlock->addCell('<table><tr><form action="?m=projects" method="post" name="searchfilter" accept-charset="utf-8"><td nowrap="nowrap" align="right">' . $AppUI->_('Search') . ':</td><td nowrap="nowrap" align="left">' . $bufferSearch . '</form></tr><tr><form action="?m=projects" method="post" name="typeIdForm" accept-charset="utf-8"><td nowrap="nowrap" align="right">' . $AppUI->_('Type') . '</td><td nowrap="nowrap" align="left">' . arraySelect($project_types, 'project_type', 'size="1" class="text" onChange="document.typeIdForm.submit();"', $project_type, false) . '</td></form></tr></table>', '', '', '');
$titleBlock->addCell('<table><tr><form action="?m=projects" method="post" name="pickCompany" accept-charset="utf-8"><td nowrap="nowrap" align="right">' . $AppUI->_('Company') . '</td><td nowrap="nowrap" align="left">' . $buffer . '</td></form></tr><tr><form action="?m=projects" method="post" name="userIdForm" accept-charset="utf-8"><td nowrap="nowrap" align="right">' . $AppUI->_('Owner') . '</td><td nowrap="nowrap" align="left">' . arraySelect($user_list, 'project_owner', 'size="1" class="text" onChange="document.userIdForm.submit();"', $owner, false) . '</td></form></tr></table>', '', '', '');
if ($canAuthor) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new project') . '">', '', '<form action="?m=projects&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->addCell('<span title="' . $AppUI->_('Projects') . '::' . $AppUI->_('Print projects list') . '."><a href="javascript: void(0);" onclick ="window.open(\'index.php?m=projects&a=printprojects&dialog=1&suppressHeaders=1\', \'printprojects\',\'width=1200, height=600, menubar=1, scrollbars=1\')">
		<img src="' . w2PfindImage('printer.png') . '" border="0" width="22" heigth"22" alt="" />
		</a></span>
		');

$titleBlock->show();

$project_statuses = w2PgetSysVal('ProjectStatus');

$active = 0;
$archived = 0;

foreach ($project_statuses as $key => $value) {
	$counter[$key] = 0;
	if (is_array($projects)) {
		foreach ($projects as $p) {
			if ($p['project_status'] == $key && $p['project_active'] > 0) {
				++$counter[$key];
			}
		}
	}
	$project_statuses[$key] = $AppUI->_($project_statuses[$key], UI_OUTPUT_RAW) . ' (' . $counter[$key] . ')';
}

if (is_array($projects)) {
	foreach ($projects as $p) {
		if ($p['project_active'] == 0) {
			++$archived;
		} else {
			++$active;
		}
	}
}

$project_statuses[] = $AppUI->_('Archived', UI_OUTPUT_RAW) . ' (' . $archived . ')';

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page
$tabBox = new CTabBox('?m=projects', W2P_BASE_DIR . '/modules/projects/', $tab);
$is_tabbed = $tabBox->isTabbed();
if ($is_tabbed) {
	// This will overwrited the initial tab, so we need to add that separately.
	$allactive = (int)count($projects) - (int)($archived);
	array_unshift($project_statuses, $AppUI->_('All Projects', UI_OUTPUT_RAW) . ' (' . count($projects) . ')', $AppUI->_('All Active', UI_OUTPUT_RAW) . ' (' . $allactive . ')');
}

$project_status_file = array();

// tabbed information boxes
foreach ($project_statuses as $project_status) {
	$tabBox->add('vw_idx_projects', mb_trim($project_status), true);
}
$min_view = true;
$tabBox->add('viewgantt', 'Gantt');
$tabBox->show();