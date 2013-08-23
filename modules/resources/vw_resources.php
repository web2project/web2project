<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $tab;

$obj = new CResource();
$where = ($tab) ? 'resource_type = '. $tab : '';
$items = $obj->loadAll('resource_name', $where);

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('resources', 'index_list');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('resource_key', 'resource_name', 'resource_max_allocation',
        'resource_type', 'resource_note');
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