<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

$perms = &$AppUI->acl();

$canRead = $perms->checkModuleItem('forums', 'view', null);
if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();

// retrieve any state parameters
if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('ForumIdxOrderDir') ? ($AppUI->getState('ForumIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('ForumIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('ForumIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('ForumIdxOrderBy') ? $AppUI->getState('ForumIdxOrderBy') : 'forum_name';
$orderdir = $AppUI->getState('ForumIdxOrderDir') ? $AppUI->getState('ForumIdxOrderDir') : 'asc';

$perms = &$AppUI->acl();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$f = w2PgetParam($_POST, 'f', 0);

$forum = new CForum();
$forums = $forum->getAllowedForums($AppUI->user_id, $AppUI->user_company, $f, $orderby, $orderdir);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Forums', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onChange="document.forum_filter.submit();"', $f, true), '', '<form name="forum_filter" action="?m=forums" method="post" accept-charset="utf-8">', '</form>');

$canAdd = canAdd($m);
if ($canAdd) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new forum') . '">', '', '<form action="?m=forums&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->show();
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$fieldList = array();
$fieldNames = array();
$fields = $module->loadSettings('files', 'index_list');
if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('', 'watch_user', 'forum_name', 'forum_topics',
        'forum_replies', 'forum_last_date');
    $fieldNames = array('', 'Watch', 'Forum Name', 'Topics',
        'Replies', 'Last Post Info');

    $module->storeSettings('files', 'index_list', $fieldList, $fieldNames);
}
?>

<form name="watcher" action="./index.php?m=forums&f=<?php echo $f; ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_watch_forum" />
	<input type="hidden" name="watch" value="forum" />

    <table class="tbl list">
        <tr>
            <?php
            foreach ($fieldNames as $index => $name) {
                ?><th nowrap="nowrap">
                    <a href="?m=forums&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">
                        <?php echo $AppUI->_($fieldNames[$index]); ?>
                    </a>
                </th><?php
            }
            ?>
        </tr>
        <?php
        $p = '';
        $now = new w2p_Utilities_Date();
        foreach ($forums as $row) {
            $message_date = intval($row['forum_last_date']) ? new w2p_Utilities_Date($row['forum_last_date']) : null;

            if ($p != $row['forum_project']) {
                $create_date = $AppUI->formatTZAwareTime($row['forum_create_date'], $df);
                $forum_project_name = ($row['project_name']) ? $row['project_name'] : 'No associated project';
                $forum_project_color = ($row['project_color_identifier']) ? bestColor($row['project_color_identifier']) : '';
                ?>
                <tr>
                    <td colspan="6" style="background-color:#<?php echo $row['project_color_identifier']; ?>">
                        <a href="?m=projects&a=view&project_id=<?php echo $row['forum_project']; ?>">
                            <font color="<?php echo $forum_project_color; ?>">
                            <strong><?php echo $forum_project_name; ?></strong>
                            </font>
                        </a>
                    </td>
                </tr>
                <?php
                $p = $row['forum_project'];
            }
            ?>
            <tr>
                <td nowrap="nowrap" align="center">
                    <?php if ($row["forum_owner"] == $AppUI->user_id || canAdd('forums')) { ?>
                        <a href="?m=forums&a=addedit&forum_id=<?php echo $row['forum_id']; ?>" title="<?php echo $AppUI->_('edit'); ?>">
                        <?php echo w2PshowImage('icons/stock_edit-16.png', 16, 16, ''); ?>
                        </a>
                    <?php }
                    if ($row['visit_count'] != $row['message_count']) {
                        echo '&nbsp;' . w2PshowImage('icons/stock_new_small.png', false, false, 'You have unread messages in this forum');
                    } ?>
                </td>

                <td nowrap="nowrap" align="center">
                    <input type="checkbox" name="forum_<?php echo $row['forum_id']; ?>" <?php echo $row['watch_user'] ? 'checked="checked"' : ''; ?> />
                </td>

                <td>
                    <span style="font-size:10pt;font-weight:bold">
                        <a href="?m=forums&a=viewer&forum_id=<?php echo $row['forum_id']; ?>"><?php echo $row['forum_name']; ?></a>
                    </span>
                    <br /><?php echo w2p_textarea($row['forum_description']); ?>
                    <br /><font color="#777777"><?php echo $AppUI->_('Owner') . ' ' . $row['owner_name']; ?>,
                    <?php echo $AppUI->_('Started') . ' ' . $create_date; ?>
                    </font>
                </td>
                <?php echo $htmlHelper->createCell('topic_count', $row['forum_topics']); ?>
                <?php echo $htmlHelper->createCell('reply_count', $row['forum_replies']); ?>
                <td width="225">
                    <?php
                    if ($message_date !== null) {
                        echo $AppUI->formatTZAwareTime($row['forum_last_date'], $df . ' ' . $tf);
                    } else {
                        echo $AppUI->_('No posts');
                    } ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="std">

        <tr>
            <td align="left">
                <input type="submit" class="button" value="<?php echo $AppUI->_('update watches'); ?>" />
            </td>
        </tr>
    </table>
</form>