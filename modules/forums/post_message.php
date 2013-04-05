<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$canAddEdit = $forum->canAddEdit();
$canAdd = $forum->canCreate();
$canEdit = $forum->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

if (!$forum) {
	$AppUI->setMsg('Forum');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect('m=forums');
} else {
	$AppUI->savePlace();
}

// Build a back-url for when the back button is pressed
$back_url_params = array();
foreach ($_GET as $k => $v) {
	if ($k != 'post_message' && $k != 'message_id') {
        if ($k == 'message_parent') $k = 'message_id';
		$back_url_params[] = "$k=$v";
	}
}
$back_url = implode('&', $back_url_params);

//pull message information
$message = new CForum_Message();
$message->load($message_id);

//pull message information from last response
if ($message_parent != -1) {
    $last_message = new CForum_Message();
    $last_message->load($message_parent);
}

?>
<script language="javascript" type="text/javascript">
function submitIt(){
	var form = document.changeforum;
	if (form.message_title.value.search(/^\s*$/) >= 0 ) {
		alert("<?php echo $AppUI->_('forumSubject', UI_OUTPUT_JS); ?>");
		form.message_title.focus();
	} else if (form.message_body.value.search(/^\s*$/) >= 0) {
		alert("<?php echo $AppUI->_('forumTypeMessage', UI_OUTPUT_JS); ?>");
		form.message_body.focus();
	} else {
		return true;
	}
    return false;
}
</script>

<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>

<form name="changeforum" action="?m=forums&forum_id=<?= $forum_id ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_forum" value="<?= $forum_id ?>" />
	<input type="hidden" name="message_parent" value="<?= $message_parent ?>" />
	<input type="hidden" name="message_published" value="<?= $forum->forum_moderated ? '1' : '0' ?>" />
	<input type="hidden" name="message_author" value="<?= (isset($message->message_author) && ($message_id || $message_parent < 0)) ? $message->message_author : $AppUI->user_id ?>" />
	<input type="hidden" name="message_editor" value="<?= (isset($message->message_author) && ($message_id || $message_parent < 0)) ? $AppUI->user_id : '0' ?>" />
	<input type="hidden" name="message_id" value="<?= $message_id ?>" />
    <h2><?= $AppUI->_($message_id ? 'Edit Message' : 'Add Message') ?></h2>
    <table class="std addedit">
        <?php
        if ($message_parent >= 0) { //check if this is a reply-post; if so, printout the original message
            $messageAuthor = isset($message->message_author) ? $message->message_author : $AppUI->user_id;
            $date = intval($message->message_date) ? new w2p_Utilities_Date($message->message_date) : new w2p_Utilities_Date();
            ?>
            <tr>
                <td><?= $AppUI->_('Author') ?>:</td>
                <td><?= CContact::getContactByUserid($messageAuthor); ?> (<?php echo $AppUI->formatTZAwareTime($message->message_date, $df . ' ' . $tf) ?>)</td>
            </tr>
            <tr><td colspan="2"><hr /></td></tr>
            <?php
        }
        ?>
        <tr>
            <td><?= $AppUI->_('Subject') ?>:</td>
            <td>
                <input type="text" class="text" name="message_title" value="<?= $message->message_title ?>" size="50" maxlength="250" />
            </td>
        </tr>
        <tr>
            <td><?php echo $AppUI->_('Message'); ?>:</td>
            <td>
               <textarea cols="60" name="message_body"><?= $message->message_body; ?></textarea>
                <a class="button" href="./index.php?<?= $back_url ?>'"><?= $AppUI->_('back') ?></a>
                <input type="submit" value="<?= $AppUI->_('submit') ?>" onclick="return submitIt()">
            </td>
        </tr>
    </table>
</form>