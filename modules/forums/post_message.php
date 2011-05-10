<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Add / Edit forum
$message_parent = (int) w2PgetParam($_GET, 'message_parent', -1);
$message_id = (int) w2PgetParam($_GET, 'message_id', 0);
$forum_id = (int) w2PgetParam($_REQUEST, 'forum_id', 0);

$perms = &$AppUI->acl();
$canAdd = $perms->checkModuleItem('forums', 'add');
$canEdit = $perms->checkModuleItem('forums', 'edit', $forum_id);

// check permissions
if (!$canEdit && !$canAdd) {
	$AppUI->redirect('m=public&a=access_denied');
}

//Pull forum information
$myForum = new CForum();
$myForum->load($AppUI, $forum_id);

// Build a back-url for when the back button is pressed
$back_url_params = array();
foreach ($_GET as $k => $v) {
	if ($k != 'post_message') {
		$back_url_params[] = "$k=$v";
	}
}
$back_url = implode('&', $back_url_params);

//pull message information
$q = new w2p_Database_Query;
$q->addTable('forum_messages');
$q->addQuery('forum_messages.*');
$q->addWhere('message_id = ' . $message_id);
$res = $q->exec();
$message_info = $q->fetchRow();
$q->clear();

//pull message information from last response
if ($message_parent != -1) {
	$q->addTable('forum_messages');
	$q->addWhere('message_parent = ' . $message_id);
	$q->addOrder('message_id DESC'); // fetch last message first
	$q->setLimit(1);
	$res = $q->exec();
	$last_message_info = $q->fetchRow();
	if (!$last_message_info) { // if it's first response, use original message
		$last_message_info = &$message_info;
		$last_message_info['message_body'] = wordwrap($last_message_info['message_body'], 50, "\n> ");
	} else {
		$last_message_info['message_body'] = mb_str_replace("\n", "\n> ", $last_message_info['message_body']);
	}
	$q->clear();
}

$crumbs = array();
$crumbs['?m=forums'] = 'forums list';
$crumbs['?m=forums&a=viewer&forum_id=' . $forum_id] = 'topics for this forum';
if ($message_parent > -1) {
	$crumbs['?m=forums&a=viewer&forum_id=' . $forum_id . '&message_id=' . $message_parent] = 'this topic';
}
?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit || $canAdd) {
?>
function submitIt(){
	var form = document.changeforum;
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
	var form = document.changeforum;
	if (confirm( "<?php echo $AppUI->_('forumDeletePost', UI_OUTPUT_JS); ?>" )) {
		form.del.value="<?php echo $message_id; ?>";
		form.submit();
	}
}
<?php } ?>
function orderByName(x){
	var form = document.changeforum;
	if (x == 'name') {
		form.forum_order_by.value = form.forum_last_name.value + ', ' + form.forum_name.value;
	} else {
		form.forum_order_by.value = form.forum_project.value;
	}
}
</script>
<br />
<?php
if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>

<form name="changeforum" action="?m=forums&forum_id=<?php echo $forum_id; ?>" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_forum" value="<?php echo $forum_id; ?>" />
	<input type="hidden" name="message_parent" value="<?php echo $message_parent; ?>" />
	<input type="hidden" name="message_published" value="<?php echo $myForum->forum_moderated ? '1' : '0'; ?>" />
	<input type="hidden" name="message_author" value="<?php echo (isset($message_info['message_author']) && ($message_id || $message_parent < 0)) ? $message_info['message_author'] : $AppUI->user_id; ?>" />
	<input type="hidden" name="message_editor" value="<?php echo (isset($message_info['message_author']) && ($message_id || $message_parent < 0)) ? $AppUI->user_id : '0'; ?>" />
	<input type="hidden" name="message_id" value="<?php echo $message_id; ?>" />
    <table cellspacing="0" cellpadding="3" border="0" width="100%" class="std">
        <tr><td>
            <table cellspacing="1" cellpadding="2" border="0" width="100%">
            <tr>
                <td align="left" nowrap="nowrap"><?php echo breadCrumbs($crumbs); ?></td>
                <td width="100%" align="right"></td>
            </tr>
            </table>
        </td></tr>
        <tr>
            <th valign="top" colspan="2"><strong><?php
        echo $AppUI->_($message_id ? 'Edit Message' : 'Add Message');
        ?></strong></th>
        </tr>
        <?php
        if ($message_parent >= 0) { //check if this is a reply-post; if so, printout the original message
            $date = intval($message_info['message_date']) ? new w2p_Utilities_Date($message_info['message_date']) : new w2p_Utilities_Date();
            ?>
            <tr>
                <td align="right"><?php echo $AppUI->_('Author') ?>:</td>
                <td align="left"><?php echo CContact::getContactByUserid($message_info['message_author']); ?> (<?php echo $AppUI->formatTZAwareTime($message_info['message_date'], $df . ' ' . $tf); ?>)</td>
            </tr>
            <tr><td align="right"><?php echo $AppUI->_('Subject') ?>:</td><td align="left"><?php echo $message_info['message_title'] ?></td></tr>
            <tr><td align="right" valign="top"><?php echo $AppUI->_('Message') ?>:</td><td align="left">
            <?php
                $message = $bbparser->qparse($message_info['message_body']);
                $message = nl2br($message);
                echo $message;
            ?></td></tr>
            <tr><td colspan="2" align="left"><hr /></td></tr>
            <?php
        } //end of if-condition

        ?>
        <tr>
            <td align="right"><?php echo $AppUI->_('Subject'); ?>:</td>
            <td>
                <input type="text" class="text" name="message_title" value="<?php echo ($message_id || $message_parent < 0 ? '' : 'Re: ') . $message_info['message_title']; ?>" size="50" maxlength="250" />
            </td>
        </tr>
        <tr>
            <td align="right" valign="top"><?php echo $AppUI->_('Message'); ?>:</td>
            <td align="left" valign="top">
               <textarea cols="60" name="message_body" style="height:200px"><?php echo (($message_id == 0) and ($message_parent != -1)) ? "\n>" . $last_message_info['message_body'] . "\n" : $message_info['message_body']; ?></textarea>
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
                <input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:window.location='./index.php?<?php echo $back_url; ?>';" />
            </td>
            <td align="right"><?php
            echo '<input type="button" value="' . $AppUI->_('submit') . '" class=button onclick="submitIt()">';
        ?></td>
        </tr>
    </table>
</form>