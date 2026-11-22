<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    refactor to use a core controller
// @todo    remove database query

$perms = &$AppUI->acl();
if (!canView('forums')) {
	$AppUI->redirect(ACCESS_DENIED);
}

##
## Change forum watches
##
$watch = w2PgetParam($_POST, 'watch', '');
// $watch becomes part of a column name (watch_forum / watch_topic).
if (!in_array($watch, array('forum', 'topic'), true)) {
	$watch = '';
}

if ($watch) {
	// clear existing watches
	$q = new w2p_Database_Query;
	$q->setDelete('forum_watch');
	$q->addWhere('watch_user = ' . (int)$AppUI->user_id);
	$q->addWhere('watch_' . $watch . ' IS NOT NULL');
	if (!$q->exec()) {
		$AppUI->setMsg((new w2p_Database_Connection($GLOBALS['db']))->error(), UI_MSG_ERROR);
		$q->clear();
	} else {
		$q->clear();
		// The checkboxes are named forum_<id>: forum ids on the forum list,
		// topic (message) ids on a forum's topic list.
		$ids = array();
		foreach ($_POST as $k => $v) {
			if (0 === strpos($k, 'forum_')) {
				$ids[] = (int) substr($k, 6);
			}
		}
		// Watch only what this user may read: a watch mails every new post,
		// so a watch on any posted id read forums and projects the user
		// can't see. (GHSA-qp43-fcwc-p3j3)
		$forum = new CForum();
		$ids = ('forum' == $watch) ? $forum->filterViewable($AppUI->user_id, $ids)
			: $forum->filterViewableTopics($AppUI->user_id, $ids);
		foreach ($ids as $id) {
			$q->addTable('forum_watch');
			$q->addInsert('watch_user', $AppUI->user_id);
			$q->addInsert('watch_' . $watch, (int) $id);
			if (!$q->exec()) {
				$AppUI->setMsg((new w2p_Database_Connection($GLOBALS['db']))->error(), UI_MSG_ERROR);
			} else {
				$AppUI->setMsg('Watch updated', UI_MSG_OK);
			}
			$q->clear();
		}
	}
} else {
	$AppUI->setMsg('Incorrect watch type passed to sql handler.', UI_MSG_ERROR);
}