<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

function nl2p($str) {
    return "<p>" .
    str_replace(
    "\r", "</p><p>",
    str_replace(
    "\n", "</p><p>",
    str_replace(
    "\r\n", "</p><p>",
    str_replace(
    "<q>", "</p><q><p>",
    str_replace(
    "</q>", "</p></q><p>",
    $str))))) . "</p>";
}

//view posts
$forum_id = (int) w2PgetParam($_GET, 'forum_id', 0);

$message_id = (int) w2PgetParam($_GET, 'message_id', 0);
$message_parent = (int) w2PgetParam($_GET, 'message_parent', -1);
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
	$AppUI->redirect(ACCESS_DENIED);
}

$forum = new CForum();
$forum->loadFull(null, $forum_id);

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
$titleBlock = new w2p_Theme_TitleBlock('Forum', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCell(arraySelect($filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f, true), '', '<form action="?m=forums&a=view&forum_id=' . $forum_id . '" method="post" name="filterFrm" accept-charset="utf-8">', '</form>');
if ($forum_id && $canAuthor) {
    $titleBlock->addCell('<a class="button" href="?m=forums&a=view&forum_id='.$forum_id.'&post_message=1">'.$AppUI->_('New Topic').'</a>');
}
if ($message_id > 0 && !$post_message) {
    $sort = w2PgetParam($_REQUEST, 'sort', w2PgetParam($_COOKIE, 'forum_sort', 'asc'));
    if ($sort != w2PgetParam($_COOKIE, 'forum_sort', 'asc')) {
        // Need to set cookie via JS because headers have long been sent.
        echo '<script type="text/javascript">var date = new Date(); date.setTime(date.getTime()+3650*24*60*60*1000); document.cookie="forum_sort='.$sort.'; "+date.toGMTString()+"; path=/";</script>';
    }

    $titleBlock->addCell('<a class="button" href="?m=forums&a=view&forum_id='.$forum_id.'&message_id='.$message_id.'&sort='.($sort == 'asc'? 'desc' : 'asc').'">'.$AppUI->_('Reverse Order').'</a>');
}

if ($forum_id) $titleBlock->addCrumb('?m=forums', 'forums list');
if ($message_id > 0 || $post_message) $titleBlock->addCrumb('?m=forums&a=view&forum_id=' . $forum_id, 'topics for this forum');
if ($message_parent > -1) $titleBlock->addCrumb('?m=forums&a=view&forum_id=' . $forum_id . '&message_id=' . $message_parent, 'this topic');

$titleBlock->show();
?>

<div<?php if ($forum->project_color_identifier) { ?> style="background-color:#<?= $forum->project_color_identifier ?>;color:<?= bestColor($forum->project_color_identifier) ?>"<?php } ?> id="forum-info">
    <div id="forum-name"><?= $forum->forum_name ?></div>
    <div id="forum-project"><?= ($forum->project_name) ? $forum->project_name : $AppUI->_('No associated project') ?></div>
    <div id="forum-owner"><?= $forum->contact_display_name . ($forum_id? ' (' . $AppUI->_('moderated') . ') ' : '') ?></div>
    <div id="forum-date"><?= $start_date; ?></div>
    <div id="forum-description"><?= $forum->forum_description ?></div>
</div>

<?php
if (function_exists('styleRenderBoxBottom')) {
	echo styleRenderBoxBottom();
}
if ($post_message) {
	include (W2P_BASE_DIR . '/modules/forums/post_message.php');
} else {
	if ($message_id > 0) {
		include (W2P_BASE_DIR . '/modules/forums/view_messages.php');
	} else {
        include (W2P_BASE_DIR . '/modules/forums/view_topics.php');
	}
}