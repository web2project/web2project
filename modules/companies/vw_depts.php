<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View Departments sub-table
##

global $AppUI, $company_id, $canEdit;

$depts = CCompany::getDepartments($AppUI, $company_id);
?>
<a name="departments-company_view"> </a>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl list">
    <tr>
        <?php
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
//TODO: The link below is commented out because this view doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=companies&a=view&company_id=<?php echo $company_id; ?>&sort=<?php echo $fieldList[$index]; ?>#departments-company_view" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>
<?php
if (count($depts)) {
	$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

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

echo '
<tr>
	<td colspan="'.count($fieldNames).'" nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	echo '<input type="button" class=button value="' . $AppUI->_('new department') . '" onclick="javascript:window.location=\'./index.php?m=departments&amp;a=addedit&amp;company_id=' . $company_id . '\';" />';
}
echo '</td></tr>';
?>
</table>