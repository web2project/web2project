<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();
if (!canView('forums')) {
	$AppUI->redirect('m=public&a=access_denied');
}

##
## Change forum watches
##
$watch = w2PgetParam($_POST, 'watch', '');

if ($watch) {
	// clear existing watches
	$q = new w2p_Database_Query;
	$q->setDelete('forum_watch');
	$q->addWhere('watch_user = ' . (int)$AppUI->user_id);
	$q->addWhere('watch_' . $watch . ' IS NOT NULL');
	if (!$q->exec()) {
		$AppUI->setMsg(db_error(), UI_MSG_ERROR);
		$q->clear();
	} else {
		$q->clear();
		foreach ($_POST as $k => $v) {
			if (strpos($k, 'forum_') !== false) {
				$q->addTable('forum_watch');
				$q->addInsert('watch_user', $AppUI->user_id);
				$q->addInsert('watch_' . $watch, substr($k, 6));
				if (!$q->exec()) {
					$AppUI->setMsg(db_error(), UI_MSG_ERROR);
				} else {
					$AppUI->setMsg('Watch updated', UI_MSG_OK);
				}
				$q->clear();
			}
		}
	}
} else {
	$AppUI->setMsg('Incorrect watch type passed to sql handler.', UI_MSG_ERROR);
}