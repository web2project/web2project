<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for handling task log functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Tasks
 * @package     Task Logs
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */

/**
 * This class contains functionality for Task Logs
 *
 * @category    Tasks
 * @package     Task Logs
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class CTaskLog extends CW2pObject
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
     * Constructor for class
     *
     * @return void
     *
     * @access public
     */
	public function __construct()
    {
	  parent::__construct('task_log', 'task_log_id');

		// ensure changes to checkboxes are honoured
		$this->task_log_problem = intval($this->task_log_problem);
	}

    /**
     * Stores the current task log in the database updating the task_log_updated
     * field appropriately. Then updates total hours worked cache on task.
     *
     * @return void
     *
     * @access public
     */
	public function store(CAppUI $AppUI = null)
    {

		$q = new DBQuery();

		if ($this->task_log_id) {
			$this->task_log_updated = $q->dbfnNow();
		} else {
			$this->task_log_created = $q->dbfnNow();
			$this->task_log_updated = $q->dbfnNow();
		}

		parent::store();
		$this->updateHoursWorked($this->task_log_task);
	}

    /**
     * Deletes the current task log from the database. Then updated total hours
     * worked cache on task.
     *
     * @return void
     *
     * @access public
     */
	public function delete(CAppUI $AppUI = null)
    {
		$q = new DBQuery();
		$q->addQuery('task_log_task');
		$q->addTable('task_log');
		$q->addWhere('task_log_id = ' . (int) $this->task_log_id);
		$task_log_task = $q->loadResult();

		parent::delete();
		$this->updateHoursWorked($task_log_task);
	}

    /**
     * Updates the total hours worked cache on task.
     *
     * @param int $task_log_task that task id of task this task log is for
     *
     * @return void
     *
     * @access public
     */
	private function updateHoursWorked($task_log_task)
    {
		$q = new DBQuery();
		$q->addQuery('SUM(task_log_hours)');
		$q->addTable('task_log');
		$q->addWhere('task_log_task = ' . (int) $task_log_task);
		$totalHours = $q->loadResult();

		CTask::updateHoursWorked($task_log_task, $totalHours);
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

	/**
     * Checks the class for validity
     *
     * @return null
     */
	public function check()
    {
		$this->task_log_hours = (float)$this->task_log_hours;
		return null;
	}

    /**
     * Determines whether the currently logged in user can delete this task log.
     *
     * @global AppUI $AppUI global user permissions
     *
     * @param string by ref $msg error msg to be populated on failure
     * @param int optional $oid key to check
     * @param array $joins optional list of tables to join on
     *
     * @return bool
     */
	public function canDelete(&$msg, $oid = null, $joins = null)
    {
		global $AppUI;
		$q = new DBQuery;

		// First things first.	Are we allowed to delete?
		$acl = &$AppUI->acl();
		if (!canDelete('task_log')) {
			$msg = $AppUI->_('noDeletePermission');
			return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		if (is_array($joins)) {
			$q->addTable($this->_tbl, 'k');
			$q->addQuery($k);
			$i = 0;
			foreach ($joins as $table) {
				$table_alias = 't' . $i++;
				$q->leftJoin($table['name'], $table_alias, $table_alias . '.' . $table['joinfield'] . ' = ' . 'k' . '.' . $k);
				$q->addQuery('COUNT(DISTINCT ' . $table_alias . '.' . $table['idfield'] . ') AS ' . $table['idfield']);
			}
			$q->addWhere($k . ' = ' . $this->$k);
			$q->addGroup($k);
			$obj = null;
			$q->loadObject($obj);
			$q->clear();

			if (!$obj) {
				$msg = db_error();
				return false;
			}
			$msg = array();
			foreach ($joins as $table) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_($table['label']);
				}
			}

			if (count($msg)) {
				$msg = $AppUI->_('noDeleteRecord') . ': ' . implode(', ', $msg);
				return false;
			}
		}

		return true;
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
		global $AppUI;
		$oTsk = new CTask();

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
