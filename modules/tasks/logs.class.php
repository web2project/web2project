<?php

/**
 * Class for handling task log functionality
 *
 * @category CTask_Logs
 * @package  web2project\modules\core
 * @license  Clear BSD
 * @link     http://www.web2project.net
 */

class CTask_Log extends w2p_Core_BaseObject
{

	/**
	 * The id of the task log
	 *
	 * @var int
	 */
	public $task_log_id = null;

	/**
	 * The id of the task this task log belongs to
	 *
	 * @var int
	 */
	public $task_log_task = null;

	/**
	 * The project of the task this log belongs to. Set from the task on
	 * every store, and kept in step when the task moves to another project.
	 *
	 * @var int
	 */
	public $task_log_project = null;

	/**
	 * The name of the task log
	 *
	 * @var string
	 */
	public $task_log_name = null;

	/**
	 * Description of the task log
	 *
	 * @var string
	 */
	public $task_log_description = null;

	/**
	 * The id of user that created the task log
	 *
	 * @var int
	 */
	public $task_log_creator = null;

	/**
	 * The number of hours worked for the task log
	 *
	 * @var float
	 */
	public $task_log_hours = null;

	/**
	 * Date of the task log, stored as a date in the db
	 *
	 * @var string
	 */
	public $task_log_date = null;

	/**
	 * Cost code for the task log
	 * Integer value, can be defined by user
	 *
	 * @var int
	 */
	public $task_log_costcode = null;

	/**
	 * Indicates if there was a problem while working on the task
	 *
	 * @var int|bool|null
	 */
	public $task_log_problem = null;

	/**
	 * The id of reference for the task log
	 * default values are 0 - Not Defined, 1 - Email, 2 - Helpdesk, 3 - Phone Call,
	 * 4 - Fax however they can be altered by user
	 *
	 * @var int
	 */
	public $task_log_reference = null;

	/**
	 * Url related to the work done in this task log
	 *
	 * @var string
	 */
	public $task_log_related_url = null;

	/**
	 * Datetime stamp of when task log was created
	 * Is handled by database layer
	 *
	 * @var string
	 */
	public $task_log_created = null;

	/**
	 * Datetime stamp of when task log was laste updated
	 * Is handled by database layer
	 *
	 * @var string
	 */
	public $task_log_updated = null;

    /**
     * Id of user that created the record for this task log.
     * For task logs when user is creating it for another user
     *
     * @var int
     */
    public $task_log_record_creator;

    /**
     * Percent complete of a given task at the time of the specific tasklog
     *
     * @var int
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

        // Always the task's project, whatever was bound from the form.
        $this->task_log_project = $this->getTaskProject();

        // Who the log is for and who recorded it are never taken from the
        // request on an edit, and the recorder of a new log is always the
        // current user. See canEdit().
        $stored = $this->getStoredLog();
        if (null !== $stored) {
            $this->task_log_creator        = $stored['task_log_creator'];
            $this->task_log_record_creator = $stored['task_log_record_creator'];
        } else {
            $this->task_log_record_creator = $this->currentUserId();
            if (!$this->canLogFor((int) $this->task_log_creator)) {
                $this->task_log_creator = 0;
            }
        }

        $this->task_log_creator = (int) $this->task_log_creator ? $this->task_log_creator : $this->currentUserId();

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
        // Records the id that hook_postDelete() uses to remove custom field values.
        parent::hook_preDelete();

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
                // No log left (the task's last one was just deleted): no
                // recorded progress, rather than an empty percentage.
                $percentComplete = (is_array($results) && isset($results['task_log_percent_complete']))
                    ? $results['task_log_percent_complete'] : 0;
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
        $stored = $this->getStoredLog();
        if (null === $stored) {
            return false;
        }
        // Deletes post only the id; the caller redirects back to the task.
        if (!(int) $this->task_log_task) {
            $this->task_log_task = $stored['task_log_task'];
        }

        return $this->canChangeStoredLog($stored);
	}

    public function canCreate() {
//TODO: allow someone to add a log if they're assigned to the Task
        return $this->_perms->checkModuleItem($this->_tbl_module, 'view', $this->task_log_task);
    }

    /**
     * You are allowed to edit a task log if you are:
     *   a) the creator of the log; OR
     *   b) the subject of the log; OR
     *   c) have edit permissions on the corresponding task.
     *
     * These are checked against the log as stored, never against the values
     * bound from a request (GHSA-xcpx-p68j-gm7w). Moving a log to another
     * task also needs the right to add a log there.
     *
     * @return bool
     */
    public function canEdit() {
        $stored = $this->getStoredLog();
        if (null === $stored || !$this->canChangeStoredLog($stored)) {
            return false;
        }

        $task_id = (int) $this->task_log_task;
        if ($task_id && $task_id != $stored['task_log_task']) {
            return $this->canCreate();
        }

        return true;
    }

