<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

$sort = w2PgetParam($_REQUEST, 'sort', 'asc');
$viewtype = w2PgetParam($_REQUEST, 'viewtype', 'normal');
$hideEmail = w2PgetConfig('hide_email_addresses', false);

$forum = new CForum();
$messages = $forum->getMessages(null, $forum_id, $message_id, $sort);
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<script language="javascript" type="text/javascript">
<?php if ($viewtype != 'normal') { ?>
	function toggle(id) {
        <?php if ($viewtype == 'single') { ?>
		var elems = document.getElementsByTagName('div');
		for (var i=0, i_cmp=elems.length; i<i_cmp; i++)
			if (elems[i].className == 'message') {
				elems[i].style.display = 'none';
			}
			document.getElementById(id).style.display = 'block';

        <?php } elseif ($viewtype == 'short') { ?>
			vista = (document.getElementById(id).style.display == 'none') ? 'block' : 'none';
			document.getElementById(id).style.display = vista;
        <?php } ?>
	}
<?php
}
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canAuthor || $canEdit) {
?>
function delIt(id) {
	var form = document.messageForm;
	if (confirm( '<?php echo $AppUI->_('forumsDelete'); ?>' )) {
		form.del.value = 1;
		form.message_id.value = id;
		form.submit();
	}
}
<?php } ?>
</script>
<?php
$thispage = '?m=' . $m . '&a=viewer&forum_id=' . $forum_id . '&message_id=' . $message_id . '&sort=' . $sort;

?>
<br />
<?php
echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<form name="messageForm" method="post" action="?m=forums&forum_id=<?php echo $forum_id; ?>" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_id" value="0" />
</form>

<table class="std view forums-message">
<tr>
    <td colspan="2">
        <div class="left" style="padding-left: 20px;">
            <form action="<?php echo $thispage; ?>" method="post" accept-charset="utf-8">
                <?php echo $AppUI->_('View') ?>:
                <input type="radio" name="viewtype" value="normal" <?php echo ($viewtype == 'normal') ? 'checked' : ''; ?> onclick="this.form.submit();" /><?php echo $AppUI->_('Normal') ?>
                <input type="radio" name="viewtype" value="short" <?php echo ($viewtype == 'short') ? 'checked' : ''; ?> onclick="this.form.submit();" /><?php echo $AppUI->_('Collapsed') ?>
                <input type="radio" name="viewtype" value="single" <?php echo ($viewtype == 'single') ? 'checked' : ''; ?> onclick="this.form.submit();" /><?php echo $AppUI->_('Single Message at a time') ?>
            </form>
        </div>
        <div class="right">
            <input type="button" class="button" value="<?php echo $AppUI->_('Sort By Date') . ' (' . $AppUI->_($sort) . ')'; ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&message_id=<?php echo $message_id; ?>&sort=<?php echo $sort; ?>'" />
            <?php if ($canAuthor) { ?>
                <input type="button" class="button" value="<?php echo $AppUI->_('Post Reply'); ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&message_parent=<?php echo $message_id; ?>&post_message=1';" />
                <input type="button" class="button" value="<?php echo $AppUI->_('New Topic'); ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&message_id=0&post_message=1';" />
            <?php } ?>
        </div>
    </td>
</tr>

<tr>
    <?php
    echo '<th nowrap>' . $AppUI->_('Author') . ':</th>';
    echo '<th width="' . (($viewtype == 'single') ? '60' : '100') . '%">' . $AppUI->_('Message') . ':</th>';
    ?>
</tr>

<?php
$x = false;

$date = new w2p_Utilities_Date();

if ($viewtype == 'single') {
	$s = '';
	$first = true;
}

$new_messages = array();

foreach ($messages as $row) {
	// Find the parent message - the topic.
	if ($row['message_id'] == $message_id) {
		$topic = $row['message_title'];
    }

    $editor = __extract_from_forums_view_messages($row);

	$date = intval($row['message_date']) ? new w2p_Utilities_Date($row['message_date']) : null;
	if ($viewtype != 'single') {
		$s = '';
	}
	$style = $x ? 'background-color:#eeeeee' : '';

    $bbparser = new HTML_BBCodeParser();
	//!!! Different table building for the three different views
	// To be cleaned up, and reuse common code at later stage.
    switch ($viewtype) {
        case 'normal':
            list($s, $new_messages) = __extract_from_view_messages4($s, $style, $row, $hideEmail, $editor, $AppUI, $new_messages, $bbparser, $m, $df, $tf, $canEdit, $canAdminEdit, $canDelete);
            break;
        case 'short':
            $s = __extract_from_view_messages1($s, $style, $row, $editor, $AppUI, $bbparser);
            break;
        case 'single':
            list($s, $side) = __extract_from_view_messages3($s, $style, $AppUI, $row, $df, $tf, $editor, $side, $bbparser, $first, $messages);
            break;
    }
    $first = false;

    if ($viewtype != 'single') {
		echo $s;
	}
	$x = !$x;

}
if ($viewtype == 'single') {
	echo $side . $s;
}
?>

<tr>
    <td colspan="2">
        <div class="right">
            <input type="button" class="button" value="<?php echo $AppUI->_('Sort By Date') . ' (' . $AppUI->_($sort) . ')'; ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&message_id=<?php echo $message_id; ?>&sort=<?php echo $sort; ?>'" />
            <?php if ($canAuthor) { ?>
                <input type="button" class="button" value="<?php echo $AppUI->_('Post Reply'); ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&message_parent=<?php echo $message_id; ?>&post_message=1';" />
                <input type="button" class="button" value="<?php echo $AppUI->_('New Topic'); ?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id; ?>&message_id=0&post_message=1';" />
            <?php } ?>
        </div>
    </td>
</tr>
</table>
<?php

foreach ($new_messages as $msg_id) {
    __extract_from_forums_view_messages2($AppUI, $forum_id, $msg_id, $date);
}