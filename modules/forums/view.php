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

// A topic belongs to its own forum: take the forum from the message, not the
// URL. The check below then covers the forum that is actually shown, so a
// message id alone (or with another forum's id) can't open a topic in a
// forum the user may not read.
$message = new CForum_Message();
if ($message_id) {
	if (!$message->load($message_id)) {
		$AppUI->setMsg('Message');
		$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
		$AppUI->redirect('m=' . $m);
	}
	$forum_id = (int) $message->message_forum;
}

$forum = new CForum();
if (!$forum_id || !$forum->load($forum_id)) {
	$AppUI->setMsg('Forum');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect('m=' . $m);
}

// check permissions: view on the forum and on its project
$perms = &$AppUI->acl();
$canAuthor = canAdd('forums');
$canDelete = canDelete('forums', $forum_id);
$canRead = $perms->checkModuleItem('forums', 'view', $forum_id)
	&& count($forum->filterViewable($AppUI->user_id, array($forum_id)));
$canEdit = $perms->checkModuleItem('forums', 'edit', $forum_id);
$canAdminEdit = canEdit('system');

if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$project = new CProject();
$project->load($forum->forum_project);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Forum', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
if ($message_id) {
    $titleBlock->addCrumb('?m=forums&a=view&forum_id=' . $forum_id, 'topics for this forum');
}
if ($canEdit) {
    $titleBlock->addCrumb('?m=forums&a=addedit&forum_id=' . $forum_id, 'edit this forum');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete forum', true);
    }
}

$filters = (isset($filters) && is_array($filters)) ? $filters : [];

$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f, true), '', '<form action="?m=forums&a=view&forum_id=' . $forum_id . '" method="post" name="filterFrm" accept-charset="utf-8">', '</form>');
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $forum, 'Forum');
echo $view->renderDelete();

include $AppUI->getTheme()->resolveTemplate('forums/view');

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