<?php
/**
 * @package     web2project\modules\misc
 */

class CResource extends w2p_Core_BaseObject
{
    public $resource_id = null;
    public $resource_key = null;
    public $resource_name = null;
    public $resource_type = null;
    public $resource_max_allocation = null;
    public $resource_description = null;

    public function __construct()
    {
        parent::__construct('resources', 'resource_id');
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->resource_name)) {
            $this->_error['resource_name'] = $baseErrorMsg . 'resource name is not set';
        }
        if ('' == trim($this->resource_key)) {
            $this->_error['resource_key'] = $baseErrorMsg . 'resource key is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    public function getResourcesByTask($task_id)
    {
        $q = $this->_getQuery();
        $q->addQuery('a.*');
        $q->addQuery('b.percent_allocated');
        $q->addTable('resources', 'a');
        $q->addJoin('resource_tasks', 'b', 'b.resource_id = a.resource_id', 'inner');
        $q->addWhere('b.task_id = ' . (int) $task_id);

        return $q->loadHashList('resource_id');
    }

    public function getTasksByResources($resources, $start_date, $end_date)
    {
        $q = $this->_getQuery();
        $q->addQuery('b.resource_id, sum(b.percent_allocated) as total_allocated');
        $q->addTable('tasks', 'a');
        $q->addJoin('resource_tasks', 'b', 'b.task_id = a.task_id', 'inner');
        $q->addWhere('b.resource_id IN (' . implode(',', array_keys($resources)) . ')');
        $q->addWhere("task_start_date <= '$end_date'");
        $q->addWhere("task_end_date   >= '$start_date'");
        $q->addGroup('resource_id');

        return $q->loadHashList();
    }

    public function hook_search()
    {
        $search['table'] = 'resources';
        $search['table_module'] = 'resources';
        $search['table_key'] = 'resource_id';
        $search['table_link'] = 'index.php?m=resources&a=view&resource_id='; // first part of link
        $search['table_title'] = 'Resources';
        $search['table_orderby'] = 'resource_name';
        $search['search_fields'] = array('resource_name', 'resource_key','resource_note');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }
}