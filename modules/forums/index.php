<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

$perms = &$AppUI->acl();

$canRead = $perms->checkModuleItem('forums', 'view', null);
if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
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
$titleBlock->addCell('<form name="forum_filter" action="?m=forums" method="post" accept-charset="utf-8">' . arraySelect($filters, 'f', 'size="1" class="text" onChange="document.forum_filter.submit();"', $f, true) . '</form>');
$titleBlock->addCell($AppUI->_('Filter') . ':');

$canAdd = canAdd($m);
if ($canAdd) {
	$titleBlock->addCell('<form action="?m=forums&a=addedit" method="post" accept-charset="utf-8"><input type="submit" class="button" value="' . $AppUI->_('new forum') . '"></form>');
}

//TODO: this is a little hack to make sure the table header gets generated in the show() method below
global $a;
$a = 'list';
// End of little hack

$titleBlock->show();
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$fieldList = array();
$fieldNames = array();
$module = new w2p_Core_Module();
$fields = $module->loadSettings('forums', 'index_list');
if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('watch_user', 'forum_name', 'forum_description', 'forum_owner',
                       'forum_topics', 'forum_replies', 'forum_last_date');
    $fieldNames = array('Watch', 'Forum Name', 'Description', 'Owner', 'Topics',
                        'Replies', 'Last Post Info');

    $module->storeSettings('forums', 'index_list', $fieldList, $fieldNames);
}
$columnCount = 1 + count($fieldList);
?>

<form name="watcher" action="./index.php?m=forums&f=<?php echo $f; ?>" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_watch_forum" />
	<input type="hidden" name="watch" value="forum" />

    <table class="tbl list">
        <tr>
            <?php foreach ($fieldNames as $index => $name) { ?>
            <th nowrap="nowrap"<?= ($index == 0)?' colspan="2"':'' /* first heading extends over icons */ ?>>
                <a href="?m=forums&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
                </a>
            </th>
            <?php } ?>
        </tr>

        <?php
        $p = '';

        foreach ($forums as $row) {
            $htmlHelper->stageRowData($row);

            if ($p != $row['forum_project']) {
                $p = $row['forum_project'];
                $forum_project_name = ($row['project_name']) ? $row['project_name'] : $AppUI->_('No associated project');
                $forum_project_color = ($row['project_color_identifier']) ? bestColor($row['project_color_identifier']) : '';
        ?>
        <tr>
            <td colspan="<?php echo $columnCount; ?>" style="background-color:#<?php echo $row['project_color_identifier']; ?>">
                <a href="?m=projects&a=view&project_id=<?php echo $row['forum_project']; ?>" style="color:<?php echo $forum_project_color; ?>">
                    <strong><?php echo $forum_project_name; ?></strong>
                </a>
            </td>
        </tr>
        <?php } ?>

        <tr<?= ($row['visit_count'] != $row['forum_message_count'])?' class="unread" title="'.$AppUI->_("You have unread messages in this forum").'"' : ''?>>
            <td class="data">
                <?php if ($row["forum_owner"] == $AppUI->user_id || canAdd('forums')) { ?>
                <a href="?m=forums&a=addedit&forum_id=<?php echo $row['forum_id']; ?>" title="<?php echo $AppUI->_('edit'); ?>">
                    <?php echo w2PshowImage('icons/stock_edit-16.png', 16, 16, ''); ?>
                </a>
                <?php } ?>
            </td>

            <?php
            foreach ($fieldNames as $index => $name) {
                if ($fieldList[$index] == 'watch_user') {
                    echo '<td class="data">';
                    echo '<input type="checkbox" name="forum_'.$row['forum_id'].'" '.($row['watch_user'] ? 'checked="checked"' : '').' />';
                    echo '</td>';
                } else {
                    echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]]);
                }
            }
            ?>

        </tr>
        <?php } ?>
    </table>
    <div class="bottomnav">
        <input type="submit" class="button" value="<?php echo $AppUI->_('update watches'); ?>" />
    </div>
</form>