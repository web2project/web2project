<?php
/**
 * @package     web2project\modules\core
 */

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');

// patch 2.12.04 add all finished last 7 days, my finished last 7 days
$filters = array('my' => 'My Tasks', 'myunfinished' => 'My Unfinished Tasks', 'allunfinished' => 'All Unfinished Tasks', 'myproj' => 'My Projects', 'mycomp' => 'All Tasks for my Company', 'unassigned' => 'All Tasks (unassigned)', 'taskowned' => 'All Tasks That I Am Owner', 'taskcreated' => 'All Tasks I Have Created', 'all' => 'All Tasks', 'allfinished7days' => 'All Tasks Finished Last 7 Days', 'myfinished7days' => 'My Tasks Finished Last 7 Days');

$status = w2PgetSysVal('TaskStatus');

$priority = w2PgetSysVal('TaskPriority');

class CTask extends w2p_Core_BaseObject
{

    /**
     * @var int
     */
    public $task_id = null;

    /**
     * @var string
     */
    public $task_name = null;

    /**
     * @var int
     */
    public $task_parent = null;
    public $task_milestone = null;
    public $task_project = null;
    public $task_owner = null;
    public $task_start_date = null;
    public $task_duration = null;
    public $task_duration_type = null;

    /**
     * @var bool
     */
    protected $importing_tasks = false; // Introduced this to address bug #1064. Used to keep var between store and postStore.

    /**
     * @deprecated
     */
    public $task_hours_worked = null;
    public $task_end_date = null;
    public $task_status = null;
    public $task_priority = null;
    public $task_percent_complete = null;
    public $task_description = null;
    public $task_target_budget = null;
    public $task_related_url = null;
    public $task_creator = null;
    public $task_access = null;
    public $task_order = null;
    public $task_client_publish = null;
    public $task_dynamic = null;
    public $task_notify = null;
    public $task_departments = null;
    public $task_contacts = null;
    public $task_custom = null;
    public $task_type = null;
    public $task_created = null;
    public $task_updated = null;
    public $task_updator = null;
    public $task_allow_other_user_tasklogs;

    /*
     * TASK DYNAMIC VALUE:
     * 0  = default(OFF), no dep tracking of others, others do track
     * 1  = dynamic, umbrella task, no dep tracking, others do track
     * 11 = OFF, no dep tracking, others do not track
     * 21 = FEATURE, dep tracking, others do not track
     * 31 = ON, dep tracking, others do track
     */

    protected $_depth = 0;
    /**
     * When calculating a task's start date only consider
     * end dates of tasks with these dynamic values.
     *
     * @access public
     * @static
     */
    public static $tracked_dynamics = array('0' => '0', '1' => '1', '2' => '31');

    /**
     * Tasks with these dynamics have their dates updated when
     * one of their dependencies changes. (They track dependencies)
     *
     * @access public
     * @static
     */
    public static $tracking_dynamics = array('0' => '21', '1' => '31');

    /**
     * Class constants for task access
     */

    const ACCESS_PUBLIC = 0;
    const ACCESS_PROTECTED = 1;
    const ACCESS_PARTICIPANT = 2;
    const ACCESS_PRIVATE = 3;
    const DURATION_TYPE_HOURS = 1;
    const DURATION_TYPE_DAYS = 24;

    public function __construct()
    {
        parent::__construct('tasks', 'task_id');
    }

    public function __toString()
    {
        return $this->link . '/' . $this->type . '/' . $this->length;
    }

    
/*
 * TODO: The check() this is based on is a ridiculous hairy mess that has numerous
 *   levels of nesting, data modification, and various other things.
 * 
 * In short, Here Be Dragons.
 */ 
    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        $this->task_id = (int) $this->task_id;

        if (is_null($this->task_priority) || !is_numeric((int) $this->task_priority)) {
            $this->_error['task_priority'] = $baseErrorMsg . 'task priority is NULL';
        }
        if ($this->task_name == '') {
            $this->_error['task_name'] = $baseErrorMsg . 'task name is NULL';
        }
        if (is_null($this->task_project) || 0 == (int) $this->task_project) {
            $this->_error['task_project'] = $baseErrorMsg . 'task project is not set';
        }
        //Only check the task dates if the config option "check_task_dates" is on
        if (w2PgetConfig('check_task_dates')) {
            if ($this->task_start_date == '' || $this->task_start_date == '0000-00-00 00:00:00') {
                $this->_error['task_start_date'] = $baseErrorMsg . 'task start date is NULL';
            }
            if ($this->task_end_date == '' || $this->task_end_date == '0000-00-00 00:00:00') {
                $this->_error['task_end_date'] = $baseErrorMsg . 'task end date is NULL';
            }
            if (!isset($errorArray['task_start_date']) && !isset($errorArray['task_end_date'])) {
                $startTimestamp = strtotime($this->task_start_date);
                $endTimestamp = strtotime($this->task_end_date);

                if (60 > abs($endTimestamp - $startTimestamp)) {
                    $endTimestamp = $startTimestamp;
                }
                if ($startTimestamp > $endTimestamp) {
                    $this->_error['bad_date_selection'] = $baseErrorMsg . 'task start date is after task end date';
                }
            }
        }

        // ensure changes to checkboxes are honoured
        $this->task_milestone = (int) $this->task_milestone;
        $this->task_dynamic = (int) $this->task_dynamic;

        $this->task_percent_complete = (int) $this->task_percent_complete;

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
            $this->task_creator = $this->_AppUI->user_id;
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
                $this->_error['BadDep_DynNoDep'] = 'BadDep_DynNoDep';
                return false;
            }

            $this_dependents = $this->task_id ? explode(',', $this->dependentTasks()) : array();
            $more_dependents = array();
            // If the dependents' have parents add them to list of dependents
            foreach ($this_dependents as $dependent) {
                $dependent_task = new CTask();
                $dependent_task->overrideDatabase($this->_query);
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
                $this->_error['BadDep_CircularDep'] = 'BadDep_CircularDep';
                return false;
            }
        }

        // Has a parent
        if ($this->task_id && $this->task_parent && $this->task_id != $this->task_parent) {
            $this_children = $this->getChildren();
            $this_parent = new CTask();
            $this_parent->overrideDatabase($this->_query);
            $this_parent->load($this->task_parent);
            $parents_dependents = explode(',', $this_parent->dependentTasks());

            if (in_array($this_parent->task_id, $this_dependencies)) {
                $this->_error['BadDep_CannotDependOnParent'] = 'BadDep_CannotDependOnParent';
                return false;
            }
            // Task parent cannot be child of this task
            if (in_array($this_parent->task_id, $this_children)) {
                $this->_error['BadParent_CircularParent'] = 'BadParent_CircularParent';
                return false;
            }

            if ($this_parent->task_parent != $this_parent->task_id) {
                // ... or parent's parent, cannot be child of this task. Could go on ...
                if (in_array($this_parent->task_parent, $this_children)) {
                    $this->_error['BadParent_CircularGrandParent'] = 'BadParent_CircularGrandParent';
                    return false;
                }
                // parent's parent cannot be one of this task's dependencies
                if (in_array($this_parent->task_parent, $this_dependencies)) {
                    $this->_error['BadParent_CircularGrandParent'] = 'BadParent_CircularGrandParent';
                    return false;
                }
            } // grand parent

            if ($this_parent->task_dynamic == 1) {
                $intersect = array_intersect($this_dependencies, $parents_dependents);
                if (array_sum($intersect)) {
                    $ids = '(' . implode(',', $intersect) . ')';
                    $this->_error['BadDep_CircularDepOnParentDependent'] = 'BadDep_CircularDepOnParentDependent';
                    return false;
                }
            }
            if ($this->task_dynamic == 1) {
                // then task's children can not be dependent on parent
                $intersect = array_intersect($this_children, $parents_dependents);
                if (array_sum($intersect)) {
                    $this->_error['BadParent_ChildDepOnParent'] = 'BadParent_ChildDepOnParent';
                    return false;
                }
            }
        } // parent

        return (count($this->_error)) ? false : true;
    }

    /*
     * This should be deprecated in favor of load() on the parent
     *   w2p_Core_BaseObject once we're sure no one is using the $skipUpdate
     *   parameter any more.
     */

    public function load($oid = null, $strip = false, $skipUpdate = false)
    {
        if ($skipUpdate) {
            trigger_error("The 'skipUpdate' parameter of load() has been deprecated in v3.0 and will be removed by v4.0. Please use load() without it instead.", E_USER_NOTICE);
        }
        return parent::load($oid, $strip);
    }

    public function loadFull($notUsed = null, $taskId)
    {
        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->leftJoin('users', 'u1', 'u1.user_id = task_owner', 'outer');
        $q->leftJoin('contacts', 'ct', 'ct.contact_id = u1.user_contact', 'outer');
        $q->innerJoin('projects', 'p', 'p.project_id = task_project');
        $q->innerJoin('companies', 'co', 'co.company_id = project_company');
        $q->addWhere('task_id = ' . (int) $taskId);
        $q->addQuery('tasks.*');
        $q->addQuery('company_name, project_name, project_color_identifier');
        $q->addQuery('contact_display_name as username');                       //TODO: deprecate?
        $q->addQuery('contact_display_name as task_owner_name');
        $q->addGroup('task_id');

        $this->task_owner_name = '';

        $q->loadObject($this, true, false);
        $this->task_hours_worked += 0;
        $this->budget = $this->getBudget();
    }

    /*
     * This used to feed different parameters into load() but now we just do it.
     *
     * @deprecated
     */

    public function peek($oid = null, $strip = false)
    {
        trigger_error("peek() has been deprecated in v3.0 and will be removed by v4.0. Please use load() instead.", E_USER_NOTICE);
        return $this->load($oid, $strip);
    }

    public function updateDynamics($fromChildren = false)
    {
        //Has a parent or children, we will check if it is dynamic so that it's info is updated also
        $modified_task = new CTask();
        $modified_task->overrideDatabase($this->_query);

        if ($fromChildren) {
            $modified_task = &$this;
        } else {
            $modified_task->load($this->task_parent);
            $modified_task->htmlDecode();
        }

        $q = $this->_getQuery();
        if ($modified_task->task_dynamic == '1') {
            //Update allocated hours based on children with duration type of 'hours'
            $q->addTable('tasks');
            $q->addQuery('SUM(task_duration * task_duration_type)');
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type = 1 ');
            $q->addGroup('task_parent');
            $children_allocated_hours1 = (float) $q->loadResult();
            $q->clear();

            /*
             * Update allocated hours based on children with duration type of 'days'
             * use the daily working hours instead of the full 24 hours to calculate
             * dynamic task duration!
             */
            $q->addTable('tasks');
            $q->addQuery(' SUM(task_duration * ' . w2PgetConfig('daily_working_hours') . ')');
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type <> 1 ');
            $q->addGroup('task_parent');
            $children_allocated_hours2 = (float) $q->loadResult();
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
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_dynamic <> 1 ');
            $children_hours_worked = (float) $q->loadResult();
            $q->clear();

            //Update worked hours based on dynamic children tasks
            $q->addTable('tasks');
            $q->addQuery('SUM(task_hours_worked)');
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_dynamic = 1 ');
            $children_hours_worked += (float) $q->loadResult();
            $q->clear();

            $modified_task->task_hours_worked = $children_hours_worked;

            //Update percent complete
            //hours
            $q->addTable('tasks');
            $q->addQuery('SUM(task_percent_complete * task_duration * task_duration_type)');
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type = 1 ');
            $real_children_hours_worked = (float) $q->loadResult();
            $q->clear();

            //days
            $q->addTable('tasks');
            $q->addQuery('SUM(task_percent_complete * task_duration * ' . w2PgetConfig('daily_working_hours') . ')');
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND task_duration_type <> 1 ');
            $real_children_hours_worked += (float) $q->loadResult();
            $q->clear();

            $total_hours_allocated = (float) ($modified_task->task_duration * (($modified_task->task_duration_type > 1) ? w2PgetConfig('daily_working_hours') : 1));
            if ($total_hours_allocated > 0) {
                $modified_task->task_percent_complete = ceil($real_children_hours_worked / $total_hours_allocated);
            } else {
                $q->addTable('tasks');
                $q->addQuery('AVG(task_percent_complete)');
                $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id);
                $modified_task->task_percent_complete = $q->loadResult();
                $q->clear();
            }

            //Update start date
            $q->addTable('tasks');
            $q->addQuery('MIN(task_start_date)');
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND NOT ISNULL(task_start_date)' . ' AND task_start_date <>	\'0000-00-00 00:00:00\'');
            $d = $q->loadResult();
            $q->clear();
            if ($d) {
                $modified_task->task_start_date = $d;
                $modified_task->task_start_date = $this->_AppUI->formatTZAwareTime($modified_task->task_start_date, '%Y-%m-%d %T');
            } else {
                $modified_task->task_start_date = '0000-00-00 00:00:00';
            }

            //Update end date
            $q->addTable('tasks');
            $q->addQuery('MAX(task_end_date)');
            $q->addWhere('task_parent = ' . (int) $modified_task->task_id . ' AND task_id <> ' . $modified_task->task_id . ' AND NOT ISNULL(task_end_date)');
            $modified_task->task_end_date = $q->loadResult();
            $modified_task->task_end_date = $this->_AppUI->formatTZAwareTime($modified_task->task_end_date, '%Y-%m-%d %T');
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

    public function copy($destProject_id = 0, $destTask_id = -1)
    {
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
        $newObj->store($this->_AppUI);
        $this->copyAssignedUsers($newObj->task_id);

        return $newObj;
    }

