<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

// retrieve any state parameters
if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('ForumVwOrderDir') ? ($AppUI->getState('ForumVwOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('ForumVwOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('ForumVwOrderDir', $orderdir);
}
$orderby = $AppUI->getState('ForumVwOrderBy') ? $AppUI->getState('ForumVwOrderBy') : 'latest_reply';
$orderdir = $AppUI->getState('ForumVwOrderDir') ? $AppUI->getState('ForumVwOrderDir') : 'desc';

//Pull All Messages
$q = new w2p_Database_Query;
$q->addTable('forum_messages', 'fm1');
$q->addQuery('fm1.*, u.*, fm1.message_title as message_name, fm1.message_forum as forum_id');
$q->addQuery('COUNT(distinct fm2.message_id) AS replies');
$q->addQuery('MAX(fm2.message_date) AS latest_reply');
$q->addQuery('user_username, contact_first_name, contact_last_name, contact_display_name as contact_name, watch_user');
$q->addQuery('count(distinct v1.visit_message) as reply_visits');
$q->addQuery('v1.visit_user');
$q->leftJoin('users', 'u', 'fm1.message_author = u.user_id');
$q->leftJoin('contacts', 'con', 'contact_id = user_contact');
$q->leftJoin('forum_messages', 'fm2', 'fm1.message_id = fm2.message_parent');
$q->leftJoin('forum_watch', 'fw', 'watch_user = ' . (int)$AppUI->user_id . ' AND watch_topic = fm1.message_id');
$q->leftJoin('forum_visits', 'v1', 'v1.visit_user = ' . (int)$AppUI->user_id . ' AND v1.visit_message = fm1.message_id');
$q->addWhere('fm1.message_forum = ' . (int)$forum_id);

switch ($f) {
	case 1:
		$q->addWhere('watch_user IS NOT NULL');
		break;
	case 2:
		$q->addWhere('(NOW() < DATE_ADD(fm2.message_date, INTERVAL 30 DAY) OR NOW() < DATE_ADD(fm1.message_date, INTERVAL 30 DAY))');
		break;
}
$q->addGroup('fm1.message_id, fm1.message_parent');
$q->addOrder($orderby . ' ' . $orderdir);
$topics = $q->loadList();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
//$htmlHelper->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('forums', 'view_topics');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('watch_user', 'message_name',
        'message_author', 'replies', 'latest_reply');
    $fieldNames = array('Watch', 'Topics',
        'Author', 'Replies', 'Last Post');

    $module->storeSettings('forums', 'view_topics', $fieldList, $fieldNames);
}
?>

<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<form name="watcher" action="?m=forums&a=view&forum_id=<?php echo $forum_id; ?>&f=<?php echo $f; ?>" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_watch_forum" />
    <input type="hidden" name="watch" value="topic" />
    <table class="tbl list">
        <tr>
            <th></th>
            <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?= $AppUI->_($fieldNames[$index]) ?></th>
            <?php } ?>
        </tr>
    <?php
    foreach ($topics as $row) {
        if ($row["message_parent"] < 0) { ?>
            <tr bgcolor="white" valign="top">
                <td class="data watch">
                    <input type="checkbox" name="forum_<?php echo $row['message_id']; ?>" <?php echo $row['watch_user'] ? 'checked="checked"' : ''; ?> />
                </td>
                <?php
                $htmlHelper->stageRowData($row);
                foreach ($fieldList as $index => $column) {
                    echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]], $customLookups);
                }
                ?>
            </tr>
            <?php
        }
    } ?>
    </table>

    <input type="submit" class="button" value="<?= $AppUI->_('update watches') ?>" />
</form>