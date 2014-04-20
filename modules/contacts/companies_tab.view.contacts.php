<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $AppUI, $company;

$items = $company->contacts($company->company_id);

$fieldList = array();
$fieldNames = array();

$module = new w2p_System_Module();
$fields = $module->loadSettings('contacts', 'company_view');

if (0 == count($fields)) {
    $fieldList = array('contact_name', 'contact_job',
        'contact_email', 'contact_phone', 'dept_name');
    $fieldNames = array('Name', 'Job Title', 'Email', 'Phone',
        'Department');

    $module->storeSettings('contacts', 'company_view', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}

?><a name="contacts-company_view"> </a><?php

include $AppUI->getTheme()->resolveTemplate('companies/vw_contacts');