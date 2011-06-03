<?php

class CForumMessage extends w2p_Core_BaseObject {
	public $message_id = null;
	public $message_forum = null;
	public $message_parent = null;
	public $message_author = null;
	public $message_editor = null;
	public $message_title = null;
	public $message_date = null;
	public $message_body = null;
	public $message_published = null;

	public function __construct() {
		parent::__construct('forum_messages', 'message_id');
	}

	public function check() {
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if (0 == (int) $this->message_forum) {
            $errorArray['message_forum'] = $baseErrorMsg . 'forum is not set';
        }
        if (0 == (int) $this->message_author) {
            $errorArray['message_author'] = $baseErrorMsg . 'message author is not set';
        }
        if ('' == trim($this->message_title)) {
            $errorArray['message_title'] = $baseErrorMsg . 'message title is not set';
        }
        if ('' == trim($this->message_body)) {
            $errorArray['message_body'] = $baseErrorMsg . 'message body is not set';
        }

        $this->_error = $errorArray;
        return $errorArray;
	}

	public function store(CAppUI $AppUI = null) {
        global $AppUI;

        $perms = $AppUI->acl();
        $stored = false;

        $errorMsgArray = $this->check();

        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        $q = new w2p_Database_Query;

        if ($this->message_id && $perms->checkModuleItem('forums', 'edit', $this->forum_id)) {
            $q->setDelete('forum_visits');
            $q->addWhere('visit_message = ' . (int)$this->message_id);
			$q->exec();

            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->message_id && $perms->checkModuleItem('forums', 'add')) {
            $this->message_date = $q->dbfnNowWithTZ();
            if (($msg = parent::store())) {
                return $msg;
            }

			$q->addTable('forum_messages');
			$q->addQuery('count(message_id), MAX(message_date)');
			$q->addWhere('message_forum = ' . (int)$this->message_forum);
            $reply = $q->fetchRow();

			//update forum descriptor
			$forum = new CForum();
            $forum->load($AppUI, $this->message_forum);
			$forum->forum_message_count = $reply[0];
			/*
             * Note: the message_date here has already been adjusted for the
            *    timezone above, so don't do it again!
             */
            $forum->forum_last_date = $this->message_date;
			$forum->forum_last_id = $this->message_id;
			$forum->store($AppUI);

			$this->sendWatchMail(false);
            $stored = true;
        }
        return $stored;
	}

	public function delete(CAppUI $AppUI = null) {
        global $AppUI;

        $perms = $AppUI->acl();
        $result = false;

        if ($perms->checkModuleItem('forums', 'delete', $this->project_id)) {
            $q = new w2p_Database_Query;
            $q->setDelete('forum_visits');
            $q->addWhere('visit_message = ' . (int)$this->message_id);
            $q->exec(); // No error if this fails, it is not important.
            $q->clear();

            $q->addTable('forum_messages');
            $q->addQuery('message_forum');
            $q->addWhere('message_id = ' . (int)$this->message_id);
            $forumId = $q->loadResult();
            $q->clear();

            $q->setDelete('forum_messages');
            $q->addWhere('message_id = ' . (int)$this->message_id);
            if (!$q->exec()) {
                $result = db_error();
            } else {
                $result = null;
            }
            $q->clear();

            $q->addTable('forum_messages');
            $q->addQuery('COUNT(message_id)');
            $q->addWhere('message_forum = ' . (int)$forumId);
            $messageCount = $q->loadResult();
            $q->clear();

            $q->addTable('forums');
            $q->addUpdate('forum_message_count', $messageCount);
            $q->addWhere('forum_id = ' . (int)$forumId);
            $q->exec();
            $result = true;
        }
		return $result;
	}

    public function loadByParent($parent_id = 0) {
        $q = new w2p_Database_Query();
        $q->addTable('forum_messages');
        $q->addWhere('message_parent = ' . $parent_id);
        $q->addOrder('message_id DESC'); // fetch last message first

        $q->loadObject($this, true, false);
    }

	public function sendWatchMail($debug = false) {
		global $AppUI, $debug, $w2Pconfig;
		$subj_prefix = $AppUI->_('forumEmailSubj', UI_OUTPUT_RAW);
		$body_msg = $AppUI->_('forumEmailBody', UI_OUTPUT_RAW);

		// Get the message from details.
		$q = new w2p_Database_Query;
		$q->addTable('users', 'u');
		$q->addQuery('contact_first_name, contact_last_name, contact_email');
		$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
		$q->addWhere('user_id = ' . (int)$this->message_author);
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
		$q->addWhere('forum_id = \'' . $this->message_forum . '\'');
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
		$q->addQuery('DISTINCT user_id, contact_first_name, contact_last_name, contact_email');
		$q->leftJoin('contacts', 'con', 'con.contact_id = user_contact');

		if ($AllCount < 1) {
		//message is only delivered to users that checked the forum watch
			$q->addTable('forum_watch');
			$q->addWhere('user_id = watch_user AND (watch_forum = ' . (int)$this->message_forum . ' OR watch_topic = ' . (int)$this->message_parent . ')');
		}

		if (!($res = $q->exec(ADODB_FETCH_ASSOC))) {
			$q->clear();
			return;
		}
		if (db_num_rows($res) < 1) {
			return;
		}

		$mail = new w2p_Utilities_Mail();
		$mail->Subject($subj_prefix . ' ' . $this->message_title, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

		$body = $body_msg;

		$body .= "\n\n" . $AppUI->_('Forum', UI_OUTPUT_RAW) . ': ' . $forum_name;
		$body .= "\n" . $AppUI->_('Subject', UI_OUTPUT_RAW) . ': ' . $this->message_title;
		$body .= "\n" . $AppUI->_('Message From', UI_OUTPUT_RAW) . ': ' . $message_from;
		$body .= "\n\n" . W2P_BASE_URL . '/index.php?m=forums&a=viewer&forum_id=' . $this->message_forum;
		$body .= "\n\n" . $this->message_body;

		$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

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