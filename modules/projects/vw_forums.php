<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id;
// Forums mini-table in project view action

$forums = CProject::getForums($AppUI, $project_id);
?>
<a name="forums-projects_view"> </a>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl list">
    <tr>
        <?php
        $fieldList = array();
        $fieldNames = array();

        $module = new w2p_Core_Module();
        $fields = $module->loadSettings('forums', 'projects_view');

        if (count($fields) > 0) {
            $fieldList = array_keys($fields);
            $fieldNames = array_values($fields);
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('forum_name', 'forum_message_count', 'forum_last_date');
            $fieldNames = array('Forum Name', 'Messages', 'Last Post');

            $module->storeSettings('forums', 'projects_view', $fieldList, $fieldNames);
        }
//TODO: The link below is commented out because this module doesn't support sorting... yet.
        echo '<th></th>';
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=projects&a=view&project_id=<?php echo $project_id; ?>&sort=<?php echo $fieldList[$index]; ?>#forums-projects_view" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        echo '<th></th>';
        ?>
    </tr>
	<?php
    if (count($forums) > 0) {
        $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
        $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

        foreach ($forums as $row) {
            ?>
            <tr bgcolor="white" valign="top">
                <td nowrap="nowrap" align="center">
                    <?php if ($row["forum_owner"] == $AppUI->user_id) { ?>
                        <a href="./index.php?m=forums&a=addedit&forum_id=<?php echo $row['forum_id']; ?>"><img src="<?php echo w2PfindImage('icons/pencil.gif'); ?>" alt="expand forum" border="0" width=12 height=12></a>
                    <?php } ?>
                </td>
                <?php
                $htmlHelper->stageRowData($row);
                foreach ($fieldList as $index => $column) {
                    echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
                }
                ?>
                <td></td>
            </tr>
        <?php 
        }
    } ?>
</table>