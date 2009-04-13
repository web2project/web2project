<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once $AppUI->getSystemClass('libmail');
require_once $AppUI->getSystemClass('w2p');
require_once $AppUI->getModuleClass('projects');
require_once $AppUI->getSystemClass('event_queue');
require_once $AppUI->getSystemClass('date');

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');

// patch 2.12.04 add all finished last 7 days, my finished last 7 days
$filters = array('my' => 'My Tasks', 'myunfinished' => 'My Unfinished Tasks', 'allunfinished' => 'All Unfinished Tasks', 'myproj' => 'My Projects', 'mycomp' => 'All Tasks for my Company', 'unassigned' => 'All Tasks (unassigned)', 'taskcreated' => 'All Tasks I Have Created', 'all' => 'All Tasks', 'allfinished7days' => 'All Tasks Finished Last 7 Days', 'myfinished7days' => 'My Tasks Finished Last 7 Days');

$status = w2PgetSysVal('TaskStatus');

$priority = w2PgetSysVal('TaskPriority');

// user based access
$task_access = array('0' => 'Public', '1' => 'Protected', '2' => 'Participant', '3' => 'Private');

/*
* TASK DYNAMIC VALUE:
* 0  = default(OFF), no dep tracking of others, others do track
* 1  = dynamic, umbrella task, no dep tracking, others do track
* 11 = OFF, no dep tracking, others do not track
* 21 = FEATURE, dep tracking, others do not track
* 31 = ON, dep tracking, others do track
*/

// When calculating a task's start date only consider
// end dates of tasks with these dynamic values.
$tracked_dynamics = array('0' => '0', '1' => '1', '2' => '31');
// Tasks with these dynamics have their dates updated when
// one of their dependencies changes. (They track dependencies)
$tracking_dynamics = array('0' => '21', '1' => '31');

/*
* CTask Class
*/
class CTask extends CW2pObject {
	/**
 	@var int */
	var $task_id = null;
	/**
 	@var string */
	var $task_name = null;
	/**
 	@var int */
	var $task_parent = null;
	var $task_milestone = null;
	var $task_project = null;
	var $task_owner = null;
	var $task_start_date = null;
	var $task_duration = null;
	var $task_duration_type = null;
	/**
 	@deprecated */
	var $task_hours_worked = null;
	var $task_end_date = null;
	var $task_status = null;
	var $task_priority = null;
	var $task_percent_complete = null;
	var $task_description = null;
	var $task_target_budget = null;
	var $task_related_url = null;
	var $task_creator = null;

	var $task_order = null;
	var $task_client_publish = null;
	var $task_dynamic = null;
	var $task_access = null;
	var $task_notify = null;
	var $task_departments = null;
	var $task_contacts = null;
	var $task_custom = null;
	var $task_type = null;

	function CTask() {
		$this->CW2pObject('tasks', 'task_id');
	}

	function __toString() {
		return $this->link . '/' . $this->type . '/' . $this->length;
	}

	// overload check
	function check() {
		global $AppUI;

		if ($this->task_id === null) {
			return 'task id is NULL';
		}
		// ensure changes to checkboxes are honoured
		$this->task_milestone = intval($this->task_milestone);
		$this->task_dynamic = intval($this->task_dynamic);

		$this->task_percent_complete = intval($this->task_percent_complete);

		$this->task_target_budget = $this->task_target_budget ? $this->task_target_budget : 0.00;

		if (!$this->task_duration || $this->task_milestone) {
			$this->task_duration = '0';
		}
		if ($this->task_milestone) {
			if ($this->task_start_date && $this->task_start_date != '0000-00-00 00:00:00') {
				$this->task_end_date = $this->task_start_date;
			} else {
				$this->task_start_date = $this->task_end_date;
			}
		}
		if (!$this->task_creator) {
			$this->task_creator = $AppUI->user_id;
		}
		if (!$this->task_duration_type) {
			$this->task_duration_type = 1;
		}
		if (!$this->task_related_url) {
			$this->task_related_url = '';
		}
		if (!$this->task_notify) {
			$this->task_notify = 0;
		}

		/*
		* Check for bad or circular task relationships (dep or child-parent).
		* These checks are definately not exhaustive it is still quite possible
		* to get things in a knot.
		* Note: some of these checks may be problematic and might have to be removed
		*/
		static $addedit;
		if (!isset($addedit)) {
			$addedit = w2PgetParam($_POST, 'dosql', '') == 'do_task_aed' ? true : false;
		}
		$this_dependencies = array();

		/*
		* If we are called from addedit then we want to use the incoming
		* list of dependencies and attempt to stop bad deps from being created
		*/
		if ($addedit) {
			$hdependencies = w2PgetParam($_POST, 'hdependencies', '0');
			if ($hdependencies) {
				$this_dependencies = explode(',', $hdependencies);
			}
		} else {
			$this_dependencies = explode(',', $this->getDependencies());
		}
		// Set to false for recursive updateDynamic calls etc.
		$addedit = false;

		// Have deps
		if (array_sum($this_dependencies)) {
			if ($this->task_dynamic == 1) {
				return 'BadDep_DynNoDep';
			}

			$this_dependents = $this->task_id ? explode(',', $this->dependentTasks()) : array();
			$more_dependents = array();
			// If the dependents' have parents add them to list of dependents
			foreach ($this_dependents as $dependent) {
				$dependent_task = new CTask();
				$dependent_task->load($dependent);
				if ($dependent_task->task_id != $dependent_task->task_parent) {
					$more_dependents = explode(',', $this->dependentTasks($dependent_task->task_parent));
				}
			}
			$this_dependents = array_merge($this_dependents, $more_dependents);

			// Task dependencies can not be dependent on this task
			$intersect = array_intersect($this_dependencies, $this_dependents);
			if (array_sum($intersect)) {
				$ids = '(' . implode(',', $intersect) . ')';
				return array('BadDep_CircularDep', $ids);
			}
		}

		// Has a parent
		if ($this->task_id && $this->task_id != $this->task_parent) {
			$this_children = $this->getChildren();
			$this_parent = new CTask();
			$this_parent->load($this->task_parent);
			$parents_dependents = explode(',', $this_parent->dependentTasks());

			if (in_array($this_parent->task_id, $this_dependencies)) {
				return 'BadDep_CannotDependOnParent';
			}
			// Task parent cannot be child of this task
			if (in_array($this_parent->task_id, $this_children)) {
				return 'BadParent_CircularParent';
			}

			if ($this_parent->task_parent != $this_parent->task_id) {
				// ... or parent's parent, cannot be child of this task. Could go on ...
				if (in_array($this_parent->task_parent, $this_children)) {
					return array('BadParent_CircularGrandParent', '(' . $this_parent->task_parent . ')');
				}
				// parent's parent cannot be one of this task's dependencies
				if (in_array($this_parent->task_parent, $this_dependencies)) {
					return array('BadDep_CircularGrandParent', '(' . $this_parent->task_parent . ')');
				}
			} // grand parent

			if ($this_parent->task_dynamic == 1) {
				$intersect = array_intersect($this_dependencies, $parents_dependents);
				if (array_sum($intersect)) {
					$ids = '(' . implode(',', $intersect) . ')';
					return array('BadDep_CircularDepOnParentDependent', $ids);
				}
			}
			if ($this->task_dynamic == 1) {
				// then task's children can not be dependent on parent
				$intersect = array_intersect($this_children, $parents_dependents);
				if (array_sum($intersect)) {
					return 'BadParent_ChildDepOnParent';
				}
			}
		} // parent

		return null;
	}

	/*
	* overload the load function
	* We need to update dynamic tasks of type '1' on each load process!
	* @param int $oid optional argument, if not specifed then the value of current key is used
	* @return any result from the database operation
	*/

	function load($oid = null, $strip = false, $skipUpdate = false) {
		// use parent function to load the given object
		$loaded = parent::load($oid, $strip);

		/*
		** Update the values of a dynamic task from
		** the children's properties each time the
		** dynamic task is loaded.
		** Additionally store the values in the db.
		** Only treat umbrella tasks of dynamics '1'.
		*/
		if ($this->task_dynamic == 1 && !($skipUpdate)) {
			// update task from children
			$this->htmlDecode();
			$this->updateDynamics(true);

			/*
			** Use parent function to store the updated values in the db
			** instead of store function of this object in order to
			** prevent from infinite loops.
			*/
			parent::store();
			$loaded = parent::load($oid, $strip);
		}

		// return whether the object load process has been successful or not
		return $loaded;
	}
	public function fullLoad($taskId) {
		$q = new DBQuery;
		$q->addTable('tasks');
		$q->addJoin('users', 'u1', 'u1.user_id = task_owner', 'inner');
		$q->addJoin('contacts', 'ct', 'ct.contact_id = u1.user_contact', 'inner');
		$q->addJoin('projects', 'p', 'p.project_id = task_project', 'inner');
		$q->leftJoin('task_log', 'tl', 'tl.task_log_task = task_id');
		$q->addWhere('task_id = ' . (int) $taskId);
		$q->addQuery('tasks.*');
		$q->addQuery('project_name, project_color_identifier');
		$q->addQuery('CONCAT(contact_first_name, \' \', contact_last_name) as username');
		$q->addQuery('ROUND(SUM(task_log_hours),2) as log_hours_worked');
		$q->addGroup('task_id');

		$q->loadObject($this, true, false);
	}

	/*
	* call the load function but don't update dynamics
	*/
	function peek($oid = null, $strip = false) {
		$loadme = $this->load($oid, $strip, true);
		return $loadme;
	}

	function updateDynamics($fromChildren = false) {
		//Has a parent or children, we will check if it is dynamic so that it's info is updated also
		$q = &new DBQuery;
		$modified_task = new CTask();

		if ($fromChildren) {
			$modified_task = &$this;
		} else {
			$modified_task->load($this->task_parent);
			$modified_task->htmlDecode();
		}

		if ($modified_task->task_dynamic == '1') {
			//Update allocated hours based on children with duration type of 'hours'
			$q->addTable($this->_tbl);
			$q->addQuery('SUM(task_duration * task_duration_type)');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type = 1 ');
			$q->addGroup('task_parent');
			$children_allocated_hours1 = (float)$q->loadResult();
			$q->clear();

			/*
			* Update allocated hours based on children with duration type of 'days'
			* use the daily working hours instead of the full 24 hours to calculate 
			* dynamic task duration!
			*/
			$q->addTable($this->_tbl);
			$q->addQuery(' SUM(task_duration * ' . w2PgetConfig('daily_working_hours') . ')');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type <> 1 ');
			$q->addGroup('task_parent');
			$children_allocated_hours2 = (float)$q->loadResult();
			$q->clear();

			// sum up the two distinct duration values for the children with duration type 'hrs'
			// and for those with the duration type 'day'
			$children_allocated_hours = $children_allocated_hours1 + $children_allocated_hours2;

			if ($modified_task->task_duration_type == 1) {
				$modified_task->task_duration = round($children_allocated_hours, 2);
			} else {
				$modified_task->task_duration = round($children_allocated_hours / w2PgetConfig('daily_working_hours'), 2);
			}

			//Update worked hours based on children
			$q->addTable('tasks', 't');
			$q->innerJoin('task_log', 'tl', 't.task_id = tl.task_log_task');
			$q->addQuery('SUM(task_log_hours)');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_dynamic <> 1 ');
			$children_hours_worked = (float)$q->loadResult();
			$q->clear();

			//Update worked hours based on dynamic children tasks
			$q->addTable('tasks');
			$q->addQuery('SUM(task_hours_worked)');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_dynamic = 1 ');
			$children_hours_worked += (float)$q->loadResult();
			$q->clear();

			$modified_task->task_hours_worked = $children_hours_worked;

			//Update percent complete
			//hours
			$q->addTable('tasks');
			$q->addQuery('SUM(task_percent_complete * task_duration * task_duration_type)');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type = 1 ');
			$real_children_hours_worked = (float)$q->loadResult();
			$q->clear();

			//days
			$q->addTable('tasks');
			$q->addQuery('SUM(task_percent_complete * task_duration * ' . w2PgetConfig('daily_working_hours') . ')');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type <> 1 ');
			$real_children_hours_worked += (float)$q->loadResult();
			$q->clear();

			$total_hours_allocated = (float)($modified_task->task_duration * (($modified_task->task_duration_type > 1) ? w2PgetConfig('daily_working_hours') : 1));
			if ($total_hours_allocated > 0) {
				$modified_task->task_percent_complete = ceil($real_children_hours_worked / $total_hours_allocated);
			} else {
				$q->addTable('tasks');
				$q->addQuery('AVG(task_percent_complete)');
				$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id);
				$modified_task->task_percent_complete = $q->loadResult();
				$q->clear();
			}

			//Update start date
			$q->addTable('tasks');
			$q->addQuery('MIN(task_start_date)');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND NOT ISNULL(task_start_date)' . ' AND task_start_date <>	\'0000-00-00 00:00:00\'');
			$d = $q->loadResult();
			$q->clear();
			if ($d) {
				$modified_task->task_start_date = $d;
			} else {
				$modified_task->task_start_date = '0000-00-00 00:00:00';
			}

