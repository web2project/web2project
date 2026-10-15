<?php
/**
 * @package web2project\modules\misc
 */

class CResource extends w2p_Core_BaseObject
{
    public $resource_id = null;
    public $resource_key = null;
    public $resource_name = null;
    public $resource_type = null;
    public $resource_max_allocation = null;
    public $resource_description = null;

    public function __construct() {
        parent::__construct('resources', 'resource_id');
    }

    public function isValid()
    {
        $this->_error = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->resource_name)) {
            $this->_error['resource_name'] = $baseErrorMsg . 'resource name is not set';
        }
        if ('' == trim($this->resource_key)) {
            $this->_error['resource_key'] = $baseErrorMsg . 'resource key is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    /**
     * @deprecated
     */
    public function &loadTypes() {
        trigger_error("CResource->loadTypes() has been deprecated in v3.0 and will be removed in v4.0. Please use w2PgetSysVal('ResourceTypes') instead.", E_USER_NOTICE);

        return $this->typeSelect();
    }

    /**
     * @deprecated
     */
    public function typeSelect() {
        trigger_error("CResource->typeSelect() has been deprecated in v3.0 and will be removed in v4.0. Please use w2PgetSysVal('ResourceTypes') instead.", E_USER_NOTICE);

        $typelist = w2PgetSysVal('ResourceTypes');
        if (!count($typelist)) {
            include W2P_BASE_DIR . '/modules/resources/setup.php';
            $setup = new SResource();
            $setup->upgrade('1.0.1');
            $typelist = w2PgetSysVal('ResourceTypes');
        }

        return $typelist;
    }

    /**
     * @deprecated
     */
    public function getTypeName() {
        trigger_error("CResource->getTypeName() has been deprecated in v3.0 and will be removed in v4.0. Please use w2PgetSysVal('ResourceTypes') instead.", E_USER_NOTICE);

        $typelist = $this->typeSelect();
        return $typelist[$this->resource_type];
    }

    public function getResourcesByTask($task_id)
    {
        $q = $this->_getQuery();
        $q->addQuery('a.*');
        $q->addQuery('b.percent_allocated');
        $q->addTable('resources', 'a');
        $q->addJoin('resource_tasks', 'b', 'b.resource_id = a.resource_id', 'inner');
        $q->addWhere('b.task_id = ' . (int)$task_id);

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

    /**
     * Replace the resources assigned to a task with those posted by the task
     * form's Other Resources tab ("resource_id=percent;resource_id=percent;").
     * Only resources the current user may view are touched; percentages are
     * kept within 0-100 like the form's choices.
     *
     * Restores saving of task resources, which stopped in v3.2. Based on the
     * fix from Klaus Buecher (opto), 50db537d and a6952959.
     *
     * @param int    $task_id
     * @param string $allocations
     *
     * @return bool
     */
    public function updateTaskResources($task_id, $allocations)
    {
        $task_id = (int) $task_id;
        if (!$task_id || !$this->_perms->checkModuleItem('tasks', 'edit', $task_id)) {
            return false;
        }

        $allowed = $this->getAllowedRecords($this->_AppUI->user_id, 'resource_id, resource_name');
        if (!count($allowed)) {
            return false;
        }

        $values = array();
        foreach (explode(';', (string) $allocations) as $allocation) {
            $parts = explode('=', $allocation);
            $resource_id = (int) $parts[0];
            if (!isset($allowed[$resource_id])) {
                continue;
            }
            $percent = isset($parts[1]) ? (int) $parts[1] : 100;
            $values[$resource_id] = max(0, min(100, $percent));
        }

        $q = $this->_getQuery();
        $q->setDelete('resource_tasks');
        $q->addWhere('task_id = ' . $task_id);
        $q->addWhere('resource_id IN (' . implode(',', array_map('intval', array_keys($allowed))) . ')');
        $q->exec();
        $q->clear();

        foreach ($values as $resource_id => $percent) {
            $q->addTable('resource_tasks');
            $q->addInsert(array('task_id', 'resource_id', 'percent_allocated'), array($task_id, $resource_id, $percent), true);
            $q->exec();
            $q->clear();
        }

        return true;
    }

    public function hook_search() {
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
