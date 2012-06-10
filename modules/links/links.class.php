<?php /* $Id$ $URL$ */

/**
 * 	@package web2Project
 * 	@subpackage modules
 * 	@version $Revision$
 */
class CLink extends w2p_Core_BaseObject
{

    public $link_id = null;
    public $link_project = null;
    public $link_url = null;
    public $link_task = null;
    public $link_name = null;
    public $link_parent = null;
    public $link_description = null;
    public $link_owner = null;
    public $link_date = null;
    public $link_icon = null;
    public $link_category = null;

    public function __construct()
    {
        parent::__construct('links', 'link_id');
    }

    public function loadFull($AppUI = null, $link_id)
    {

        $q = $this->_getQuery();
        $q->addQuery('links.*');
        $q->addQuery('user_username');
        $q->addQuery('contact_first_name, contact_last_name, contact_display_name as contact_name');
        $q->addQuery('project_id');
        $q->addQuery('task_id, task_name');
        $q->addTable('links');
        $q->leftJoin('users', 'u', 'link_owner = user_id');
        $q->leftJoin('contacts', 'c', 'user_contact = contact_id');
        $q->leftJoin('projects', 'p', 'project_id = link_project');
        $q->leftJoin('tasks', 't', 'task_id = link_task');
        $q->addWhere('link_id = ' . (int) $link_id);
        $q->loadObject($this, true, false);
    }

    public function getProjectTaskLinksByCategory($AppUI = null, $project_id = 0, $task_id = 0, $category_id = 0, $search = '')
    {
        // load the following classes to retrieved denied records

        $project = new CProject();
        $project->overrideDatabase($this->_query);
        $task = new CTask();
        $task->overrideDatabase($this->_query);

        // SETUP FOR LINK LIST
        $q = $this->_getQuery();
        $q->addQuery('DISTINCT links.*');
        $q->addQuery('contact_first_name, contact_last_name, contact_display_name as contact_name');
        $q->addQuery('project_name, project_color_identifier, project_status');
        $q->addQuery('task_name, task_id');

        $q->addTable('links');

        $q->leftJoin('users', 'u', 'user_id = link_owner');
        $q->leftJoin('contacts', 'c', 'user_contact = contact_id');

        if ($search != '') {
            $q->addWhere('(link_name LIKE \'%' . $search . '%\' OR link_description LIKE \'%' . $search . '%\')');
        }
        if ($project_id > 0) { // Project
            $q->addWhere('link_project = ' . (int) $project_id);
        }
        if ($task_id > 0) { // Task
            $q->addWhere('link_task = ' . (int) $task_id);
        }
        if ($category_id >= 0) { // Category
            $q->addWhere('link_category = ' . $category_id);
        }
        // Permissions
        $project->setAllowedSQL($this->_AppUI->user_id, $q, 'link_project');
        $task->setAllowedSQL($this->_AppUI->user_id, $q, 'link_task and task_project = link_project');
        $q->addOrder('project_name, link_name');

        return $q->loadList();
    }

    public function check()
    {
        // ensure the integrity of some variables
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->link_name)) {
            $errorArray['link_name'] = $baseErrorMsg . 'link name is not set';
        }
        if (7 >= strlen(trim($this->link_url))) {
            $errorArray['link_url'] = $baseErrorMsg . 'link url is not set';
        }
        if (0 == (int) $this->link_owner) {
            $errorArray['link_owner'] = $baseErrorMsg . 'link owner is not set';
        }

        $this->_error = $errorArray;
        return $errorArray;
    }

    public function delete()
    {
        $result = false;

        if ($this->canDelete()) {
            $result = parent::delete();
        }
        return $result;
    }

    public function store()
    {
        $stored = false;

        if (strpos($this->link_url, ':') === false && strpos($this->link_url, "//") === false) {
            $this->link_url = 'http://' . $this->link_url;
        }

        $this->_error = $this->check();

        if (count($this->_error)) {
            return $this->_error;
        }
        /*
         * TODO: I don't like the duplication on each of these two branches, but I
         *   don't have a good idea on how to fix it at the moment...
         */
        $q = $this->_getQuery();
        $this->link_date = $q->dbfnNowWithTZ();
        if ($this->{$this->_tbl_key} && $this->canEdit()) {
            if (($msg = parent::store())) {
                $this->_error['store'] = $msg;
            } else {
                $stored = true;
            }
        }
        if (0 == $this->{$this->_tbl_key} && $this->canCreate()) {
            if (($msg = parent::store())) {
                $this->_error['store'] = $msg;
            } else {
                $stored = true;
            }
        }
        return $stored;
    }

    public function hook_search()
    {
        $search['table'] = 'links';
        $search['table_alias'] = 'l';
        $search['table_module'] = 'links';
        $search['table_key'] = 'link_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=links&a=addedit&link_id='; // first part of link
        $search['table_title'] = 'Links';
        $search['table_orderby'] = 'link_name';
        $search['search_fields'] = array('l.link_name', 'l.link_url', 'l.link_description');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }

}
