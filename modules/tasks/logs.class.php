<?php

/**
 * Class for handling task log functionality
 *
 * @category    CTask_Logs
 * @package     web2project\modules\core
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class CTask_Log extends w2p_Core_BaseObject
{

	/**
	 * The id of the task log
	 *
	 * @var int
	 * @access public
	 */
	public $task_log_id = null;

	/**
	 * The id of the task this task log belongs to
	 *
	 * @var int
	 * @access public
	 */
	public $task_log_task = null;

	/**
	 * The name of the task log
	 *
	 * @var string
	 * @access public
	 */
	public $task_log_name = null;

	/**
	 * Description of the task log
	 *
	 * @var string
	 * @access public
	 */
	public $task_log_description = null;

	/**
	 * The id of user that created the task log
	 *
	 * @var int
	 * @access public
	 */
	public $task_log_creator = null;

	/**
	 * The number of hours worked for the task log
	 *
	 * @var float
	 * @access public
	 */
	public $task_log_hours = null;

	/**
	 * Date of the task log, stored as a date in the db
	 *
	 * @var string
	 * @access public
	 */
	public $task_log_date = null;

	/**
	 * Cost code for the task log
	 * Integer value, can be defined by user
	 *
	 * @var int
	 * @access public
	 */
	public $task_log_costcode = null;

	/**
	 * Indicates if there was a problem while working on the task
	 *
	 * @var bool
	 * @access public
	 */
	public $task_log_problem = null;

	/**
	 * The id of reference for the task log
	 * default values are 0 - Not Defined, 1 - Email, 2 - Helpdesk, 3 - Phone Call,
	 * 4 - Fax however they can be altered by user
	 *
	 * @var int
	 * @access public
	 */
	public $task_log_reference = null;

	/**
	 * Url related to the work done in this task log
	 *
	 * @var string
	 * @access public
	 */
	public $task_log_related_url = null;

	/**
	 * Datetime stamp of when task log was created
	 * Is handled by database layer
	 *
	 * @var string
	 * @access public
	 */
	public $task_log_created = null;

	/**
	 * Datetime stamp of when task log was laste updated
	 * Is handled by database layer
	 *
	 * @var string
	 * @access public
	 */
	public $task_log_updated = null;

    /**
     * Id of user that created the record for this task log.
     * For task logs when user is creating it for another user
     *
     * @var int
     * @access public
     */
    public $task_log_record_creator;

    /**
     * Percent complete of a given task at the time of the specific tasklog
     * 20130318 - These columns store the values relative to this task log, 
     *            not the values previously in the task.
     *
     * @var int
     * @access public
     */
    public $task_log_percent_complete;
    public $task_log_task_end_date;

    /**
     * Constructor for class
     *
     * @return void
     *
     * @access public
     */
	public function __construct()
	{
		parent::__construct('task_log', 'task_log_id', 'tasks');

		// ensure changes to checkboxes are honoured
		$this->task_log_problem = (int) $this->task_log_problem;

		$this->_tbl_project_id = 'task_log_project';
	}

	protected function hook_preStore()
	{
		$q = $this->_getQuery();
		$this->task_log_updated = $q->dbfnNowWithTZ();

		if ($this->task_log_date) {
			$date = new w2p_Utilities_Date($this->task_log_date);
			$this->task_log_date = $date->format(FMT_DATETIME_MYSQL);
		}
		$dot = strpos($this->task_log_hours, ':');
		if ($dot > 0) {
			$log_duration_minutes = sprintf('%.3f', substr($this->task_log_hours, $dot + 1) / 60.0);
			$this->task_log_hours = floor($this->task_log_hours) + $log_duration_minutes;
		}
		$this->task_log_hours = $this->task_log_hours;
		$this->task_log_costcode = cleanText($this->task_log_costcode);

		if (!((float)$this->task_log_hours)) {
			// before evaluating a non-float work hour as 0 lets try to check if user is trying
			// to enter in hour:minute format and convert it to decimal. If that is not the format
			// then we consider that there was no time worked at all (i.e. 0 time worked)
			$log_time_hour = $log_time_minute = 0;
			list($log_time_hour, $log_time_minute) = explode(':', $this->task_log_hours);
			$this->task_log_hours = ((int)$log_time_hour) + (((int)$log_time_minute) / 60);
			if (!((float)$this->task_log_hours)) {
				$this->task_log_hours = 0;
			}
		}
	
		// Add the task's end time to the new end date, to avoid dates with 00:00 time.
	        $task = new CTask();
	        $task->overrideDatabase($this->_query);
	        $task->load($this->task_log_task);
		$this->task_log_task_end_date = date('Y-m-d H:i:s', strtotime($this->task_log_task_end_date) + (strtotime($task->task_original_end_date) % 86400));

	        parent::hook_preStore();
	}

	protected function  hook_preCreate() {
	        $q = $this->_getQuery();
	        $this->task_log_created = $q->dbfnNowWithTZ();

	        parent::hook_preCreate();
	}

	/**
	 * Deletes the current task log from the database. Then updated total hours
	 * worked cache on task.
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function delete()
	{
		$this->load($this->task_log_id);
		$this->_task_id = $this->task_log_task;

	        return parent::delete();
	}

	protected function hook_postStore()
	{
	        $this->updateTaskSummary(null, $this->task_log_task);

	        parent::hook_postStore();
	}

	protected function hook_postDelete()
	{
	        $this->updateTaskSummary(null, $this->_task_id);

	        parent::hook_postDelete();
	}

	/**
	 * Updates the variable information on the task.
	 *
	 * @param int $task_log_task that task id of task this task log is for
	 *
	 * @return void
	 *
	 * @access protected
	 */
	protected function updateTaskSummary($notUsed = null, $task_id)
	{
		$q = $this->_getQuery();

		// The user's edit permission of the task was previously checked,
		// forcing an user to have edit permission on a task just for
		// updating its completion state and end date.
		// I see no sense in this, so now this method always updates that
		// information, being the responsability of the form setup code to
		// ensure that if a user shouldn't change this data, them no field
		// should be presented and the (hidden) fields should be initialized
		// to the latest values, to avoid changes. I am aware that concurrent
		// adding of task logs to the same task may screw this up but its
		// worth it.
		$q->addQuery('task_log_percent_complete, task_log_task_end_date, task_log_date');
	        $q->addTable('task_log');
	        $q->addWhere('task_log_task = ' . (int)$task_id);
	        $q->addOrder('task_log_date DESC, task_log_id DESC');
	        $q->setLimit(1);
	        $results = $q->loadHash();

	        $task = new CTask();
	        $task->overrideDatabase($this->_query);
	        $task->load($task_id);

                /*
                 * We're using a database update here instead of store() because a
	         *   bunch of other things happen when you call store().. like the
	         *   processing of contacts, departments, etc.
	         */
	        $q = $this->_getQuery();
	        $q->addTable('tasks');
		if ($results) {
		        $q->addUpdate('task_percent_complete', $results['task_log_percent_complete']);
		        $q->addUpdate('task_end_date', $results['task_log_task_end_date']);
			$end_date = $results['task_log_task_end_date'];
		} else {
		        $q->addUpdate('task_percent_complete', $task->task_original_percent_complete);
		        $q->addUpdate('task_end_date', $task->task_original_end_date);
			$end_date = $task->task_original_end_date;
		}
	        $q->addWhere('task_id = ' . (int)$task_id);
	        $success = $q->exec();

	        if (!$success) {
	            $this->_AppUI->setMsg($task->getError(), UI_MSG_ERROR, true);
	        }

		$task->updateDynamics();
	        $task->pushDependencies($task_id, $end_date);

		$q->addQuery('SUM(task_log_hours)');
		$q->addTable('task_log');
		$q->addWhere('task_log_task = ' . (int)$task_id);
		$totalHours = $q->loadResult();

		CTask::updateHoursWorked($task_id, $totalHours);
	}

	/**
	 * Trims all vars of this object of type string, except the task_log_description.
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function w2PTrimAll()
	{
		$spacedDescription = $this->task_log_description;
		parent::w2PTrimAll();
		$this->task_log_description = $spacedDescription;
	}

	public function isValid()
	{
	        $baseErrorMsg = get_class($this) . '::store-check failed - ';

	        if (0 == (int) $this->task_log_task) {
	            $this->_error['task_log_task'] = $baseErrorMsg . 'task log task is NULL';
	        }
	        if ('' == trim($this->task_log_name)) {
	            $this->_error['task_log_name'] = $baseErrorMsg . 'task log name is not set';
	        }
	        if (0 == (int) $this->task_log_creator) {
	            $this->_error['task_log_creator'] = $baseErrorMsg . 'task log creator is NULL';
	        }

	        return (count($this->_error)) ? false : true;
	}

	/**
	 * You are allowed to delete a task log if you are:
	 *   a) the creator of the log; OR
	 *   b) the subject of the log; OR
	 *   c) have edit permissions on the corresponding task.
	 *
	 * @return bool
	 */
	public function canDelete(&$msg = '', $oid = null, $joins = null)
	{
	        if($this->_AppUI->user_id == $this->task_log_creator ||
	                $this->_AppUI->user_id == $this->task_log_record_creator ||
	                $this->_perms->checkModuleItem($this->_tbl_module, 'edit', $this->{$this->_tbl_key})) {
	
        	    return true;
	        }
	}

	public function canCreate($task_id) {
		// Get the task's data to check who can add task logs to it.
		// Only assigned users, the task's owner or an administrator are allowed,
		// except if the task has the 'Allow users to add task logs for others'
		// setting active. In that case anyone can add a task log.

		// As an exception if the $task_id param is undefined then assume
		// the user is allowed to create the task log.
		if (!isset($task_id)) {
			return true;
		}

	        $task = new CTask();
	        $task->overrideDatabase($this->_query);
	        $task->load($task_id);

		// Is the '..task logs for others' setting on ?
		if ($task->task_allow_other_user_tasklogs) {
			return true;
		}

		// Is this user the task owner or an administrator ?
		if (($this->_AppUI->user_id == $task->task_owner) || canView('admin')) {
			return true;
		}

		// Is this user the project owner ?
		$project = new CProject();
		$project->overrideDatabase($this->_query);
		$project->load($task->task_project);
		if ($this->_AppUI->user_id == $project->project_owner) {
			return true;
		}

		// Check if the user is an assignee
		$assigned = $task->getAssignedUsers($task_id);
		foreach ($assigned as $uid => $assignee) {
			if ($uid == $this->_AppUI->user_id) {
				return true;
			}
		}

		return false;
	}

	/*
	 * You are allowed to edit a task log if you are:
	 *   a) the creator of the log; OR
	 *   b) the subject of the log; OR
	 *   c) have edit permissions on the corresponding task.
	 *
	 * @return bool
	 */
	public function canEdit() {
	        if($this->_AppUI->user_id == $this->task_log_creator ||
	                $this->_AppUI->user_id == $this->task_log_record_creator ||
	                $this->_perms->checkModuleItem($this->_tbl_module, 'edit', $this->{$this->_tbl_key})) {
	
	            return true;
	        }

	        return false;
	}

	/**
	 * Get a list of task logs the current user is allowed to access
	 *
	 * @global AppUI $AppUI global user permissions
	 * @param int $uid user id to test
	 * @param string $fields optional fields to be returned by the query, default is all
	 * @param string $orderby optional sort order for the query
	 * @param int $index optional name of field to index the returned array
	 * @param array $extra optional array of additional sql parameters (from and where supported)
	 *
	 * @return array
	 */
	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null)
	{
		$oTsk = new CTask();
	        $oTsk->overrideDatabase($this->_query);

		$aTasks = $oTsk->getAllowedRecords($uid, 'task_id, task_name');
		if (count($aTasks)) {
			$buffer = '(task_log_task IN (' . implode(',', array_keys($aTasks)) . ') OR task_log_task IS NULL OR task_log_task = \'\' OR task_log_task = 0)';

			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND ' . $buffer;
			} else {
				$extra['where'] = $buffer;
			}
		} else {
			// There are no allowed tasks, so don't allow task_logs.
			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND 1 = 0 ';
			} else {
				$extra['where'] = '1 = 0';
			}
		}
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);
	}


    protected function generateHistoryDescription($event) {
        global $AppUI;

	$event = mb_strtolower($event);
	if ($event == 'create') {
		return $AppUI->_('Task Log') . ' \'' . $this->task_log_name . '\' ' . $AppUI->_('was created with ID') . ' ' . $this->task_log_id;
	} elseif ($event == 'update') {
		return $AppUI->_('Task Log') . ' \'' . $this->task_log_name . '\', ' . $AppUI->_('with ID') . ' ' . $this->task_log_id . ', ' . $AppUI->_('was edited');
	} elseif ($event == 'delete') {
		return $AppUI->_('Task Log') . ' \'' . $this->task_log_name . '\', ' . $AppUI->_('with ID') . ' ' . $this->task_log_id . ', ' . $AppUI->_('was deleted');
	} else {
		return parent::generateHistoryDescription($event);
	}
    }
}
