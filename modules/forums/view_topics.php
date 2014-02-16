<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

// retrieve any state parameters
if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('ForumVwOrderDir') ? ($AppUI->getState('ForumVwOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('ForumVwOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('ForumVwOrderDir', $orderdir);
}
$orderby = $AppUI->getState('ForumVwOrderBy') ? $AppUI->getState('ForumVwOrderBy') : 'latest_reply';
$orderdir = $AppUI->getState('ForumVwOrderDir') ? $AppUI->getState('ForumVwOrderDir') : 'desc';

$items = __extract_from_forums_view_topics($AppUI, $forum_id, $f, $orderby, $orderdir);

$crumbs = array();
$crumbs['?m=forums'] = 'forums list';

$module = new w2p_System_Module();
$fields = $module->loadSettings('forums', 'view_topics');

if (0 == count($fields)) {
    $fieldList = array('message_name', 'message_author', 'replies', 'latest_reply');
    $fieldNames = array('Topics', 'Author', 'Replies', 'Last Post');

    $module->storeSettings('forums', 'view_topics', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}
?>
<br />
<?php echo $AppUI->getTheme()->styleRenderBoxTop(); ?>
<form name="watcher" action="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&f=<?php echo $f; ?>" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_watch_forum" />
    <input type="hidden" name="watch" value="topic" />
    <?php

    $listHelper = new w2p_Output_ListTable($AppUI);
    $listHelper->addBefore('watch', 'message_id');

    echo $listHelper->startTable();
    echo $listHelper->buildHeader($fields);
    echo $listHelper->buildRows($items);

    ?>
        <tr>
            <td colspan="12">
                <div class="left">
                    <input type="submit" class="button" value="<?php echo $AppUI->_('update watches'); ?>" />
                </div>

                <?php if ($canAuthor) { ?>
                    <input type="button" class="button right" value="<?php echo $AppUI->_('start a new topic'); ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&post_message=1';" />
                <?php } ?>
            </td>
        </tr>
    </table>
</form>