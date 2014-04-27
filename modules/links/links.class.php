<?php
/**
 * @package     web2project\modules\misc
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
    // @todo this should be link_datetime to take advantage of our templating
    public $link_date = null;
    public $link_icon = null;
    public $link_category = null;

    public function __construct()
    {
        parent::__construct('links', 'link_id');
    }

    public function getProjectTaskLinksByCategory($notUsed = null, $project_id = 0, $task_id = 0, $category_id = 0, $search = '')
    {
        // load the following classes to retrieved denied records

        $project = new CProject();
        $project->overrideDatabase($this->_query);
        $task = new CTask();
        $task->overrideDatabase($this->_query);

        // SETUP FOR LINK LIST
        $q = $this->_getQuery();
        $q->addQuery('links.*');
        $q->addTable('links');

        $q->leftJoin('projects', 'pr', 'project_id = link_project');
        $q->leftJoin('tasks', 't', 'task_id = link_task');

        if ($search != '') {
            $q->addWhere('(link_name LIKE \'%' . $search . '%\' OR link_description LIKE \'%' . $search . '%\')');
        }
        if ($project_id > 0) { // Project
            $q->addQuery('project_name, project_color_identifier, project_status');
            $q->addWhere('link_project = ' . (int) $project_id);
        }
        if ($task_id > 0) { // Task
            $q->addQuery('task_name, task_id');
            $q->addWhere('link_task = ' . (int) $task_id);
        }
        if ($category_id >= 0) { // Category
            $q->addWhere('link_category = ' . $category_id);
        }
        // Permissions
        $q = $project->setAllowedSQL($this->_AppUI->user_id, $q, 'link_project');
        $q = $task->setAllowedSQL($this->_AppUI->user_id, $q, 'link_task and task_project = link_project');
        $q->addOrder('project_name, link_name');

        return $q->loadList();
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->link_name)) {
            $this->_error['link_name'] = $baseErrorMsg . 'link name is not set';
        }
        if (7 >= strlen(trim($this->link_url))) {
            $this->_error['link_url'] = $baseErrorMsg . 'link url is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    protected function hook_preStore()
    {
        $q = $this->_getQuery();
        $this->link_date = $q->dbfnNowWithTZ();

        if (strpos($this->link_url, ':') === false && strpos($this->link_url, "//") === false) {
            $this->link_url = 'http://' . $this->link_url;
        }
        $this->link_url = str_replace(array('"', '"', '<', '>'), '', $this->link_url);
        $this->link_owner = (int) $this->link_owner ? $this->link_owner : $this->_AppUI->user_id;
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
