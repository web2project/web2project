<?php
/**
 * @package     web2project\modules\misc
 */

class CForum extends w2p_Core_BaseObject
{

    public $forum_id = null;
    public $forum_project = null;
    public $forum_status = null;
    public $forum_owner = null;
    public $forum_name = null;
    // @todo this should be forum_create_datetime to take advantage of our templating
    public $forum_create_date = null;
    // @todo this should be forum_last_datetime to take advantage of our templating
    public $forum_last_date = null;
    public $forum_last_id = null;
    public $forum_message_count = null;
    public $forum_description = null;
    public $forum_moderated = null;

    public function __construct()
    {
        parent::__construct('forums', 'forum_id');
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->forum_name)) {
            $this->_error['forum_name'] = $baseErrorMsg . 'forum name is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    public function getMessages($notUsed = null, $forum_id = 0, $message_id = 0, $sortDir = 'asc')
    {
        $q = $this->_getQuery();
        $q->addTable('forums');
        $q->addTable('forum_messages');
        $q->addQuery('forum_messages.*,	contact_first_name, contact_last_name, contact_email,
            contact_display_name, contact_display_name as contact_name, user_username, forum_moderated, visit_user');
        $q->addJoin('forum_visits', 'v', 'visit_user = ' . (int) $this->_AppUI->user_id . ' AND visit_forum = ' . (int) $forum_id . ' AND visit_message = forum_messages.message_id');
        $q->addJoin('users', 'u', 'message_author = u.user_id', 'inner');
        $q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
        $q->addWhere('forum_id = message_forum AND (message_id = ' . (int) $message_id . ' OR message_parent = ' . (int) $message_id . ')');
        $q->addOrder('message_date ' . $sortDir);

        return $q->loadList();
    }

    public function getAllowedForums($user_id, $company_id, $filter = -1, $orderby = 'forum_name', $orderdir = 'asc', $max_msg_length = 30)
    {
        $project = new CProject();
        $project->overrideDatabase($this->_query);

        $q = $this->_getQuery();
        $q->addTable('forums');

        $q->addQuery('forum_id, forum_project, forum_description, forum_owner, forum_name');
        $q->addQuery('forum_moderated, forum_create_date, forum_last_date');
        $q->addQuery('sum(if(c.message_parent=-1,1,0)) as forum_topics, sum(if(c.message_parent>0,1,0)) as forum_replies');
        $q->addQuery('user_username, project_name, project_color_identifier, contact_display_name as owner_name, user_id');
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

        $q = $project->setAllowedSQL($user_id, $q, null, 'pr');
        $q = $this->setAllowedSQL($user_id, $q);

        switch ($filter) {
            case 1:
                $q->addWhere('forum_owner = ' . $user_id);
                break;
            case 2:
                $q->addWhere('watch_user = ' . $user_id);
                break;
            case 3:
                $q->addWhere('project_owner = ' . $user_id);
                break;
            case 4:
                $q->addWhere('project_company = ' . $company_id);
                break;
            case 5:
                $q->addWhere('(project_active = 0 OR forum_project = 0)');
                break;
            default:
                $q->addWhere('(project_active = 1 OR forum_project = 0)');
                break;
        }

        $q->addGroup('forum_id');
        $orderby = (property_exists($this, $orderby)) ? $orderby : 'forum_name';
        $q->addOrder($orderby . ' ' . $orderdir);

        return $q->loadList();
    }

    protected function hook_preCreate() {
        $this->forum_create_date = $this->_AppUI->convertToSystemTZ($this->forum_create_date);

        parent::hook_preCreate();
    }

    protected function hook_preStore()
    {
        $this->forum_owner = (int) $this->forum_owner ? $this->forum_owner : $this->_AppUI->user_id;
    }

    public function delete($unused = null)
    {
        $result = false;

        if ($this->canDelete()) {
            $q = $this->_getQuery();
            $q->setDelete('forum_messages');
            $q->addWhere('message_forum = ' . (int) $this->forum_id);
            if (!$q->exec()) {
                $this->_error['delete-messages'] = db_error();
                return false;
            }

            $result = parent::delete();
        }
        return $result;
    }

    protected function hook_preDelete()
    {
        $q = $this->_getQuery();
        $q->setDelete('forum_visits');
        $q->addWhere('visit_forum = ' . (int) $this->forum_id);
        $q->exec();
    }

    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $unused = '')
    {
        $oPrj = new CProject();
        $oPrj->overrideDatabase($this->_query);

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

    public function hook_search()
    {
        $search['table'] = 'forums';
        $search['table_alias'] = 'f';
        $search['table_module'] = 'forums';
        $search['table_key'] = 'f.forum_id';
        $search['table_link'] = 'index.php?m=forums&a=viewer&forum_id='; // first part of link
        $search['table_key2'] = 'fm.message_id';
        $search['table_link2'] = '&message_id='; // second part of link

        $search['table_title'] = 'Forums';
        $search['table_orderby'] = 'forum_name';
        $search['search_fields'] = array(
            'forum_name', 'forum_description',
            'message_title', 'message_body'
        );
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(
            array(
                'table' => 'forum_messages',
                'alias' => 'fm',
                'join' => 'f.forum_id = fm.message_forum'
            )
        );

        return $search;
    }
}