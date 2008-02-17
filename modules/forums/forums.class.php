<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once ($AppUI->getSystemClass('libmail'));

$filters = array('- Filters -');

if ($a == 'viewer') {
	array_push($filters, 'My Watched', 'Last 30 days');
} else {
	array_push($filters, 'My Forums', 'My Watched', 'My Projects', 'My Company', 'Inactive Projects');
}

class CForum extends CW2pObject {
	var $forum_id = null;
	var $forum_project = null;
	var $forum_status = null;
	var $forum_owner = null;
	var $forum_name = null;
	var $forum_create_date = null;
	var $forum_last_date = null;
	var $forum_last_id = null;
	var $forum_message_count = null;
	var $forum_description = null;
	var $forum_moderated = null;

	function CForum() {
		// empty constructor
		parent::CW2pObject('forums', 'forum_id');
	}

	function bind($hash) {
		if (!is_array($hash)) {
			return "CForum::bind failed";
		} else {
			bindHashToObject($hash, $this);
			return null;
		}
	}

	function check() {
		if ($this->forum_id === null) {
			return 'forum_id is NULL';
		}
		// TODO MORE
		return null; // object is ok
	}

	function store() {
		$msg = $this->check();
		if ($msg) {
			return 'CForum::store-check failed ' . $msg;
		}
		if ($this->forum_id) {
			$q = new DBQuery;
			$ret = $q->updateObject('forums', $this, 'forum_id', false); // ! Don't update null values
			$q->clear();
			if ($this->forum_name) {
				// when adding messages, this functon is called without first setting 'forum_name'
				addHistory('forums', $this->forum_id, 'update', $this->forum_name);
			}
		} else {
			$this->forum_create_date = db_datetime(time());
			$q = new DBQuery;
			$ret = $q->insertObject('forums', $this, 'forum_id');
			$q->clear();
			addHistory('forums', $this->forum_id, 'add', $this->forum_name);
		}
		if (!$ret) {
			return 'CForum::store failed ' . db_error();
		} else {
			return null;
		}
	}

	function delete() {
		$q = new DBQuery;
		$q->setDelete('forum_visits');
		$q->addWhere('visit_forum = ' . $this->forum_id);
		$q->exec(); // No error if this fails, it is not important.
		$q->clear();

		$q->setDelete('forums');
		$q->addWhere('forum_id = ' . $this->forum_id);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		}
		$q->clear();
		$q->setDelete('forum_messages');
		$q->addWhere('message_forum = ' . $this->forum_id);
		if (!$q->exec()) {
			$result = db_error();
		} else {
			addHistory('forums', $this->forum_id, 'delete', $this->forum_name);
			$result = null;
		}
		$q->clear();
		return $result;
	}

	function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		global $AppUI;
		require_once ($AppUI->getModuleClass('projects'));
		$oPrj = new CProject();

		$aPrjs = $oPrj->getAllowedRecords($uid, 'projects.project_id, project_name', '', null, null, 'projects');
		if (count($aPrjs)) {
			$buffer = '(forum_project IN (' . implode(',', array_keys($aPrjs)) . ') OR forum_project IS NULL OR forum_project = "" OR forum_project = 0)';

			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND ' . $buffer;
			} else {
				$extra['where'] = $buffer;
			}
		} else {
			// There are no allowed projects, so don't allow tasks.
			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND 1 = 0 ';
			} else {
				$extra['where'] = '1 = 0';
			}
		}
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);

	}
}

class CForumMessage {
	var $message_id = null;
	var $message_forum = null;
	var $message_parent = null;
	var $message_author = null;
	var $message_editor = null;
	var $message_title = null;
	var $message_date = null;
	var $message_body = null;
	var $message_published = null;

	function CForumMessage() {
		// empty constructor
	}

	function bind($hash) {
		if (!is_array($hash)) {
			return 'CForumMessage::bind failed';
		} else {
			bindHashToObject($hash, $this);
			return null;
		}
	}

	function check() {
		if ($this->message_id === null) {
			return 'message_id is NULL';
		}
		// TODO MORE
		return null; // object is ok
	}

	function store() {
		$msg = $this->check();
		if ($msg) {
			return 'CForumMessage::store-check failed ' . $msg;
		}
		$q = new DBQuery;
		if ($this->message_id) {
			// First we need to remove any forum visits for this message
			// otherwise nobody will see that it has changed.
			$q->setDelete('forum_visits');
			$q->addWhere('visit_message = ' . $this->message_id);
			$q->exec(); // No error if this fails, it is not important.
			$ret = db_updateObject('forum_messages', $this, 'message_id', false); // ! Don't update null values
			$q->clear();
		} else {
			$this->message_date = db_datetime(time());
			$new_id = db_insertObject('forum_messages', $this, 'message_id'); ## TODO handle error now
			echo db_error(); ## TODO handle error better

			$q->addTable('forum_messages');
			$q->addQuery('count(message_id), MAX(message_date)');
			$q->addWhere('message_forum = ' . $this->message_forum);

			$res = $q->exec();
			echo db_error(); ## TODO handle error better
			$reply = $q->fetchRow();
			$q->clear();

			//update forum descriptor
			$forum = new CForum();
			$forum->forum_id = $this->message_forum;
			$forum->forum_message_count = $reply[0];
			$forum->forum_last_date = $reply[1];
			$forum->forum_last_id = $this->message_id;

			$forum->store(); ## TODO handle error now

			return $this->sendWatchMail(false);
		}

		if (!$ret) {
			return 'CForumMessage::store failed ' . db_error();
		} else {
			return null;
		}
	}