			//Update end date
			$q->addTable('tasks');
			$q->addQuery('MAX(task_end_date)');
			$q->addWhere('task_parent = ' . (int)$modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND NOT ISNULL(task_end_date)');
			$modified_task->task_end_date = $q->loadResult();
			$q->clear();

			//If we are updating a dynamic task from its children we don't want to store() it
			//when the method exists the next line in the store calling function will do that
			if ($fromChildren == false) {
				$modified_task->store();
			}
		}
	}

	/*
	* Copy the current task
	*
	* @author handco <handco@users.sourceforge.net>
	* @param int id of the destination project
	* @return object The new record object or null if error
	*/
	function copy($destProject_id = 0, $destTask_id = -1) {
		$newObj = $this->duplicate();

		// Copy this task to another project if it's specified
		if ($destProject_id != 0) {
			$newObj->task_project = $destProject_id;
		}

		if ($destTask_id == 0) {
			$newObj->task_parent = $newObj->task_id;
		} else
			if ($destTask_id > 0) {
				$newObj->task_parent = $destTask_id;
			}

		if ($newObj->task_parent == $this->task_id) {
			$newObj->task_parent = '';
		}
		$newObj->store();
		$this->copyAssignedUsers($newObj->task_id);

		return $newObj;
	} // end of copy()

	function copyAssignedUsers($destTask_id) {
		$q = new DBQuery;
		$q->addQuery('user_id, user_type, task_id, perc_assignment, user_task_priority');
		$q->addTable('user_tasks', 'ut');
		$q->addWhere('ut.task_id = ' . $this->task_id);
		$user_tasks = $q->loadList();
		$q->clear();
		foreach ($user_tasks as $user_task) {
			$q = new DBQuery;
			$q->addReplace('user_id', $user_task['user_id']);
			$q->addReplace('user_type', $user_task['user_type']);
			$q->addReplace('task_id', $destTask_id);
			$q->addReplace('perc_assignment', $user_task['perc_assignment']);
			$q->addReplace('user_task_priority', $user_task['user_task_priority']);
			$q->addTable('user_tasks', 'ut');
			$q->exec();
			$q->clear();
		}

	}

	function deepCopy($destProject_id = 0, $destTask_id = 0) {
		$children = $this->getChildren();
		$newObj = $this->copy($destProject_id, $destTask_id);
		$new_id = $newObj->task_id;
		if (!empty($children)) {
			$tempTask = &new CTask();
			foreach ($children as $child) {
				$tempTask->peek($child);
				$tempTask->htmlDecode($child);
				$newChild = $tempTask->deepCopy($destProject_id, $new_id);
				$newChild->store();
			}
		}

		return $newObj;
	}

	function move($destProject_id = 0, $destTask_id = -1) {
		if ($destProject_id != 0) {
			$this->task_project = $destProject_id;
		}

		if ($destTask_id == 0) {
			$this->task_parent = $this->task_id;
		} elseif ($destTask_id > 0) {
			$this->task_parent = $destTask_id;
		}
	}

	function deepMove($destProject_id = 0, $destTask_id = 0) {
		$this->move($destProject_id, $destTask_id);
		$children = $this->getDeepChildren();
		if (!empty($children)) {
			$tempChild = &new CTask();
			foreach ($children as $child) {
				$tempChild->peek($child);
				$tempChild->htmlDecode($child);
				$tempChild->deepMove($destProject_id, $this->task_id);
				$tempChild->store();
			}
		}
	}

	/**
	 * @todo Parent store could be partially used
	 */
	function store() {
		global $AppUI;
		$q = &new DBQuery;

		$this->w2PTrimAll();

		$importing_tasks = false;
		$msg = $this->check();
		if ($msg) {
			$return_msg = array(get_class($this) . '::store-check', 'failed', '-');
			if (is_array($msg)) {
				return array_merge($return_msg, $msg);
			} else {
				array_push($return_msg, $msg);
				return $return_msg;
			}
		}
		if ($this->task_id) {
			addHistory('tasks', $this->task_id, 'update', $this->task_name, $this->task_project);
			if ($this->task_start_date == '') {
				$this->task_start_date = '0000-00-00 00:00:00';
			}
			if ($this->task_end_date == '') {
				$this->task_end_date = '0000-00-00 00:00:00';
			}
			$ret = $q->updateObject('tasks', $this, 'task_id');
			$q->clear();
			$this->_action = 'updated';

			// Load and globalize the old, not yet updated task object
			// e.g. we need some info later to calculate the shifting time for depending tasks
			// see function update_dep_dates
			global $oTsk;
			$oTsk = new CTask();
			$oTsk->peek($this->task_id);

			// if task_status changed, then update subtasks
			if ($this->task_status != $oTsk->task_status) {
				$this->updateSubTasksStatus($this->task_status);
			}

			// Moving this task to another project?
			if ($this->task_project != $oTsk->task_project) {
				$this->updateSubTasksProject($this->task_project);
			}

			if ($this->task_dynamic == 1) {
				$this->updateDynamics(true);
			}

			// shiftDependentTasks needs this done first
			$this->check();
			$ret = $q->updateObject('tasks', $this, 'task_id', false);
			$q->clear();

			// Milestone or task end date, or dynamic status has changed,
			// shift the dates of the tasks that depend on this task
			if (($this->task_end_date != $oTsk->task_end_date) || ($this->task_dynamic != $oTsk->task_dynamic) || ($this->task_milestone == '1')) {
				$this->shiftDependentTasks();
			}
			
			if (!$this->task_parent) {
				$q->addTable('tasks');
				$q->addUpdate('task_parent', $this->task_id);
				$q->addWhere('task_id = ' . (int)$this->task_id);
				$q->exec();
				$q->clear();
			}
		} else {
			$this->_action = 'added';
			if ($this->task_start_date == '') {
				$this->task_start_date = '0000-00-00 00:00:00';
			}
			if ($this->task_end_date == '') {
				$this->task_end_date = '0000-00-00 00:00:00';
			}
			$ret = $q->insertObject('tasks', $this, 'task_id');
			$q->clear();
			addHistory('tasks', $this->task_id, 'add', $this->task_name, $this->task_project);

			if (!$this->task_parent) {
				$q->addTable('tasks');
				$q->addUpdate('task_parent', $this->task_id);
				$q->addWhere('task_id = ' . (int)$this->task_id);
				$q->exec();
				$q->clear();
			} else {
				// importing tasks do not update dynamics
				$importing_tasks = true;
			}
		}

		//split out related departments and store them seperatly.
		$q->setDelete('task_departments');
		$q->addWhere('task_id=' . (int)$this->task_id);
		$q->exec();
		$q->clear();
		// print_r($this->task_departments);
		if (!empty($this->task_departments)) {
			$departments = explode(',', $this->task_departments);
			foreach ($departments as $department) {
				$q->addTable('task_departments');
				$q->addInsert('task_id', $this->task_id);
				$q->addInsert('department_id', $department);
				$q->exec();
				$q->clear();
			}
		}

		//split out related contacts and store them seperatly.
		$q->setDelete('task_contacts');
		$q->addWhere('task_id=' . (int)$this->task_id);
		$q->exec();
		$q->clear();
		if (!empty($this->task_contacts)) {
			$contacts = explode(',', $this->task_contacts);
			foreach ($contacts as $contact) {
				$q->addTable('task_contacts');
				$q->addInsert('task_id', $this->task_id);
				$q->addInsert('contact_id', $contact);
				$q->exec();
				$q->clear();
			}
		}

		// if is child update parent task
		if ($this->task_parent != $this->task_id) {
			if (!$importing_tasks) {
				$this->updateDynamics(true);
			}

			$pTask = new CTask();
			$pTask->load($this->task_parent);
			$pTask->updateDynamics();

			if ($oTsk->task_parent != $this->task_parent) {
				$old_parent = new CTask();
				$old_parent->load($oTsk->task_parent);
				$old_parent->updateDynamics();
			}
		}

		// update dependencies
		if (!empty($this->task_id)) {
			$this->updateDependencies($this->getDependencies());
		} else {
			print_r($this);
		}

		if (!$ret) {
			return get_class($this) . '::store failed <br />' . db_error();
		} else {
			return null;
		}
	}

	/**
	 * @todo Parent store could be partially used
	 * @todo Can't delete a task with children
	 */
	function delete() {
		$q = &new DBQuery;
		$this->_action = 'deleted';
		// delete linked user tasks
		$q->setDelete('user_tasks');
		$q->addWhere('task_id=' . (int)$this->task_id);
		if (!($q->exec())) {
			return db_error();
		}
		$q->clear();

		//load it before deleting it because we need info on it to update the parents later on
		$this->load($this->task_id);
		addHistory('tasks', $this->task_id, 'delete', $this->task_name, $this->task_project);

		// delete the tasks...what about orphans?
		// delete task with parent is this task
		$childrenlist = $this->getDeepChildren();

		$q->setDelete('tasks');
		$q->addWhere('task_id=' . (int)$this->task_id);
		if (!($q->exec())) {
			return db_error();
		} elseif ($this->task_parent != $this->task_id) {
			// Has parent, run the update sequence, this child will no longer be in the
			// database
			$this->updateDynamics();
		}
		$q->clear();

		// delete children
		if (!empty($childrenlist)) {
			$q->setDelete('tasks');
			$q->addWhere('task_parent IN (' . implode(', ', $childrenlist) . ', ' . $this->task_id . ')');
			if (!($q->exec())) {
				return db_error();
			} else {
				$this->updateDynamics(); // to update after children are deleted (see above)
				$this->_action = 'deleted with children'; // always overriden?
			}
			$q->clear();
		}

		// delete affiliated task_logs
		$q->setDelete('task_log');
		if (!empty($childrenlist)) {
			$q->addWhere('task_log_task IN (' . implode(', ', $childrenlist) . ', ' . $this->task_id . ')');
		} else {
			$q->addWhere('task_log_task=' . $this->task_id);
		}

		if (!($q->exec())) {
			return db_error();
		}
		$q->clear();

		// delete affiliated task_dependencies
		$q->setDelete('task_dependencies');
		if (!empty($childrenlist)) {
			$q->addWhere('dependencies_task_id IN (' . implode(', ', $childrenlist) . ', ' . $this->task_id . ')');
		} else {
			$q->addWhere('dependencies_task_id=' . (int)$this->task_id);
		}

		if (!($q->exec())) {
			return db_error();
		} else {
			$this->_action = 'deleted';
		}
		$q->clear();

		return null;
	}

	function updateDependencies($cslist) {
		$q = &new DBQuery;
		// delete all current entries
		$q->setDelete('task_dependencies');
		$q->addWhere('dependencies_task_id=' . (int)$this->task_id);
		$q->exec();
		$q->clear();

		// process dependencies
		$tarr = explode(',', $cslist);
		foreach ($tarr as $task_id) {
			if (intval($task_id) > 0) {
				$q->addTable('task_dependencies');
				$q->addReplace('dependencies_task_id', $this->task_id);
				$q->addReplace('dependencies_req_task_id', $task_id);
				$q->exec();
				$q->clear();
			}
		}
	}
	
	public function pushDependencies($taskId, $lastEndDate) {

		$q = new DBQuery;
		$q->addQuery('td.dependencies_task_id, t.task_start_date');
		$q->addQuery('t.task_end_date, t.task_duration, t.task_duration_type');
		$q->addTable('task_dependencies', 'td');
		$q->leftJoin('tasks', 't', 't.task_id = td.dependencies_task_id');
		$q->addWhere('td.dependencies_req_task_id = ' . (int) $taskId);
		$q->addWhere("t.task_start_date < '$lastEndDate'");
		$q->addWhere('t.task_dynamic = 31');
		$q->addOrder('t.task_start_date');

		$cascadingTasks = $q->loadList();
		foreach ($cascadingTasks as $nextTask) {
/** BEGIN: nasty task update code - very similar to lines 192 in do_task_aed.php **/
			$tsd = new CDate($nextTask['task_start_date']);
			$ted = new CDate($nextTask['task_end_date']);

			$nsd = new CDate($lastEndDate);
			$nsd = $nsd->next_working_day();
			$ned = new CDate();
			$ned->copy($nsd);

			if (empty($tsd)) {
				// appropriately calculated end date via start+duration
				$ned->addDuration($nextTask['task_duration'], $nextTask['task_duration_type']);
			} else {
				// calc task time span start - end
				$d = $tsd->calcDuration($ted);

				// Re-add (keep) task time span for end date.
				// This is independent from $obj->task_duration.
				// The value returned by Date::Duration() is always in hours ('1')
				$ned->addDuration($d, '1');				
			}
			// prefer tue 16:00 over wed 8:00 as an end date
			$ned = $ned->prev_working_day();

			$task_start_date = $nsd->format(FMT_DATETIME_MYSQL);
			$task_end_date = $ned->format(FMT_DATETIME_MYSQL);

			$q = new DBQuery;
			$q->addTable('tasks', 't');
			$q->addUpdate('task_start_date', $task_start_date);
			$q->addUpdate('task_end_date', $task_end_date);
			$q->addWhere('task_id = ' . $nextTask['dependencies_task_id']);
			$q->exec();
/** END: nasty task update code - very similar to lines 192 in do_task_aed.php **/

			$this->pushDependencies($nextTask['dependencies_task_id'], $task_end_date);
		} 
	}

	/**
	 *		  Retrieve the tasks dependencies
	 *
	 *		  @author		 handco		   <handco@users.sourceforge.net>
	 *		  @return		 string		   comma delimited list of tasks id's
	 **/
	function getDependencies() {
		// Call the static method for this object
		$result = $this->staticGetDependencies($this->task_id);
		return $result;
	} // end of getDependencies ()

	/**
	 *		  Retrieve the tasks dependencies
	 *
	 *		  @author		 handco		   <handco@users.sourceforge.net>
	 *		  @param		integer		   ID of the task we want dependencies
	 *		  @return		 string		   comma delimited list of tasks id's
	 **/
	function staticGetDependencies($taskId) {
		$q = &new DBQuery;
		if (empty($taskId)) {
			return '';
		}
		$q->addTable('task_dependencies', 'td');
		$q->addQuery('dependencies_req_task_id');
		$q->addWhere('td.dependencies_task_id = ' . (int)$taskId);
		$list = $q->loadColumn();
		$q->clear();
		$result = $list ? implode(',', $list) : '';

		return $result;
	} // end of staticGetDependencies ()

	function notifyOwner() {
		$q = &new DBQuery;
		global $AppUI, $locale_char_set;

		$q->addTable('projects');
		$q->addQuery('project_name');
		$q->addWhere('project_id=' . (int)$this->task_project);
		$projname = htmlspecialchars_decode($q->loadResult());
		$q->clear();
		$mail = new Mail;

		$mail->Subject($projname . '::' . $this->task_name . ' ' . $AppUI->_($this->_action, UI_OUTPUT_RAW), $locale_char_set);

		// c = creator
		// a = assignee
		// o = owner
		$q->addTable('tasks', 't');
		$q->leftJoin('user_tasks', 'u', 'u.task_id = t.task_id');
		$q->leftJoin('users', 'o', 'o.user_id = t.task_owner');
		$q->leftJoin('contacts', 'oc', 'oc.contact_id = o.user_contact');
		$q->leftJoin('users', 'c', 'c.user_id = t.task_creator');
		$q->leftJoin('contacts', 'cc', 'cc.contact_id = c.user_contact');
		$q->leftJoin('users', 'a', 'a.user_id = u.user_id');
		$q->leftJoin('contacts', 'ac', 'ac.contact_id = a.user_contact');
		$q->addQuery('t.task_id, cc.contact_email as creator_email' . ', cc.contact_first_name as creator_first_name' . ', cc.contact_last_name as creator_last_name' . ', oc.contact_email as owner_email' . ', oc.contact_first_name as owner_first_name' . ', oc.contact_last_name as owner_last_name' . ', a.user_id as assignee_id, ac.contact_email as assignee_email' . ', ac.contact_first_name as assignee_first_name' . ', ac.contact_last_name as assignee_last_name');
		$q->addWhere(' t.task_id = ' . (int)$this->task_id);
		$users = $q->loadList();
		$q->clear();

		if (count($users)) {
			$body = ($AppUI->_('Project', UI_OUTPUT_RAW) . ': ' . $projname . "\n" . $AppUI->_('Task', UI_OUTPUT_RAW) . ':	' . $this->task_name . "\n" . $AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $this->task_id . "\n\n" . $AppUI->_('Description', UI_OUTPUT_RAW) . ': ' . "\n" . $this->task_description . "\n\n" . $AppUI->_('Creator', UI_OUTPUT_RAW) . ': ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name . "\n\n" . $AppUI->_('Progress', UI_OUTPUT_RAW) . ': ' . $this->task_percent_complete . '%' . "\n\n" . w2PgetParam($_POST, 'task_log_description'));

			$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
		}

		if ($mail->ValidEmail($users[0]['owner_email'])) {
			$mail->To($users[0]['owner_email'], true);
			$mail->Send();
		}

		return '';
	}

	//additional comment will be included in email body
	function notify($comment = '') {
		$q = &new DBQuery;
		global $AppUI, $locale_char_set;
		$df = $AppUI->getPref('SHDATEFORMAT');
		$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

		$q->addTable('projects');
		$q->addQuery('project_name');
		$q->addWhere('project_id=' . (int)$this->task_project);
		$projname = htmlspecialchars_decode($q->loadResult());
		$q->clear();

		$mail = new Mail;

		$mail->Subject($projname . '::' . $this->task_name . ' ' . $AppUI->_($this->_action, UI_OUTPUT_RAW), $locale_char_set);

		// c = creator
		// a = assignee
		// o = owner
		$q->addTable('tasks', 't');
		$q->leftJoin('user_tasks', 'u', 'u.task_id = t.task_id');
		$q->leftJoin('users', 'o', 'o.user_id = t.task_owner');
		$q->leftJoin('contacts', 'oc', 'oc.contact_id = o.user_contact');
		$q->leftJoin('users', 'c', 'c.user_id = t.task_creator');
		$q->leftJoin('contacts', 'cc', 'cc.contact_id = c.user_contact');
		$q->leftJoin('users', 'a', 'a.user_id = u.user_id');
		$q->leftJoin('contacts', 'ac', 'ac.contact_id = a.user_contact');
		$q->addQuery('t.task_id, cc.contact_email as creator_email' . ', cc.contact_first_name as creator_first_name' . ', cc.contact_last_name as creator_last_name' . ', oc.contact_email as owner_email' . ', oc.contact_first_name as owner_first_name' . ', oc.contact_last_name as owner_last_name' . ', a.user_id as assignee_id, ac.contact_email as assignee_email' . ', ac.contact_first_name as assignee_first_name' . ', ac.contact_last_name as assignee_last_name');
		$q->addWhere(' t.task_id = ' . (int)$this->task_id);
		$users = $q->loadList();
		$q->clear();

		if (count($users)) {
			$task_start_date = new CDate($this->task_start_date);
			$task_finish_date = new CDate($this->task_end_date);

			$body = ($AppUI->_('Project', UI_OUTPUT_RAW) . ': ' . $projname . "\n" . $AppUI->_('Task', UI_OUTPUT_RAW) . ':	 ' . $this->task_name);
			//Priority not working for some reason, will wait till later
			//$body .= "\n".$AppUI->_('Priority'). ': ' . $this->task_priority;
			$body .= ("\n" . $AppUI->_('Start Date', UI_OUTPUT_RAW) . ': ' . $task_start_date->format($df) . "\n" . $AppUI->_('Finish Date', UI_OUTPUT_RAW) . ': ' . ($this->task_end_date != '' ? $task_finish_date->format($df) : '') . "\n" . $AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $this->task_id . "\n\n" . $AppUI->_('Description', UI_OUTPUT_RAW) . ': ' . "\n" . $this->task_description);
			if ($users[0]['creator_email']) {
				$body .= ("\n\n" . $AppUI->_('Creator', UI_OUTPUT_RAW) . ':' . "\n" . $users[0]['creator_first_name'] . ' ' . $users[0]['creator_last_name'] . ', ' . $users[0]['creator_email']);
			}
			$body .= ("\n\n" . $AppUI->_('Owner', UI_OUTPUT_RAW) . ':' . "\n" . $users[0]['owner_first_name'] . ' ' . $users[0]['owner_last_name'] . ', ' . $users[0]['owner_email']);

			if ($comment != '') {
				$body .= "\n\n" . $comment;
			}
			$mail->Body($body, (isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : ''));
		}

		$mail_owner = $AppUI->getPref('MAILALL');

		foreach ($users as $row) {
			if ($mail_owner || $row['assignee_id'] != $AppUI->user_id) {
				if ($mail->ValidEmail($row['assignee_email'])) {
					$mail->To($row['assignee_email'], true);
					$mail->Send();
				}
			}
		}
		return '';
	}

	/**
	 * Email the task log to assignees, task contacts, project contacts, and others
	 * based upon the information supplied by the user.
	 */
	function email_log(&$log, $assignees, $task_contacts, $project_contacts, $others, $extras) {
		global $AppUI, $locale_char_set, $w2Pconfig;

		$mail_recipients = array();
		$q = new DBQuery;
		if ((int) $this->task_id > 0 && (int) $this->task_project > 0) {
			if (isset($assignees) && $assignees == 'on') {
				$q->addTable('user_tasks', 'ut');
				$q->leftJoin('users', 'ua', 'ua.user_id = ut.user_id');
				$q->leftJoin('contacts', 'c', 'c.contact_id = ua.user_contact');
				$q->addQuery('c.contact_email, c.contact_first_name, c.contact_last_name');
				$q->addWhere('ut.task_id = ' . $this->task_id);
				if (!$AppUI->getPref('MAILALL')) {
					$q->addWhere('ua.user_id <>' . (int)$AppUI->user_id);
				}
				$assigneeList = $q->loadList();
				$q->clear();
	
				foreach ($assigneeList as $myContact) {
					$mail_recipients[$myContact['contact_email']] = trim($myContact['contact_first_name'].' '.$myContact['contact_last_name']);
				}
			}
			if (isset($task_contacts) && $task_contacts == 'on') {
				$q->addTable('task_contacts', 'tc');
				$q->leftJoin('contacts', 'c', 'c.contact_id = tc.contact_id');
				$q->addQuery('c.contact_email, c.contact_first_name, c.contact_last_name');
				$q->addWhere('tc.task_id = ' . $this->task_id);
				$contactList = $q->loadList();
				$q->clear();

				foreach ($contactList as $myContact) {
					$mail_recipients[$myContact['contact_email']] = trim($myContact['contact_first_name'].' '.$myContact['contact_last_name']);
				}
			}
			if (isset($project_contacts) && $project_contacts == 'on') {
				$q->addTable('project_contacts', 'pc');
				$q->leftJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
				$q->addQuery('c.contact_email, c.contact_first_name, c.contact_last_name');
				$q->addWhere('pc.project_id = ' . $this->task_project);
				$projectContactList = $q->loadList();
				$q->clear();

				foreach ($projectContactList as $myContact) {
					$mail_recipients[$myContact['contact_email']] = trim($myContact['contact_first_name'].' '.$myContact['contact_last_name']);
				}
			}
			if (isset($others)) {
				$others = trim($others, " \r\n\t,"); // get rid of empty elements.
				if (strlen($others) > 0) {
					$q->addTable('contacts', 'c');
					$q->addQuery('c.contact_email, c.contact_first_name, c.contact_last_name');
					$q->addWhere('c.contact_id IN (' . $others . ')');
					$otherContacts = $q->loadList();
					$q->clear();

					foreach ($otherContacts as $myContact) {
						$mail_recipients[$myContact['contact_email']] = trim($myContact['contact_first_name'].' '.$myContact['contact_last_name']);
					}
				}
			}
			if (isset($extras) && $extras) {
				// Search for semi-colons, commas or spaces and allow any to be separators
				$extra_list = preg_split('/[\s,;]+/', $extras);
				foreach ($extra_list as $email) {
					if ($email && !isset($mail_recipients[$email])) {
						$mail_recipients[$email] = trim($email);
					}
				}
			}
			$q->clear(); // Reset to the default state.
			if (count($mail_recipients) == 0) {
				return false;
			}
	
			// Build the email and send it out.
			$char_set = isset($locale_char_set) ? $locale_char_set : '';
			$mail = new Mail;
			// Grab the subject from user preferences
			$prefix = $AppUI->getPref('TASKLOGSUBJ');
			$mail->Subject($prefix . ' ' . $log->task_log_name, $char_set);
	
			$q->addTable('projects');
			$q->addQuery('project_name');
			$q->addWhere('project_id=' . (int)$this->task_project);
			$projname = htmlspecialchars_decode($q->loadResult());
			$q->clear();
	
			$body = $AppUI->_('Project', UI_OUTPUT_RAW) . ': ' . $projname . "\n";
			if ($this->task_parent != $this->task_id) {
				$q->addTable('tasks');
				$q->addQuery('task_name');
				$q->addWhere('task_id = ' . (int)$this->task_parent);
				$req = &$q->exec(QUERY_STYLE_NUM);
				if ($req) {
					$body .= $AppUI->_('Parent Task', UI_OUTPUT_RAW) . ': ' . htmlspecialchars_decode($req->fields[0]) . "\n";
				}
				$q->clear();
			}
			$body .= $AppUI->_('Task', UI_OUTPUT_RAW) . ': ' . $this->task_name . "\n";
			$task_types = w2PgetSysVal('TaskType');
			$body .= $AppUI->_('Task Type', UI_OUTPUT_RAW) . ':' . $task_types[$this->task_type] . "\n";
			$body .= $AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $this->task_id . "\n\n";
			$body .= $AppUI->_('Summary', UI_OUTPUT_RAW) . ': ' . $log->task_log_name . "\n\n";
			$body .= $log->task_log_description;
	
			// Append the user signature to the email - if it exists.
			$q->addTable('users');
			$q->addQuery('user_signature');
			$q->addWhere('user_id = ' . (int)$AppUI->user_id);
			if ($res = $q->exec()) {
				if ($res->fields['user_signature']) {
					$body .= "\n--\n" . $res->fields['user_signature'];
				}
			}
			$q->clear();
	
			$mail->Body($body, $char_set);
	
			$recipient_list = '';
			$toList = array();

			foreach ($mail_recipients as $email => $name) {
				if ($mail->ValidEmail($email)) {
					$toList[$email] = $email;
					$recipient_list .= $email . ' (' . $name . ")\n";
				} else {
					$recipient_list .= 'Invalid email address \'' . $email . '\' for ' . $name . ', not sent' . "\n";
				}
			}

			$sendToList = array_keys ($mail_recipients);
			$mail->To($sendToList, true);
			$mail->Send();

			// Now update the log
			$save_email = $AppUI->getPref('TASKLOGNOTE');
			if ($save_email) {
				//TODO: This is where #38 - http://bugs.web2project.net/view.php?id=38 - should be applied if a change is necessary.
				$log->task_log_description .= "\n" . 'Emailed ' . date('l F j, Y H:i:s') . ' to:' . "\n" . $recipient_list;
				return true;
			}
		}

		return false; // No update needed.
	}

	/**
	 * @param Date Start date of the period
	 * @param Date End date of the period
	 * @param integer The target company
	 */
	function getTasksForPeriod($start_date, $end_date, $company_id = 0, $user_id = null) {
		global $AppUI;
		$q = &new DBQuery;
		// convert to default db time stamp
		$db_start = $start_date->format(FMT_DATETIME_MYSQL);
		$db_end = $end_date->format(FMT_DATETIME_MYSQL);

		// Allow for possible passing of user_id 0 to stop user filtering
		if (!isset($user_id)) {
			$user_id = $AppUI->user_id;
		}

		// filter tasks for not allowed projects
		$tasks_filter = '';
		// check permissions on projects
		$proj = new CProject();
		$task_filter_where = $proj->getAllowedSQL($AppUI->user_id, 't.task_project');
		// exclude read denied projects
		$deny = $proj->getDeniedRecords($AppUI->user_id);
		// check permissions on tasks
		$obj = new CTask();
		$allow = $obj->getAllowedSQL($AppUI->user_id, 't.task_id');

		$q->addTable('tasks', 't');
		if ($user_id) {
			$q->innerJoin('user_tasks', 'ut', 't.task_id=ut.task_id');
		}
		$q->innerJoin('projects', 'projects', 't.task_project = projects.project_id');
		$q->leftJoin('project_departments', '', 'projects.project_id = project_departments.project_id');
		$q->leftJoin('departments', '', 'departments.dept_id = project_departments.department_id');

		$q->addQuery('DISTINCT t.task_id, t.task_name, t.task_start_date, t.task_end_date, t.task_duration' . ', t.task_duration_type, projects.project_color_identifier AS color, projects.project_name, t.task_milestone');
		$q->addWhere('task_status > -1' . ' AND (task_start_date <= \'' . $db_end . '\' AND (task_end_date >= \'' . $db_start . '\' OR task_end_date = \'0000-00-00 00:00:00\' OR task_end_date = NULL))');
		if ($user_id) {
			$q->addWhere('ut.user_id = ' . (int)$user_id);
		}

		if ($company_id) {
			$q->addWhere('projects.project_company = ' . (int)$company_id);
		}
		if (count($task_filter_where) > 0) {
			$q->addWhere('(' . implode(' AND ', $task_filter_where) . ')');
		}
		if (count($deny) > 0) {
			$q->addWhere('(t.task_project NOT IN (' . implode(', ', $deny) . '))');
		}
		if (count($allow) > 0) {
			$q->addWhere('(' . implode(' AND ', $allow) . ')');
		}
		$q->addOrder('t.task_start_date');

		// assemble query
		//echo '<pre>' . $q->prepare() . '</pre>';
		$result = $q->loadList();
		$q->clear();
		// execute and return
		return $result;
	}

	function canAccess($user_id) {
		$q = &new DBQuery;

		// Let's see if this user has admin privileges
		if (!getDenyRead('admin')) {
			return true;
		}

		switch ($this->task_access) {
			case 0:
				// public
				$retval = true;
				break;
			case 1:
				// protected
				$q->addTable('users');
				$q->addQuery('user_company');
				$q->addWhere('user_id=' . (int)$user_id . ' OR user_id=' . (int)$this->task_owner);
				$user_owner_companies = $q->loadColumn();
				$q->clear();
				$company_match = true;
				foreach ($user_owner_companies as $current_company) {
					$company_match = $company_match && ((!(isset($last_company))) || $last_company == $current_company);
					$last_company = $current_company;
				}

			case 2:
				// participant
				$company_match = ((isset($company_match)) ? $company_match : true);
				$q->addTable('user_tasks');
				$q->addQuery('COUNT(task_id)');
				$q->addWhere('user_id=' . (int)$user_id . ' AND task_id=' . (int)$this->task_id);
				$count = $q->loadResult();
				$q->clear();
				$retval = (($company_match && $count > 0) || $this->task_owner == $user_id);
				break;
			case 3:
				// private
				$retval = ($this->task_owner == $user_id);
				break;
			default:
				$retval = false;
				break;
		}

		return $retval;
	}

	/**
	 *		 retrieve tasks are dependent of another.
	 *		 @param	 integer		 ID of the master task
	 *		 @param	 boolean		 true if is a dep call (recurse call)
	 *		 @param	 boolean		 false for no recursion (needed for calc_end_date)
	 **/
	function dependentTasks($taskId = false, $isDep = false, $recurse = true) {
		$q = &new DBQuery;
		static $aDeps = false;
		// Initialize the dependencies array
		if (($taskId == false) && ($isDep == false)) {
			$aDeps = array();
		}
		// retrieve dependents tasks
		if (!$taskId) {
			$taskId = $this->task_id;
		}
		if (empty($taskId)) {
			return '';
		}
		$q->addTable('task_dependencies', 'td');
		$q->innerJoin('tasks', 't', 'td.dependencies_task_id = t.task_id');
		$q->addQuery('dependencies_task_id');
		$q->addWhere('td.dependencies_req_task_id = ' . (int)$taskId);
		$aBuf = $q->loadColumn();
		$q->clear();
		$aBuf = !empty($aBuf) ? $aBuf : array();

		if ($recurse) {
			// recurse to find sub dependents
			foreach ($aBuf as $depId) {
				// work around for infinite loop
				if (!in_array($depId, $aDeps)) {
					$aDeps[] = $depId;
					$this->dependentTasks($depId, true);
				}
			}
		} else {
			$aDeps = $aBuf;
		}

		// return if we are in a dependency call
		if ($isDep) {
			return;
		}

		return implode(',', $aDeps);
	} // end of dependentTasks()

	/*
	*		 shift dependents tasks dates
	*		 @return void
	*/
	function shiftDependentTasks() {
		// Get tasks that depend on this task
		$csDeps = explode(',', $this->dependentTasks('', '', false));

		if ($csDeps[0] == '') {
			return;
		}

		// Stage 1: Update dependent task dates
		foreach ($csDeps as $task_id) {
			$this->update_dep_dates($task_id);
		}

		// Stage 2: Now shift the dependent tasks' dependents
		foreach ($csDeps as $task_id) {
			$newTask = new CTask();
			$newTask->load($task_id);
			$newTask->shiftDependentTasks();
		}

		return;
	} // end of shiftDependentTasks()

	/*
	*		  Update this task's dates in the DB.
	*		  start date:		  based on latest end date of dependencies
	*		  end date:			  based on start date + appropriate task time span
	*		   
	*		  @param				integer task_id of task to update
	*/
	function update_dep_dates($task_id) {
		global $tracking_dynamics;
		$q = &new DBQuery;

		$newTask = new CTask();
		$newTask->load($task_id);

		// Do not update tasks that are not tracking dependencies
		if (!in_array($newTask->task_dynamic, $tracking_dynamics)) {
			return;
		}

		// load original task dates and calculate task time span
		$tsd = new CDate($newTask->task_start_date);
		$ted = new CDate($newTask->task_end_date);
		$duration = $tsd->calcDuration($ted);

		// reset start date
		$nsd = new CDate($newTask->get_deps_max_end_date($newTask));

		// prefer Wed 8:00 over Tue 16:00 as start date
		$nsd = $nsd->next_working_day();
		$new_start_date = $nsd->format(FMT_DATETIME_MYSQL);

		// Add task time span to End Date again
		$ned = new CDate();
		$ned->copy($nsd);
		$ned->addDuration($duration, '1');

		// make sure one didn't land on a non-working day
		$ned = $ned->next_working_day(true);

		// prefer tue 16:00 over wed 8:00 as an end date
		$ned = $ned->prev_working_day();

		$new_end_date = $ned->format(FMT_DATETIME_MYSQL);

		// update the db
		$q->addTable('tasks');
		$q->addUpdate('task_start_date', $new_start_date);
		$q->addUpdate('task_end_date', $new_end_date);
		$q->addWhere('task_dynamic <> 1 AND task_id = ' . (int)$task_id);
		$q->exec();
		$q->clear();

		if ($newTask->task_parent != $newTask->task_id) {
			$newTask->updateDynamics();
		}

		return;
	}

	/*
	** Time related calculations have been moved to /classes/date.class.php
	** some have been replaced with more _robust_ functions
	** 
	** Affected functions:
	** prev_working_day()
	** next_working_day()
	** calc_task_end_date()	renamed to addDuration()
	** calc_end_date()	renamed to calcDuration()
	**
	** @date	20050525
	** @responsible gregorerhardt
	** @purpose	reusability, consistence
	*/

	/*
	
	Get the last end date of all of this task's dependencies
	
	@param Task object
	returns FMT_DATETIME_MYSQL date
	
	*/

	function get_deps_max_end_date($taskObj) {
		global $tracked_dynamics;
		$q = &new DBQuery;

		$deps = $taskObj->getDependencies();
		$obj = new CTask();

		$last_end_date = false;
		// Don't respect end dates of excluded tasks
		if ($tracked_dynamics && !empty($deps)) {
			$track_these = implode(',', $tracked_dynamics);
			$q->addTable('tasks');
			$q->addQuery('MAX(task_end_date)');
			$q->addWhere('task_id IN (' . $deps . ') AND task_dynamic IN (' . $track_these . ')');
			$last_end_date = $q->loadResult();
			$q->clear();
		}

		if (!$last_end_date) {
			// Set to project start date
			$id = $taskObj->task_project;
			$q->addTable('projects');
			$q->addQuery('project_start_date');
			$q->addWhere('project_id = ' . (int)$id);
			$last_end_date = $q->loadResult();
			$q->clear();
		}

		return $last_end_date;
	}

	/**
	 * Function that returns the amount of hours this
	 * task consumes per user each day
	 */
	function getTaskDurationPerDay($use_percent_assigned = false) {
		$duration = $this->task_duration * ($this->task_duration_type == 24 ? w2PgetConfig('daily_working_hours') : $this->task_duration_type);
		$task_start_date = new CDate($this->task_start_date);
		$task_finish_date = new CDate($this->task_end_date);
		$assigned_users = $this->getAssignedUsers($this->task_id);
		if ($use_percent_assigned) {
			$number_assigned_users = 0;
			foreach ($assigned_users as $u) {
				$number_assigned_users += ($u['perc_assignment'] / 100);
			}
		} else {
			$number_assigned_users = count($assigned_users);
		}

		$day_diff = $task_finish_date->dateDiff($task_start_date);
		$number_of_days_worked = 0;
		$actual_date = $task_start_date;

		for ($i = 0; $i <= $day_diff; $i++) {
			if ($actual_date->isWorkingDay()) {
				$number_of_days_worked++;
			}
			$actual_date->addDays(1);
		}
		// May be it was a Sunday task
		if ($number_of_days_worked == 0) {
			$number_of_days_worked = 1;
		}
		if ($number_assigned_users == 0) {
			$number_assigned_users = 1;
		}
		return ($duration / $number_assigned_users) / $number_of_days_worked;
	}

	/**
	 * Function that returns the amount of hours this
	 * task consumes per user each week
	 */
	function getTaskDurationPerWeek($use_percent_assigned = false) {
		$duration = $this->task_duration * ($this->task_duration_type == 24 ? w2PgetConfig('daily_working_hours') : $this->task_duration_type);
		$task_start_date = new CDate($this->task_start_date);
		$task_finish_date = new CDate($this->task_end_date);
		$assigned_users = $this->getAssignedUsers($this->task_id);
		if ($use_percent_assigned) {
			$number_assigned_users = 0;
			foreach ($assigned_users as $u) {
				$number_assigned_users += ($u['perc_assignment'] / 100);
			}
		} else {
			$number_assigned_users = count($assigned_users);
		}

		$number_of_weeks_worked = $task_finish_date->workingDaysInSpan($task_start_date) / count(explode(',', w2PgetConfig('cal_working_days')));
		$number_of_weeks_worked = (($number_of_weeks_worked < 1) ? ceil($number_of_weeks_worked) : $number_of_weeks_worked);

		// zero adjustment
		if ($number_of_weeks_worked == 0) {
			$number_of_weeks_worked = 1;
		}
		if ($number_assigned_users == 0) {
			$number_assigned_users = 1;
		}
		return ($duration / $number_assigned_users) / $number_of_weeks_worked;
	}

	// unassign a user from task
	function removeAssigned($user_id) {
		$q = &new DBQuery;
		// delete all current entries
		$q->setDelete('user_tasks');
		$q->addWhere('task_id = ' . (int)$this->task_id . ' AND user_id = ' . (int)$user_id);
		$q->exec();
		$q->clear();
	}

	//using user allocation percentage ($perc_assign)
	// @return returns the Names of the over-assigned users (if any), otherwise false
	function updateAssigned($cslist, $perc_assign, $del = true, $rmUsers = false) {
		$q = &new DBQuery;

		// process assignees
		$tarr = explode(',', $cslist);

		// delete all current entries from $cslist
		if ($del == true && $rmUsers == true) {
			foreach ($tarr as $user_id) {
				$user_id = (int)$user_id;
				if (!empty($user_id)) {
					$this->removeAssigned($user_id);
				}
			}
			return false;
		} elseif ($del == true) { // delete all users assigned to this task (to properly update)
			$q->setDelete('user_tasks');
			$q->addWhere('task_id = ' . (int)$this->task_id);
			$q->exec();
			$q->clear();
		}

		// get Allocation info in order to check if overAssignment occurs
		$alloc = $this->getAllocation('user_id');
		$overAssignment = false;
		foreach ($tarr as $user_id) {
			if (intval($user_id) > 0) {
				$perc = $perc_assign[$user_id];
				if (w2PgetConfig('check_overallocation') && $perc > $alloc[$user_id]['freeCapacity']) {
					// add Username of the overAssigned User
					$overAssignment .= ' ' . $alloc[$user_id]['userFC'];
				} else {
					$q->addTable('user_tasks');
					$q->addReplace('user_id', $user_id);
					$q->addReplace('task_id', $this->task_id);
					$q->addReplace('perc_assignment', $perc);
					$q->exec();
					$q->clear();
				}
			}
		}
		return $overAssignment;
	}

	public function getAssignedUsers($taskId) {
		$q = new DBQuery;
		$q->addTable('users', 'u');
		$q->innerJoin('user_tasks', 'ut', 'ut.user_id = u.user_id');
		$q->leftJoin('contacts', 'co', ' co.contact_id = u.user_contact');
		$q->addQuery('u.*, ut.perc_assignment, ut.user_task_priority, co.contact_last_name');
		$q->addWhere('ut.task_id = ' . (int) $taskId);

		return $q->loadHashList('user_id');
	}
	
	public function getDependencyList($taskId) {
		$q = new DBQuery;
		$q->addQuery('td.dependencies_req_task_id, t.task_name');
		$q->addTable('tasks', 't');
		$q->addTable('task_dependencies', 'td');
		$q->addWhere('td.dependencies_req_task_id = t.task_id');
		$q->addWhere('td.dependencies_task_id = ' . (int) $taskId);
		
		return $q->loadHashList();
	}
	public function getDependentTaskList($taskId) {
		$q = new DBQuery;
		$q->addQuery('td.dependencies_task_id, t.task_name');
		$q->addTable('tasks', 't');
		$q->addTable('task_dependencies', 'td');
		$q->addWhere('td.dependencies_task_id = t.task_id');
		$q->addWhere('td.dependencies_req_task_id = ' . $taskId);

		return $q->loadHashList();
	}
	public function getTaskDepartments($AppUI, $taskId) {
		if ($AppUI->isActiveModule('departments')) {
			$q = new DBQuery;
			$q->addTable('departments', 'd');
			$q->addTable('task_departments', 't');
			$q->addWhere('t.department_id = d.dept_id');
			$q->addWhere('t.task_id = ' . (int) $taskId);
			$q->addQuery('dept_id, dept_name, dept_phone');

			$department = new CDepartment;
			$department->setAllowedSQL($AppUI->user_id, $q);
			return $q->loadHashList('dept_id');
		}
	}
	public function getTaskContacts($AppUI, $taskId) {
		$perms = $AppUI->acl();

		if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
			$q = new DBQuery;
			$q->addTable('contacts', 'c');
			$q->addJoin('task_contacts', 'tc', 'tc.contact_id = c.contact_id', 'inner');
			$q->leftJoin('departments', 'd', 'dept_id = contact_department');
			$q->addWhere('tc.task_id = ' . (int) $taskId);
			$q->addQuery('c.contact_id, contact_first_name, contact_last_name, contact_email');
			$q->addQuery('contact_phone, dept_name');
			$q->addWhere('(contact_owner = ' . (int) $AppUI->user_id . ' OR contact_private = 0)');

			$department = new CDepartment;
			$department->setAllowedSQL($AppUI->user_id, $q);
			return $q->loadHashList('contact_id');
		}
	}

	/**
	 *	Calculate the extent of utilization of user assignments
	 *	@param string hash	 a hash for the returned hashList
	 *	@param array users	 an array of user_ids calculating their assignment capacity
	 *	@return array		 returns hashList of extent of utilization for assignment of the users
	 */
	function getAllocation($hash = null, $users = null, $get_user_list = false) {
		global $AppUI;
		if (!w2PgetConfig('check_overallocation')) {
			if ($get_user_list) {
				$users_list = w2PgetUsersHashList();
				foreach ($users_list as $key => $user) {
					$users_list[$key]['userFC'] = $user['contact_name'];
				}
				$hash = $users_list;
			} else {
				$hash = array();
			}
		} else {
			$q = &new DBQuery;
			// retrieve the systemwide default preference for the assignment maximum
			$q->addTable('user_preferences');
			$q->addQuery('pref_value');
			$q->addWhere('pref_user = 0 AND pref_name = \'' . TASKASSIGNMAX . '\'');
			$sysChargeMax = $q->loadHash();
			$q->clear();
			if (!$sysChargeMax) {
				$scm = 0;
			} else {
				$scm = $sysChargeMax['pref_value'];
			}
	
			/*
			* provide actual assignment charge, individual chargeMax 
			* and freeCapacity of users' assignments to tasks
			*/
			$q->addTable('users', 'u');
			$q->addJoin('contacts', 'c', 'c.contact_id = u.user_contact', 'inner');
			$q->leftJoin('user_tasks', 'ut', 'ut.user_id = u.user_id');
			$q->leftJoin('user_preferences', 'up', 'up.pref_user = u.user_id');
			$q->addQuery('u.user_id, CONCAT(CONCAT_WS(\' [\', CONCAT_WS(\' \', contact_first_name, contact_last_name), IF(IFNULL((IFNULL(up.pref_value, ' . $scm . ') - SUM(ut.perc_assignment)), up.pref_value) > 0, IFNULL((IFNULL(up.pref_value, ' . $scm . ') - SUM(ut.perc_assignment)), up.pref_value), 0)), \'%]\') AS userFC, IFNULL(SUM(ut.perc_assignment), 0) AS charge, u.user_username, IFNULL(up.pref_value,' . $scm . ') AS chargeMax, IF(IFNULL((IFNULL(up.pref_value, ' . $scm . ') - SUM(ut.perc_assignment)), up.pref_value) > 0, IFNULL((IFNULL(up.pref_value, ' . $scm . ') - SUM(ut.perc_assignment)), up.pref_value), 0) AS freeCapacity');
			if (!empty($users)) { // use userlist if available otherwise pull data for all users
				$q->addWhere('u.user_id IN (' . implode(',', $users) . ')');
			}
			$q->addGroup('u.user_id');
			$q->addOrder('contact_first_name, contact_last_name');
			// get CCompany() to filter by company
			require_once ($AppUI->getModuleClass('companies'));
			$obj = new CCompany();
			$companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
			$q->addJoin('companies', 'com', 'company_id = contact_company');
			if ($companies) {
				$q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
			}
			require_once ($AppUI->getModuleClass('departments'));
			$dpt = new CDepartment();
			$depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
			$q->addJoin('departments', 'dep', 'dept_id = contact_department');
			if ($depts) {
				$q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
			}
			$hash = $q->loadHashList($hash);
			$q->clear();
			//echo "<pre>$sql</pre>";
		}
		return $hash;
	}

	function getUserSpecificTaskPriority($user_id = 0, $task_id = null) {
		$q = &new DBQuery;
		// use task_id of given object if the optional parameter task_id is empty
		$task_id = empty($task_id) ? $this->task_id : $task_id;

		$q->addTable('user_tasks');
		$q->addQuery('user_task_priority');
		$q->addWhere('user_id = ' . (int)$user_id . ' AND task_id = ' . (int)$task_id);
		$priority = $q->loadHash();
		$q->clear();
		return $prio ? $priority['user_task_priority'] : null;
	}

	function updateUserSpecificTaskPriority($user_task_priority = 0, $user_id = 0, $task_id = null) {
		$q = &new DBQuery;
		// use task_id of given object if the optional parameter task_id is empty
		$task_id = empty($task_id) ? $this->task_id : $task_id;

		$q->addTable('user_tasks');
		$q->addReplace('user_id', $user_id);
		$q->addReplace('task_id', $task_id);
		$q->addReplace('user_task_priority', $user_task_priority);
		$q->exec();
		$q->clear();
	}

	function getProject() {
		$q = &new DBQuery;

		$q->addTable('projects');
		$q->addQuery('project_name, project_short_name, project_color_identifier');
		$q->addWhere('project_id = ' . (int)$this->task_project);
		$projects = $q->loadHash();
		$q->clear();
		return $projects;
	}

	//Returns task children IDs
	function getChildren() {
		$q = &new DBQuery;

		$q->addTable('tasks');
		$q->addQuery('task_id');
		$q->addWhere('task_id <> ' . (int)$this->task_id . ' AND task_parent = ' . (int)$this->task_id);
		$result = $q->loadColumn();
		$q->clear();

		return $result;
	}

	// Returns task deep children IDs
	function getDeepChildren() {
		$children = $this->getChildren();

		if ($children) {
			$deep_children = array();
			$tempTask = &new CTask();
			foreach ($children as $child) {
				$tempTask->peek($child);
				$deep_children = array_merge($deep_children, $tempTask->getDeepChildren());
			}

			return array_merge($children, $deep_children);
		}
		return array();
	}

	/**
	 * This function, recursively, updates all tasks status
	 * to the one passed as parameter
	 */
	function updateSubTasksStatus($new_status, $task_id = null) {
		$q = &new DBQuery;

		if (is_null($task_id)) {
			$task_id = $this->task_id;
		}

		// get children
		$q->addTable('tasks');
		$q->addQuery('task_id');
		$q->addWhere('task_parent = ' . (int)$task_id);
		$tasks_id = $q->loadColumn();
		$q->clear();
		if (count($tasks_id) == 0) {
			return true;
		}

		// update status of children
		$q->addTable('tasks');
		$q->addUpdate('task_status', $new_status);
		$q->addWhere('task_parent = ' . (int)$task_id);
		$q->exec();
		$q->clear();

		// update status of children's children
		foreach ($tasks_id as $id) {
			if ($id != $task_id) {
				$this->updateSubTasksStatus($new_status, $id);
			}
		}
	}

	/**
	 * This function recursively updates all tasks project
	 * to the one passed as parameter
	 */
	function updateSubTasksProject($new_project, $task_id = null) {
		$q = &new DBQuery;

		if (is_null($task_id)) {
			$task_id = $this->task_id;
		}

		$q->addTable('tasks');
		$q->addQuery('task_id');
		$q->addWhere('task_parent = ' . (int)$task_id);
		$tasks_id = $q->loadColumn();
		$q->clear();

		if (count($tasks_id) == 0) {
			return true;
		}

		// update project of children
		$q->addTable('tasks');
		$q->addUpdate('task_project', $new_project);
		$q->addWhere('task_parent = ' . (int)$task_id);
		$q->exec();
		$q->clear();

		foreach ($tasks_id as $id) {
			if ($id != $task_id) {
				$this->updateSubTasksProject($new_project, $id);
			}
		}
	}

	function canUserEditTimeInformation() {
		global $AppUI;

		$project = new CProject();
		$project->load($this->task_project);

		// Code to see if the current user is
		// enabled to change time information related to task
		$can_edit_time_information = false;
		// Let's see if all users are able to edit task time information
		if (w2PgetConfig('restrict_task_time_editing') == true && $this->task_id > 0) {

			// Am I the task owner?
			if ($this->task_owner == $AppUI->user_id) {
				$can_edit_time_information = true;
			}

			// Am I the project owner?
			if ($project->project_owner == $AppUI->user_id) {
				$can_edit_time_information = true;
			}

			// Am I sys admin?
			if (!getDenyEdit('admin')) {
				$can_edit_time_information = true;
			}

		} else
			if (w2PgetConfig('restrict_task_time_editing') == false || $this->task_id == 0) {
				// If all users are able, then don't check anything
				$can_edit_time_information = true;
			}

		return $can_edit_time_information;
	}

	/**
	 * Injects a reminder event into the event queue.
	 * Repeat interval is one day, repeat count
	 * and days to trigger before event overdue is
	 * set in the system config.
	 */
	function addReminder() {
		$day = 86400;

		if (!w2PgetConfig('task_reminder_control')) {
			return;
		}

		if (!$this->task_end_date) { // No end date, can't do anything.
			return $this->clearReminder(true); // Also no point if it is changed to null
		}

		if ($this->task_percent_complete >= 100) {
			return $this->clearReminder(true);
		}

		$eq = new EventQueue;
		$pre_charge = w2PgetConfig('task_reminder_days_before', 1);
		$repeat = w2PgetConfig('task_reminder_repeat', 100);

		/*
		* If we don't need any arguments (and we don't) then we set this to null. 
		* We can't just put null in the call to add as it is passed by reference.
		*/
		$args = null;

		// Find if we have a reminder on this task already
		$old_reminders = $eq->find('tasks', 'remind', $this->task_id);
		if (count($old_reminders)) {
			/*
			* It shouldn't be possible to have more than one reminder, 
			* but if we do, we may as well clean them up now.
			*/
			foreach ($old_reminders as $old_id => $old_data) {
				$eq->remove($old_id);
			}
		}

		// Find the end date of this task, then subtract the required number of days.
		$date = new CDate($this->task_end_date);
		$today = new CDate(date('Y-m-d'));
		if (CDate::compare($date, $today) < 0) {
			$start_day = time();
		} else {
			$start_day = $date->getDate(DATE_FORMAT_UNIXTIME);
			$start_day -= ($day * $pre_charge);
		}

		$eq->add(array($this, 'remind'), $args, 'tasks', false, $this->task_id, 'remind', $start_day, $day, $repeat);
	}

	/**
	 * Called by the Event Queue processor to process a reminder
	 * on a task.
	 * @access		  public
	 * @param		 string		   $module		  Module name (not used)
	 * @param		 string		   $type Type of event (not used)
	 * @param		 integer		$id ID of task being reminded
	 * @param		 integer		$owner		  Originator of event
	 * @param		 mixed		  $args event-specific arguments.
	 * @return		  mixed		   true, dequeue event, false, event stays in queue.
	 * -1, event is destroyed.
	 */
	function remind($module, $type, $id, $owner, &$args) {
		global $locale_char_set, $AppUI;
		$q = &new DBQuery;

		$df = $AppUI->getPref('SHDATEFORMAT');
		$tf = $AppUI->getPref('TIMEFORMAT');
		// If we don't have preferences set for these, use ISO defaults.
		if (!$df) {
			$df = '%Y-%m-%d';
		}
		if (!$tf) {
			$tf = '%H:%m';
		}
		$df .= ' ' . $tf;

		// At this stage we won't have an object yet
		if (!$this->load($id)) {
			return - 1; // No point it trying again later.
		}
		$this->htmlDecode();

		// Only remind on working days.
		$today = new CDate();
		if (!$today->isWorkingDay()) {
			return true;
		}

		// Check if the task is completed
		if ($this->task_percent_complete == 100) {
			return - 1;
		}

		// Grab the assignee list
		$q->addTable('user_tasks', 'ut');
		$q->addJoin('users', 'u', 'u.user_id = ut.user_id', 'inner');
		$q->addJoin('contacts', 'c', 'c.contact_id = u.user_contact', 'inner');
		$q->addQuery('c.contact_id, contact_first_name, contact_last_name, contact_email');
		$q->addWhere('ut.task_id = ' . (int)$id);
		$contacts = $q->loadHashList('contact_id');
		$q->clear();

		// Now we also check the owner of the task, as we will need
		// to notify them as well.
		$owner_is_not_assignee = false;
		$q->addTable('users', 'u');
		$q->addJoin('contacts', 'c', 'c.contact_id = u.user_contact', 'inner');
		$q->addQuery('c.contact_id, contact_first_name, contact_last_name, contact_email');
		$q->addWhere('u.user_id = ' . (int)$this->task_owner);
		if ($q->exec(ADODB_FETCH_NUM)) {
			list($owner_contact, $owner_first_name, $owner_last_name, $owner_email) = $q->fetchRow();
			if (!isset($contacts[$owner_contact])) {
				$owner_is_not_assignee = true;
				$contacts[$owner_contact] = array('contact_id' => $owner_contact, 'contact_first_name' => $owner_first_name, 'contact_last_name' => $owner_last_name, 'contact_email' => $owner_email);
			}
		}
		$q->clear();

		// build the subject line, based on how soon the
		// task will be overdue.
		$starts = new CDate($this->task_start_date);
		$expires = new CDate($this->task_end_date);
		$now = new CDate();
		$diff = $expires->dateDiff($now);
		$diff *= CDate::compare($expires, $now);
		$prefix = $AppUI->_('Task Due', UI_OUTPUT_RAW);
		if ($diff == 0) {
			$msg = $AppUI->_('TODAY', UI_OUTPUT_RAW);
		} elseif ($diff == 1) {
			$msg = $AppUI->_('TOMORROW', UI_OUTPUT_RAW);
		} elseif ($diff < 0) {
			$msg = $AppUI->_(array('OVERDUE', abs($diff), 'DAYS'));
			$prefix = $AppUI->_('Task', UI_OUTPUT_RAW);
		} else {
			$msg = $AppUI->_(array($diff, 'DAYS'));
		}

		$q->addTable('projects');
		$q->addQuery('project_name');
		$q->addWhere('project_id = ' . (int)$this->task_project);
		$project_name = htmlspecialchars_decode($q->loadResult());
		$q->clear();

		$subject = $prefix . ' ' . $msg . ' ' . $this->task_name . '::' . $project_name;

		$body = ($AppUI->_('Task Due', UI_OUTPUT_RAW) . ': ' . $msg . "\n" . $AppUI->_('Project', UI_OUTPUT_RAW) . ': ' . $project_name . "\n" . $AppUI->_('Task', UI_OUTPUT_RAW) . ': ' . $this->task_name . "\n" . $AppUI->_('Start Date', UI_OUTPUT_RAW) . ': ' . $starts->format($df) . "\n" . $AppUI->_('Finish Date', UI_OUTPUT_RAW) . ': ' . $expires->format($df) . "\n" . $AppUI->_('URL', UI_OUTPUT_RAW) . ': ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $this->task_id . '&reminded=1' . "\n\n" . $AppUI->_('Resources', UI_OUTPUT_RAW) . ":\n");
		foreach ($contacts as $contact) {
			if ($owner_is_not_assignee || $contact['contact_id'] != $owner_contact) {
				$body .= ($contact['contact_first_name'] . ' ' . $contact['contact_last_name'] . ' <' . $contact['contact_email'] . ">\n");
			}
		}
		$body .= ("\n" . $AppUI->_('Description', UI_OUTPUT_RAW) . ":\n" . $this->task_description . "\n");

		$mail = new Mail;
		foreach ($contacts as $contact) {
			if ($mail->ValidEmail($contact['contact_email'])) {
				$mail->To($contact['contact_email']);
			}
		}
		$mail->Subject($subject, $locale_char_set);
		$mail->Body($body, $locale_char_set);
		return $mail->Send();
	}

	/**
	 *
	 */
	function clearReminder($dont_check = false) {
		$ev = new EventQueue;

		$event_list = $ev->find('tasks', 'remind', $this->task_id);
		if (count($event_list)) {
			foreach ($event_list as $id => $data) {
				if ($dont_check || $this->task_percent_complete >= 100) {
					$ev->remove($id);
				}
			}
		}
	}

	function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		global $AppUI;
		$oPrj = new CProject();

		$aPrjs = $oPrj->getAllowedRecords($uid, 'projects.project_id, project_name', '', null, null, 'projects');
		if (count($aPrjs)) {
			$buffer = '(task_project IN (' . implode(',', array_keys($aPrjs)) . '))';

			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND ' . $buffer;
			} else {
				$extra['where'] = $buffer;
			}
		} else {
			// There are no allowed projects, so don't allow tasks.
			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND 1 = 0 ';
			} else {
				$extra['where'] = '1 = 0';
			}
		}
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);
	}

	function &getAssigned() {
		$q = new DBQuery;
		$q->addTable('users', 'u');
		$q->addTable('user_tasks', 'ut');
		$q->addTable('contacts', 'con');
		$q->addQuery('u.user_id, CONCAT_WS(\' \',contact_first_name, contact_last_name, CONCAT(perc_assignment, \'%\'))');
		$q->addWhere('ut.task_id = ' . (int)$this->task_id);
		$q->addWhere('user_contact = contact_id');
		$q->addWhere('ut.user_id = u.user_id');
		$assigned = $q->loadHashList();
		return $assigned;
	}

	public function calendar_hook($userId) {
		/*
		 * This list of fields - id, name, description, startDate, endDate,
		 * updatedDate - are named specifically for the iCal creation.
		 * If you change them, it's probably going to break.  So don't do that.
		 */
		$taskArray = array();
		$taskList = $this->getTaskList($userId);

		//TODO: A user should be able to select if they get distinct start/end dates or two tasks for each task.
		foreach ($taskList as $taskItem) {			
			$taskArray[] = array_merge($taskItem, array('endDate' => $taskItem['startDate'], 'name' => 'Start: '.$taskItem['name']));
			$taskArray[] = array_merge($taskItem, array('startDate' => $taskItem['endDate'], 'name' => 'End: '.$taskItem['name']));
		}

		return $taskArray;
	}

	public function getTaskList($userId, $days = 30) {
		/*
		 * This list of fields - id, name, description, startDate, endDate,
		 * updatedDate - are named specifically for the iCal creation.
		 * If you change them, it's probably going to break.  So don't do that.
		 */

		$q = new DBQuery();
		$q->addQuery('t.task_id as id');
		$q->addQuery('task_name as name');
		$q->addQuery('task_description as description');
		$q->addQuery('task_start_date as startDate');
		$q->addQuery('task_end_date as endDate');
		$q->addQuery('now() as updatedDate');
		$q->addTable('tasks', 't');

		$q->addWhere("task_start_date < DATE_ADD(CURDATE(), INTERVAL $days DAY)");
		$q->addWhere('task_percent_complete < 100');
		$q->addWhere('task_dynamic = 0');

		$q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
		$q->addWhere("ut.user_id = $userId");
		
		$q->innerJoin('projects', 'p', 'p.project_id = t.task_project');
		$q->addWhere('project_active > 0');

		$q->addOrder('task_start_date, task_end_date');

		return $q->loadList();		
	}
	public static function pinUserTask($userId, $taskId) {
		$q = new DBQuery;
		$q->addTable('user_task_pin');
		$q->addInsert('user_id', (int) $userId);
		$q->addInsert('task_id', (int) $taskId);

		if (!$q->exec()) {
			return 'Error pinning task';
		} else {
			return true;
		}
	}
	public static function unpinUserTask($userId, $taskId) {
		$q = new DBQuery;
		$q->setDelete('user_task_pin');
		$q->addWhere('user_id = ' . (int) $userId);
		$q->addWhere('task_id = ' . (int) $taskId);
		
		if (!$q->exec()) {
			return 'Error unpinning task';
		} else {
			return true;
		}
	}
}

