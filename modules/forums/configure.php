<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// deny all but system admins
$canEdit = canEdit('system');
if (!$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$AppUI->savePlace();
$q = new w2p_Database_Query;

if (isset($_POST['forcewatch']) && isset($_POST['forcesubmit'])) { // insert row into forum_watch for forcing Watch
	$q->addTable('forum_watch');
	$q->addInsert('watch_user', '0');
	$q->addInsert('watch_forum', '0');
	$q->addInsert('watch_topic', '0');
	if (!$q->exec()) {
		$AppUI->setMsg(db_error(), UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('Watch Forced', UI_MSG_OK);
	}
	$q->clear();
	$AppUI->redirect('m=forums&a=configure');
} elseif (isset($_POST['forcesubmit']) && !isset($_POST['forcewatch'])) { // delete row from forum_watch for unorcing Watch
	$q->setDelete('forum_watch');
	$q->addWhere('watch_user = 0');
	$q->addWhere('watch_forum = 0');
	$q->addWhere('watch_topic = 0');
	if (!$q->exec()) {
		$AppUI->setMsg(db_error(), UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('Watch Unforced', UI_MSG_OK);
	}
	$q->clear();
	$AppUI->redirect('m=forums&a=configure');
}

// SQL-Query to check if the message should be delivered to all users (forced) (checkbox)
$q->addTable('forum_watch');
$q->addQuery('*');
$q->addWhere('watch_user = 0');
$q->addWhere('watch_forum = 0');
$q->addWhere('watch_topic = 0');
$resAll = $q->exec();

if (db_num_rows($resAll) >= 1) { // message has to be sent to all users	
	$watchAll = true;
}
$q->clear();

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Configure Forums Module', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'modules list');
$titleBlock->show();
?>

<script language="javascript" type="text/javascript">
function submitFrm( frmName ) {

	eval('document.'+frmName+'.submit();');

}
</script>
<form name="frmForceWatch" method="post" action="?m=forums&a=configure" accept-charset="utf-8">
<input type="hidden" name="forcesubmit" value="true" />
<input type="checkbox" name="forcewatch" id="forcewatch" value="dod" <?php echo $watchAll ? 'checked="checked"' : ''; ?> onclick="javascript:submitFrm('frmForceWatch');" />
<label for="forcewatch"><?php echo $AppUI->_('forumForceWatch'); ?></label>
</form>