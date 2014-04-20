<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $department;

$items = $department->contacts($department->dept_id);

$module = new w2p_System_Module();
$fields = $module->loadSettings('contacts', 'department_view');

if (0 == count($fields)) {
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