/**
 * CTaskLog Class
 */
class CTaskLog extends CW2pObject {
	var $task_log_id = null;
	var $task_log_task = null;
	var $task_log_name = null;
	var $task_log_description = null;
	var $task_log_creator = null;
	var $task_log_hours = null;
	var $task_log_date = null;
	var $task_log_costcode = null;
	var $task_log_problem = null;
	var $task_log_reference = null;
	var $task_log_related_url = null;

	function CTaskLog() {
		$this->CW2pObject('task_log', 'task_log_id');

		// ensure changes to checkboxes are honoured
		$this->task_log_problem = intval($this->task_log_problem);
	}

	function w2PTrimAll() {
		$spacedDescription = $this->task_log_description;
		parent::w2PTrimAll();
		$this->task_log_description = $spacedDescription;
	}

	// overload check method
	function check() {
		$this->task_log_hours = (float)$this->task_log_hours;
		return null;
	}

	function canDelete(&$msg, $oid = null, $joins = null) {
		global $AppUI;
		$q = &new DBQuery;

		// First things first.	Are we allowed to delete?
		$acl = &$AppUI->acl();
		if (!$acl->checkModule('task_log', 'delete')) {
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

	function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
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

//This kludgy function echos children tasks as threads

function showtask(&$arr, $level = 0, $is_opened = true, $today_view = false, $hideOpenCloseLink = false, $allowRepeat = false) {
	global $AppUI, $query_string, $durnTypes, $userAlloc, $showEditCheckbox;
	global $m, $a, $history_active, $expanded;

	$now = new CDate();
	$tf = $AppUI->getPref('TIMEFORMAT');
	$df = $AppUI->getPref('SHDATEFORMAT');
	$perms = &$AppUI->acl();	
	$fdf = $df . ' ' . $tf;
	$show_all_assignees = w2PgetConfig('show_all_task_assignees', false);

	$start_date = intval($arr['task_start_date']) ? new CDate($arr['task_start_date']) : null;
	$end_date = intval($arr['task_end_date']) ? new CDate($arr['task_end_date']) : null;
	$last_update = ((isset($arr['last_update']) && intval($arr['last_update'])) ? new CDate($arr['last_update']) : null);

	// prepare coloured highlight of task time information
	$sign = 1;
	$style = '';
	if ($start_date) {
		if (!$end_date) {
			/*
			** end date calc has been moved to calcEndByStartAndDuration()-function
			** called from array_csort and tasks.php 
			** perhaps this fallback if-clause could be deleted in the future, 
			** didn't want to remove it shortly before the 2.0.2
			*/
			$end_date = new CDate('0000-00-00 00:00:00');
		}

		if ($now->after($start_date) && $arr['task_percent_complete'] == 0) {
			$style = 'background-color:#ffeebb';
		} elseif ($now->after($start_date) && $arr['task_percent_complete'] < 100) {
			$style = 'background-color:#e6eedd';
		}

		if ($now->after($end_date)) {
			$sign = -1;
			$style = 'background-color:#cc6666;color:#ffffff';
		}
		if ($arr['task_percent_complete'] == 100) {
			$style = 'background-color:#aaddaa; color:#00000';
		}

		$days = $now->dateDiff($end_date) * $sign;
	}

	//     $s = "\n<tr id=\"project_".$arr['task_project'].'_level>'.$level.'<task_'.$arr['task_id']."_\" ".((($level>0 && !($m=='tasks' && $a=='view')) || ($m=='tasks' && ($a=='' || $a=='index'))) ? 'style="display:none"' : '').'>';
	if ($expanded) {
		$s = '<tr id="project_' . $arr['task_project'] . '_level>' . $level . '<task_' . $arr['task_id'] . '_" >';
	} else {
		$s = '<tr id="project_' . $arr['task_project'] . '_level>' . $level . '<task_' . $arr['task_id'] . '_" ' . (($level > 0 && !($m == 'tasks' && $a == 'view')) ? 'style="display:none"' : '') . '>';
	}
	// edit icon
	$s .= '<td align="center">';
	$canEdit = true;
	$canViewLog = true;
	if ($canEdit) {
		$s .= w2PtoolTip('edit task', 'click to edit this task') . '<a href="?m=tasks&a=addedit&task_id=' . $arr['task_id'] . '">' . w2PshowImage('icons/pencil.gif', 12 , 12). '</a>' . w2PendTip();
	}
	$s .= '</td>';
	// pinned
	$pin_prefix = $arr['task_pinned'] ? '' : 'un';
	$s .= ('<td align="center"><a href="?m=tasks&pin=' . ($arr['task_pinned'] ? 0 : 1) . '&task_id=' . $arr['task_id'] . '">' . w2PtoolTip('Pin', 'pin/unpin task') . '<img src="' . w2PfindImage('icons/' . $pin_prefix . 'pin.gif') . '" border="0" />' . w2PendTip() . '</a></td>');
	// New Log
	if ($arr['task_log_problem'] > 0) {
		$s .= ('<td align="center" valign="middle"><a href="?m=tasks&a=view&task_id=' . $arr['task_id'] . '&tab=0&problem=1">' . w2PshowImage('icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem!') . '</a></td>');
	} elseif ($canViewLog && $arr['task_dynamic'] != 1) {
		$s .= ('<td align="center"><a href="?m=tasks&a=view&task_id=' . $arr['task_id'] . '&tab=1">' . w2PtoolTip('Add Log', 'create a new log record against this task') . w2PshowImage('edit_add.png') . w2PendTip() . '</a></td>');
	} else {
		$s .= '<td align="center">' . $AppUI->_('-') . '</td>';
	}
	// percent complete and priority
	$s .= ('<td align="right">' . intval($arr['task_percent_complete']) . '%</td><td align="center" nowrap="nowrap">');
	if ($arr['task_priority'] < 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority-' . -$arr['task_priority'] . '.gif') . '" />';
	} elseif ($arr['task_priority'] > 0) {
		$s .= '<img src="' . w2PfindImage('icons/priority+' . $arr['task_priority'] . '.gif') . '" />';
	}
	$s .= (($arr['file_count'] > 0) ? '<img src="' . w2PfindImage('clip.png') . '" alt="F" />' : '') . '</td>';
	// dots
	$s .= '<td width="' . (($today_view) ? '50%' : '90%') . '">';
	//level
	if ($level == -1) {
		$s .= '...';
	}
	for ($y = 0; $y < $level; $y++) {
		if ($y + 1 == $level) {
			$s .= '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="' . w2PfindImage('shim.gif') . '" width="16" height="12"  border="0">';
		}
	}
	if ($arr['task_description']) {
		$s .= w2PtoolTip('Task Description', $arr['task_description'], true);
	}
	$open_link = '<a href="javascript: void(0);"><img onclick="expand_collapse(\'project_' . $arr['task_project'] . '_level>' . $level . '<task_' . $arr['task_id'] . '_\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="project_' . $arr['task_project'] . '_level>' . $level . '<task_' . $arr['task_id'] . '__collapse" src="' . w2PfindImage('icons/collapse.gif') . '" border="0" align="center" ' . (!$expanded ? 'style="display:none"' : '') . ' /><img onclick="expand_collapse(\'project_' . $arr['task_project'] . '_level>' . $level . '<task_' . $arr['task_id'] . '_\', \'tblProjects\',\'\',' . ($level + 1) . ');" id="project_' . $arr['task_project'] . '_level>' . $level . '<task_' . $arr['task_id'] . '__expand" src="' . w2PfindImage('icons/expand.gif') . '" border="0" align="center" ' . ($expanded ? 'style="display:none"' : '') . ' /></a>';
	if ($arr['task_nr_of_children']) {
		$is_parent = true;
	} else {
		$is_parent = false;
	}
	if ($arr['task_milestone'] > 0) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" ><b>' . $arr['task_name'] . '</b></a> <img src="' . w2PfindImage('icons/milestone.gif') . '" border="0" /></td>';
	} elseif ($arr['task_dynamic'] == '1' || $is_parent) {
		if (!$today_view) {
			$s .= $open_link;
		}
		if ($arr['task_dynamic'] == '1') {
			$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" ><b><i>' . $arr['task_name'] . '</i></b></a></td>';
		} else {
			$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a></td>';
		}
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a></td>';
	}
	if ($arr['task_description']) {
		$s .= w2PendTip();
	}
	if ($today_view) { // Show the project name
		$s .= ('<td width="50%"><a href="./index.php?m=projects&a=view&project_id=' . $arr['task_project'] . '">' . '<span style="padding:2px;background-color:#' . $arr['project_color_identifier'] . ';color:' . bestColor($arr['project_color_identifier']) . '">' . $arr['project_name'] . '</span>' . '</a></td>');
	}
	// task owner
	if (!$today_view) {
		$s .= ('<td nowrap="nowrap" align="center">' . '<a href="?m=admin&a=viewuser&user_id=' . $arr['user_id'] . '">' . $arr['owner'] . '</a>' . '</td>');
	}
	if (isset($arr['task_assigned_users']) && ($assigned_users = $arr['task_assigned_users'])) {
		$a_u_tmp_array = array();
		if ($show_all_assignees) {
			$s .= '<td align="center">';
			foreach ($assigned_users as $val) {
				$a_u_tmp_array[] = ('<a href="?m=admin&a=viewuser&user_id=' . $val['user_id'] . '"' . 'title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$val['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$val['user_id']]['freeCapacity'] . '%' : '') . '">' . $val['assignee'] . ' (' . $val['perc_assignment'] . '%)</a>');
			}
			$s .= join(', ', $a_u_tmp_array) . '</td>';
		} else {
			$s .= ('<td align="center" nowrap="nowrap">' . '<a href="?m=admin&a=viewuser&user_id=' . $assigned_users[0]['user_id'] . '" title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[0]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[0]['user_id']]['freeCapacity'] . '%' : '') . '">' . $assigned_users[0]['assignee'] . ' (' . $assigned_users[0]['perc_assignment'] . '%)</a>');
			if ($arr['assignee_count'] > 1) {
				$s .= (' <a href="javascript: void(0);" onclick="toggle_users(' . "'users_" . $arr['task_id'] . "'" . ');" title="' . join(', ', $a_u_tmp_array) . '">(+' . ($arr['assignee_count'] - 1) . ')</a>' . '<span style="display: none" id="users_' . $arr['task_id'] . '">');
				$a_u_tmp_array[] = $assigned_users[0]['assignee'];
				for ($i = 1, $i_cmp = count($assigned_users); $i < $i_cmp; $i++) {
					$a_u_tmp_array[] = $assigned_users[$i]['assignee'];
					$s .= ('<br /><a href="?m=admin&a=viewuser&user_id=' . $assigned_users[$i]['user_id'] . '" title="' . (w2PgetConfig('check_overallocation') ? $AppUI->_('Extent of Assignment') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['charge'] . '%; ' . $AppUI->_('Free Capacity') . ':' . $userAlloc[$assigned_users[$i]['user_id']]['freeCapacity'] . '%' : '') . '">' . $assigned_users[$i]['assignee'] . ' (' . $assigned_users[$i]['perc_assignment'] . '%)</a>');
				}
				$s .= '</span>';
			}
			$s .= '</td>';
		}
	} elseif (!$today_view) {
		// No users asigned to task
		$s .= '<td align="center">-</td>';
	}
	// duration or milestone
	$s .= ('<td nowrap="nowrap" align="center" style="' . $style . '">' . ($start_date ? $start_date->format($fdf) : '-') . '</td>' . '<td align="right" nowrap="nowrap" style="' . $style . '">' . $arr['task_duration'] . ' ' . substr($AppUI->_($durnTypes[$arr['task_duration_type']]), 0, 1) . '</td>' . '<td nowrap="nowrap" align="center" style="' . $style . '">' . ($end_date ? $end_date->format($fdf) : '-') . '</td>');
	if ($today_view) {
		$s .= ('<td nowrap="nowrap" align="center" style="' . $style . '">' . $arr['task_due_in'] . '</td>');
	} elseif ($history_active) {
		$s .= ('<td nowrap="nowrap" align="center" style="' . $style . '">' . ($last_update ? $last_update->format($fdf) : '-') . '</td>');
	}

	// Assignment checkbox
	if ($showEditCheckbox) {
		$s .= ('<td align="center">' . '<input type="checkbox" name="selected_task[' . $arr['task_id'] . ']" value="' . $arr['task_id'] . '"/></td>');
	}
	$s .= '</tr>';
	echo $s;
}

function findchild(&$tarr, $parent, $level = 0) {
	global $shown_tasks;

	$level = $level + 1;
	$n = count($tarr);

	for ($x = 0; $x < $n; $x++) {
		if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
			showtask($tarr[$x], $level, true);
			$shown_tasks[] = $tarr[$x]['task_id'];			
			findchild($tarr, $tarr[$x]['task_id'], $level);
		}
	}
}

