<?php

/**
 * @package     web2project\modules\delegations
 */

$filtersA = array('my' => 'My Tasks', 'myunfinished' => 'My Unfinished Tasks', 'taskowned' => 'All Tasks That I Am Owner', 'taskcreated' => 'All Tasks I Have Created', 'myfinished7days' => 'My Tasks Finished Last 7 Days');
$filtersB = array('my' => 'My Delegations', 'myunfinished' => 'My Unfinished Delegations', 'taskowned' => 'All Delegations of Tasks That I Am Owner', 'taskcreated' => 'All Delegations of Tasks I Have Created', 'myfinished7days' => 'My Delegations Finished Last 7 Days');


class CDelegation extends w2p_Core_BaseObject
{

    public $delegation_id = null;
    public $delegating_user_id = null;
    public $delegated_to_user_id = null;
    public $delegation_task = null;
    public $delegation_start_date = null;
    public $delegation_name = null;
    public $delegation_description = null;
    public $delegation_rejection_date = null;
    public $delegation_rejection_reason = null;
    public $delegation_rejection_validation_date = null;
    public $delegation_percent_complete = null;
    public $delegation_end_date = null;
    public $delegation_project = null;
    public $delegation_creator = null;
    public $delegation_created = null;
    public $delegation_rejection_updator = null;
    public $delegation_completion_updator = null;

    public function __construct()
    {
        parent::__construct('user_delegations', 'delegation_id', 'delegations');
	$this->_tbl_project_id = 'delegation_project';
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

	if ($this->delegating_user_id == $this->delegated_to_user_id) {
            $this->_error['delegation_users'] = $baseErrorMsg . 'the \'delegating\' and \'delegated to\' users are the same';
	}
        if ('' == trim($this->delegation_name)) {
            $this->_error['delegation_name'] = $baseErrorMsg . 'delegation name is not set';
        }
        if ('' == trim($this->delegation_description)) {
            $this->_error['delegation_description'] = $baseErrorMsg . 'delegation description is not set';
        }
        if (0 == (int)$this->delegated_to_user_id) {
            $this->_error['delegated_to_user_id'] = $baseErrorMsg . 'delegation user is not set';
        }
        if (!empty($this->delegation_rejection_date)) {
	    if ('' == trim($this->delegation_rejection_reason)) {
	        $this->_error['delegation_rejection_reason'] = $baseErrorMsg . 'delegation rejection reason is not set';
	    }
        }

        $q = $this->_getQuery();
        $q->addTable('user_delegations');
        $q->addQuery('count(delegation_id)');
        $q->addWhere('delegation_task = ' . (int)$this->delegation_task . ' AND delegating_user_id = ' . (int)$this->delegating_user_id . ' AND delegation_id != ' . (int)$this->delegation_id) . ' AND delegation_rejection_date IS NOT NULL';
        $already = $q->loadResult();
	if ($already) {
	        $this->_error['delegation_duplicate'] = $baseErrorMsg . 'this user already has delegated task ' . $this->delegation_task;
	}

        return (count($this->_error)) ? false : true;
    }

    protected function hook_preStore()
    {
        $this->delegation_start_date = $this->_AppUI->convertToSystemTZ($this->delegation_start_date);
	if (!empty($this->delegation_rejection_date)) {
	        $this->delegation_rejection_date = $this->_AppUI->convertToSystemTZ($this->delegation_rejection_date);
	}
	if (!empty($this->delegation_rejection_validation_date)) {
	        $this->delegation_rejection_validation_date = $this->_AppUI->convertToSystemTZ($this->delegation_rejection_validation_date);
	}
	if (!empty($this->delegation_end_date)) {
	        $this->delegation_end_date = $this->_AppUI->convertToSystemTZ($this->delegation_end_date);
	}
	parent::hook_preStore();
    }

    protected function hook_postDelete()
    {
    	$this->deleteRelatedTaskLogs();
	parent::hook_postDelete();
    }

    protected function hook_preCreate() 
    {
        $q = $this->_getQuery();
        $this->delegation_created = $q->dbfnNowWithTZ();

        parent::hook_preCreate();
    }

    public function hook_search()
    {
        $search['table'] = 'user_delegations';
        $search['table_alias'] = 'ud';
        $search['table_module'] = 'delegations';
        $search['table_key'] = 'delegation_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=delegations&a=view&delegation_id='; // first part of link
        $search['table_title'] = 'Delegations';
        $search['table_orderby'] = 'delegation_description';
        $search['search_fields'] = array('ud.delegation_description');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }

    protected function generateHistoryDescription($event) 
    {
        global $AppUI;

	$task = new CTask();
	$task->load($this->task_id);
	$contact = new CContact();
	$contact->findContactByUserid($this->delegated_to_user_id);
	$user_name = $contact->contact_name;
	
	$event = mb_strtolower($event);
	if ($event == 'create') {
		return $AppUI->_('Delegation of task') . ' \'' . $task->task_name . '\', ' . $AppUI->_('to user') . ' \'' . $user_name . '\' ' . $AppUI->_('was created with ID') . ' ' . $this->delegation_id;
	} elseif ($event == 'update') {
		return $AppUI->_('Delegation of task') . ' \'' . $task->task_name . '\', ' . $AppUI->_('to user') . ' \'' . $user_name . '\', ' . $AppUI->_('with ID') . ' ' . $this->delegation_id . ', ' . $AppUI->_('was edited');
	} elseif ($event == 'delete') {
		return $AppUI->_('Delegation of task') . ' \'' . $task->task_name . '\', ' . $AppUI->_('to user') . ' \'' . $user_name . '\', ' . $AppUI->_('with ID') . ' ' . $this->delegation_id . ', ' . $AppUI->_('was deleted');
	} else {
		return parent::generateHistoryDescription($event);
	}
    }

    public function deleteRelatedTaskLogs($delegation_op = null) 
    {
	CTask_Log::deleteTaskLogsByDelegationId($this->delegation_id, $delegation_op);
	// Update the completion status of the task, if needed
	if (($delegation_op >= 1) && ($delegation_op <= 4)) {
		$tlog = new CTask_Log();
		$tlog->updateTaskSummary(true, $this->delegation_task);
	}
    }

    public function canDelete(&$msg = '', $oid = null, $joins = null)
    {
	if (($this->_AppUI->user_id == $this->delegation_creator) || parent::canDelete($msg, $oid, $joins)) {
       	    return true;
	}
        return false;
    }

    public function canEdit() 
    {
        if (($this->_AppUI->user_id == $this->delegation_creator) || parent::canEdit()) {
            return true;
        }
        return false;
    }

    public static function getDelegationsForPeriod($start_date, $end_date, $company_id = 0, $user_id = null)
    {
        global $AppUI;
        $q = new w2p_Database_Query();
	$q->addTable('user_delegations','ud');
	$q->addTable('projects', 'pr');
	$q->addQuery('ud.*, ta.task_end_date, pr.project_color_identifier');
	$q->leftJoin('tasks', 'ta', 'ta.task_id = ud.delegation_task');

        // convert to default db time stamp
        $db_start = $start_date->format(FMT_DATETIME_MYSQL);
        $db_end = $end_date->format(FMT_DATETIME_MYSQL);

        // Allow for possible passing of user_id 0 to stop user filtering
        if (!isset($user_id)) {
            $user_id = $AppUI->user_id;
        }

	$project = new CProject;
	$allowedProjects = $project->getAllowedSQL($user_id,'pr.project_id');

	$task = new CTask;
	$allowedTasks = $task->getAllowedSQL($user_id, 'ta.task_id');

	if ($company_id) {
		$q->addWhere('pr.project_company = "' . (int)$company_id . '"');
	}

        $q->addWhere('(delegation_start_date <= \'' . $db_end . '\' AND (task_end_date >= \'' . $db_start . '\' OR task_end_date = \'0000-00-00 00:00:00\' OR task_end_date = NULL))');

	$q->addWhere('ud.delegated_to_user_id = ' . (int)$user_id);

	$q->addWhere('ta.task_status = 0');
	$q->addWhere('pr.project_id = ta.task_project');
	
	$q->addWhere('project_active = 1');
	$q->addWhere('task_dynamic <> 1');

	if (count($allowedProjects)) {
		$q->addWhere($allowedProjects);
	}

	if (count($allowedTasks)) {
		$q->addWhere($allowedTasks);
	}

        $q->addOrder('ud.delegation_start_date');

        // assemble query
        $tasks = $q->loadList(-1, 'delegation_id');

        // check tasks access
	$obj = new CDelegation();
        $result = array();
        foreach ($tasks as $key => $row) {
            $obj->load($row['delegation_task']);
            $canAccess = $obj->canAccess();
            if (!$canAccess) {
                continue;
            }
            $result[$key] = $row;
        }
        // execute and return
        return $result;
    }
}
