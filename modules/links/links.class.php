<?php
/**
 * @package web2project\modules\misc
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
    public $link_datetime = null;
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
            $pattern = $q->quoteLike($search);
            $q->addWhere('(link_name LIKE ' . $pattern . ' OR link_description LIKE ' . $pattern . ')');
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
        $q = $task->setAllowedSQL($this->_AppUI->user_id, $q, 'link_task');
        $q->addOrder('project_name, link_name');

        return $q->loadList();
    }

    /**
     * A link is seen only by users who may see its project and task, the
     * rule the Links list applies.
     */
    public function canView()
    {
        return parent::canView() && $this->canSeeProjectAndTask($this->link_project, $this->link_task);
    }

    /**
     * A link is added only to a project and task the user may see
     * (GHSA-5748-w3cw-m24j). bind() takes both from the request.
     */
    public function canCreate()
    {
        return parent::canCreate() && $this->canUseProjectAndTask($this->link_project, $this->link_task);
    }

    /**
     * Edit permission on this link and, when a save moves it to another
     * project or task, the right to add a link there (GHSA-5748-w3cw-m24j).
     */
    public function canEdit()
    {
        if (!parent::canEdit()) {
            return false;
        }
        $stored = $this->storedValues(array('link_project', 'link_task'));
        if (is_null($stored)
            || (int) $stored['link_project'] != (int) $this->link_project
            || (int) $stored['link_task'] != (int) $this->link_task) {
            return $this->canUseProjectAndTask($this->link_project, $this->link_task);
        }

        return true;
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->link_name)) {
            $this->_error['link_name'] = $baseErrorMsg . 'link name is not set';
        }
        if (7 >= strlen(trim($this->link_url))) {
            $this->_error['link_url'] = $baseErrorMsg . 'link url is not set';
        } elseif ('' == w2p_safe_url(html_entity_decode($this->link_url, ENT_QUOTES))) {
            $this->_error['link_url'] = $baseErrorMsg . 'only http, https, ftp and mailto links are allowed';
        }

        return (count($this->_error)) ? false : true;
    }

    protected function hook_preStore()
    {
        $q = $this->_getQuery();
        $this->link_datetime = $q->dbfnNowWithTZ();

        $this->link_url = str_replace(array('"', '"', '<', '>'), '', $this->link_url);
        // Adds http:// when there is no scheme; isValid() rejected the rest.
        $safe = w2p_safe_url(html_entity_decode($this->link_url, ENT_QUOTES));
        $this->link_url = ('' == $safe) ? $this->link_url : htmlentities($safe, ENT_QUOTES);
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