/* please throw this in an include file somewhere, its very useful */

function array_csort() { //coded by Ichier2003

	$args = func_get_args();
	$marray = array_shift($args);

	if (empty($marray)) {
		return array();
	}

	$i = 0;
	$msortline = 'return(array_multisort(';
	$sortarr = array();
	foreach ($args as $arg) {
		$i++;
		if (is_string($arg)) {
			for ($j = 0, $j_cmp = count($marray); $j < $j_cmp; $j++) {

				/* we have to calculate the end_date via start_date+duration for
				** end='0000-00-00 00:00:00' before sorting, see mantis #1509:
				
				** Task definition writes the following to the DB:
				** A without start date: start = end = NULL
				** B with start date and empty end date: start = startdate, 
				end = '0000-00-00 00:00:00'
				** C start + end date: start= startdate, end = end date
				
				** A the end_date for the middle task (B) is ('dynamically') calculated on display 
				** via start_date+duration, it may be that the order gets wrong due to the fact 
				** that sorting has taken place _before_.
				*/
				if ($marray[$j]['task_end_date'] == '0000-00-00 00:00:00') {
					$marray[$j]['task_end_date'] = calcEndByStartAndDuration($marray[$j]);
				}
				$sortarr[$i][] = $marray[$j][$arg];
			}
		} else {
			$sortarr[$i] = $arg;
		}
		$msortline .= '$sortarr[' . $i . '],';
	}
	$msortline .= '$marray));';

	eval($msortline);

	return $marray;
}

