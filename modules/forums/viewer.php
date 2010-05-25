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

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$q = new DBQuery;
$q->addTable('forums');
$q->addTable('users', 'u');
$q->addQuery('forum_id, forum_project,	forum_description, forum_owner, forum_name,
	forum_create_date, forum_last_date, forum_message_count, forum_moderated,
	user_username, contact_first_name, contact_last_name,
	project_name, project_color_identifier');
$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
$q->addJoin('projects', 'p', 'p.project_id = forum_project');
$q->addWhere('user_id = forum_owner');
$q->addWhere('forum_id = ' . (int)$forum_id);
$q->exec(ADODB_FETCH_ASSOC);
$forum = $q->fetchRow();
$forum_name = $forum['forum_name'];
echo db_error();
$q->clear();

$start_date = intval($forum['forum_create_date']) ? new CDate($forum['forum_create_date']) : null;

// setup the title block
$titleBlock = new CTitleBlock('Forum', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f, true), '', '<form action="?m=forums&a=viewer&forum_id=' . $forum_id . '" method="post" name="filterFrm" accept-charset="utf-8">', '</form>');
$titleBlock->show();
?>
<table width="100%" cellspacing="0" cellpadding="2" border="0" class="std">
    <tr>
        <td height="20" colspan="3" style="border: outset #D1D1CD 1px;background-color:#<?php echo $forum['project_color_identifier']; ?>">
            <font size="2" color="<?php echo bestColor($forum["project_color_identifier"]); ?>"><strong><?php echo $forum['forum_name']; ?></strong></font>
        </td>
    </tr>
    <tr>
        <td align="left" nowrap="nowrap"><?php echo $AppUI->_('Related Project'); ?>:</td>
        <td nowrap="nowrap"><strong><?php echo ($forum['project_name']) ? $forum['project_name'] : 'No associated project'; ?></strong></td>
        <td valign="top" width="50%" rowspan="99">
            <strong><?php echo $AppUI->_('Description'); ?>:</strong><br />
            <?php echo $forum['forum_description']; ?>
        </td>
    </tr>
    <tr>
        <td align="left"><?php echo $AppUI->_('Owner'); ?>:</td>
        <td nowrap="nowrap">
            <?php
            echo $forum['contact_first_name'] . ' ' . $forum['contact_last_name'];
            if (intval($forum['forum_id']) <> 0) {
                echo ' (' . $AppUI->_('moderated') . ') ';
            } ?>
        </td>
    </tr>
    <tr>
        <td align="left"><?php echo $AppUI->_('Created On'); ?>:</td>
        <td nowrap="nowrap"><?php echo $start_date ? $start_date->format($df) : '-'; ?></td>
    </tr>
</table>
<?php
if (function_exists('styleRenderBoxBottom')) {
	echo styleRenderBoxBottom();
}
if ($post_message) {
	include (W2P_BASE_DIR . '/modules/forums/post_message.php');
} else {
	if ($message_id == 0) {
		include (W2P_BASE_DIR . '/modules/forums/view_topics.php');
	} else {
		include (W2P_BASE_DIR . '/modules/forums/view_messages.php');
	}
}