<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('ProjIdxTab', $_GET, 'tab', 1);

$project = new CProject();
$structprojs = $project->getProjects();

// Let's update project status!
if (isset($_GET['update_project_status']) && isset($_GET['project_status']) && isset($_GET['project_id'])) {
	$projects_id = w2PgetParam($_GET, 'project_id', array()); // This must be an array
	$statusId = w2PgetParam($_GET, 'project_status', 0);
	$project = new CProject();

	foreach ($projects_id as $project_id) {
		$project->load($project_id);
		$project->project_status = $statusId;
		$project->store();
	}
}

if (isset($_POST['projsearchtext'])) {
	$AppUI->setState('projsearchtext', w2PformSafe($_POST['projsearchtext'], true));
}
$search_text = $AppUI->getState('projsearchtext') !== null ? $AppUI->getState('projsearchtext') : '';

$company_id = $AppUI->processIntState('ProjIdxCompany', $_POST, 'project_company', $AppUI->user_company);
$orderby = (property_exists('CProject', $_GET['orderby'])) ? $_GET['orderby'] : 'project_company';
$project_type = $AppUI->processIntState('ProjIdxType', $_POST, 'project_type', -1);
$owner = $AppUI->processIntState('ProjIdxowner', $_POST, 'project_owner', -1);

$orderdir = $AppUI->getState('ProjIdxOrderDir') ? $AppUI->getState('ProjIdxOrderDir') : 'asc';
if (isset($_GET['orderby'])) {
	if ($AppUI->getState('ProjIdxOrderDir') == 'asc') {
		$orderdir = 'desc';
	} else {
		$orderdir = 'asc';
	}
}
$AppUI->setState('ProjIdxOrderDir', $orderdir);

// collect the full projects list data via function in projects.class.php
$projects = projects_list_data();

$oCompany = new CCompany;
$allowedCompanies[-1] = $AppUI->_('all');
$allowedCompanies += $oCompany->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');

$project_types = array(-1 => '(' . $AppUI->_('all') . ')') + w2PgetSysVal('ProjectType');

$user_list = array(0 => '(' . $AppUI->_('all') . ')') + CProject::getOwners();

$bufferSearch = '<input type="text" class="text" size="20" name="projsearchtext" onChange="document.searchfilter.submit();" value=' . "'$search_text'" . 'title="' . $AppUI->_('Search in name and description fields') . '"/>';

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Projects', 'applet3-48.png', $m, $m . '.' . $a);
$titleBlock->addCell('<form action="?m=projects" method="post" name="searchfilter" accept-charset="utf-8">' . $bufferSearch . '</form>');
$titleBlock->addCell($AppUI->_('Search') . ':');
$titleBlock->addCell('<form action="?m=projects" method="post" name="typeIdForm" accept-charset="utf-8">' .
        arraySelect($project_types, 'project_type', 'size="1" class="text" onChange="document.typeIdForm.submit();"', $project_type, false) .
        '</form>');
$titleBlock->addCell($AppUI->_('Type') . ':');
$titleBlock->addCell('<form action="?m=projects" method="post" name="pickCompany" accept-charset="utf-8">' .
        arraySelect($allowedCompanies, 'project_company', 'size="1" class="text" onChange="document.pickCompany.submit();"', $company_id, false) .
        '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCell('<form action="?m=projects" method="post" name="userIdForm" accept-charset="utf-8">' .
        arraySelect($user_list, 'project_owner', 'size="1" class="text" onChange="document.userIdForm.submit();"', $owner, false) .
        '</form>');
$titleBlock->addCell($AppUI->_('Owner') . ':');
if ($canAuthor) {
	$titleBlock->addCell('<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new project') . '">', '', '<form action="?m=projects&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->addCell('<span title="' . $AppUI->_('Projects') . '::' . $AppUI->_('Print projects list') . '.">' .
        '<a href="javascript: void(0);" onclick ="window.open(\'index.php?m=projects&a=printprojects&dialog=1&suppressHeaders=1&company_id='.$company_id.'&project_type='.$project_type.'&project_owner='.$owner.'\', \'printprojects\',\'width=1200, height=600, menubar=1, scrollbars=1\')">
		<img src="' . w2PfindImage('printer.png') . '" border="0" width="22" heigth"22" alt="" />
		</a></span>');

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

foreach ($project_statuses as $project_status) {
	$tabBox->add('vw_idx_projects', mb_trim($project_status), true);
}
$min_view = true;
$tabBox->add('viewgantt', 'Gantt');
$tabBox->show();