<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $search_string, $owner_filter_id, $tab, $orderby, $orderdir;

$type_filter = $tab - 1;

$company = new CCompany();
$items = $company->getCompanyList(null, $type_filter, $search_string, $owner_filter_id, $orderby, $orderdir);

$module = new w2p_System_Module();
$fields = $module->loadSettings('companies', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('company_name', 'countp', 'inactive', 'company_type');
    $fieldNames = array('Company Name', 'Active Projects', 'Archived Projects', 'Type');

    $module->storeSettings('companies', 'index_list', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}

$company_types = w2PgetSysVal('CompanyType');
$customLookups = array('company_type' => $company_types);

include $AppUI->getTheme()->resolveTemplate('companies/list');