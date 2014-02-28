<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $tab;

$obj = new CResource();
$where = ($tab) ? 'resource_type = '. $tab : '';
$items = $obj->loadAll('resource_name', $where);

$module = new w2p_System_Module();
$fields = $module->loadSettings('resources', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('resource_key', 'resource_name', 'resource_max_allocation',
        'resource_type', 'resource_description');
    $fieldNames = array('Identifier', 'Resource Name', 'Max Alloc %',
        'Type', 'Notes');

    $module->storeSettings('resources', 'index_list', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}

$resource_types = w2PgetSysVal('ResourceTypes');
$customLookups = array('resource_type' => $resource_types);

$listTable = new w2p_Output_ListTable($AppUI);
echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();