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
if ($viewtype != 'short') {
	echo '<th nowrap>' . $AppUI->_('Author') . ':</th>';
}
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
	if ($viewtype == 'normal') {
		$s .= '<tr>';

		$s .= '<td valign="top" style="' . $style . '" nowrap="nowrap">';
        $s .= '<a href="?m=users&a=view&user_id='.$row['message_author'].'">';
        $s .= $row['contact_name'];
        $s .= '</a>';
		if (!$hideEmail) {
			$s .= '&nbsp;';
            $s .= '<a href="mailto:' . $row['contact_email'] . '">';
    		$s .= '<img src="' . w2PfindImage('email.gif') . '" width="16" height="16" border="0" alt="email" />';
			$s .= '</a>';
		}

		if (sizeof($editor) > 0) {
			$s .= '<br/>&nbsp;<br/>' . $AppUI->_('last edited by');
			$s .= ':<br/>';
			if (!$hideEmail) {
				$s .= '<a href="mailto:' . $editor[0]['contact_email'] . '">';
			}
			$s .= '<font size="1">' . $editor[0]['contact_name'] . '</font>';
			if (!$hideEmail) {
				$s .= '</a>';
			}
		}
		if ($row['visit_user'] != $AppUI->user_id) {
			$s .= '<br />&nbsp;' . w2PshowImage('icons/stock_new_small.png');
			$new_messages[] = $row['message_id'];
		}
		$s .= '</td>';
		$s .= '<td valign="top" style="' . $style . '">';
		$s .= '<font size="2"><strong>' . $row['message_title'] . '</strong><hr size=1>';
		$row['message_body'] = $bbparser->qparse($row['message_body']);
        $row['message_body'] = nl2br($row['message_body']);
		$s .= $row['message_body'];
		$s .= '</font></td>';

		$s .= '</tr><tr>';

		$s .= '<td valign="top" style="' . $style . '" nowrap="nowrap">';
		$s .= '<img src="' . w2PfindImage('icons/posticon.gif', $m) . '" alt="date posted" border="0" width="14" height="11">' . $AppUI->formatTZAwareTime($row['message_date'], $df . ' ' . $tf) . '</td>';
		$s .= '<td valign="top" align="right" style="' . $style . '">';

                // in some weird permission cases
                // it can happen that the table gets opened but never closed,
                // or the other way around, thus breaking the layout
                // introducing these variables to help us out with proper
                // table tag opening and closing.
                $tableOpened = false;
                $tableClosed = false;
		//the following users are allowed to edit/delete a forum message: 1. the forum creator  2. a superuser with read-write access to 'all' 3. the message author
		if ($canEdit || $AppUI->user_id == $row['forum_moderated'] || $AppUI->user_id == $row['message_author'] || $canAdminEdit) {
                    $tableOpened = true;
                    $s .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                    // edit message
                    $s .= '<td><a href="./index.php?m=forums&a=viewer&post_message=1&forum_id=' . $row['message_forum'] . '&message_parent=' . $row['message_parent'] . '&message_id=' . $row["message_id"] . '" title="' . $AppUI->_('Edit') . ' ' . $AppUI->_('Message') . '">';
                    $s .= w2PshowImage('icons/stock_edit-16.png', '16', '16');
                    $s .= '</td>';
		}
		if ($canDelete || $AppUI->user_id == $row['forum_moderated'] || $AppUI->user_id == $row['message_author'] || $canAdminEdit) {
                    $tableClosed = true;
                    if(!$tableOpened) {
                        $s .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                    }
                    // delete message
                    $s .= '<td><a href="javascript:delIt(' . $row['message_id'] . ')" title="' . $AppUI->_('delete') . '">';
                    $s .= w2PshowImage('icons/stock_delete-16.png', '16', '16');
                    $s .= '</a>';
                    $s .= '</td></tr></table>';
		}
                
                if($tableOpened and !$tableClosed) {
                    $s .= '</tr></table>';
                }
                
		$s .= '</td>';
		$s .= '</tr>';
	} else
		if ($viewtype == 'short') {
			$s .= "<tr>";

			$s .= '<td valign="top" style="' . $style . '" >';
			$s .= '<a href="mailto:' . $row['contact_email'] . '">';
			$s .= '<font size="2">' . $row['contact_name'] . ' ' . $row['contact_name'] . '</font></a>';
			$s .= ' (' . $AppUI->formatTZAwareTime($row['message_date'], $df . ' ' . $tf) . ') ';
			if (sizeof($editor) > 0) {
				$s .= '<br/>&nbsp;<br/>' . $AppUI->_('last edited by');
				$s .= ':<br/><a href="mailto:' . $editor[0]['contact_email'] . '">';
				$s .= '<font size="1">' . $editor[0]['contact_name'] . '</font></a>';
			}
			$s .= '<a name="' . $row['message_id'] . '" href="javascript: void(0);" onclick="toggle(' . $row['message_id'] . ')">';
			$s .= '<span size="2"><strong>' . $row['message_title'] . '</strong></span></a>';
			$s .= '<div class="message" id="' . $row['message_id'] . '" style="display: none">';
			$row['message_body'] = $bbparser->qparse($row['message_body']);
			$s .= $row['message_body'];
			$s .= '</div></td>';

			$s .= '</tr>';
		} else
			if ($viewtype == 'single') {
				$s .= '<tr>';

				$s .= '<td valign="top" style="' . $style . '">';
				$s .= $AppUI->formatTZAwareTime($row['message_date'], $df . ' ' . $tf) . ' - ';
				$s .= '<a href="mailto:' . $row['contact_email'] . '">';
				$s .= '<font size="2">' . $row['contact_name'] . '</font></a>';
				$s .= '<br />';
				if (sizeof($editor) > 0) {
					$s .= '<br/>&nbsp;<br/>' . $AppUI->_('last edited by');
					$s .= ':<br/><a href="mailto:' . $editor[0]['contact_email'] . '">';
					$s .= '<font size="1">' . $editor[0]['contact_name'] . '</font></a>';
				}
				$s .= '<a href="javascript: void(0);" onclick="toggle(' . $row['message_id'] . ')">';
				$s .= '<span size="2"><strong>' . $row['message_title'] . '</strong></span></a>';
				$side .= '<div class="message" id="' . $row['message_id'] . '" style="display: none">';
				$row['message_body'] = $bbparser->qparse($row['message_body']);
				$side .= $row['message_body'];
				$side .= '</div>';
				$s .= '</td>';
				if ($first) {
					$s .= '<td rowspan="' . count($messages) . '" valign="top">';
					echo $s;
					$s = '';
					$first = false;
				}
				$s .= '</tr>';
			}

	if ($viewtype != 'single') {
		echo $s;
	}
	$x = !$x;

}
if ($viewtype == 'single') {
	echo $side . '</td>' . $s;
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