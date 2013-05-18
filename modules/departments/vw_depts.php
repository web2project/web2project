<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $search_string, $owner_filter_id, $tab, $orderby, $orderdir;

$type_filter = $tab - 1;

$dept = new CDepartment();
$depts = $dept->getFilteredDepartmentList(null, $type_filter, $search_string, $owner_filter_id, $orderby, $orderdir);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('departments', 'index_list');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('dept_name', 'countp', 'inactive', 'dept_type');
    $fieldNames = array('Department Name', 'Active Projects', 'Archived Projects', 'Type');

    $module->storeSettings('departments', 'index_list', $fieldList, $fieldNames);
}
?>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php
if (count($depts)) {
	$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    $deptTypes = w2PgetSysVal('DepartmentType');
    $customLookups = array('dept_type' => $deptTypes);

    foreach ($depts as $row) {
        echo '<tr>';
        $htmlHelper->stageRowData($row);
//TODO: how do we tweak this to get the parent/child relationship to display?
        foreach ($fieldList as $index => $column) {
            echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
        }
        echo '</tr>';
	}
} else {
    echo '<tr><td colspan="'.count($fieldNames).'">' . $AppUI->_('No data available') . '</td></tr>';
}
?>
</table>