<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $tab;

$obj = new CResource();
$where = ($tab) ? 'resource_type = '. $tab : '';
$items = $obj->loadAll('resource_name', $where);

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl list">
    <tr>
        <?php
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
            $fieldList = array('resource_key', 'resource_name', 'resource_max_allocation');
            $fieldNames = array('Identifier', 'Resource Name', 'Max Alloc %');

            $module->storeSettings('resources', 'index_list', $fieldList, $fieldNames);
        }
//TODO: The link below is commented out because this module doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=links&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>
    <?php
    foreach ($items as $row) {
        $htmlHelper->stageRowData($row);
        ?><tr><?php
        foreach ($fieldList as $index => $column) {
            echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
        }
        ?></tr><?php
    } ?>
</table>