<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View Departments sub-table
##

global $AppUI, $company_id, $canEdit;

$depts = CCompany::getDepartments($AppUI, $company_id);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('departments', 'company_view');
if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('dept_name', 'dept_users');
    $fieldNames = array('Name', 'Users');

    $module->storeSettings('departments', 'company_view', $fieldList, $fieldNames);
}
?>
<a name="departments-company_view"> </a>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php
if (count($depts)) {
	$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    $dept_types = w2PgetSysVal('DepartmentType');
    $customLookups = array('dept_type' => $dept_types);

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