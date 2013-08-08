<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $dept_id, $dept, $company_id;

$items = CDepartment::getContactList($AppUI, $dept_id);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('contacts', 'department_view');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('contact_name', 'contact_job',
        'contact_email', 'contact_phone', 'dept_name');
    $fieldNames = array('Name', 'Job Title', 'Email', 'Phone',
        'Department');

    $module->storeSettings('contacts', 'department_view', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}

?><a name="contacts-department_view"> </a><?php

$listTable = new w2p_Output_ListTable($AppUI);
echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();