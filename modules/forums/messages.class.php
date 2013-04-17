<?php

/**
 * @package     web2project\modules\misc
 */

class CForum_Message extends w2p_Core_BaseObject
{

    public $message_id = null;
    public $message_forum = null;
    public $message_parent = null;
    public $message_author = null;
    public $message_editor = null;
    public $message_task = null;
    public $message_title = null;
    public $message_date = null;
    public $message_body = null;
    public $message_published = null;

    public $_project_id = null;

    public function __construct()
    {
        parent::__construct('forum_messages', 'message_id');
	$this->_tbl_project_id = '_project_id';
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if (0 == (int) $this->message_forum) {
            $this->_error['message_forum'] = $baseErrorMsg . 'forum is not set';
        }
        if (0 == (int) $this->message_author) {
            $this->_error['message_author'] = $baseErrorMsg . 'message author is not set';
        }
        if ('' == trim($this->message_title)) {
            $this->_error['message_title'] = $baseErrorMsg . 'message title is not set';
        }
        if ('' == trim($this->message_body)) {
            $this->_error['message_body'] = $baseErrorMsg . 'message body is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    public function store()
    {
        $stored = false;

        $q = $this->_getQuery();

        if ($this->{$this->_tbl_key} && $this->canEdit()) {
            $q->setDelete('forum_visits');
            $q->addWhere('visit_message = ' . (int) $this->message_id);
            $q->exec();

            $stored = parent::store();
        }

        if (0 == $this->{$this->_tbl_key} && $this->canCreate()) {
            $this->message_date = $q->dbfnNowWithTZ();
            
            $stored = parent::store();

            if ($stored) {
		$q->clear();
                $q->addTable('forum_messages');
                $q->addQuery('count(message_id), MAX(message_date)');
                $q->addWhere('message_forum = ' . (int) $this->message_forum);
	        $q->exec();
                $reply = $q->fetchRow();

                //update forum descriptor
                $forum = new CForum();
                $forum->overrideDatabase($this->_query);
                $forum->load(null, $this->message_forum);
                $forum->forum_message_count = $reply[0];
                /*
                 * Note: the message_date here has already been adjusted for the
                 *    timezone above, so don't do it again!
                 */
                $forum->forum_last_date = $this->message_date;
                $forum->forum_last_id = $this->message_id;
                $forum->store();

                $this->sendWatchMail(false);
            }
        }
        return $stored;
    }

    public function delete()
    {
        $result = false;

        $q = $this->_getQuery();

        if ($this->canDelete()) {
            $result = parent::delete();

	    if ($result) {
		$q->clear();
                $q->addTable('forum_messages');
                $q->addQuery('count(message_id), MAX(message_date)');
                $q->addWhere('message_forum = ' . (int) $this->message_forum);
	        $q->exec();
                $reply = $q->fetchRow();

                //update forum descriptor
                $forum = new CForum();
                $forum->overrideDatabase($this->_query);
                $forum->load(null, $this->message_forum);
                $forum->forum_message_count = $reply[0];
                /*
                 * Note: the message_date here has already been adjusted for the
                 *    timezone above, so don't do it again!
                 */
                $forum->forum_last_date = $this->message_date;
                $forum->forum_last_id = $this->message_id;
                $forum->store();
	    }
        }
        return $result;
    }

    protected function hook_preDelete()
    {
        $q = $this->_getQuery();
        $q->setDelete('forum_visits');
        $q->addWhere('visit_message = ' . (int) $this->message_id);
        $q->exec(); // No error if this fails, it is not important.
        $q->clear();
        $q->setDelete('forum_watch');
        $q->addWhere('watch_topic = ' . (int) $this->message_id);
        $q->exec(); // No error if this fails, it is not important.
    }

    public function loadByParent($parent_id = 0)
    {

        $q = $this->_getQuery();
        $q->addTable('forum_messages');
        $q->addWhere('message_parent = ' . $parent_id);
        $q->addOrder('message_id DESC'); // fetch last message first

        $q->loadObject($this, true, false);
    }

    public function sendWatchMail($debug = false)
    {
        global $debug;

        // Get the message from details.
        $q = $this->_getQuery();
        $q->clear();
        $q->addTable('users', 'u');
        $q->addQuery('contact_first_name, contact_last_name, contact_email');
        $q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
        $q->addWhere('user_id = ' . (int) $this->message_author);
        $q->exec();
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
            $q->addQuery('SUM(forum_watch.notify_by_email) AS notifies');
            $q->addWhere('user_id = watch_user AND (watch_forum = ' . (int) $this->message_forum . ' OR watch_topic = ' . (int) $this->message_parent . ')');
        }

        if (!($res = $q->exec(ADODB_FETCH_ASSOC))) {
            $q->clear();
            return;
        }
        if (db_num_rows($res) < 1) {
            return;
        }

        $mail = new w2p_Utilities_Mail();
        $subj_prefix = $this->_AppUI->_('forumEmailSubj', UI_OUTPUT_RAW);
        $mail->Subject($subj_prefix . ' ' . $this->message_title, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

        $emailManager = new w2p_Output_EmailManager($this->_AppUI);
        $body = $emailManager->getForumWatchEmail($this, $forum_name, $message_from);
        $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

        while ($row = $q->fetchRow()) {
	    // If either the forum or the topic has a 'notify by email' flag set, do it
            if ($row['notifies'] && $mail->ValidEmail($row['contact_email'])) {
                $mail->To($row['contact_email'], true);
                $mail->Send();
            }
        }
        $q->clear();
        return;
    }

    // We can now check for edit permissions on messages, but not individually. So the following users are allowed to edit a forum message: 
    //
    //   1. Someone with edit permissions over forum messages
    //   2. The forum moderator
    //   3. The message author
    public function canEdit() 
    {
        $forum = new CForum();
        $forum->overrideDatabase($this->_query);
        $forum->load(null, $this->message_forum);

	return ($this->_AppUI->user_id == $forum->forum_moderated) || 
               ($this->_AppUI->user_id == $this->message_author) || 
	       $this->_perms->checkModule('forum_messages', 'edit');
    }

    // We can now check for delete permissions on messages, but not individual. So the following users are allowed to delete a forum message: 
    //
    //   1. Someone with delete permissions over forum messages
    //   2. The forum moderator
    //   3. The message author
    public function canDelete() 
    {
	// If there's replies to it don't let it be deleted.
	if ($this->message_parent == -1) {
	        $q = $this->_getQuery();
	        $q->addTable('forum_messages');
	        $q->addWhere('message_parent = ' . $this->message_id);
	        $q->addQuery('count(message_id)');
		$rep_cnt = $q->loadResult();
		if ($rep_cnt) {
	            $this->_error['cantDeleteParent'] = $this->_AppUI->_('A topic with replies cannot be deleted');
		    return false;
		}
	}

        $forum = new CForum();
        $forum->overrideDatabase($this->_query);
        $forum->load(null, $this->message_forum);

	return ($this->_AppUI->user_id == $forum->forum_moderated) || 
               ($this->_AppUI->user_id == $this->message_author) || 
	       $this->_perms->checkModule('forum_messages', 'delete');
    }

    public static function getHRef($forum_id, $msg_id)
    {
	   return 'm=forums&a=viewer&forum_id=' . (string)$forum_id . '&message_id=' . (string)$msg_id . '&sort=asc';
    }

    protected function generateHistoryDescription($event) {
        global $AppUI;

	// Fill an aliased variable with the project associated with the forum containing this message
        $forum = new CForum();
        $forum->overrideDatabase($this->_query);
        $forum->load(null, $this->message_forum);
	$this->_project_id = $forum->forum_project;

	$event = mb_strtolower($event);
	if ($event == 'create') {
		return $AppUI->_('Forum message') . ' \'' . $this->message_title . '\' ' . $AppUI->_('was created with ID') . ' ' . $this->message_id;
	} elseif ($event == 'update') {
		return $AppUI->_('Forum message') . ' \'' . $this->message_title . '\', ' . $AppUI->_('with ID') . ' ' . $this->message_id . ', ' . $AppUI->_('was edited');
	} elseif ($event == 'delete') {
		return $AppUI->_('Forum message') . ' \'' . $this->message_title . '\', ' . $AppUI->_('with ID') . ' ' . $this->message_id . ', ' . $AppUI->_('was deleted');
	} else {
		return parent::generateHistoryDescription($event);
	}
    }

    public static function getTopicsByTask($AppUI, $task_id) {
        $q = new w2p_Database_Query();
        $q->addTable('forum_messages', 'fm');
	$q->leftJoin('forums', 'f', 'f.forum_id = fm.message_forum');
	$q->leftJoin('forum_messages', 'fm2', 'fm.message_id = fm2.message_parent');
	$q->leftJoin('users', 'u', 'fm.message_author = u.user_id');
        $q->addWhere('fm.message_task = ' . $task_id);
        $q->addQuery('f.forum_name, fm.message_title as message_name, fm.message_author');
	$q->addQuery('COUNT(distinct fm2.message_id) AS replies, MAX(fm2.message_date) AS latest_reply');
	$q->addQuery('u.user_id, fm.message_id, fm.message_forum as forum_id');
	$q->addGroup('fm.message_id, fm.message_parent');
	return $q->loadList();
    }

    public function getWatchers() {
        $q = $this->_getQuery();
        $q->addTable('forum_watch');
        $q->addQuery('watch_user');
	$q->addWhere('watch_topic = ' . (int)$this->message_id);
        return $q->loadColumn();
    }

    public function setWatchers() {
	// This operation will be done in three steps to preserve the
	// email notification settings of watchers already present.
	$selected_watchers = w2PgetParam($_POST, 'topic_watchers', '');
	// Delete any watcher not present on the new list
        $q = $this->_getQuery();
        $q->setDelete('forum_watch');
	if (strlen($selected_watchers) > 0) {
		$q->addWhere('watch_topic = ' . $this->message_id . ' AND watch_user NOT IN (' . $selected_watchers . ')');
	} else {
		$q->addWhere('watch_topic = ' . $this->message_id);
	}
	$q->exec();
	$q->clear();
	// Get the remaining watchers
	$already = $this->getWatchers();
	// Compute the difference so that we're left only with watchers not already set.
	$watchers = explode(',', $selected_watchers);
	$watchers = array_diff($watchers, $already);
	// Insert the new watchers
	foreach ($watchers as $watch) {
		if ((int)$watch) {
			$q->addTable('forum_watch');
			$q->addInsert('watch_user', $watch);
			$q->addInsert('watch_topic', $this->message_id);
			$q->addInsert('notify_by_email', false);
	                $q->exec();
	                $q->clear();
		}
	}
    }

    protected function hook_postStore() {
	if ($this->message_parent == -1) {
		$this->setWatchers();
	}
        parent::hook_postStore();
    }
}