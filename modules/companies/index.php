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

$owner_filter_id = $AppUI->processIntState('owner_filter_id', $_POST, 'owner_filter_id', 0);

$search_string = w2PgetParam($_POST, 'search_string', '');
$search_string = w2PformSafe($search_string, true);

$company = new CCompany();
$canCreate = $company->canCreate();

$perms = &$AppUI->acl();
$baseArray = array(0 => $AppUI->_('All', UI_OUTPUT_RAW));
$allowedArray = $perms->getPermittedUsers('companies');
$owner_list = is_array($allowedArray) ? ($baseArray + $allowedArray) : $baseArray;

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Companies', 'icon.png', $m);
$titleBlock->addSearchCell($search_string);
$titleBlock->addFilterCell('Owner', 'owner_filter_id', $owner_list, $owner_filter_id);

if ($canCreate) {
    $titleBlock->addButton('new company', '?m=companies&a=addedit');
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