	function delete() {
		$q = new DBQuery;
		$q->setDelete('forum_visits');
		$q->addWhere('visit_message = ' . $this->message_id);
		$q->exec(); // No error if this fails, it is not important.
		$q->clear();

		$q->addTable('forum_messages');
		$q->addQuery('message_forum');
		$q->addWhere('message_id = ' . $this->message_id);
		$forumId = $q->loadResult();
		$q->clear();

		$q->setDelete('forum_messages');
		$q->addWhere('message_id = ' . $this->message_id);
		if (!$q->exec()) {
			$result = db_error();
		} else {
			$result = null;
		}
		$q->clear();

		$q->addTable('forum_messages');
		$q->addQuery('COUNT(message_id)');
		$q->addWhere('message_forum = ' . $forumId);
		$messageCount = $q->loadResult();
		$q->clear();

		$q->addTable('forums');
		$q->addUpdate('forum_message_count', $messageCount);
		$q->addWhere('forum_id = ' . $forumId);
		$q->exec();
		$q->clear();

		return $result;
	}

	function sendWatchMail($debug = false) {
		global $AppUI, $debug, $w2Pconfig;
		$subj_prefix = $AppUI->_('forumEmailSubj', UI_OUTPUT_RAW);
		$body_msg = $AppUI->_('forumEmailBody', UI_OUTPUT_RAW);

		// Get the message from details.
		$q = new DBQuery;
		$q->addTable('users', 'u');
		$q->addQuery('contact_email, contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
		$q->addWhere('user_id = "' . $this->message_author . '"');
		$res = $q->exec();
		if ($row = $q->fetchRow()) {
			$message_from = $row['contact_first_name'] . ' ' . $row['contact_last_name'] . '<' . $row['contact_email'] . '>';
		} else {
			$message_from = 'Unknown user';
		}
		// Get the forum name;
		$q->clear();
		$q->addTable('forums');
		$q->addQuery('forum_name');
		$q->addWhere("forum_id = '{$this->message_forum}'");
		$res = $q->exec();
		if ($row = $q->fetchRow()) {
			$forum_name = $row['forum_name'];
		} else {
			$forum_name = 'Unknown';
		}

		// SQL-Query to check if the message should be delivered to all users (forced)
		// In positive case there will be a (0,0,0) row in the forum_watch table
		$q->clear();
		$q->addTable('forum_watch');
		$q->addQuery('*');
		$q->addWhere('watch_user = 0 AND watch_forum = 0 AND watch_topic = 0');
		$resAll = $q->exec();
		$AllCount = db_num_rows($resAll);

		$q->clear();
		$q->addTable('users');
		$q->addQuery('DISTINCT contact_email, user_id, contact_first_name, contact_last_name');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');

		if ($AllCount < 1) //message is only delivered to users that checked the forum watch
			{
			$q->addTable('forum_watch');
			$q->addWhere('user_id = watch_user AND (watch_forum = ' . $this->message_forum . ' OR watch_topic = ' . $this->message_parent . ')');
		}

		if (!($res = $q->exec(ADODB_FETCH_ASSOC))) {
			$q->clear();
			return;
		}
		if (db_num_rows($res) < 1) {
			return;
		}

		$mail = new Mail;
		$mail->Subject("$subj_prefix $this->message_title", isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "");

		$body = $body_msg;

		$body .= "\n\n" . $AppUI->_('Forum', UI_OUTPUT_RAW) . ": $forum_name";
		$body .= "\n" . $AppUI->_('Subject', UI_OUTPUT_RAW) . ": {$this->message_title}";
		$body .= "\n" . $AppUI->_('Message From', UI_OUTPUT_RAW) . ": $message_from";
		$body .= "\n\n" . W2P_BASE_URL . '/index.php?m=forums&a=viewer&forum_id=' . $this->message_forum;
		$body .= "\n\n$this->message_body";

		$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "");
		$mail->From($AppUI->_('forumEmailFrom', UI_OUTPUT_RAW));

		while ($row = $q->fetchRow()) {
			if ($mail->ValidEmail($row['contact_email'])) {
				$mail->To($row['contact_email'], true);
				$mail->Send();
			}
		}
		$q->clear();
		return;
	}
}
?>