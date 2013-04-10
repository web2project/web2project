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
$q->addQuery('v1.visit_user, fw.notify_by_email');
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

<script language="javascript" type="text/javascript">

function displayEMailCtrls(index) {
	var ctl = document.getElementById('watch_' + index);
	var span = document.getElementById('span_' + index);
	var email = document.getElementById('email_' + index);

	span.style.display = ctl.checked ? 'inline' : 'none';
	if (!ctl.checked) {
		email.checked = false;
	}
}

</script>

<br />
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<form name="watcher" action="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&f=<?php echo $f; ?>" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_watch_forum" />
    <input type="hidden" name="watch" value="topic" />
    <table class="tbl list">
        <tr><td colspan="25">
            <table width="100%" cellspacing="1" cellpadding="2" border="0">
            <tr>
                <td align="left" nowrap="nowrap">
		    <?php
                    $titleBlock = new w2p_Theme_TitleBlock('', '', $m, "$m.$a");
                    $titleBlock->addCrumb('?m=forums', 'forums list');
                    $titleBlock->show();
		    ?>
                </td>
		<td align="center" width="100%"><h1><?php echo $AppUI->_('Topics'); ?></h1></td>
                <td width="25%" align="right">
                <?php if ($canAuthor) { ?>
                    <input type="button" class="button" value="<?php echo $AppUI->_('start a new topic'); ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&post_message=1';" />
                <?php } ?>
                </td>
            </tr>
            </table>
        </td></tr>
        <tr>
            <?php foreach ($fieldNames as $index => $name) { ?>
                <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
            <?php } ?>
        </tr>
    <?php
    foreach ($topics as $row) {
        if ($row["message_parent"] < 0) { ?>
            <tr bgcolor="white" valign="top">
                <?php
//TODO: add the checkbox
                $htmlHelper->stageRowData($row);
                foreach ($fieldList as $index => $column) {
		    if ($column == 'watch_user') {
		?>
                <td nowrap="nowrap" align="center" width="1%">
                    <input type="checkbox" name="watch_<?php echo $row['message_id']; ?>" id="watch_<?php echo $row['message_id']; ?>" <?php echo $row['watch_user'] ? 'checked="checked"' : ''; ?> onclick="displayEMailCtrls(<?php echo $row['message_id']; ?>)"/>
		    <span id="span_<?php echo $row['message_id']; ?>" style="display: <?php echo $row['watch_user'] ? 'inline' : 'none'; ?>;">
		    &nbsp;/&nbsp;
                    <input type="checkbox" name="email_<?php echo $row['message_id']; ?>" id="email_<?php echo $row['message_id']; ?>" <?php echo $row['notify_by_email'] ? 'checked="checked"' : ''; ?> />
		    </span>
                </td>
		<?php
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

    <table width="100%" border="0" cellpadding="0" cellspacing="1" class="std">
        <tr>
            <td align="right" width="100%">
                <input type="submit" class="button" value="<?php echo $AppUI->_('update watches'); ?>" />
            </td>
        </tr>
    </table>
</form>