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
    public $message_title = null;
    public $message_date = null;
    public $message_body = null;
    public $message_published = null;

    public function __construct()
    {
        parent::__construct('forum_messages', 'message_id', 'forums');
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
                $q->addTable('forum_messages');
                $q->addQuery('count(message_id), MAX(message_date)');
                $q->addWhere('message_forum = ' . (int) $this->message_forum);
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

    public function canDelete($notUsed = '', $oid = null, $joins = null)
    {
	    global $AppUI;

        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = (int) $oid;
        }

        $q = $this->_getQuery();
        $q->addTable('forums');
        $q->addTable('forum_messages');
        $q->addQuery('forum_moderated, message_author, message_parent');
        $q->addWhere('forum_id = message_forum AND (message_id = ' . (int) $this->$k . ')');
        $rows = $q->loadList();

        if ($AppUI->user_id == $rows[0]['forum_moderated'] || canEdit('admin')) return true;
        if ($AppUI->user_id != $rows[0]['message_author']) return false;

        // Message authors may only delete the top post (i.e., the whole thread) if there are no replies
        $q = $this->_getQuery();
        $q->addTable('forum_messages');
        $q->addQuery('COUNT(*)');
        $q->addWhere('message_parent = ' . (int) $this->$k);

        return ((int)$q->loadResult()) == 0;
    }

    public function canEdit() { 
	    global $AppUI;

        $k = $this->_tbl_key;
        if ($oid) {
            $this->$k = (int) $oid;
        }

        $q = $this->_getQuery();
        $q->addTable('forums');
        $q->addTable('forum_messages');
        $q->addQuery('forum_moderated, message_author');
        $q->addWhere('forum_id = message_forum AND (message_id = ' . (int) $this->$k . ')');
        $rows = $q->loadList();

        return $AppUI->user_id == $rows[0]['forum_moderated'] || $AppUI->user_id == $rows[0]['message_author']
	        || canEdit('admin');

    }


    public function delete()
    {
        $result = false;

        if ($this->canDelete()) {
            $q = $this->_getQuery();
            $q->addTable('forum_messages');
            $q->addQuery('message_forum');
            $q->addWhere('message_id = ' . (int) $this->message_id);
            $forumId = $q->loadResult();
            $q->clear();

            $q->setDelete('forum_messages');
            $q->addWhere('message_id = ' . (int) $this->message_id);
            
            $result = parent::delete();

            $q->addTable('forum_messages');
            $q->addQuery('COUNT(message_id)');
            $q->addWhere('message_forum = ' . (int) $forumId);
            $messageCount = $q->loadResult();
            $q->clear();

            $q->addTable('forums');
            $q->addUpdate('forum_message_count', $messageCount);
            $q->addWhere('forum_id = ' . (int) $forumId);
            $q->exec();
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

        $message = new CForum_Message();
        $q = $this->_getQuery();
        $q->addTable('forum_messages');
        $q->addQuery('message_id');
        $q->addWhere('message_parent = ' . (int) $this->message_id);
        $q->exec();
        while ($row = $q->fetchRow()) {
	        $message->load($row['message_id']);
	        $message->delete();
        }
        $q->clear();
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
            if ($mail->ValidEmail($row['contact_email'])) {
                $mail->To($row['contact_email'], true);
                $mail->Send();
            }
        }
        $q->clear();
        return;
    }

}