/*
** Calc End Date via Startdate + Duration
** @param array task	A DB row from the earlier fetched tasklist
** @return string	Return calculated end date in MySQL-TIMESTAMP format	
*/

function calcEndByStartAndDuration($task) {
	$end_date = new CDate($task['task_start_date']);
	$end_date->addSeconds($task['task_duration'] * $task['task_duration_type'] * SEC_HOUR);
	return $end_date->format(FMT_DATETIME_MYSQL);
}

function sort_by_item_title($title, $item_name, $item_type, $a = '') {
	global $AppUI, $project_id, $task_id, $min_view, $m;
	global $task_sort_item1, $task_sort_type1, $task_sort_order1;
	global $task_sort_item2, $task_sort_type2, $task_sort_order2;

	if ($task_sort_item2 == $item_name) {
		$item_order = $task_sort_order2;
	}
	if ($task_sort_item1 == $item_name) {
		$item_order = $task_sort_order1;
	}
	
	$s = '';
	
	if (isset($item_order)) {
		$show_icon = true;
	} else {
		$show_icon = false;
		$item_order = SORT_DESC;
	}

	/* flip the sort order for the link */
	$item_order = ($item_order == SORT_ASC) ? SORT_DESC : SORT_ASC;
	if ($m == 'tasks') {
		$s .= '<a href="./index.php?m=tasks' . (($task_id > 0) ? ('&a=view&task_id=' . $task_id) : $a);
	} elseif ($m == 'calendar') {
		$s .= '<a href="./index.php?m=calendar&a=day_view';
	} else {
		$s .= '<a href="./index.php?m=projects&bypass=1' . (($project_id > 0) ? ('&a=view&project_id=' . $project_id) : '');
	}
	$s .= '&task_sort_item1=' . $item_name;
	$s .= '&task_sort_type1=' . $item_type;
	$s .= '&task_sort_order1=' . $item_order;
	if ($task_sort_item1 == $item_name) {
		$s .= '&task_sort_item2=' . $task_sort_item2;
		$s .= '&task_sort_type2=' . $task_sort_type2;
		$s .= '&task_sort_order2=' . $task_sort_order2;
	} else {
		$s .= '&task_sort_item2=' . $task_sort_item1;
		$s .= '&task_sort_type2=' . $task_sort_type1;
		$s .= '&task_sort_order2=' . $task_sort_order1;
	}
	$s .= '" class="hdr">' . $AppUI->_($title);
	if ($show_icon) {
		$s .= '&nbsp;<img src="' . w2PfindImage('arrow-' . (($item_order == SORT_ASC) ? 'up' : 'down') . '.gif') . '" border="0" /></a>';
	}
	echo $s;
}
?>