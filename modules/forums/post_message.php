<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    I think this file should actually be named 'addedit_message.php'

$message_parent = (int) w2PgetParam($_GET, 'message_parent', -1);
$message_id = (int) w2PgetParam($_GET, 'message_id', 0);
$forum_id = (int) w2PgetParam($_REQUEST, 'forum_id', 0);

$myForum = new CForum();
$myForum->forum_id = $forum_id;

$obj = $myForum;
$canAddEdit = $obj->canAddEdit();
$canAdd = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

//Pull forum information
$myForum->load($forum_id);
if (!$myForum) {
	$AppUI->setMsg('Forum');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect('m=forums');
}

// Build a back-url for when the back button is pressed
$back_url_params = array();
foreach ($_GET as $k => $v) {
	if ($k != 'post_message') {
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
    if (!$last_message->message_id) { // if it's first response, use original message
        $last_message = clone $message;
        $last_message->message_body = wordwrap($last_message->message_body, 50, "\n> ");
    } else {
        $last_message->message_body = mb_str_replace("\n", "\n> ", $last_message->message_body);
    }
}

$crumbs = array();
$crumbs['?m=forums'] = 'forums list';
$crumbs['?m=forums&a=viewer&forum_id=' . $forum_id] = 'topics for this forum';
if ($message_parent > -1) {
	$crumbs['?m=forums&a=viewer&forum_id=' . $forum_id . '&message_id=' . $message_parent] = 'this topic';
}

$bbparser = new HTML_BBCodeParser();
?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit || $canAdd) {
?>
function submitIt(){
	var form = document.editFrm;
	if (form.message_title.value.search(/^\s*$/) >= 0 ) {
		alert("<?php echo $AppUI->_('forumSubject', UI_OUTPUT_JS); ?>");
		form.message_title.focus();
	} else if (form.message_body.value.search(/^\s*$/) >= 0) {
		alert("<?php echo $AppUI->_('forumTypeMessage', UI_OUTPUT_JS); ?>");
		form.message_body.focus();
	} else {
		form.submit();
	}
}

function delIt(){
	var form = document.editFrm;
	if (confirm( "<?php echo $AppUI->_('forumDeletePost', UI_OUTPUT_JS); ?>" )) {
		form.del.value="<?php echo $message_id; ?>";
		form.submit();
	}
}
<?php } ?>
function orderByName(x){
	var form = document.editFrm;
	if (x == 'name') {
		form.forum_order_by.value = form.forum_last_name.value + ', ' + form.forum_name.value;
	} else {
		form.forum_order_by.value = form.forum_project.value;
	}
}
</script>
<br />
<?php
echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>&forum_id=<?php echo $forum_id; ?>" method="post" accept-charset="utf-8" class="addedit forums-message">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_forum" value="<?php echo $forum_id; ?>" />
	<input type="hidden" name="message_parent" value="<?php echo $message_parent; ?>" />
	<input type="hidden" name="message_published" value="<?php echo $myForum->forum_moderated ? '1' : '0'; ?>" />
	<input type="hidden" name="message_author" value="<?php echo (isset($message->message_author) && ($message_id || $message_parent < 0)) ? $message->message_author : $AppUI->user_id; ?>" />
	<input type="hidden" name="message_editor" value="<?php echo (isset($message->message_author) && ($message_id || $message_parent < 0)) ? $AppUI->user_id : '0'; ?>" />
	<input type="hidden" name="message_id" value="<?php echo $message_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <table class="std addedit forums-message" border="0">
        <tr><td style="width: 25%">
            <table cellspacing="1" cellpadding="2" border="0" width="100%">
            <tr>
                <td align="left" nowrap="nowrap"><?php echo breadCrumbs($crumbs); ?></td>
                <td width="100%" align="right"></td>
            </tr>
            </table>
        </td></tr>
        <tr>
            <th valign="top" colspan="2">
                <strong><?php echo $AppUI->_($message_id ? 'Edit Message' : 'Add Message'); ?></strong>
            </th>
        </tr>
        <?php
        if ($message_parent >= 0) { //check if this is a reply-post; if so, printout the original message
            $messageAuthor = isset($message->message_author) ? $message->message_author : $AppUI->user_id;
            $date = intval($message->message_date) ? new w2p_Utilities_Date($message->message_date) : new w2p_Utilities_Date();
            ?>
            <tr>
                <td align="right"><?php $form->showLabel('Author'); ?></td>
                <td align="left"><?php echo CContact::getContactByUserid($messageAuthor); ?> (<?php echo $AppUI->formatTZAwareTime($message->message_date, $df . ' ' . $tf); ?>)</td>
            </tr>
            <tr><td align="right"><?php $form->showLabel('Subject'); ?></td>
                <td align="left"><?php echo $message->message_title ?></td></tr>
            <tr><td align="right" valign="top"><?php $form->showLabel('Previous Message'); ?></td>
                <td align="left">
            <?php
                $messageBody = $bbparser->qparse($last_message->message_body);
                $messageBody = nl2br($messageBody);
                echo $messageBody;
            ?></td></tr>
            <tr><td colspan="2" align="left"><hr /></td></tr>
            <?php
        } //end of if-condition

        ?>
        <tr>
            <td align="right"><?php $form->showLabel('Subject'); ?></td>
            <td>
                <input type="text" class="text" name="message_title" value="<?php echo ($message_id || $message_parent < 0 ? '' : 'Re: ') . $message->message_title; ?>" size="50" maxlength="250" />
            </td>
        </tr>
        <tr>
            <td align="right" valign="top"><?php $form->showLabel('Message'); ?></td>
            <td align="left" valign="top">
               <textarea cols="60" name="message_body" style="height:200px"><?php echo (($message_id == 0) and ($message_parent != -1)) ? "\n>" . $last_message->message_body . "\n\n" : $message->message_body; ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
            </td>
            <td align="left">
                <small><b><?php echo $AppUI->_('BBCode Ready');?>!</b></small>
                <?php echo w2PshowImage('log-info.gif','','','BBCode Tags Accepted','
                [b][/b] Bold. Example: [b]<b>This text will be bold</b>[/b]<br />
                [i][/i] Italic. Example: [i]<i>This text will be in italic</i>[/i]<br />
                [u][/u] Underlined. Example: [u]<u>This text will be underlined</u>[/u]<br />
                [s][/s] Scratched. Example: [s]<del>This text will be scratched</del>[/s]<br />
                [sub][/sub] Subscript. Example: [sub]<sub>This text will be subscript</sub>[/sub]<br />
                [sup][/sup] Superscript. Example: [sup]<sup>This text will be superscript</sup>[/sup]<br />
                [email][/email] Email Address. Example: [email]my@mail.net[/email]<br />
                [color=color_name][/color] Colorized Text. Example: [color=blue]I am Blue[/color]<br />
                [size=size_value][/size], [font=font_name][/font] and [align=left|center|right][align] Format Text. Example: [align=right]I am on the Right[/align]<br />
                [url=url_address][/url] Link. Example: [url=http://web2project.net]web2Project[/url]<br />
                [list][/list],[ulist][/ulist] and [li][/li] Lists.<br />
                [quote][/quote] Quoted Text. Example: [quote]<q>This text will be superscript</q>[/quote]<br />
                [code][/code] Text in code format. Example: [code]//This is a code comment;[/code]<br />
                '); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php $form->showCancelButton(); ?>
            </td>
            <td align="right">
                <?php $form->showSaveButton(); ?>
            </td>
        </tr>
    </table>
</form>