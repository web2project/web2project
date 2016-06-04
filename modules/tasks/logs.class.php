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
     *
     * @var int
     * @access public
     */
    public $task_log_percent_complete;
    // @todo this should be task_log_task_end_datetime to take advantage of our templating
    public $task_log_task_end_date;

    public function __construct()
	{
		parent::__construct('task_log', 'task_log_id', 'tasks');

		// ensure changes to checkboxes are honoured
		$this->task_log_problem = (int) $this->task_log_problem;
	}

    protected function hook_preStore()
    {
		$q = $this->_getQuery();
		$this->task_log_updated = $q->dbfnNowWithTZ();

        $this->task_log_creator = (int) $this->task_log_creator ? $this->task_log_creator : $this->_AppUI->user_id;

		if ($this->task_log_date) {
			$date = new w2p_Utilities_Date($this->task_log_date);
			$this->task_log_date = $date->format(FMT_DATETIME_MYSQL);
		}
		$dot = strpos($this->task_log_hours, ':');
		if ($dot > 0) {
			$log_duration_minutes = sprintf('%.3f', substr($this->task_log_hours, $dot + 1) / 60.0);
			$this->task_log_hours = floor($this->task_log_hours) + $log_duration_minutes;
		}

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

        parent::hook_preStore();
    }

    protected function  hook_preCreate() {
        $q = $this->_getQuery();
        $this->task_log_created = $q->dbfnNowWithTZ();

        parent::hook_preCreate();
    }

    protected function hook_postStore()
    {
        $this->updateTaskSummary(null, $this->task_log_task);

        parent::hook_postStore();
    }

    protected function hook_preDelete()
    {
        $this->load($this->task_log_id);
        $this->_task_id = $this->task_log_task;
    }

    protected function hook_postDelete()
    {
        $this->updateTaskSummary(null, $this->_task_id);

        parent::hook_postStore();
    }
	/**
	 * Updates the variable information on the task.
	 *
	 * @param int $notUsed not used
     * @param int $task_id that task id of task this task log is for
	 *
	 * @return void
	 *
	 * @access protected
	 */
	protected function updateTaskSummary($notUsed = null, $task_id)
	{
        $task = new CTask();
        $task->overrideDatabase($this->_query);
        $task->load($task_id);

        $q = $this->_getQuery();

        if($this->_perms->checkModuleItem('tasks', 'edit', $task_id)) {
            if ($this->task_log_percent_complete <= 100) {
                $q->addQuery('task_log_percent_complete, task_log_date');
                $q->addTable('task_log');
                $q->addWhere('task_log_task = ' . (int)$task_id);
                $q->addOrder('task_log_date DESC, task_log_id DESC');
                $q->setLimit(1);
                $results = $q->loadHash();
                $percentComplete = $results['task_log_percent_complete'];
            } else {
                $percentComplete = 100;
            }

            $old_end_date = new w2p_Utilities_Date($task->task_end_date);
            $new_end_date = new w2p_Utilities_Date($this->task_log_task_end_date);

            $new_end_date->setHour($old_end_date->getHour());
            $new_end_date->setMinute($old_end_date->getMinute());
            $task_end_date = $new_end_date->format(FMT_DATETIME_MYSQL);

            /*
             * We're using a database update here instead of store() because a
             *   bunch of other things happen when you call store().. like the
             *   processing of contacts, departments, etc.
             */
            $q = $this->_getQuery();
            $q->addTable('tasks');
            $q->addUpdate('task_percent_complete', $percentComplete);
            $q->addUpdate('task_end_date', $task_end_date);
            $q->addWhere('task_id = ' . (int)$task_id);
            $success = $q->exec();

            if (!$success) {
                $this->_AppUI->setMsg($task->getError(), UI_MSG_ERROR, true);
            }

            $task->pushDependencies($task_id, $task_end_date);
        }

		$q->addQuery('SUM(task_log_hours)');
		$q->addTable('task_log');
		$q->addWhere('task_log_task = ' . (int)$task_id);
		$totalHours = $q->loadResult();

        $task->updateHoursWorked2($task_id, $totalHours);
        $task->updateDynamics();
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
    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null)
	{
        if($this->_AppUI->user_id == $this->task_log_creator ||
                $this->_AppUI->user_id == $this->task_log_record_creator ||
                $this->_perms->checkModuleItem($this->_tbl_module, 'edit', $this->{$this->_tbl_key})) {

            return true;
        }

        return false;
	}

    public function canCreate() {
//TODO: allow someone to add a log if they're assigned to the Task
        return $this->_perms->checkModuleItem($this->_tbl_module, 'view', $this->task_log_task);
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

    public function canView()
    {
        return $this->_perms->checkModuleItem($this->_tbl_module, 'view', $this->task_log_task);
    }
	/**
	 * Get a list of task logs the current user is allowed to access
	 *
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
}