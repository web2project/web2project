<?php /* $Id$ $URL$ */
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
$q = new DBQuery;
$q->addTable('forum_messages', 'fm1');
$q->addQuery('fm1.*');
$q->addQuery('COUNT(distinct fm2.message_id) AS replies');
$q->addQuery('MAX(fm2.message_date) AS latest_reply');
$q->addQuery('user_username, contact_first_name, contact_last_name, watch_user');
$q->addQuery('count(distinct v1.visit_message) as reply_visits');
$q->addQuery('v1.visit_user');
$q->addJoin('users', 'u', 'fm1.message_author = u.user_id', 'inner');
$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
$q->addJoin('forum_messages', 'fm2', 'fm1.message_id = fm2.message_parent');
$q->addJoin('forum_watch', 'fw', 'watch_user = ' . (int)$AppUI->user_id . ' AND watch_topic = fm1.message_id');
$q->addJoin('forum_visits', 'v1', 'v1.visit_user = ' . (int)$AppUI->user_id . ' AND v1.visit_message = fm1.message_id');
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

$crumbs = array();
$crumbs['?m=forums'] = 'forums list';
?>
<br />
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<table width="100%" cellspacing="1" cellpadding="2" border="0" class="tbl">
<form name="watcher" action="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&f=<?php echo $f; ?>" method="post">
<tr><td colspan="5">
	<table width="100%" cellspacing="1" cellpadding="2" border="0">
	<tr>
		<td align="left" nowrap="nowrap"><?php echo breadCrumbs($crumbs); ?></td>
		<td width="100%" align="right">
		<?php if ($canEdit) { ?>
			<input type="button" class="button" style="width:120;" value="<?php echo $AppUI->_('start a new topic'); ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&post_message=1';">
		<?php } ?>
		</td>
	</tr>
	</table>
</td></tr>
<tr>
	<th><a href="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&orderby=watch_user" class="hdr"><?php echo $AppUI->_('Watch'); ?></a></th>
	<th><a href="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&orderby=message_title" class="hdr"><?php echo $AppUI->_('Topics'); ?></a></th>
	<th><a href="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&orderby=user_username" class="hdr"><?php echo $AppUI->_('Author'); ?></a></th>
	<th><a href="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&orderby=replies" class="hdr"><?php echo $AppUI->_('Replies'); ?></a></th>
	<th><a href="?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&orderby=latest_reply" class="hdr"><?php echo $AppUI->_('Last Post'); ?></a></th>
</tr>
<?php

$now = new CDate();

foreach ($topics as $row) {
	$last = intval($row['latest_reply']) ? new CDate($row['latest_reply']) : null;

	//JBF limit displayed messages to first-in-thread
	if ($row["message_parent"] < 0) { ?>
<tr>
	<td nowrap="nowrap" align="center" width="1%">
		<input type="checkbox" name="forum_<?php echo $row['message_id']; ?>" <?php echo $row['watch_user'] ? 'checked="checked"' : ''; ?> />
	</td>
	<td>
		<?php
		if ($row['visit_user'] != $AppUI->user_id || $row['reply_visits'] != $row['replies']) {
			echo w2PshowImage('icons/stock_new_small.png', false, false, 'You have unread posts in this topic');
		}
?>
		<span style="font-size:10pt;">
		<a href="?m=forums&a=viewer&forum_id=<?php echo $forum_id . '&message_id=' . $row["message_id"]; ?>"><?php echo $row['message_title']; ?></a>
		</span>
	</td>
	<td bgcolor="#dddddd" width="10%"><?php echo $row['contact_first_name'] . ' ' . $row['contact_last_name']; ?></td>
	<td align="center" width="10%"><?php echo $row['replies']; ?></td>
	<td bgcolor="#dddddd" width="150" nowrap="nowrap">
<?php if ($row['latest_reply']) {
			echo $last->format($df . ' ' . $tf) . '<br /><font color="#999966">(';

			$span = new Date_Span();
			$span->setFromDateDiff($now, $last);

			printf('%.1f', $span->format('%d'));
			echo ' ' . $AppUI->_('days ago');

			echo ')</font>';
		} else {
			echo $AppUI->_('No replies');
		}
?>
	</td>
</tr>
<?php
	}
} ?>
</table>

<table width="100%" border="0" cellpadding="0" cellspacing="1" class="std">
<input type="hidden" name="dosql" value="do_watch_forum" />
<input type="hidden" name="watch" value="topic" />
<tr>
	<td align="left">
		<input type="submit" class="button" value="<?php echo $AppUI->_('update watches'); ?>" />
	</td>
</tr>
</form>
</table>