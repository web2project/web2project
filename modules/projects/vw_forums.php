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
        $fields = w2p_Core_Module::getSettings('forums', 'projects_view');
        if (count($fields) > 0) {
            foreach ($fields as $field => $text) {
                $fieldList[] = $field;
                $fieldNames[] = $text;
            }
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('', 'forum_name', '', '');
            $fieldNames = array('', 'Forum Name', 'Messages', 'Last Post');
        }
//TODO: The link below is commented out because this module doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=projects&a=view&project_id=<?php echo $project_id; ?>&sort=<?php echo $fieldList[$index]; ?>#forums-projects_view" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>
	<?php
    if (count($forums) > 0) {
        $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
        foreach ($forums as $forumId  => $forum_info) {?>
            <tr>
                <td nowrap="nowrap" align="center">
            <?php
                if ($forum_info["forum_owner"] == $AppUI->user_id) { ?>
                    <a href="./index.php?m=forums&a=addedit&forum_id=<?php echo $forum_info['forum_id']; ?>"><img src="<?php echo w2PfindImage('icons/pencil.gif'); ?>" alt="expand forum" border="0" width=12 height=12></a>
            <?php } ?>
                </td>
                <td nowrap="nowrap"><a href="./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_info["forum_id"]; ?>"><?php echo $forum_info['forum_name']; ?></a></td>
                <?php echo $htmlHelper->createCell('forum_message_count', $forum_info['forum_message_count']); ?>
                <td nowrap="nowrap">
                    <?php echo (intval($forum_info['forum_last_date']) > 0) ? $forum_info['forum_last_date'] : 'n/a'; ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3"><?php echo $forum_info['forum_description']; ?></td>
            </tr>
        <?php 
        }
    } ?>
</table>