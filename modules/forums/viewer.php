<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

//view posts
$forum_id = (int) w2PgetParam($_GET, 'forum_id', 0);

$message_id = (int) w2PgetParam($_GET, 'message_id', 0);
$post_message = (int) w2PgetParam($_GET, 'post_message', 0);
$f = w2PgetParam($_POST, 'f', 0);

// check permissions
$perms = &$AppUI->acl();
$canAuthor = canAdd('forums');
$canDelete = canDelete('forums', $forum_id);
$canRead = $perms->checkModuleItem('forums', 'view', $forum_id);
$canEdit = $perms->checkModuleItem('forums', 'edit', $forum_id);
$canAdminEdit = canEdit('admin');

if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$message = new CForum_Message();
$message->loadFull(null, $message_id);

if (0 == $forum_id) {
    $forum_id = $message->message_forum;
}

$forum = new CForum();
$forum->load($forum_id);

$project = new CProject();
$project->load($forum->forum_project);

if (!$forum) {
	$AppUI->setMsg('Forum');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Forum', 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=forums', 'forums list');
if ($message_id) {
    $titleBlock->addCrumb('?m=forums&a=viewer&forum_id=' . $forum_id, 'topics for this forum');
}
if ($canEdit) {
    $titleBlock->addCrumb('?m=forums&a=addedit&forum_id=' . $forum_id, 'edit this forum');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete forum', true, $msg);
    }
}
$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f, true), '', '<form action="?m=forums&a=viewer&forum_id=' . $forum_id . '" method="post" name="filterFrm" accept-charset="utf-8">', '</form>');
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<table class="std view forums">
    <tr>
        <td height="20" colspan="3" style="border: outset #D1D1CD 1px;background-color:#<?php echo $project->project_color_identifier; ?>">
            <font size="2" color="<?php echo bestColor($project->project_color_identifier); ?>"><strong><?php echo $forum->forum_name; ?></strong></font>
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top" class="view-column">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <td align="left" nowrap="nowrap"><?php echo $AppUI->_('Related Project'); ?>:</td>
                    <?php echo $htmlHelper->createCell('forum_project', $forum->forum_project); ?>
                </tr>
                <tr>
                    <td align="left"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <?php echo $htmlHelper->createCell('forum_owner', $forum->forum_owner); ?>
                </tr>
                <tr>
                    <td align="left"><?php echo $AppUI->_('Created On'); ?>:</td>
                    <?php echo $htmlHelper->createCell('forum_create_date', $forum->forum_create_date); ?>
                </tr>
            </table>
        </td>
        <td width="50%" valign="top" class="view-column">
            <strong><?php echo $AppUI->_('Description'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <?php echo $htmlHelper->createCell('forum_description', $forum->forum_description); ?>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php if ($canDelete && ($forum_id > 0)) { ?>
<script language="javascript" type="text/javascript">
function delIt(){
    var form = document.frmDelete;
    if (confirm( "<?php echo $AppUI->_('forumDeleteForum', UI_OUTPUT_JS); ?>" )) {
        form.del.value="<?php echo $forum_id; ?>";
        form.submit();
    }
}
</script>
<form name="frmDelete" action="./index.php?m=forums" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_forum_aed" />
    <input type="hidden" name="del" value="1" />
    <input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>" />
</form>
<?php } ?>
<?php
echo $AppUI->getTheme()->styleRenderBoxBottom();
if ($post_message) {
	include (W2P_BASE_DIR . '/modules/forums/post_message.php');
} else {
	if ($message_id) {
		include (W2P_BASE_DIR . '/modules/forums/view_messages.php');
	} else {
        include (W2P_BASE_DIR . '/modules/forums/view_topics.php');
	}
}