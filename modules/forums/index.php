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
$titleBlock = new CTitleBlock('Forums', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onChange="document.forum_filter.submit();"', $f, true), '', '<form name="forum_filter" action="?m=forums" method="post" accept-charset="utf-8">', '</form>');

$canAdd = canAdd($m);
if ($canAdd) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new forum') . '">', '', '<form action="?m=forums&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->show();
?>

<form name="watcher" action="./index.php?m=forums&f=<?php echo $f; ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_watch_forum" />
	<input type="hidden" name="watch" value="forum" />

    <table width="100%" cellspacing="1" cellpadding="2" border="0" class="tbl">
        <tr>
            <th nowrap="nowrap">&nbsp;</th>
            <th nowrap="nowrap" width="25"><a href="?m=forums&orderby=watch_user" class="hdr"><?php echo $AppUI->_('Watch'); ?></a></th>
            <th nowrap="nowrap"><a href="?m=forums&orderby=forum_name" class="hdr"><?php echo $AppUI->_('Forum Name'); ?></a></th>
            <th nowrap="nowrap" width="50" align="center"><a href="?m=forums&orderby=forum_topics" class="hdr"><?php echo $AppUI->_('Topics'); ?></a></th>
            <th nowrap="nowrap" width="50" align="center"><a href="?m=forums&orderby=forum_replies" class="hdr"><?php echo $AppUI->_('Replies'); ?></a></th>
            <th nowrap="nowrap" width="200"><a href="?m=forums&orderby=forum_last_date" class="hdr"><?php echo $AppUI->_('Last Post Info'); ?></a></th>
        </tr>
        <?php
        $p = '';
        $now = new CDate();
        foreach ($forums as $row) {
            $message_date = intval($row['forum_last_date']) ? new CDate($row['forum_last_date']) : null;

            if ($p != $row['forum_project']) {
                $create_date = intval($row['forum_create_date']) ? new CDate($row['forum_create_date']) : null;
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
                    <?php echo $AppUI->_('Started') . ' ' . $create_date->format($df); ?>
                    </font>
                </td>
                <td nowrap="nowrap" align="center"><?php echo $row['forum_topics']; ?></td>
                <td nowrap="nowrap" align="center"><?php echo $row['forum_replies']; ?></td>
                <td width="225">
                    <?php
                    if ($message_date !== null) {
                        echo $message_date->format($df . ' ' . $tf);
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