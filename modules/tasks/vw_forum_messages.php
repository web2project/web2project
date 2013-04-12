<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $task_id;
// Forum messages mini-table in task view action

$forum_messages = CForum_Message::getTopicsByTask($AppUI, $task_id);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('forum_messages', 'task_view');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('forum_name', 'message_name',
        'message_author', 'replies', 'latest_reply');
    $fieldNames = array('Forum Name', 'Topic',
        'Author', 'Replies', 'Last Post');
    $module->storeSettings('forum_messages', 'task_view', $fieldList, $fieldNames);
}
?>
<a name="forum-messages-tasks_view"> </a>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
	<?php
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    $htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

    foreach ($forum_messages as $row) {
	if (isset($row['forum_name']) && isset($row['message_name'])) {
	    ?>
	    <tr bgcolor="white" valign="top">
	    <?php
	    // hack to remove the message_id param from the forum name, otherwise would jump to the topic
	    $rowclone = $row;
	    unset($rowclone['message_id']);
            foreach ($fieldList as $index => $column) {
		if ($index == 0) {
	   	    $htmlHelper->stageRowData($rowclone);
        	    echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
        	    $htmlHelper->stageRowData($row);
	    	} else {
	            echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
	    	}
            }
            ?>
            </tr>
        <?php
        }
    } ?>
</table>