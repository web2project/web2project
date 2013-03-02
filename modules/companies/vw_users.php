<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View User sub-table
##

global $AppUI, $company_id;

$userList = CCompany::getUsers($AppUI, $company_id);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('admin', 'company_view');
if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('user_username', 'contact_name', 'user_type');
    $fieldNames = array('Username', 'Name', 'Type');

    $module->storeSettings('admin', 'company_view', $fieldList, $fieldNames);
}
?>
<a name="users-company_view"> </a>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php

if (count($userList) > 0) {
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    $user_types = w2PgetSysVal('UserType');
    $customLookups = array('user_type' => $user_types);

    foreach ($userList as $row) {
        echo '<tr>';
        $htmlHelper->stageRowData($row);
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