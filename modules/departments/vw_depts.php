<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $search_string, $owner_filter_id, $tab, $orderby, $orderdir;

$type_filter = $tab - 1;

$dept = new CDepartment();
$items = $dept->getFilteredDepartmentList(null, $type_filter, $search_string, $owner_filter_id, $orderby, $orderdir);

$module = new w2p_System_Module();
$fields = $module->loadSettings('departments', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('dept_name', 'countp', 'inactive', 'dept_type');
    $fieldNames = array('Department Name', 'Active Projects', 'Archived Projects', 'Type');

    $module->storeSettings('departments', 'index_list', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}

$deptTypes = w2PgetSysVal('DepartmentType');
$customLookups = array('dept_type' => $deptTypes);

$listTable = new w2p_Output_ListTable($AppUI);
echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();