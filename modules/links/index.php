<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('LinkIdxTab', $_GET, 'tab', 0);

if (isset($_REQUEST['project_id'])) {
	$AppUI->setState('LinkIdxProject', w2PgetParam($_REQUEST, 'project_id', null));
}
$project_id = $AppUI->getState('LinkIdxProject') !== null ? $AppUI->getState('LinkIdxProject') : 0;

// get the list of visible companies
$extra = array('from' => 'links', 'where' => 'projects.project_id = link_project');

$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$projects = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_JS)), $projects);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Links', 'folder5.png', $m, "$m.$a");

$search = '';
$titleBlock->addCell('<form action="?m=links" method="post" id="searchfilter" accept-charset="utf-8"><input type="text" class="text" SIZE="10" name="search" onChange="document.searchfilter.submit();" value=' . "'$search'" . 'title="' . $AppUI->_('Search in name and description fields', UI_OUTPUT_JS) . '"/>' . '</form>');
$titleBlock->addCell($AppUI->_('Search') . ':');
$titleBlock->addCell('<form name="pickProject" action="?m=links" method="post" accept-charset="utf-8">' . arraySelect($projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id) . '</form>');
$titleBlock->addCell($AppUI->_('Filter') . ':');
if ($canEdit) {
	$titleBlock->addCell('<form action="?m=links&a=addedit" method="post" accept-charset="utf-8"><input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new link') . '"></form>');
}
$titleBlock->show();

$linkTypes = w2PgetSysVal('LinkType');

$tabBox = new CTabBox('?m=links', W2P_BASE_DIR . '/modules/links/', $tab);
if ($tabBox->isTabbed()) {
	array_unshift($linkTypes, $AppUI->_('All Links', UI_OUTPUT_RAW));
}
foreach ($linkTypes as $link_type) {
	$tabBox->add('index_table', $link_type);
}
$showProject = true;
$tabBox->show();