    /**
     * The creator, record creator and task of this log as stored in the
     * database, or null for a new log or an id with no row.
     *
     * @return array{task_log_creator: int, task_log_record_creator: int, task_log_task: int}|null
     */
    protected function getStoredLog()
    {
        if ((int) $this->task_log_id < 1) {
            return null;
        }

        $q = $this->_getQuery();
        $q->addTable('task_log');
        $q->addQuery('task_log_creator, task_log_record_creator, task_log_task');
        $q->addWhere('task_log_id = ' . (int) $this->task_log_id);
        $row = $q->loadHash();
        if (!is_array($row) || !isset($row['task_log_task'])) {
            return null;
        }

        $toInt = static function ($value): int {
            return is_numeric($value) ? (int) $value : 0;
        };

        return array(
            'task_log_creator'        => $toInt($row['task_log_creator'] ?? 0),
            'task_log_record_creator' => $toInt($row['task_log_record_creator'] ?? 0),
            'task_log_task'           => $toInt($row['task_log_task']),
        );
    }

    /**
     * Whether the current user is the stored log's subject or recorder, or
     * may edit the task it belongs to.
     *
     * @param array{task_log_creator: int, task_log_record_creator: int, task_log_task: int} $stored from getStoredLog()
     *
     * @return bool
     */
    protected function canChangeStoredLog(array $stored)
    {
        $user_id = $this->currentUserId();

        return $user_id == $stored['task_log_creator']
            || $user_id == $stored['task_log_record_creator']
            || (bool) $this->_perms->checkModuleItem($this->_tbl_module, 'edit', $stored['task_log_task']);
    }

    /**
     * @return int
     */
    private function currentUserId()
    {
        $user_id = $this->_AppUI->user_id;

        return is_numeric($user_id) ? (int) $user_id : 0;
    }

    /**
     * Whether the current user may record a new log for $user_id: always for
     * themselves, and for another user only when the task allows logs for
     * other users and that user is assigned to it (the users the log form
     * offers).
     *
     * @param int $user_id
     *
     * @return bool
     */
    protected function canLogFor($user_id)
    {
        if (0 == $user_id || $user_id == $this->currentUserId()) {
            return true;
        }

        $q = $this->_getQuery();
        $q->addTable('tasks', 't');
        $q->addJoin('user_tasks', 'ut', 'ut.task_id = t.task_id', 'inner');
        $q->addQuery('COUNT(*)');
        $q->addWhere('t.task_id = ' . (int) $this->task_log_task);
        $q->addWhere('t.task_allow_other_user_tasklogs = 1');
        $q->addWhere('ut.user_id = ' . (int) $user_id);
        $count = $q->loadResult();

        return is_numeric($count) && (int) $count > 0;
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
	 * @param string|null $index optional name of field to index the returned array
	 * @param array<mixed>|null $extra optional array of additional sql parameters (from and where supported)
	 *
	 * @return array
	 */
	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $unused = '')
	{
		$oTsk = new CTask();
        $oTsk->overrideDatabase($this->_query);

        // $extra is optional (null by default, e.g. from
        // Permissions::getViewableItems()), so don't read keys it lacks.
        if (!is_array($extra)) {
            $extra = array();
        }
        $where = isset($extra['where']) ? (string) $extra['where'] : '';

		$aTasks = $oTsk->getAllowedRecords($uid, 'task_id, task_name');
		if (count($aTasks)) {
			$buffer = '(task_log_task IN (' . implode(',', array_keys($aTasks)) . ') OR task_log_task IS NULL OR task_log_task = \'\' OR task_log_task = 0)';
		} else {
			// There are no allowed tasks, so don't allow task_logs.
			$buffer = '1 = 0';
		}
		$extra['where'] = ('' != $where) ? $where . ' AND ' . $buffer : $buffer;
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);
	}

    /**
     * A task log belongs to its task's project.
     *
     * @return int
     */
    protected function getProjectId()
    {
        return $this->getTaskProject();
    }

    /**
     * The project of this log's task, read from the tasks table (by the log's
     * task, or by the log's id when the object was not loaded).
     *
     * @return int
     */
    protected function getTaskProject()
    {
        $q = new w2p_Database_Query();
        $q->addTable('tasks');
        $q->addQuery('task_project');
        if ((int) $this->task_log_task > 0) {
            $q->addWhere('task_id = ' . (int) $this->task_log_task);
        } elseif ((int) $this->task_log_id > 0) {
            $q->addJoin('task_log', 'tl', 'tl.task_log_task = task_id', 'inner');
            $q->addWhere('tl.task_log_id = ' . (int) $this->task_log_id);
        } else {
            return 0;
        }

        return (int) $q->loadResult();
    }
}
