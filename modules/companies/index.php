<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('CompanyIdxTab', $_GET, 'tab', 0);

if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('CompIdxOrderDir') ? ($AppUI->getState('CompIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('CompIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('CompIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('CompIdxOrderBy') ? $AppUI->getState('CompIdxOrderBy') : 'company_name';
$orderdir = $AppUI->getState('CompIdxOrderDir') ? $AppUI->getState('CompIdxOrderDir') : 'asc';

if (isset($_REQUEST['owner_filter_id'])) {
	$AppUI->setState('owner_filter_id', w2PgetParam($_REQUEST, 'owner_filter_id', null));
	$owner_filter_id = w2PgetParam($_REQUEST, 'owner_filter_id', null);
} else {
	$owner_filter_id = $AppUI->getState('owner_filter_id');
	if (!isset($owner_filter_id)) {
		$owner_filter_id = 0; //By default show all companies instead of $AppUI->user_id current user.
		$AppUI->setState('owner_filter_id', $owner_filter_id);
	}
}

$search_string = w2PgetParam($_POST, 'search_string', '');
if ($search_string != '') {
	$search_string = ($search_string == '-1') ? '' : $search_string;
	$AppUI->setState('dept_search_string', $search_string);
}
$search_string = w2PformSafe($search_string, true);

$perms = &$AppUI->acl();

$baseArray = array(0 => $AppUI->_('All', UI_OUTPUT_RAW));
$allowedArray = $perms->getPermittedUsers('companies');
$owner_list = is_array($allowedArray) ? ($baseArray + $allowedArray) : $baseArray;

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Companies', 'handshake.png', $m, $m . '.' . $a);
$titleBlock->addCell('<form name="searchform" action="?m=companies" method="post" accept-charset="utf-8">
                    <input type="text" class="text" name="search_string" value="' . $search_string . '" /></form>');
$titleBlock->addCell($AppUI->_('Search') . ':');

$titleBlock->addCell('<form name="searchform2" action="?m=companies" method="post" accept-charset="utf-8">' .
        arraySelect($owner_list, 'owner_filter_id', 'onChange="document.searchform2.submit()" size="1" class="text"', $owner_filter_id) .
        '</form>');
$titleBlock->addCell($AppUI->_('Owner filter') . ':');
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new company') . '">', '', '<form action="?m=companies&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->show();

// load the company types
$companyTypes = w2PgetSysVal('CompanyType');

$tabBox = new CTabBox('?m=companies', W2P_BASE_DIR . '/modules/companies/', $tab);
if ($tabBox->isTabbed()) {
	array_unshift($companyTypes, $AppUI->_('All Companies', UI_OUTPUT_RAW));
}

foreach ($companyTypes as $type_name) {
	$tabBox->add('vw_companies', $type_name);
}
$tabBox->show();