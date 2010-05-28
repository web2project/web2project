<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once ($AppUI->getLibraryClass('PEAR/BBCodeParser'));
$bbparser = new HTML_BBCodeParser();

$filters = array('- Filters -');

if ($a == 'viewer') {
	array_push($filters, 'My Watched', 'Last 30 days');
} else {
	array_push($filters, 'My Forums', 'My Watched', 'My Projects', 'My Company', 'Inactive Projects');
}

class CForum extends CW2pObject {
	public $forum_id = null;
	public $forum_project = null;
	public $forum_status = null;
	public $forum_owner = null;
	public $forum_name = null;
	public $forum_create_date = null;
	public $forum_last_date = null;
	public $forum_last_id = null;
	public $forum_message_count = null;
	public $forum_description = null;
	public $forum_moderated = null;

	public function __construct() {
        parent::__construct('forums', 'forum_id');
	}

    public function check() {
        // ensure the integrity of some variables
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->forum_name)) {
            $errorArray['forum_name'] = $baseErrorMsg . 'forum name is not set';
        }
        if (0 == (int) $this->forum_owner) {
            $errorArray['forum_owner'] = $baseErrorMsg . 'forum owner is not set';
        }

        return $errorArray;
    }

    public function load(CAppUI $AppUI, $forum_id) {
        $q = new DBQuery();
        $q->addQuery('*');
        $q->addTable('forums');
        $q->addWhere('forum_id = ' . (int) $forum_id);
        $q->loadObject($this, true, false);
    }

    public function getAllowedForums($user_id, $company_id, $filter = -1, $orderby = 'forum_name', $orderdir = 'asc', $max_msg_length = 30)
    {
        $project = new CProject();

        $q = new DBQuery;
        $q->addTable('forums');
        //$q->addTable('projects', 'pr');
        
        $q->addQuery('forum_id, forum_project, forum_description, forum_owner, forum_name');
        $q->addQuery('forum_moderated, forum_create_date, forum_last_date');
        $q->addQuery('sum(if(c.message_parent=-1,1,0)) as forum_topics, sum(if(c.message_parent>0,1,0)) as forum_replies');
        $q->addQuery('user_username, project_name, project_color_identifier, CONCAT(contact_first_name,\' \',contact_last_name) owner_name');
        $q->addQuery('SUBSTRING(l.message_body,1,' . $max_msg_length . ') message_body');
        $q->addQuery('LENGTH(l.message_body) message_length, watch_user, l.message_parent, l.message_id');
        $q->addQuery('count(distinct v.visit_message) as visit_count, count(distinct c.message_id) as message_count');

        $q->addJoin('users', 'u', 'u.user_id = forum_owner');
        $q->addJoin('projects', 'pr', 'pr.project_id = forum_project');
        $q->addJoin('forum_messages', 'l', 'l.message_id = forum_last_id');
        $q->addJoin('forum_messages', 'c', 'c.message_forum = forum_id');
        $q->addJoin('forum_watch', 'w', 'watch_user = ' . $user_id . ' AND watch_forum = forum_id');
        $q->addJoin('forum_visits', 'v', 'visit_user = ' . $user_id . ' AND visit_forum = forum_id and visit_message = c.message_id');
        $q->addJoin('contacts', 'cts', 'contact_id = u.user_contact');

        $project->setAllowedSQL($user_id, $q, null, 'pr');
        $this->setAllowedSQL($user_id, $q);

        switch ($filter) {
            case 1:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND forum_owner = ' . $user_id);
                break;
            case 2:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND watch_user IS NOT NULL');
                break;
            case 3:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND project_owner = ' . $user_id);
                break;
            case 4:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND project_company = ' . $company_id);
                break;
            case 5:
                $q->addWhere('(project_active = 0 OR forum_project = 0)');
                break;
            default:
                $q->addWhere('(project_active = 1 OR forum_project = 0)');
                break;
        }

        $q->addGroup('forum_id');
        $q->addOrder($orderby . ' ' . $orderdir);

        return $q->loadList();
    }

	public function store(CAppUI $AppUI) {
        $perms = $AppUI->acl();
        $stored = false;

        $errorMsgArray = $this->check();

        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        if ($this->forum_id && $perms->checkModuleItem('forums', 'edit', $this->forum_id)) {
            if (($msg = parent::store())) {
                return $msg;
            }
            addHistory('forums', $this->forum_id, 'update', $this->forum_name, $this->forum_id);
            $stored = true;
        }
        if (0 == $this->forum_id && $perms->checkModuleItem('forums', 'add')) {
            $q = new DBQuery;
            $this->forum_create_date = $q->dbfnNow();
            if (($msg = parent::store())) {
                return $msg;
            }
            addHistory('forums', $this->forum_id, 'add', $this->forum_name, $this->forum_id);
            $stored = true;
        }
        return $stored;
	}

	public function delete(CAppUI $AppUI = null) {
        global $AppUI;

        $perms = $AppUI->acl();
        $result = false;

        if ($perms->checkModuleItem('forums', 'delete', $this->project_id)) {
            $q = new DBQuery;
            $q->setDelete('forum_visits');
            $q->addWhere('visit_forum = ' . (int)$this->forum_id);
            $q->exec(); // No error if this fails, it is not important.
            $q->clear();

            $q->setDelete('forums');
            $q->addWhere('forum_id = ' . (int)$this->forum_id);
            if (!$q->exec()) {
                $q->clear();
                return db_error();
            }
            $q->clear();
            $q->setDelete('forum_messages');
            $q->addWhere('message_forum = ' . (int)$this->forum_id);
            if (!$q->exec()) {
                $result = db_error();
            } else {
                addHistory('forums', $this->forum_id, 'delete', $this->forum_name);
                $result = null;
            }
        }
		return $result;
	}

	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		global $AppUI;
		$oPrj = new CProject();

		$aPrjs = $oPrj->getAllowedRecords($uid, 'projects.project_id, project_name', '', null, null, 'projects');
		if (count($aPrjs)) {
			$buffer = '(forum_project IN (' . implode(',', array_keys($aPrjs)) . ') OR forum_project IS NULL OR forum_project = \'\' OR forum_project = 0)';

			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND ' . $buffer;
			} else {
				$extra['where'] = $buffer;
			}
		} else {
			// There are no allowed projects, so only allow forums with no project associated.
			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND (forum_project IS NULL OR forum_project = \'\' OR forum_project = 0) ';
			} else {
				$extra['where'] = '(forum_project IS NULL OR forum_project = \'\' OR forum_project = 0)';
			}
		}
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);

	}
}

