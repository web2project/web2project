<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();
$hideEmail = w2PgetConfig('hide_email_addresses', false);

$messages = $forum->getMessages(null, $forum_id, $message_id, $sort);
?>
<script language="javascript" type="text/javascript">
function delIt(id) {
	var form = document.messageForm;
	if (confirm( '<?= $AppUI->_('forumsDelete'); ?>' )) {
		form.del.value = 1;
		form.message_id.value = id;
		form.submit();
	}
}
</script>

<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>

<form name="messageForm" method="post" action="?m=forums&forum_id=<?= $forum_id ?>" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_id" value="0" />
	<input type="hidden" name="message_parent" value="<?= $message_id ?>" />
</form>

<table border="0" cellpadding="4" cellspacing="1" width="100%" class="std view <?= $viewtype ?>" align="center">
<tr>
	<th><?= $AppUI->_('Author') ?></th>
    <th><?= $AppUI->_('Message') ?></th>
</tr>

<?php
$date = new w2p_Utilities_Date();

$new_messages = array();

foreach ($messages as $row) {
	// Find the parent message - the topic.
	if ($row['message_id'] == $message_id) {
		$topic = $row['message_title'];
    }

	$q = new w2p_Database_Query;
	$q->addTable('forum_messages');
	$q->addTable('users');
	$q->addQuery('DISTINCT contact_first_name, contact_last_name, contact_display_name as contact_name, user_username, contact_email, user_id');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
	$q->addWhere('users.user_id = ' . (int)$row['message_editor']);
	$editor = $q->loadList();

	$date = intval($row['message_date']) ? new w2p_Utilities_Date($row['message_date']) : null;
?>
<tr>
    <td>
        <a href="?m=admin&a=viewuser&user_id=<?= $row['message_author'] ?>"><?= $row['contact_name'] ?></a>

	    <?php if (!$hideEmail) { ?>
        <a href="mailto:<?= $row['contact_email'] ?>">
            <img src="<?= w2PfindImage('email.gif') ?>" width="16" height="16" border="0" alt="email" />
        </a>
	    <?php } ?>

	    <?php if (sizeof($editor) > 0) { ?>
		<div class="editor"><?= $AppUI->_('last edited by') ?>:<br/>
            <a href="?m=admin&a=viewuser&user_id=<?= $editor[0]['user_id'] ?>"><?= $editor[0]['contact_name'] ?></a>
        </div>
	    <?php } ?>

	    <?php
        if ($row['visit_user'] != $AppUI->user_id) {
		    echo w2PshowImage('icons/stock_new_small.png');
		    $new_messages[] = $row['message_id'];
	    }
        ?>
    </td>

    <td>
	    <h3><?= $row['message_title'] ?></h3>
        <?= nl2p($bbparser->qparse($row['message_body'])) ?>
    </td>
</tr>

<tr>
    <td>
        <img src="<?= w2PfindImage('icons/posticon.gif', $m) ?>" alt="date posted" border="0" width="14" height="11">        
        <?= $AppUI->formatTZAwareTime($row['message_date'], $df . ' ' . $tf) ?>
    </td>

    <td>

	    <?php if ($AppUI->user_id == $row['forum_moderated'] || $AppUI->user_id == $row['message_author'] || $canAdminEdit) { ?>
        <a href="?m=forums&a=view&post_message=1&forum_id=<?= $row['message_forum'] ?>&message_parent=<?= $row['message_parent'] ?>&message_id=<?= $row["message_id"] ?>" title="<?= $AppUI->_('Edit') . ' ' . $AppUI->_('Message') ?>">
            <?= w2PshowImage('icons/stock_edit-16.png', '16', '16') ?>
        </a>
	    <?php } ?>

	    <?php if ($AppUI->user_id == $row['forum_moderated'] || $AppUI->user_id == $row['message_author'] || $canAdminEdit) { ?>
        <a href="javascript:delIt(<?= $row['message_id'] ?>)" title="<?= $AppUI->_('delete') ?>">
            <?= w2PshowImage('icons/stock_delete-16.png', '16', '16') ?>
        </a>
	    <?php } ?>

    </td>
</tr>
<?php } ?>

<?php if ($canAuthor) { ?>
<tr class="reply">
    <th><?= $AppUI->_('Post Reply') ?>:</th>
	<td>
        <form name="changeforum" action="?m=forums&forum_id=<?= $forum_id ?>" method="post" accept-charset="utf-8">
	        <input type="hidden" name="dosql" value="do_post_aed" />
	        <input type="hidden" name="del" value="0" />
	        <input type="hidden" name="message_forum" value="<?= $forum_id ?>" />
	        <input type="hidden" name="message_parent" value="<?= $message_id ?>" />
	        <input type="hidden" name="message_published" value="<?= $forum->forum_moderated ? '1' : '0'; ?>" />
	        <input type="hidden" name="message_author" value="<?= $AppUI->user_id ?>" />
	        <input type="hidden" name="message_editor" value="0" />
	        <input type="hidden" name="message_id" value="0" />
            <input type="hidden" name="message_title" value="<?= 'Re: '.$topic ?>" />
            <textarea cols="60" name="message_body"></textarea>
            <input type="submit" value="<?= $AppUI->_('submit') ?>" class=button />
        </form>
	</td>
</tr>
<?php } ?>

</table>
<?php
// Now we need to update the forum visits with the new messages so they don't show again.
foreach ($new_messages as $msg_id) {
	$q = new w2p_Database_Query;
	$q->addTable('forum_visits');
	$q->addInsert('visit_user', $AppUI->user_id);
	$q->addInsert('visit_forum', $forum_id);
	$q->addInsert('visit_message', $msg_id);
	$q->addInsert('visit_date', $date->getDate());
	$q->exec();
	$q->clear();
}