// end of copy()

    /**
     * Import tasks from another project
     *
     * 	@param	int Project ID of the tasks come from.
     * 	@return	bool
     *
     *  @todo - this entire thing has nothing to do with projects.. it should move to the CTask class - dkc 25 Nov 2012
     *  @todo - why are we returning either an array or a boolean? You make my head hurt. - dkc 25 Nov 2012
     *
     *  @todo - we should decide if we want to include the contacts associated with each task
     *  @todo - we should decide if we want to include the files associated with each task
     *  @todo - we should decide if we want to include the links associated with each task
     *
     * Of the three - contacts, files, and links - I can see a case made for
     *   all three. Imagine you have a task which requires a particular form to
     *   be filled out (Files) but there's also documentation you need about it
     *   (Links) and once the task is underway, you need to let some people
     *   know (Contacts). - dkc 25 Nov 2012
     * */
    public function importTasks($from_project_id, $to_project_id, $project_start_date)
    {
        $errors = array();

        $old_new_task_mapping = array();
        $old_dependencies = array();
        $old_parents = array();

        $project_start_date = new w2p_Utilities_Date($project_start_date);
        $timeOffset = 0;

        $newTask = new CTask();
        $task_list = $newTask->loadAll('task_start_date', "task_project = " . $from_project_id);

        foreach($task_list as $orig_id => $orig_task) {
            /**
             * This gets the first (earliest) task start date and figures out
             *   how much we have to shift all the tasks by.
             */
            if ($orig_task == reset($task_list)) {
                $original_start_date = new w2p_Utilities_Date($orig_task['task_start_date']);
                $timeOffset = $original_start_date->dateDiff($project_start_date);
            }

            $orig_task['task_id'] = 0;
            $orig_task['task_project'] = $to_project_id;
            $orig_task['task_sequence'] = 0;

            $old_parents[$orig_id] = $orig_task['task_parent'];
            $orig_task['task_parent'] = 0;

            $tz_start_date = $this->_AppUI->formatTZAwareTime($orig_task['task_start_date'], '%Y-%m-%d %T');
            $new_start_date = new w2p_Utilities_Date($tz_start_date);
            $new_start_date->addDays($timeOffset);
            $new_start_date->next_working_day();
            $orig_task['task_start_date'] = $new_start_date->format(FMT_DATETIME_MYSQL);

            $new_start_date->addDuration($orig_task['task_duration'], $orig_task['task_duration_type']);
            $orig_task['task_end_date'] = $new_start_date->format(FMT_DATETIME_MYSQL);

            $newTask->bind($orig_task);
            $result = $newTask->store();
            if (!$result) {
                $errors = $newTask->getError();
                break;
            }

            $old_dependencies[$orig_id] = array_keys($newTask->getDependentTaskList($orig_id));
            $old_new_task_mapping[$orig_id] = $newTask->task_id;
        }

        if (count($errors)) {
            $this->_error = $errors;

            /* If there's an error, this deletes the already imported tasks. */
            foreach($old_new_task_mapping as $new_id) {
                $newTask->task_id = $new_id;
                $newTask->delete();
            }
        } else {
            $q = $this->_getQuery();

            /* This makes sure we have all the dependencies mapped out. */
            foreach($old_dependencies as $from => $to_array) {
                foreach($to_array as $to) {
                    $q->addTable('task_dependencies');
                    $q->addInsert('dependencies_req_task_id', $old_new_task_mapping[$from]);
                    $q->addInsert('dependencies_task_id',     $old_new_task_mapping[$to]);

                    $q->exec();
                    $q->clear();
                }
            }

            /* This makes sure all the parents are connected properly. */
            foreach($old_parents as $old_child => $old_parent) {
                if ($old_child == $old_parent) {
                    /** Remember, this means skip the rest of the loop. */
                    continue;
                }
                $q->addTable('tasks');
                $q->addUpdate('task_parent', $old_new_task_mapping[$old_parent]);
                $q->addWhere('task_id   = ' . $old_new_task_mapping[$old_child]);
                $q->exec();
                $q->clear();
            }

            /* This copies the task assigness to the new tasks. */
            foreach($old_new_task_mapping as $old_id => $new_id) {
                $newTask->task_id = $old_id;
                $newTask->copyAssignedUsers($new_id);
            }
        }

        return $errors;
    }

    // end of importTasks

    public function copyAssignedUsers($destTask_id)
    {

        $q = $this->_getQuery();
        $q->addQuery('user_id, user_type, task_id, perc_assignment, user_task_priority');
        $q->addTable('user_tasks', 'ut');
        $q->addWhere('ut.task_id = ' . $this->task_id);
        $user_tasks = $q->loadList();
        $q->clear();
        foreach ($user_tasks as $user_task) {
            $q->addReplace('user_id', $user_task['user_id']);
            $q->addReplace('user_type', $user_task['user_type']);
            $q->addReplace('task_id', $destTask_id);
            $q->addReplace('perc_assignment', $user_task['perc_assignment']);
            $q->addReplace('user_task_priority', $user_task['user_task_priority']);
            $q->addTable('user_tasks', 'ut');
            $q->exec();
        }
    }

    public function deepCopy($destProject_id = 0, $destTask_id = 0)
    {
        $children = $this->getChildren();
        $newObj = $this->copy($destProject_id, $destTask_id);
        $new_id = $newObj->task_id;
        if (!empty($children)) {
            $tempTask = new CTask();
            $tempTask->overrideDatabase($this->_query);
            foreach ($children as $child) {
                $tempTask->load($child);
                $tempTask->htmlDecode($child);
                $newChild = $tempTask->deepCopy($destProject_id, $new_id);
                $newChild->store();
            }
        }

        return $newObj;
    }

    public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false)
    {
        parent::bind($hash, $prefix, $checkSlashes, $bindAll);

        if ($this->task_start_date != '' && $this->task_start_date != '0000-00-00 00:00:00') {
            $this->task_start_date = $this->_AppUI->convertToSystemTZ($this->task_start_date);
        }
        if ($this->task_end_date != '' && $this->task_end_date != '0000-00-00 00:00:00') {
            $this->task_end_date = $this->_AppUI->convertToSystemTZ($this->task_end_date);
        }

        return true;
    }

    public function getBudget()
    {
        $q = $this->_getQuery();
        $q->addQuery('budget_category, budget_amount');
        $q->addTable('budgets_assigned');
        $q->addWhere('budget_task =' . (int) $this->task_id);

        return $q->loadHashList('budget_category');
    }

    public function storeBudget(array $budgets)
    {
        $q = $this->_getQuery();
        $q->setDelete('budgets_assigned');
        $q->addWhere('budget_task =' . (int) $this->task_id);
        $q->exec();

        $q->clear();
        foreach ($budgets as $category => $amount) {
            $q->addTable('budgets_assigned');
            $q->addInsert('budget_task', $this->task_id);
            $q->addInsert('budget_category', $category);
            $q->addInsert('budget_amount', $amount);
            $q->exec();
            $q->clear();
        }

        return true;
    }

    /**
     * @todo Parent store could be partially used
     */
    public function store()
    {
        $stored = false;

        if ($this->task_start_date == '') {
            $this->task_start_date = '0000-00-00 00:00:00';
        }
        if ($this->task_end_date == '') {
            $this->task_end_date = '0000-00-00 00:00:00';
        }

        if ($this->{$this->_tbl_key} && $this->canEdit()) {

            // Load and globalize the old, not yet updated task object
            // e.g. we need some info later to calculate the shifting time for depending tasks
            // see function update_dep_dates
            global $oTsk;
            $oTsk = new CTask();
            $oTsk->overrideDatabase($this->_query);
            $oTsk->load($this->task_id);

            if (($msg = parent::store())) {
                $this->_error['store'] = $msg;
                return $msg;
            }

            // if task_status changed, then update subtasks
            if ($this->task_status != $oTsk->task_status) {
                $this->updateSubTasksStatus($this->task_status);
            }
            if ($this->task_dynamic == 1) {
                $this->updateDynamics(true);
            }
            $stored = parent::store();
        }

        if (0 == $this->{$this->_tbl_key} && $this->canCreate()) {
            $stored = parent::store();
        }

        return $stored;
    }

    protected function hook_preCreate() {
        $q = $this->_getQuery();
        $this->task_created = $q->dbfnNowWithTZ();

        parent::hook_preCreate();
    }
    protected function hook_postCreate()
    {
        if ($this->task_parent) {
            // importing tasks do not update dynamics
            $this->importing_tasks = true;
        }
        
        parent::hook_postCreate();
    }
    protected function hook_preStore()
    {
        $this->importing_tasks = false;

        $this->w2PTrimAll();

        $q = $this->_getQuery();
        $this->task_updated = $q->dbfnNowWithTZ();
        $this->task_target_budget = filterCurrency($this->task_target_budget);
        $this->task_owner = ($this->task_owner) ? $this->task_owner : $this->_AppUI->user_id;
        $this->task_contacts = is_array($this->task_contacts) ? $this->task_contacts : explode(',', $this->task_contacts);

        parent::hook_preStore();
    }

    protected function hook_postStore()
    {
         // TODO $oTsk is a global set by store() and is the task before update.
         // Using it here as a global is probably a bad idea, but the only way until the old task is stored somewhere
         // else than a global variable...
        global $oTsk;
        $q = $this->_query;
        /*
         * TODO: I don't like that we have to run an update immediately after the store
         *   but I don't have a better solution at the moment.
         *                                      ~ caseydk 2012 Aug 04
         */
        if (!$this->task_parent) {
            $q->addTable('tasks');
            $q->addUpdate('task_parent', $this->task_id);
            $q->addUpdate('task_updated', "'" . $q->dbfnNowWithTZ() . "'", false, true);
            $q->addWhere('task_id = ' . (int) $this->task_id);
            $q->exec();
            $q->clear();
        }
        $last_task_data = $this->getLastTaskData($this->task_project);
        
        CProject::updateTaskCache(
            $this->task_project,
            $last_task_data['task_id'], 
            $last_task_data['last_date'],
            $this->getTaskCount($this->task_project)
        );

        // update dependencies
        if (!empty($this->task_id)) {
            $this->updateDependencies($this->getDependencies(), $this->task_parent);
        }
        $this->pushDependencies($this->task_id, $this->task_end_date);

        //split out related departments and store them seperatly.
        $q->setDelete('task_departments');
        $q->addWhere('task_id=' . (int) $this->task_id);
        $q->exec();
        $q->clear();
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
        $q->addWhere('task_id=' . (int) $this->task_id);

        $q->exec();
        $q->clear();
        if ($this->task_contacts && is_array($this->task_contacts)) {
            foreach ($this->task_contacts as $contact) {
                if ($contact) {
                    $q->addTable('task_contacts');
                    $q->addInsert('task_id', $this->task_id);
                    $q->addInsert('contact_id', $contact);
                    $q->exec();
                    $q->clear();
                }
            }
        }

        // if is child update parent task
        if ($this->task_parent != $this->task_id) {
            if (!$this->importing_tasks) {
                $this->updateDynamics();
            }

            $pTask = new CTask();
            $pTask->overrideDatabase($this->_query);
            $pTask->load($this->task_parent);
            $pTask->updateDynamics();

            if ($oTsk->task_parent != $this->task_parent) {
                $old_parent = new CTask();
                $old_parent->overrideDatabase($this->_query);
                $old_parent->load($oTsk->task_parent);
                $old_parent->updateDynamics();
            }
        }

        parent::hook_postStore();
    }

    protected function hook_postUpdate()
    {
        $q = $this->_query;
        /*
         * TODO: I don't like that we have to run an update immediately after the store
         *   but I don't have a better solution at the moment.
         *                                      ~ caseydk 2012 Aug 04
         */
        $q->addTable('tasks');
        $q->addUpdate('task_sequence', "task_sequence+1", false, true);
        $q->addUpdate('task_updated', "'" . $q->dbfnNowWithTZ() . "'", false, true);
        $q->addWhere('task_id = ' . (int) $this->task_id);
        $q->exec();

        parent::hook_postUpdate();
    }

    /**
     *
     * @param w2p_Core_CAppUI $AppUI
     * @param CProject $projectId
     *
     * The point of this function is to create/update a task to represent a
     *   subproject.
     *
     */
    public static function storeTokenTask(w2p_Core_CAppUI $AppUI, $project_id)
    {
        $subProject = new CProject();
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $subProject->load($project_id);

        if ($subProject->project_parent > 0 && ($subProject->project_id != $subProject->project_parent)) {
            $q = new w2p_Database_Query();
            $q->addTable('tasks');
            $q->addQuery('MIN(task_start_date) AS min_task_start_date');
            $q->addQuery('MAX(task_end_date) AS max_task_end_date');
            $q->addWhere('task_project = ' . $subProject->project_id);
            $q->addWhere('task_status <> -1');
            $projectDates = $q->loadList();

            $q->clear();
            $q->addTable('tasks');
            $q->addQuery('task_id');
            $q->addWhere('task_represents_project = ' . $subProject->project_id);
            $task_id = (int) $q->loadResult();

            $task = new CTask();
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
            if ($task_id) {
                $task->load($task_id);
            } else {
                $task->task_description = $task->task_name;
                $task->task_priority = $subProject->project_priority;
                $task->task_project = $subProject->project_parent;
                $task->task_represents_project = $subProject->project_id;
                $task->task_owner = $AppUI->user_id;
            }
            $task->task_name = $AppUI->_('Subproject') . ': ' . $subProject->project_name;
            $task->task_duration_type = 1;
            $task->task_duration = $subProject->project_worked_hours;
            $task->task_start_date = $projectDates[0]['min_task_start_date'];
            $task->task_end_date = $projectDates[0]['max_task_end_date'];
            $task->task_percent_complete = $subProject->project_percent_complete;
            $task->store();
            //TODO: we should do something with this store result?
        }
    }

    /**
     * @todo Parent store could be partially used
     * @todo Can't delete a task with children
     */
    public function delete()
    {
        $result = false;
        $this->clearErrors();

        if ($this->canDelete()) {
            //load it before deleting it because we need info on it to update the parents later on
            $this->load($this->task_id);

            // delete children
            $childrenlist = $this->getChildren();
            foreach ($childrenlist as $child) {
                $task = new CTask();
                $task->overrideDatabase($this->_query);
                $task->task_id = $child;
                $task->delete($this->_AppUI);
            }

            $taskList = $childrenlist + array($this->task_id);
            $implodedTaskList = implode(',', $taskList);

            $q = $this->_getQuery();
            // delete linked user tasks
            $q->setDelete('user_tasks');
            $q->addWhere('task_id IN (' . $implodedTaskList . ')');
            if (!($q->exec())) {
                $this->_error['delete-user-assignments'] = db_error();
                return false;
            }
            $q->clear();

            // delete affiliated task_dependencies
            $q->setDelete('task_dependencies');
            $q->addWhere('dependencies_task_id IN (' . $implodedTaskList . ') OR
                dependencies_req_task_id IN (' . $implodedTaskList . ')');
            if (!$q->exec()) {
                $this->_error['delete-dependencies'] = db_error();
                return false;
            }
            $q->clear();

            // delete affiliated contacts
            $q->setDelete('task_contacts');
            $q->addWhere('task_id = ' . $this->task_id);
            if (!$q->exec()) {
                $this->_error['delete-contacts'] = db_error();
                return false;
            }

            $result = parent::delete();

            if ($this->task_parent != $this->task_id) {
                $this->updateDynamics();
            }

            $last_task_data = $this->getLastTaskData($this->task_project);
            CProject::updateTaskCache(
                    $this->task_project, $last_task_data['task_id'], $last_task_data['last_date'], $this->getTaskCount($this->task_project));
        }

        return $result;
    }

    /** Retrieve tasks with latest task_end_dates within given project
     * @param int Project_id
     * @param int SQL-limit to limit the number of returned tasks
     * @return array List of criticalTasks
     */
    public function getLastTaskData($project_id)
    {

        $q = $this->_getQuery();
        $q->clear();
        $q->addQuery('task_id, MAX(task_end_date) as last_date');
        $q->addTable('tasks');
        $q->addWhere('task_dynamic <> 1');
        $q->addWhere('task_project = ' . (int) $project_id);
        $q->addGroup('task_project');

        return $q->loadHash();
    }

    public function updateDependencies($cslist, $parent_id = 0)
    {

        $q = $this->_getQuery();
        // delete all current entries
        $q->setDelete('task_dependencies');
        $q->addWhere('dependencies_task_id=' . (int) $this->task_id);
        $q->exec();
        $q->clear();

        // process dependencies
        $tarr = explode(',', $cslist);
        $tarr = array_flip($tarr);
        unset($tarr[$parent_id]);

        foreach ($tarr as $task_id => $notUsed) {
            if ((int) $task_id) {
                $q->addTable('task_dependencies');
                $q->addReplace('dependencies_task_id', $this->task_id);
                $q->addReplace('dependencies_req_task_id', $task_id);
                $q->exec();
                $q->clear();
            }
        }
    }

    /*
     * This function is run immediately after a Task is stored. It uses that Task's
     *    end date and checks for dependent tasks beginning before that date.
     *
     * If there are any dependencies that match those criteria, it updates those
     *    and recurses.
     * If not, it returns.
     *
     * @todo TODO: This entire function needs to be timezone aware.. current it isn't.
     */
    public function pushDependencies($task_id, $lastEndDate)
    {
        $task_end_int = strtotime($lastEndDate);

        $dependent_tasks = $this->getDependentTaskList($task_id);

        foreach($dependent_tasks as $_task_id => $_task_data) {
            $task_start_int = strtotime($_task_data['task_start_date']);

            if ($task_start_int >= $task_end_int) {
                /**
                 * Remember, this continue just means 'skip this iteration and
                 *   go to the next one.' In this case, we're skipping the
                 *   iteration because either the dependent task's start date is
                 *   already at or after the end date we have.
                 */
                continue;
            }

            $nsd = new w2p_Utilities_Date($lastEndDate);
            // Because we prefer the beginning of the next day as opposed to the
            //   end of the current for a task_start_date
            $nsd = $nsd->next_working_day();

            $multiplier = ('24' == $_task_data['task_duration_type']) ? 3 : 1;
            $d = $_task_data['task_duration'] * $multiplier;

            $ned = new w2p_Utilities_Date();
            $ned->copy($nsd);
            $ned->addDuration($d);

            // Because we prefer the end of the previous as opposed to the
            //   beginning of the current for a task_end_date
            $ned = $ned->prev_working_day();

            $new_start_date = $nsd->format(FMT_DATETIME_MYSQL);
            $new_start_date = $this->_AppUI->convertToSystemTZ($new_start_date);
            $new_end_date = $ned->format(FMT_DATETIME_MYSQL);
            $new_end_date = $this->_AppUI->convertToSystemTZ($new_end_date);

            $q = $this->_getQuery();
            $q->addTable('tasks');
            $q->addUpdate('task_start_date', $new_start_date);
            $q->addUpdate('task_end_date', $new_end_date);
            $q->addUpdate('task_updated', "'" . $q->dbfnNowWithTZ() . "'", false, true);
            $q->addWhere('task_dynamic > 1 AND task_id = ' . (int) $_task_id);
            $q->exec();
            
            $this->pushDependencies($_task_id, $new_end_date);
        }
    }

    /**
     * 		  Retrieve the tasks dependencies
     *
     * 		  @author		 handco		   <handco@users.sourceforge.net>
     * 		  @return		 string		   comma delimited list of tasks id's
     * */
    public function getDependencies()
    {
        // Call the static method for this object
        $result = $this->staticGetDependencies($this->task_id);
        return $result;
    }

// end of getDependencies ()

    /**
     * 		  Retrieve the tasks dependencies
     *
     * 		  @author		 handco		   <handco@users.sourceforge.net>
     * 		  @param		integer		   ID of the task we want dependencies
     * 		  @return		 string		   comma delimited list of tasks id's
     * */
    public function staticGetDependencies($taskId)
    {

        $q = $this->_getQuery();
        if (empty($taskId)) {
            return '';
        }
        $q->clear();
        $q->addTable('task_dependencies', 'td');
        $q->addQuery('dependencies_req_task_id');
        $q->addWhere('td.dependencies_task_id = ' . (int) $taskId);
        $list = $q->loadColumn();
        $q->clear();
        $result = $list ? implode(',', $list) : '';

        return $result;
    }

// end of staticGetDependencies ()

    public function notifyOwner()
    {
        $mail = new w2p_Utilities_Mail();
        $mail->Subject($projname . '::' . $this->task_name . ' ' . $this->_AppUI->_($this->_action, UI_OUTPUT_RAW), $this->_locale_char_set);

        // c = creator
        // a = assignee
        // o = owner
        $q = $this->_getQuery();
        $q->addTable('tasks', 't');
        $q->leftJoin('user_tasks', 'u', 'u.task_id = t.task_id');

        $q->leftJoin('users', 'o', 'o.user_id = t.task_owner');
        $q->leftJoin('contacts', 'oc', 'oc.contact_id = o.user_contact');
        $q->addQuery('oc.contact_id as owner_contact_id');

        $q->leftJoin('users', 'c', 'c.user_id = t.task_creator');
        $q->leftJoin('contacts', 'cc', 'cc.contact_id = c.user_contact');
        $q->addQuery('cc.contact_id as creator_contact_id');

        $q->leftJoin('users', 'a', 'a.user_id = u.user_id');
        $q->leftJoin('contacts', 'ac', 'ac.contact_id = a.user_contact');
        $q->addQuery('ac.contact_id as assignee_contact_id');

        $q->addQuery('t.task_id, oc.contact_email as owner_email');
        $q->addWhere(' t.task_id = ' . (int) $this->task_id);
        $users = $q->loadList();
        $q->clear();

        if (count($users)) {
            $emailManager = new w2p_Output_EmailManager($this->_AppUI);
            $body = $emailManager->getTaskNotifyOwner($this);
            $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
        }

        if ($mail->ValidEmail($users[0]['owner_email'])) {
            $mail->To($users[0]['owner_email'], true);
            $mail->Send();
        }

        return '';
    }

//TODO: additional comment will be included in email body
//TODO: should we resolve $projname ?
    public function notify($comment = '')
    {
        $mail = new w2p_Utilities_Mail();

		$project = new CProject();
		$projname = $project->load($this->task_project)->project_name;

        $mail->Subject($projname . '::' . $this->task_name . ' ' . $this->_AppUI->_($this->_action, UI_OUTPUT_RAW), $this->_locale_char_set);

        // c = creator
        // a = assignee
        // o = owner
        $q = $this->_getQuery();
        $q->addTable('tasks', 't');
        $q->leftJoin('user_tasks', 'u', 'u.task_id = t.task_id');

        $q->leftJoin('users', 'o', 'o.user_id = t.task_owner');
        $q->leftJoin('contacts', 'oc', 'oc.contact_id = o.user_contact');
        $q->addQuery('oc.contact_id as owner_contact_id');

        $q->leftJoin('users', 'c', 'c.user_id = t.task_creator');
        $q->leftJoin('contacts', 'cc', 'cc.contact_id = c.user_contact');
        $q->addQuery('cc.contact_id as creator_contact_id');

        $q->leftJoin('users', 'a', 'a.user_id = u.user_id');
        $q->leftJoin('contacts', 'ac', 'ac.contact_id = a.user_contact');
        $q->addQuery('ac.contact_id as assignee_contact_id');

        $q->addQuery('t.task_id, cc.contact_email as creator_email, cc.contact_display_name as creator_name,
            cc.contact_first_name as creator_first_name, cc.contact_last_name as creator_last_name,
            oc.contact_email as owner_email, oc.contact_display_name as owner_name,
            oc.contact_first_name as owner_first_name, oc.contact_last_name as owner_last_name,
            a.user_id as assignee_id, ac.contact_email as assignee_email');
        $q->addWhere(' t.task_id = ' . (int) $this->task_id);
        $users = $q->loadList();
        $q->clear();

        $mail_owner = $this->_AppUI->getPref('MAILALL');

        foreach ($users as $row) {
            if ($mail_owner || $row['assignee_id'] != $this->_AppUI->user_id) {
                if ($mail->ValidEmail($row['assignee_email'])) {

                    $emailManager = new w2p_Output_EmailManager($this->_AppUI);
                    $body = $emailManager->getTaskNotify($this, $row, $projname);

                    $mail->Body($body, (isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : ''));

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
    public function email_log(&$log, $assignees, $task_contacts, $project_contacts, $others, $extras, $specific_user = 0)
    {
        $mail_recipients = array();
        $q = $this->_getQuery();
        if ((int) $this->task_id > 0 && (int) $this->task_project > 0) {
            if (isset($assignees) && $assignees == 'on') {
                $q->addTable('user_tasks', 'ut');
                $q->leftJoin('users', 'ua', 'ua.user_id = ut.user_id');
                $q->leftJoin('contacts', 'c', 'c.contact_id = ua.user_contact');
                $q->addQuery('c.contact_email, c.contact_display_name as contact_name');
                $q->addWhere('ut.task_id = ' . $this->task_id);
                if (!$this->_AppUI->getPref('MAILALL')) {
                    $q->addWhere('ua.user_id <>' . (int) $this->_AppUI->user_id);
                }
                $assigneeList = $q->loadList();
                $q->clear();

                foreach ($assigneeList as $myContact) {
                    $mail_recipients[$myContact['contact_email']] = $myContact['contact_name'];
                }
            }
            if (isset($task_contacts) && $task_contacts == 'on') {
                $q->addTable('task_contacts', 'tc');
                $q->leftJoin('contacts', 'c', 'c.contact_id = tc.contact_id');
                $q->addQuery('c.contact_email, c.contact_display_name as contact_name');
                $q->addWhere('tc.task_id = ' . $this->task_id);
                $contactList = $q->loadList();
                $q->clear();

                foreach ($contactList as $myContact) {
                    $mail_recipients[$myContact['contact_email']] = $myContact['contact_name'];
                }
            }
            if (isset($project_contacts) && $project_contacts == 'on') {
                $q->addTable('project_contacts', 'pc');
                $q->leftJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
                $q->addQuery('c.contact_email, c.contact_display_name as contact_name');
                $q->addWhere('pc.project_id = ' . $this->task_project);
                $projectContactList = $q->loadList();
                $q->clear();

                foreach ($projectContactList as $myContact) {
                    $mail_recipients[$myContact['contact_email']] = $myContact['contact_name'];
                }
            }
            if (isset($others)) {
                $others = trim($others, " \r\n\t,"); // get rid of empty elements.
                if (strlen($others) > 0) {
                    $q->addTable('contacts', 'c');
                    $q->addQuery('c.contact_email, c.contact_display_name as contact_name');
                    $q->addWhere('c.contact_id IN (' . $others . ')');
                    $otherContacts = $q->loadList();
                    $q->clear();

                    foreach ($otherContacts as $myContact) {
                        $mail_recipients[$myContact['contact_email']] = $myContact['contact_name'];
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
            // If this should be sent to a specific user, add their contact details here
            if (isset($specific_user) && $specific_user) {
                $q->addTable('users', 'u');
                $q->leftJoin('contacts', 'c', 'c.contact_id = u.user_contact');
                $q->addQuery('c.contact_email, c.contact_display_name as contact_name');
                $q->addWhere('u.user_id = ' . $specific_user);
                $su_list = $q->loadList();

                foreach ($su_list as $su_contact) {
                    $mail_recipients[$su_contact['contact_email']] = $su_contact['contact_name'];
                }
            }

            if (count($mail_recipients) == 0) {
                return false;
            }

            // Build the email and send it out.
            $char_set = isset($this->_locale_char_set) ? $this->_locale_char_set : '';
            $mail = new w2p_Utilities_Mail();
            // Grab the subject from user preferences
            $prefix = $this->_AppUI->getPref('TASKLOGSUBJ');
            $mail->Subject($prefix . ' ' . $log->task_log_name, $char_set);

            $emailManager = new w2p_Output_EmailManager($this->_AppUI);
            $body = $emailManager->getTaskEmailLog($this, $log);
            $mail->Body($body, $char_set);

            $recipient_list = '';
            $toList = array();

            foreach ($mail_recipients as $email => $name) {
                if ($mail->ValidEmail($email)) {
                    $toList[$email] = $email;
                    $recipient_list .= $email . ' (' . $name . ")\n";
                } else {
                    $recipient_list .= "Invalid email address '$email' for '$name' not sent \n";
                }
            }

            $sendToList = array_keys($mail_recipients);
            $mail->SendSeparatelyTo($sendToList);

            // Now update the log
            $save_email = $this->_AppUI->getPref('TASKLOGNOTE');
            if ($save_email) {
//TODO: This is where #38 - http://bugs.web2project.net/view.php?id=38 - should be applied if a change is necessary.
//TODO: This datetime should be added/displayed in UTC/GMT.
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
     *
     * WARNING: This is actually called staticly so $this is not available.
     */
    public function getTasksForPeriod($start_date, $end_date, $company_id = 0, $user_id = null)
    {
        global $AppUI;
        $q = new w2p_Database_Query();

        // convert to default db time stamp
        $db_start = $start_date->format(FMT_DATETIME_MYSQL);
        $db_end = $end_date->format(FMT_DATETIME_MYSQL);

        // Allow for possible passing of user_id 0 to stop user filtering
        if (!isset($user_id)) {
            $user_id = $AppUI->user_id;
        }

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
        $q->innerJoin('companies', 'companies', 'projects.project_company = companies.company_id');
        $q->leftJoin('project_departments', '', 'projects.project_id = project_departments.project_id');
        $q->leftJoin('departments', '', 'departments.dept_id = project_departments.department_id');

        $q->addQuery('DISTINCT t.task_id, t.task_name, t.task_start_date, t.task_end_date, t.task_duration' . ', t.task_duration_type, projects.project_color_identifier AS color, projects.project_name, t.task_milestone, task_description, task_type, company_name, task_access, task_owner');
        $q->addWhere('task_status > -1' . ' AND (task_start_date <= \'' . $db_end . '\' AND (task_end_date >= \'' . $db_start . '\' OR task_end_date = \'0000-00-00 00:00:00\' OR task_end_date = NULL))');
        $q->addWhere('project_active = 1');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('project_status <> ' . $template_status);
        }
        if ($user_id) {
            $q->addWhere('ut.user_id = ' . (int) $user_id);
        }

        if ($company_id) {
            $q->addWhere('projects.project_company = ' . (int) $company_id);
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
        $tasks = $q->loadList(-1, 'task_id');

        // check tasks access
        $result = array();
        foreach ($tasks as $key => $row) {
            $obj->load($row['task_id']);
            $canAccess = $obj->canAccess();
            if (!$canAccess) {
                continue;
            }
            $result[$key] = $row;
        }
        // execute and return
        return $result;
    }

    public function canAccess($user_id = 0)
    {
        $this->load($this->task_id);
        $user_id = ($user_id) ? $user_id : $this->_AppUI->user_id;
        // Let's see if this user has admin privileges
        if (canView('admin')) {
            return true;
        }

        switch ($this->task_access) {
            case self::ACCESS_PUBLIC:
                $retval = true;
                break;
            case self::ACCESS_PROTECTED:
                $q = $this->_getQuery();
                $q->addTable('users');
                $q->addQuery('user_company');
                $q->addWhere('user_id=' . (int) $user_id . ' OR user_id=' . (int) $this->task_owner);
                $user_owner_companies = $q->loadColumn();
                $q->clear();
                $company_match = true;
                foreach ($user_owner_companies as $current_company) {
                    $company_match = $company_match && ((!(isset($last_company))) || $last_company == $current_company);
                    $last_company = $current_company;
                }

            case self::ACCESS_PARTICIPANT:
                $company_match = ((isset($company_match)) ? $company_match : true);
                $q = $this->_getQuery();
                $q->addTable('user_tasks');
                $q->addQuery('COUNT(task_id)');
                $q->addWhere('user_id=' . (int) $user_id . ' AND task_id=' . (int) $this->task_id);
                $count = $q->loadResult();
                $q->clear();
                $retval = (($company_match && $count > 0) || $this->task_owner == $user_id);
                break;
            case self::ACCESS_PRIVATE:
                $retval = ($this->task_owner == $user_id);
                break;
            default:
                $retval = false;
                break;
        }

        return $retval;
    }

    /**
     * 		 retrieve tasks are dependent of another.
     * 		 @param	 integer		 ID of the master task
     * 		 @param	 boolean		 true if is a dep call (recurse call)
     * 		 @param	 boolean		 false for no recursion (needed for calc_end_date)
     * */
    public function dependentTasks($taskId = false, $isDep = false, $recurse = true)
    {

        $q = $this->_getQuery();
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
        $q->clear();
        $q->addTable('task_dependencies', 'td');
        $q->innerJoin('tasks', 't', 'td.dependencies_task_id = t.task_id');
        $q->addQuery('dependencies_task_id');
        $q->addWhere('td.dependencies_req_task_id = ' . (int) $taskId);
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
    }

// end of dependentTasks()

    /**
     * @deprecated since version 3.0
     */
    public function shiftDependentTasks()
    {
        trigger_error("The CTask->shiftDependentTasks method has been deprecated in v3.0 and will be removed in v4.0. Please use CTask->pushDependencies instead", E_USER_NOTICE );

        $this->pushDependencies($this->task_id, $this->task_end_date);
    }

    /**
     * @deprecated since version 3.0
     */
    public function update_dep_dates($task_id)
    {

        $newTask = new CTask();
        $newTask->overrideDatabase($this->_query);
        $newTask->load($task_id);

        trigger_error("The CTask->update_dep_dates method has been deprecated in v3.0 and will be removed in v4.0. Please use CTask->pushDependencies instead", E_USER_NOTICE );

        $this->pushDependencies($task_id, $newTask->task_end_date);
    }

    /*
     * * Time related calculations have been moved to /classes/date.class.php
     * * some have been replaced with more _robust_ functions
     * *
     * * Affected functions:
     * * prev_working_day()
     * * next_working_day()
     * * calc_task_end_date()	renamed to addDuration()
     * * calc_end_date()	renamed to calcDuration()
     * *
     * * @date	20050525
     * * @responsible gregorerhardt
     * * @purpose	reusability, consistence
     */

    /*

      Get the last end date of all of this task's dependencies

      @param Task object
      returns FMT_DATETIME_MYSQL date

     */

    public function get_deps_max_end_date($taskObj)
    {

        $deps = $taskObj->getDependencies();
        $obj = new CTask();
        $obj->overrideDatabase($this->_query);

        $last_end_date = false;
        $q = $this->_getQuery();
        // Don't respect end dates of excluded tasks
        if (self::$tracked_dynamics && !empty($deps)) {
            $track_these = implode(',', self::$tracked_dynamics);
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
            $q->addWhere('project_id = ' . (int) $id);
            $last_end_date = $q->loadResult();
            $q->clear();
        }

        return $last_end_date;
    }

    /**
     * Function that returns the amount of hours this
     * task consumes per user each day
     */
    public function getTaskDurationPerDay($use_percent_assigned = false)
    {
        $duration = $this->task_duration * ($this->task_duration_type == 24 ? w2PgetConfig('daily_working_hours') : $this->task_duration_type);
        $task_start_date = new w2p_Utilities_Date($this->task_start_date);
        $task_finish_date = new w2p_Utilities_Date($this->task_end_date);
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
     * Function that returns the amount of hours this task consumes per user each week
     *
     * @todo wtf - dkc 25 Nov 2012
     */
    public function getTaskDurationPerWeek($use_percent_assigned = false)
    {
        $duration = $this->task_duration * ($this->task_duration_type == 24 ? w2PgetConfig('daily_working_hours') : $this->task_duration_type);
        $task_start_date = new w2p_Utilities_Date($this->task_start_date);
        $task_finish_date = new w2p_Utilities_Date($this->task_end_date);
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
    public function removeAssigned($user_id)
    {
        $q = $this->_getQuery();
        $q->setDelete('user_tasks');
        $q->addWhere('task_id = ' . (int) $this->task_id . ' AND user_id = ' . (int) $user_id);
        $q->exec();
    }

    /*
     * using user allocation percentage ($perc_assign)
     *
     * @return returns the Names of the over-assigned users (if any), otherwise false
     *
     * @todo - a given function/method should return one data type consistently - dkc 25 Nov 2012
     */
    public function updateAssigned($cslist, $perc_assign, $del = true, $rmUsers = false)
    {
        $q = $this->_getQuery();
        // process assignees
        $tarr = explode(',', $cslist);

        // delete all current entries from $cslist
        if ($del == true && $rmUsers == true) {
            foreach ($tarr as $user_id) {
                $user_id = (int) $user_id;
                if (!empty($user_id)) {
                    $this->removeAssigned($user_id);
                }
            }
            return false;
        } elseif ($del == true) { // delete all users assigned to this task (to properly update)
            $q->setDelete('user_tasks');
            $q->addWhere('task_id = ' . (int) $this->task_id);
            $q->exec();
            $q->clear();
        }

        // get Allocation info in order to check if overAssignment occurs
        $alloc = $this->getAllocation('user_id');
        $overAssignment = false;
        foreach ($tarr as $user_id) {
            if ((int) $user_id) {
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

    public function getAssignedUsers($taskId)
    {

        $q = $this->_getQuery();
        $q->addTable('users', 'u');
        $q->innerJoin('user_tasks', 'ut', 'ut.user_id = u.user_id');
        $q->leftJoin('contacts', 'co', ' co.contact_id = u.user_contact');
        $q->addQuery('u.*, ut.perc_assignment, ut.user_task_priority, contact_display_name,
            co.contact_last_name, co.contact_first_name, contact_display_name as contact_name');
        $q->addQuery('co.contact_email AS user_email, co.contact_phone AS user_phone');
        $q->addWhere('ut.task_id = ' . (int) $taskId);

        return $q->loadHashList('user_id');
    }

    /*
     * This looks quite similar to getDependentTaskList below but this gets a
     *   list of the dependencies for $taskId (aka tasks leading into $taskId).
     */

    public function getDependencyList($taskId)
    {
        $q = $this->_getQuery();
        $q->addQuery('td.dependencies_req_task_id, t.task_name, t.task_percent_complete');
        $q->addQuery('t.task_id, t.task_start_date, t.task_end_date');
        $q->addTable('tasks', 't');
        $q->addTable('task_dependencies', 'td');
        $q->addWhere('td.dependencies_req_task_id = t.task_id');
        $q->addWhere('td.dependencies_task_id = ' . (int) $taskId);

        return $q->loadHashList('dependencies_req_task_id');
    }

    /*
     * This looks quite similar to getDependencyList above but this gets a list
     *   of tasks that are dependent on $taskId (aka coming after $taskId).
     */

    public function getDependentTaskList($taskId)
    {
        $q = $this->_getQuery();
        $q->addQuery('td.dependencies_task_id, t.task_name, t.task_percent_complete');
        $q->addQuery('t.task_id, t.task_start_date, t.task_end_date');
        $q->addQuery('task_start_date, task_end_date, task_dynamic');
        $q->addQuery('task_duration, task_duration_type');
        $q->addTable('tasks', 't');
        $q->addTable('task_dependencies', 'td');
        $q->addWhere('td.dependencies_task_id = t.task_id');
        $q->addWhere('td.dependencies_req_task_id = ' . $taskId);

        return $q->loadHashList('dependencies_task_id');
    }

    public function getTaskDepartments($notUsed = null, $taskId)
    {
        if ($this->_AppUI->isActiveModule('departments')) {
            $q = $this->_getQuery();
            $q->addTable('departments', 'd');
            $q->addTable('task_departments', 't');
            $q->addWhere('t.department_id = d.dept_id');
            $q->addWhere('t.task_id = ' . (int) $taskId);
            $q->addQuery('dept_id, dept_name, dept_phone');

            $department = new CDepartment;
            $department->overrideDatabase($this->_query);
            $department->setAllowedSQL($this->_AppUI->user_id, $q);

            return $q->loadHashList('dept_id');
        }
    }

    public function getContacts($notUsed = null, $task_id)
    {
        if (canView('contacts')) {
            $q = $this->_getQuery();
            $q->addTable('contacts', 'c');
            $q->addQuery('c.*, dept_id');
            $q->addQuery('contact_display_name as contact_name');

            $q->leftJoin('departments', 'd', 'dept_id = contact_department');
            $q->addQuery('dept_name');

            $q->addJoin('task_contacts', 'tc', 'tc.contact_id = c.contact_id', 'inner');
            $q->addWhere('tc.task_id = ' . (int) $task_id);

            $q->addWhere('(contact_owner = ' . (int) $this->_AppUI->user_id . ' OR contact_private = 0)');

            $department = new CDepartment;
            $department->overrideDatabase($this->_query);
            $department->setAllowedSQL($this->_AppUI->user_id, $q);

            return $q->loadHashList('contact_id');
        }
    }

    public function getTaskContacts($notUsed = null, $task_id)
    {
        return $this->getContacts($this->_AppUI, $task_id);
    }

    /**
     * 	Calculate the extent of utilization of user assignments
     * 	@param string hash	 a hash for the returned hashList
     * 	@param array users	 an array of user_ids calculating their assignment capacity
     * 	@return array		 returns hashList of extent of utilization for assignment of the users
     */
    public function getAllocation($hash = null, $users = null, $get_user_list = false)
    {
        /*
         * TODO: The core of this function has been simplified to always return 100%
         * free capacity available.  The allocation checking (aka resource
         * management) is a complex subject which is currently not even close to be
         * handled properly.
         */

        if (!w2PgetConfig('check_overallocation', false)) {
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
            $q = $this->_getQuery();
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
            $q->addWhere("up.pref_name = 'TASKASSIGNMAX'");
            $q->addQuery('u.user_id, CONCAT(CONCAT_WS(\' [\', contact_display_name, IF(IFNULL((IFNULL(up.pref_value, ' . $scm . ') - SUM(ut.perc_assignment)), up.pref_value) > 0, IFNULL((IFNULL(up.pref_value, ' . $scm . ') - SUM(ut.perc_assignment)), up.pref_value), 0)), \'%]\') AS userFC, IFNULL(SUM(ut.perc_assignment), 0) AS charge');
            $q->addQuery('u.user_username, IFNULL(up.pref_value,' . $scm . ') AS chargeMax');
            $q->addQuery('IFNULL(up.pref_value, ' . $scm . ') AS freeCapacity');

            if (!empty($users)) { // use userlist if available otherwise pull data for all users
                $q->addWhere('u.user_id IN (' . implode(',', $users) . ')');
            }
            $q->addGroup('u.user_id');
            $q->addOrder('contact_first_name, contact_last_name');
            // get CCompany() to filter by company
            $obj = new CCompany();
            $obj->overrideDatabase($this->_query);
            $companies = $obj->getAllowedSQL($this->_AppUI->user_id, 'company_id');
            $q->addJoin('companies', 'com', 'company_id = contact_company');
            if ($companies) {
                $q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
            }
            $dpt = new CDepartment();
            $dpt->overrideDatabase($this->_query);
            $depts = $dpt->getAllowedSQL($this->_AppUI->user_id, 'dept_id');
            $q->addJoin('departments', 'dep', 'dept_id = contact_department');
            if ($depts) {
                $q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
            }

            $hash = $q->loadHashList($hash);
            $q->clear();
        }
        return $hash;
    }

    public function getUserSpecificTaskPriority($user_id = 0, $task_id = null)
    {
        // use task_id of given object if the optional parameter task_id is empty
        $task_id = empty($task_id) ? $this->task_id : $task_id;

        $q = $this->_getQuery();
        $q->addTable('user_tasks');
        $q->addQuery('user_task_priority');
        $q->addWhere('user_id = ' . (int) $user_id . ' AND task_id = ' . (int) $task_id);
        $priority = $q->loadHash();

        return ($priority) ? $priority['user_task_priority'] : null;
    }

    public function updateUserSpecificTaskPriority($user_task_priority = 0, $user_id = 0, $task_id = null)
    {
        // use task_id of given object if the optional parameter task_id is empty
        $task_id = empty($task_id) ? $this->task_id : $task_id;

        $q = $this->_getQuery();
        $q->addTable('user_tasks');
        $q->addReplace('user_id', $user_id);
        $q->addReplace('task_id', $task_id);
        $q->addReplace('user_task_priority', $user_task_priority);
        $q->exec();
        $q->clear();
    }

    public function getProject()
    {
        $q = $this->_getQuery();
        $q->addTable('projects');
        $q->addQuery('project_name, project_short_name, project_color_identifier');
        $q->addWhere('project_id = ' . (int) $this->task_project);
        $projects = $q->loadHash();
        $q->clear();
        return $projects;
    }

    //Returns task children IDs
    public function getChildren($task_id = 0)
    {
        if (!$task_id) {
            $task_id = $this->task_id;
        }

        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addQuery('task_id');
        $q->addWhere('task_id <> ' . (int) $task_id . ' AND task_parent = ' . (int) $task_id);
        $result = $q->loadColumn();

        return $result;
    }

    public function getRootTasks($project_id)
    {
        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addQuery('task_id, task_name, task_end_date, task_start_date, task_milestone, task_parent, task_dynamic');
        $q->addWhere('task_project = ' . (int) $project_id);
        $q->addWhere('task_id = task_parent');
        $q->addOrder('task_start_date');

        return $q->loadHashList('task_id');
    }

    public function getNonRootTasks($project_id)
    {
        $q = $this->_getQuery();
        $q->addQuery('task_id, task_name, task_end_date, task_start_date, task_milestone, task_parent, task_dynamic');
        $q->addTable('tasks');
        $q->addWhere('task_project = ' . (int) $project_id);
        $q->addWhere('task_id <> task_parent');
        $q->addOrder('task_start_date');

        return $q->loadHashList('task_id');
    }

    // Returns task deep children IDs
    public function getDeepChildren()
    {
        $children = $this->getChildren();

        if ($children) {
            $deep_children = array();
            $tempTask = new CTask();
            $tempTask->overrideDatabase($this->_query);
            foreach ($children as $child) {
                $tempTask->load($child);
                $deep_children = array_merge($deep_children, $tempTask->getDeepChildren());
            }

            return array_merge($children, $deep_children);
        }
        return array();
    }

    public function getTaskTree($project_id, $task_id = 0)
    {
        $this->_depth++;

        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addQuery('task_id, task_name, task_description, task_end_date, task_start_date');
        $q->addQuery('task_milestone, task_parent, task_dynamic, task_percent_complete');
        $q->addWhere('task_project = ' . (int) $project_id);
        
        if ($task_id) {
            $q->addWhere('task_parent = ' . (int) $task_id);
            $q->addWhere('task_id != ' . (int) $task_id);
        } else {
            $q->addWhere('(task_id = task_parent OR task_parent = 0)');
        }
        $q->addOrder('task_start_date, task_end_date');

        $tasks = $q->loadHashList('task_id');
        foreach ($tasks as $task) {
            $task['depth'] = $this->_depth;
            $taskTree[$task['task_id']] = $task;
            $taskTree = arrayMerge($taskTree, $this->getTaskTree($project_id, $task['task_id']));
        }
        $this->_depth--;

        return $taskTree;
    }

    /**
     * This function, recursively, updates all tasks status
     * to the one passed as parameter
     */
    public function updateSubTasksStatus($new_status, $task_id = null)
    {

        if (is_null($task_id)) {
            $task_id = $this->task_id;
        }

        $q = $this->_getQuery();
        // get children
        $q->addTable('tasks');
        $q->addQuery('task_id');
        $q->addWhere('task_parent = ' . (int) $task_id);
        $tasks_id = $q->loadColumn();
        $q->clear();
        if (count($tasks_id) == 0) {
            return true;
        }

        // update status of children
        $q->addTable('tasks');
        $q->addUpdate('task_status', $new_status);
        $q->addWhere('task_parent = ' . (int) $task_id);
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
     * This method handles moving the $task_id specified from $project_old to
     *   $project_new and then updates the corresponding task counts and task
     *   start/end dates for the projects.
     *
     * @todo Get the start/end dates for the cache.. - updateTaskCache
     *
     * @param type $task_id
     * @param type $project_old
     * @param type $project_new
     */
    public function moveTaskBetweenProjects($task_id, $project_old, $project_new)
    {
        $this->updateSubTasksProject($project_new, $task_id);

        $taskCount_oldProject = $this->getTaskCount($project_old);
        CProject::updateTaskCount($project_old, $taskCount_oldProject);

        $taskCount_newProject = $this->getTaskCount($project_new);
        CProject::updateTaskCount($project_new, $taskCount_newProject);
    }

    /**
     * This function recursively updates all tasks project
     * to the one passed as parameter
     */
    protected function updateSubTasksProject($new_project, $task_id = 0)
    {
        if (!$task_id) {
            $task_id = $this->task_id;
        }

        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addUpdate('task_project', $new_project);
        $q->addWhere('task_id = ' . (int) $task_id);
        $q->exec();

        $task_ids = $this->getChildren($task_id);
        foreach ($task_ids as $id) {
            $this->updateSubTasksProject($new_project, $id);
        }
    }

    public function canUserEditTimeInformation($project_owner = 0, $user_id = 0)
    {
        if (!$project_owner) {
            $project = new CProject();
            $project->overrideDatabase($this->_query);
            $project->load($this->task_project);
            $project_owner = $project->project_owner;
        }
        $user_id = ($user_id) ? $user_id : $this->_AppUI->user_id;

        // Code to see if the current user is
        // enabled to change time information related to task
        $can_edit_time_information = false;
        // Let's see if all users are able to edit task time information
        if (w2PgetConfig('restrict_task_time_editing') == true && $this->task_id > 0) {

            // Am I the task owner?
            if ($this->task_owner == $user_id) {
                $can_edit_time_information = true;
            }

            // Am I the project owner?
            if ($project_owner == $user_id) {
                $can_edit_time_information = true;
            }

            // Am I sys admin?
            if (canEdit('admin')) {
                $can_edit_time_information = true;
            }
        } else {
            if (w2PgetConfig('restrict_task_time_editing') == false || $this->task_id == 0) {
                // If all users are able, then don't check anything
                $can_edit_time_information = true;
            }
        }

        return $can_edit_time_information;
    }

    /**
     * Injects a reminder event into the event queue.
     * Repeat interval is one day, repeat count
     * and days to trigger before event overdue is
     * set in the system config.
     */
    public function addReminder()
    {
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

        $eq = new w2p_Core_EventQueue();
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
            foreach ($old_reminders as $old_id => $notUsed) {
                $eq->remove($old_id);
            }
        }

        // Find the end date of this task, then subtract the required number of days.
        $date = new w2p_Utilities_Date($this->task_end_date);
        $today = new w2p_Utilities_Date(date('Y-m-d'));
        if (w2p_Utilities_Date::compare($date, $today) < 0) {
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
    public function remind($notUsed = null, $notUsed2 = null, $id, $owner, $notUsed = null)
    {
        // At this stage we won't have an object yet
        if (!$this->load($id)) {
            return - 1; // No point it trying again later.
        }
        $this->htmlDecode();

        // Only remind on working days.
        $today = new w2p_Utilities_Date();
        if (!$today->isWorkingDay()) {
            return true;
        }

        // Check if the task is completed
        if ($this->task_percent_complete == 100) {
            return - 1;
        }

        $contacts = array();
        $contacts = $this->getAssigned('contact_id');

        $contact = new CContact();
        $owner = $contact->findContactByUserId($this->task_owner);
        $contacts[$owner->contact_id] = array(
                                    'user_id' => $this->task_owner,
                                    'contact_id' => $owner->contact_id,
                                    'contact_name' => $owner->contact_display_name,
                                    'contact_email' => $owner->contact_email);

        // build the subject line, based on how soon the
        // task will be overdue.
        $starts = new w2p_Utilities_Date($this->task_start_date);
        $expires = new w2p_Utilities_Date($this->task_end_date);
        $now = new w2p_Utilities_Date();
        $diff = $expires->dateDiff($now);
        $diff *= w2p_Utilities_Date::compare($expires, $now);
        $prefix = $this->_AppUI->_('Task Due', UI_OUTPUT_RAW);
        if ($diff == 0) {
            $msg = $this->_AppUI->_('TODAY', UI_OUTPUT_RAW);
        } elseif ($diff == 1) {
            $msg = $this->_AppUI->_('TOMORROW', UI_OUTPUT_RAW);
        } elseif ($diff < 0) {
            $msg = $this->_AppUI->_(array('OVERDUE', abs($diff), 'DAYS'));
            $prefix = $this->_AppUI->_('Task', UI_OUTPUT_RAW);
        } else {
            $msg = $this->_AppUI->_(array($diff, 'DAYS'));
        }

        $project = new CProject();
        $project->overrideDatabase($this->_query);
        $project_name = $project->load($this->task_project)->project_name;

        // Check to see that the project is both active and not a template
        if (!$project->project_active || $project->project_status == w2PgetConfig('template_projects_status_id', 0)) {
            return -1;
        }

        $subject = $prefix . ' ' . $msg . ' ' . $this->task_name . '::' . $project_name;

        $emailManager = new w2p_Output_EmailManager($this->_AppUI);
        $body = $emailManager->getTaskRemind($this, $msg, $project_name, $contacts);

        $mail = new w2p_Utilities_Mail();
        $mail->Subject($subject, $this->_locale_char_set);

        foreach ($contacts as $contact) {
            $user_id = $contact['user_id'];
            $this->_AppUI->loadPrefs($user_id);

            $df = $this->_AppUI->getPref('SHDATEFORMAT');
            $tz = $this->_AppUI->getPref('TIMEZONE');

            $body = str_replace('START-TIME', $starts->convertTZ($tz)->format($df), $body);
            $body = str_replace('END-TIME', $expires->convertTZ($tz)->format($df), $body);
            $mail->Body($body, $this->_locale_char_set);

            if ($mail->ValidEmail($contact['contact_email'])) {
                $mail->To($contact['contact_email'], true);
                $mail->Send();
            }
        }
        return true;
    }

    /**
     *
     */
    public function clearReminder($dont_check = false)
    {
        $ev = new w2p_Core_EventQueue();

        $event_list = $ev->find('tasks', 'remind', $this->task_id);
        if (count($event_list)) {
            foreach ($event_list as $id => $notUsed) {
                if ($dont_check || $this->task_percent_complete >= 100) {
                    $ev->remove($id);
                }
            }
        }
    }

    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null)
    {
        $oPrj = new CProject();
        $oPrj->overrideDatabase($this->_query);

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

    public function getAssigned($index = 'user_id')
    {
        $q = $this->_getQuery();
        $q->addTable('users', 'u');
        $q->addTable('user_tasks', 'ut');
        $q->addTable('contacts', 'con');
        $q->addQuery('u.user_id, perc_assignment');
        $q->addQuery('contact_id, contact_display_name AS user_name, contact_display_name AS contact_name, contact_email');
        $q->addWhere('ut.task_id = ' . (int) $this->task_id);
        $q->addWhere('user_contact = contact_id');
        $q->addWhere('ut.user_id = u.user_id');
        $assigned = $q->loadHashList($index);
        return $assigned;
    }

//TODO: this method should be moved to CTaskLog
    public function getTaskLogs($taskId, $problem = false)
    {
        $q = $this->_getQuery();
        $q->addTable('task_log');
        $q->addQuery('task_log.*, task_log_task as task_id, user_username');
        $q->addQuery('billingcode_name as task_log_costcode, billingcode_category');
        $q->addQuery('contact_display_name AS real_name');
        $q->addQuery('contact_display_name AS task_log_creator');
        $q->addWhere('task_log_task = ' . (int) $taskId . ($problem ? ' AND task_log_problem > 0' : ''));
        $q->addOrder('task_log_date');
        $q->addOrder('task_log_created');
        $q->leftJoin('billingcode', '', 'task_log.task_log_costcode = billingcode_id');
        $q->addJoin('users', '', 'task_log_creator = user_id', 'inner');
        $q->addJoin('contacts', 'ct', 'contact_id = user_contact', 'inner');

        return $q->loadList();
    }

    public function hook_calendar($userId)
    {
        /*
         * This list of fields - id, name, description, startDate, endDate,
         * updatedDate - are named specifically for the iCal creation.
         * If you change them, it's probably going to break.  So don't do that.
         */
        $taskArray = array();
        $taskList = $this->getTaskList($userId);

        //TODO: A user should be able to select if they get distinct start/end dates or two tasks for each task.
        foreach ($taskList as $taskItem) {
            //$taskArray[] = $taskItem;
            $taskArray[] = array_merge($taskItem, array('sequence' => $taskItem['sequence'],
                'endDate' => $taskItem['startDate'], 'name' => 'Start: ' . $taskItem['name'],
                'UID' => 'tasks_' . $taskItem['id'] . 'S'));
            $taskArray[] = array_merge($taskItem, array('sequence' => $taskItem['sequence'],
                'startDate' => $taskItem['endDate'], 'name' => 'End: ' . $taskItem['name'],
                'UID' => 'tasks_' . $taskItem['id'] . 'E'));
        }

        return $taskArray;
    }

    public function hook_search()
    {
        $search['table'] = 'tasks';
        $search['table_alias'] = 't';
        $search['table_module'] = 'tasks';
        $search['table_key'] = 't.task_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=tasks&a=view&task_id='; // first part of link
        $search['table_key2'] = 'tl.task_log_id';
        $search['table_link2'] = '&task_log_id='; // second part of link

        $search['table_title'] = 'Tasks';
        $search['table_orderby'] = 'task_name';
        $search['search_fields'] = array('task_name', 'task_description',
            'task_related_url', 'task_log_name', 'task_log_description');
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(array('table' => 'task_log',
                'alias' => 'tl', 'join' => 't.task_id = tl.task_log_task'));

        return $search;
    }

    public function getTaskList($userId, $days = 30)
    {
        /*
         * This list of fields - id, name, description, startDate, endDate,
         * updatedDate - are named specifically for the iCal creation.
         * If you change them, it's probably going to break.  So don't do that.
         */

        $q = $this->_getQuery();
        $q->addQuery('t.task_id as id');
        $q->addQuery('task_name as name, task_sequence');
        $q->addQuery('task_description as description');
        $q->addQuery('task_start_date as startDate');
        $q->addQuery('task_end_date as endDate');
        $q->addQuery('task_updated as updatedDate');
        $q->addQuery('CONCAT(\'' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . '\', t.task_id) as url');
        $q->addQuery('p.project_id, p.project_name');
        $q->addTable('tasks', 't');

        $q->addWhere('(task_start_date < ' . $q->dbfnDateAdd($q->dbfnNow(), $days, 'DAY') . ' OR task_end_date < ' . $q->dbfnDateAdd($q->dbfnNow(), $days, 'DAY') . ')');
        $q->addWhere('task_percent_complete < 100');
        $q->addWhere('task_dynamic <> 1');

        $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
        $q->addWhere('ut.user_id = ' . $userId);

        $q->innerJoin('projects', 'p', 'p.project_id = t.task_project');
        $q->addWhere('project_active > 0');
        if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
            $q->addWhere('project_status <> ' . $template_status);
        }

        $q->addOrder('task_start_date, task_end_date');

        return $q->loadList();
    }

    public function getAllowedTaskList($notUsed = null, $task_project = 0, $orderby='')
    {
        $results = array();
        
        $q = $this->_getQuery();
        $q->addQuery('task_id, task_name, task_parent, task_access, task_owner');
        $q->addQuery('task_start_date, task_end_date, task_percent_complete');
        $q->addOrder('task_parent, task_parent = task_id desc');
        $q->addTable('tasks', 't');
        if ($task_project) {
            $q->addWhere('task_project = ' . (int) $task_project);
        }
        if ($orderby == '') {
            $q->addOrder('task_parent, task_parent = task_id desc');
        } else {
            $q->addOrder($orderby);
        }
        $task_list = $q->loadList();

        $obj = new CTask();
        foreach ($task_list as $task) {
            $obj->load($task['task_id']);
            $canAccess = $obj->canAccess();
            if ($canAccess) {
                $results[] = $task;
            }
        }

        return $results;
    }

    public function getTaskCount($projectId)
    {
        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addQuery('COUNT(distinct tasks.task_id) AS total_tasks');
        $q->addWhere('task_project = ' . (int) $projectId);
        return $q->loadResult();
    }

    public static function pinUserTask($userId, $taskId)
    {
        $q = new w2p_Database_Query;
        $q->addTable('user_task_pin');
        $q->addInsert('user_id', (int) $userId);
        $q->addInsert('task_id', (int) $taskId);

        if (!$q->exec()) {
            return 'Error pinning task';
        } else {
            return true;
        }
    }

    public static function unpinUserTask($userId, $taskId)
    {
        $q = new w2p_Database_Query;
        $q->setDelete('user_task_pin');
        $q->addWhere('user_id = ' . (int) $userId);
        $q->addWhere('task_id = ' . (int) $taskId);

        if (!$q->exec()) {
            return 'Error unpinning task';
        } else {
            return true;
        }
    }

    public static function updateHoursWorked($taskId, $totalHours)
    {
        $q = new w2p_Database_Query;
        $q->addTable('tasks');
        $q->addUpdate('task_hours_worked', $totalHours + 0);
        $q->addWhere('task_id = ' . $taskId);
        $q->exec();
        $q->clear();

        $q->addTable('tasks');
        $q->addQuery('task_project');
        $q->addWhere('task_id = ' . $taskId);
        $project_id = $q->loadResult();

        CProject::updateHoursWorked($project_id);
    }

}

// user based access
$task_access = array(CTask::ACCESS_PUBLIC => 'Public', CTask::ACCESS_PROTECTED => 'Protected', CTask::ACCESS_PARTICIPANT => 'Participant', CTask::ACCESS_PRIVATE => 'Private');
