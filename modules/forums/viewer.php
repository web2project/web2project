<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//view posts
$forum_id = (int) w2PgetParam($_GET, 'forum_id', 0);

$message_id = (int) w2PgetParam($_GET, 'message_id', 0);
$post_message = (int) w2PgetParam($_GET, 'post_message', 0);
$f = w2PgetParam($_POST, 'f', 0);

// check permissions
$perms = &$AppUI->acl();
$canAuthor = canAdd('forums');
$canDelete = canDelete('forums');
$canRead = $perms->checkModuleItem('forums', 'view', $forum_id);
$canEdit = $perms->checkModuleItem('forums', 'edit', $forum_id);
$canAdminEdit = canEdit('admin');

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$forum = new CForum();
// load the record data
$forum->loadFull($AppUI, $forum_id);

if (!$forum) {
	$AppUI->setMsg('Forum');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$start_date = $AppUI->formatTZAwareTime($forum->forum_create_date, $df);

// setup the title block
$titleBlock = new CTitleBlock('Forum', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f, true), '', '<form action="?m=forums&a=viewer&forum_id=' . $forum_id . '" method="post" name="filterFrm" accept-charset="utf-8">', '</form>');
$titleBlock->show();
?>
<table width="100%" cellspacing="0" cellpadding="2" border="0" class="std">
    <tr>
        <td height="20" colspan="3" style="border: outset #D1D1CD 1px;background-color:#<?php echo $forum->project_color_identifier; ?>">
            <font size="2" color="<?php echo bestColor($forum->project_color_identifier); ?>"><strong><?php echo $forum->forum_name; ?></strong></font>
        </td>
    </tr>
    <tr>
        <td align="left" nowrap="nowrap"><?php echo $AppUI->_('Related Project'); ?>:</td>
        <td nowrap="nowrap"><strong><?php echo ($forum->project_name) ? $forum->project_name : 'No associated project'; ?></strong></td>
        <td valign="top" width="50%" rowspan="99">
            <strong><?php echo $AppUI->_('Description'); ?>:</strong><br />
            <?php echo $forum->forum_description; ?>
        </td>
    </tr>
    <tr>
        <td align="left"><?php echo $AppUI->_('Owner'); ?>:</td>
        <td nowrap="nowrap">
            <?php
            echo $forum->contact_display_name;
            if ($forum_id) {
                echo ' (' . $AppUI->_('moderated') . ') ';
            } ?>
        </td>
    </tr>
    <tr>
        <td align="left"><?php echo $AppUI->_('Created On'); ?>:</td>
        <td nowrap="nowrap"><?php echo $start_date; ?></td>
    </tr>
</table>
<?php
if (function_exists('styleRenderBoxBottom')) {
	echo styleRenderBoxBottom();
}
if ($post_message) {
	include (W2P_BASE_DIR . '/modules/forums/post_message.php');
} else {
	if ($message_id) {
		include (W2P_BASE_DIR . '/modules/forums/view_messages.php');
	} else {
        include (W2P_BASE_DIR . '/modules/forums/view_topics.php');
	}
}