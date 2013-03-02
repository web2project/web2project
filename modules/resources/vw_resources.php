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
}
?>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
    <?php
if (count($items)) {
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

    $resource_types = w2PgetSysVal('ResourceTypes');
    $customLookups = array('resource_type' => $resource_types);

    foreach ($items as $row) {
        $htmlHelper->stageRowData($row);
        echo '<tr>';
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