class CForumMessage extends CW2pObject {
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

        return $errorArray;
	}

	public function store(CAppUI $AppUI) {
        global $AppUI;

        $perms = $AppUI->acl();
        $stored = false;

        $errorMsgArray = $this->check();

        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        if ($this->message_id && $perms->checkModuleItem('forums', 'edit', $this->forum_id)) {
            $q = new DBQuery;
            $q->setDelete('forum_visits');
            $q->addWhere('visit_message = ' . (int)$this->message_id);
			$q->exec();

            if (($msg = parent::store())) {
                return $msg;
            }
            addHistory('forum_messages', $this->message_id, 'update', $this->message_title, $this->message_id);
            $stored = true;
        }
        if (0 == $this->message_id && $perms->checkModuleItem('forums', 'add')) {
            $q = new DBQuery;
            $this->message_date = $q->dbfnNow();
            if (($msg = parent::store())) {
                return $msg;
            }
            addHistory('forum_messages', $this->message_id, 'add', $this->message_title, $this->message_id);

			$q->addTable('forum_messages');
			$q->addQuery('count(message_id), MAX(message_date)');
			$q->addWhere('message_forum = ' . (int)$this->message_forum);
            $reply = $q->fetchRow();

			//update forum descriptor
			$forum = new CForum();
            $forum->load($AppUI, $this->message_forum);
			$forum->forum_message_count = $reply[0];
			$forum->forum_last_date = $reply[1];
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
            $q = new DBQuery;
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
        }
		return $result;
	}

	public function sendWatchMail($debug = false) {
		global $AppUI, $debug, $w2Pconfig;
		$subj_prefix = $AppUI->_('forumEmailSubj', UI_OUTPUT_RAW);
		$body_msg = $AppUI->_('forumEmailBody', UI_OUTPUT_RAW);

		// Get the message from details.
		$q = new DBQuery;
		$q->addTable('users', 'u');
		$q->addQuery('contact_email, contact_first_name, contact_last_name');
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
		$q->addQuery('DISTINCT contact_email, user_id, contact_first_name, contact_last_name');
		$q->leftJoin('contacts', 'con', 'contact_id = user_contact');

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

		$mail = new Mail;
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