<?php /* $Id$ $URL$ */

/**
 * 	@package web2Project
 * 	@subpackage modules
 * 	@version $Revision$
 */
// project statii
$pstatus = w2PgetSysVal('ProjectStatus');
$ptype = w2PgetSysVal('ProjectType');

$ppriority_name = w2PgetSysVal('ProjectPriority');
$ppriority_color = w2PgetSysVal('ProjectPriorityColor');

$priority = array();
foreach ($ppriority_name as $key => $val) {
    $priority[$key]['name'] = $val;
}
foreach ($ppriority_color as $key => $val) {
    $priority[$key]['color'] = $val;
}

/*
  // kept for reference
  $priority = array(
  -1 => array(
  'name' => 'low',
  'color' => '#E5F7FF'
  ),
  0 => array(
  'name' => 'normal',
  'color' => ''//#CCFFCA
  ),
  1 => array(
  'name' => 'high',
  'color' => '#FFDCB3'
  ),
  2 => array(
  'name' => 'immediate',
  'color' => '#FF887C'
  )
  );
 */

/**
 * The Project Class
 */
class CProject extends w2p_Core_BaseObject
{

    public $project_id = null;
    public $project_company = null;
    public $project_name = null;
    public $project_short_name = null;
    public $project_owner = null;
    public $project_url = null;
    public $project_demo_url = null;
    public $project_start_date = null;
    public $project_end_date = null;
    public $project_actual_end_date = null;
    public $project_status = null;
    public $project_percent_complete = null;
    public $project_color_identifier = null;
    public $project_description = null;
    public $project_target_budget = null;
    public $project_actual_budget = null;
    public $project_scheduled_hours = null;
    public $project_worked_hours = null;
    public $project_task_count = null;
    public $project_creator = null;
    public $project_active = null;
    public $project_private = null;
    public $project_priority = null;
    public $project_type = null;
    public $project_parent = null;
    public $project_location = null;
    public $project_original_parent = null;
    /*
     * @deprecated fields, kept to make sure the bind() works properly
     */
    public $project_departments = null;
    public $project_contacts = null;

    public function __construct()
    {
        parent::__construct('projects', 'project_id');
    }

    public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false)
    {
        $result = parent::bind($hash, $prefix, $checkSlashes, $bindAll);
        $this->project_contacts = is_array($this->project_contacts) ? $this->project_contacts : explode(',', $this->project_contacts);

        return $result;
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == $this->project_name) {
            $this->_error['project_name'] = $baseErrorMsg . 'project name is not set';
        }
        if ('' == $this->project_short_name) {
            $this->_error['project_short_name'] = $baseErrorMsg . 'project short name is not set';
        }
        if (0 == (int) $this->project_company) {
            $this->_error['project_company'] = $baseErrorMsg . 'project company is not set';
        }
        if (0 == (int) $this->project_owner) {
            $this->_error['project_owner'] = $baseErrorMsg . 'project owner is not set';
        }
        if (0 == (int) $this->project_creator) {
            $this->_error['project_creator'] = $baseErrorMsg . 'project creator is not set';
        }
        if (!is_int($this->project_priority) && '' == $this->project_priority) {
            $this->_error['project_priority'] = $baseErrorMsg . 'project priority is not set';
        }
        if ('' == $this->project_color_identifier) {
            $this->_error['project_color_identifier'] = $baseErrorMsg . 'project color identifier is not set';
        }
        if (!is_int($this->project_type) && '' == $this->project_type) {
            $this->_error['project_type'] = $baseErrorMsg . 'project type is not set';
        }
        if (!is_int($this->project_status) && '' == $this->project_status) {
            $this->_error['project_status'] = $baseErrorMsg . 'project status is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    public function loadFull($AppUI = null, $projectId)
    {

        $q = $this->_getQuery();
        $q->addTable('projects');
        $q->addQuery('company_name, projects.*');
        $q->addQuery('contact_display_name as user_name');                      //TODO: deprecate?
        $q->addQuery('contact_display_name as project_owner_name');
        $q->addJoin('companies', 'com', 'company_id = project_company', 'inner');
        $q->leftJoin('users', 'u', 'user_id = project_owner');
        $q->leftJoin('contacts', 'con', 'contact_id = user_contact');
        $q->addWhere('project_id = ' . (int) $projectId);
        $q->addGroup('project_id');

        $this->company_name = '';
        $this->project_owner_name = '';
        $this->project_last_task = 0;
        $this->user_name = '';

        $q->loadObject($this);
        $this->budget = $this->getBudget();
    }

    protected function hook_preDelete()
    {
        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addQuery('task_id');
        $q->addWhere('task_project = ' . (int) $this->project_id);
        $tasks_to_delete = $q->loadColumn();

        $q->clear();
        $task = new CTask();
        $task->overrideDatabase($this->_query);
        foreach ($tasks_to_delete as $task_id) {
            $task->task_id = $task_id;
            $task->delete();
        }

        $q->clear();
        $q->addTable('files');
        $q->addQuery('file_id');
        $q->addWhere('file_project = ' . (int) $this->project_id);
        $files_to_delete = $q->loadColumn();

        $q->clear();
        $file = new CFile();
        $file->overrideDatabase($this->_query);
        foreach ($files_to_delete as $file_id) {
            $file->file_id = $file_id;
            $file->delete();
        }

        $q->clear();
        $q->addTable('events');
        $q->addQuery('event_id');
        $q->addWhere('event_project = ' . (int) $this->project_id);
        $events_to_delete = $q->loadColumn();

        $q->clear();
        $event = new CEvent();
        $event->overrideDatabase($this->_query);
        foreach ($events_to_delete as $event_id) {
            $event->event_id = $event_id;
            $event->delete();
        }

        $q->clear();
        // remove the project-contacts and project-departments map
        $q->setDelete('project_contacts');
        $q->addWhere('project_id =' . (int) $this->project_id);
        $q->exec();

        $q->clear();
        $q->setDelete('project_departments');
        $q->addWhere('project_id =' . (int) $this->project_id);
        $q->exec();

        $q->clear();
        $q->setDelete('tasks');
        $q->addWhere('task_represents_project =' . (int) $this->project_id);

        parent::hook_preDelete();
    }

    /** 	
     * Import tasks from another project
     *
     * 	@param	int Project ID of the tasks come from.
     * 	@return	bool
     * */
    public function importTasks($from_project_id)
    {
        $errors = array();

        // Load the original
        $origProject = new CProject();
        $origProject->overrideDatabase($this->_query);
        $origProject->load($from_project_id);
        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addQuery('task_id');
        $q->addWhere('task_project =' . (int) $from_project_id);
        $tasks = array_flip($q->loadColumn());
        $q->clear();

        $origDate = new w2p_Utilities_Date($origProject->project_start_date);

        $destDate = new w2p_Utilities_Date($this->project_start_date);

        $timeOffset = $origDate->dateDiff($destDate);
        if ($origDate->compare($origDate, $destDate) > 0) {
            $timeOffset = -1 * $timeOffset;
        }

        // Dependencies array
        $deps = array();

        $objTask = new CTask();
        $objTask->overrideDatabase($this->_query);
        // Copy each task into this project and get their deps
        $objTask = new CTask();
        $objTask->overrideDatabase($this->_query);
        foreach ($tasks as $orig => $void) {
            $objTask->load($orig);
            $destTask = $objTask->copy($this->project_id);
            $destTask->task_parent = (0 == $destTask->task_parent) ? $destTask->task_id : $destTask->task_parent;
            $destTask->store();
            $tasks[$orig] = $destTask;
            $deps[$orig] = $objTask->getDependencies();
        }

        // Fix record integrity
        foreach ($tasks as $old_id => $newTask) {

            // Fix parent Task
            // This task had a parent task, adjust it to new parent task_id
            if ($newTask->task_id != $newTask->task_parent) {
                $newTask->task_parent = $tasks[$newTask->task_parent]->task_id;
            }

            // Fix task start date from project start date offset
            $origDate->setDate($newTask->task_start_date);
            $origDate->addDays($timeOffset);
            $destDate = $origDate;
            $newTask->task_start_date = $destDate->format(FMT_DATETIME_MYSQL);

            // Fix task end date from start date + work duration
            if (!empty($newTask->task_end_date) && $newTask->task_end_date != '0000-00-00 00:00:00') {
                $origDate->setDate($newTask->task_end_date);
                $origDate->addDays($timeOffset);
                $destDate = $origDate;
                $newTask->task_end_date = $destDate->format(FMT_DATETIME_MYSQL);
            }

            // Dependencies
            if (!empty($deps[$old_id])) {
                $oldDeps = explode(',', $deps[$old_id]);
                // New dependencies array
                $newDeps = array();
                foreach ($oldDeps as $dep) {
                    $newDeps[] = $tasks[$dep]->task_id;
                }

                // Update the new task dependencies
                $csList = implode(',', $newDeps);
                $newTask->updateDependencies($csList);
            } // end of update dependencies
            $result = $newTask->store();
            $newTask->addReminder();
            $importedTasks[] = $newTask->task_id;

            if (is_array($result) && count($result)) {
                foreach ($result as $key => $error_msg) {
                    $errors[] = $newTask->task_name . ': ' . $error_msg;
                }
            }
        } // end Fix record integrity
        // We have errors, so rollback everything we've done so far
        if (count($errors)) {
            $delTask = new CTask();
            $delTask->overrideDatabase($this->_query);
            foreach ($importedTasks as $badTask) {
                $delTask->task_id = $badTask;
                $delTask->delete();
            }
        } else {

            // All is OK! Now update task cache
            $numTasks = count($importedTasks);
            $lastImportIndex = $numTasks-1;

            // TODO Unsure if we should update the end date from tasks... Thoughts?
            $this->updateTaskCache($this->project_id, $importedTasks[$lastImportIndex], $this->project_actual_end_date, $numTasks);
        }
        return $errors;
    }

    // end of importTasks

    /**
     * *	Overload of the w2PObject::getAllowedRecords
     * *	to ensure that the allowed projects are owned by allowed companies.
     * *
     * *	@author	handco <handco@sourceforge.net>
     * *	@see	w2PObject::getAllowedRecords
     * */
    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $table_alias = '')
    {
        $oCpy = new CCompany();
        $oCpy->overrideDatabase($this->_query);

        $aCpies = $oCpy->getAllowedRecords($uid, 'company_id, company_name');
        if (count($aCpies)) {
            $buffer = '(project_company IN (' . implode(',', array_keys($aCpies)) . '))';

            if (!isset($extra['from']) && !isset($extra['join'])) {
                $extra['join'] = 'project_departments';
                $extra['on'] = 'projects.project_id = project_departments.project_id';
            } elseif ((isset($extra['from']) && $extra['from'] != 'project_departments') && !isset($extra['join'])) {
                $extra['join'] = 'project_departments';
                $extra['on'] = 'projects.project_id = project_departments.project_id';
            }
            //Department permissions
            $oDpt = new CDepartment();
            $oDpt->overrideDatabase($this->_query);
            $aDpts = $oDpt->getAllowedRecords($uid, 'dept_id, dept_name');
            if (count($aDpts)) {
                $dpt_buffer = '(department_id IN (' . implode(',', array_keys($aDpts)) . ') OR department_id IS NULL)';
            } else {
                // There are no allowed departments, so allow projects with no department.
                $dpt_buffer = '(department_id IS NULL)';
            }

            if (isset($extra['where']) && $extra['where'] != '') {
                $extra['where'] = $extra['where'] . ' AND ' . $buffer . ' AND ' . $dpt_buffer;
            } else {
                $extra['where'] = $buffer . ' AND ' . $dpt_buffer;
            }
        } else {
            // There are no allowed companies, so don't allow projects.
            if ($extra['where'] != '') {
                $extra['where'] = $extra['where'] . ' AND 1 = 0 ';
            } else {
                $extra['where'] = '1 = 0';
            }
        }
        return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra, $table_alias);
    }

    public function getAllowedSQL($uid, $index = null)
    {
        $oCpy = new CCompany();
        $oCpy->overrideDatabase($this->_query);
        $where = $oCpy->getAllowedSQL($uid, 'project_company');

        $oDpt = new CDepartment();
        $oDpt->overrideDatabase($this->_query);
        $where += $oDpt->getAllowedSQL($uid, 'dept_id');

        $project_where = parent::getAllowedSQL($uid, $index);
        return array_merge($where, $project_where);
    }

    public function setAllowedSQL($uid, &$query, $index = null, $key = 'pr')
    {
        $oCpy = new CCompany;
        $oCpy->overrideDatabase($this->_query);
        parent::setAllowedSQL($uid, $query, $index, $key);
        $oCpy->setAllowedSQL($uid, $query, ($key ? $key . '.' : '') . 'project_company');
        //Department permissions
        $oDpt = new CDepartment();
        $oDpt->overrideDatabase($this->_query);
        $query->leftJoin('project_departments', '', $key . '.project_id = project_departments.project_id');
        $oDpt->setAllowedSQL($uid, $query, 'project_departments.department_id');
    }

    /**
     * 	Overload of the w2PObject::getDeniedRecords
     * 	to ensure that the projects owned by denied companies are denied.
     *
     * 	@author	handco <handco@sourceforge.net>
     * 	@see	w2PObject::getAllowedRecords
     */
    public function getDeniedRecords($uid)
    {
        $aBuf1 = parent::getDeniedRecords($uid);

        $oCpy = new CCompany();
        $oCpy->overrideDatabase($this->_query);
        // Retrieve which projects are allowed due to the company rules
        $aCpiesAllowed = $oCpy->getAllowedRecords($uid, 'company_id,company_name');

        //Department permissions
        $oDpt = new CDepartment();
        $oDpt->overrideDatabase($this->_query);
        $aDptsAllowed = $oDpt->getAllowedRecords($uid, 'dept_id,dept_name');

        $q = $this->_getQuery();
        $q->addTable('projects');
        $q->addQuery('projects.project_id');
        $q->addJoin('project_departments', 'pd', 'pd.project_id = projects.project_id');

        if (count($aCpiesAllowed)) {
            if ((array_search('0', $aCpiesAllowed)) === false) {
                //If 0 (All Items of a module) are not permited then just add the allowed items only
                $q->addWhere('NOT (project_company IN (' . implode(',', array_keys($aCpiesAllowed)) . '))');
            } else {
                //If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
            }
        } else {
            //if the user is not allowed any company then lets shut him off
            $q->addWhere('0=1');
        }

        if (count($aDptsAllowed)) {
            if ((array_search('0', $aDptsAllowed)) === false) {
                //If 0 (All Items of a module) are not permited then just add the allowed items only
                $q->addWhere('NOT (department_id IN (' . implode(',', array_keys($aDptsAllowed)) . '))');
            } else {
                //If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
                $q->addWhere('NOT (department_id IS NULL)');
            }
        } else {
            //If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
            $q->addWhere('NOT (department_id IS NULL)');
        }

        $aBuf2 = $q->loadColumn();
        $q->clear();

        return array_merge($aBuf1, $aBuf2);
    }

    public function getAllowedProjectsInRows($userId)
    {
        trigger_error("CProject->getAllowedProjectsInRows() has been deprecated in v3.0 and will be removed in v4.0", E_USER_NOTICE);

        $q = $this->_getQuery();
        $q->clear();
        $q->addQuery('pr.project_id, project_status, project_name, project_description, project_short_name');
        $q->addTable('projects', 'pr');
        $q->addOrder('project_short_name');
        $this->setAllowedSQL($userId, $q, null, 'pr');
        $allowedProjectRows = $q->exec();
        $q->clear();

        return $allowedProjectRows;
    }

    /** Retrieve tasks with latest task_end_dates within given project
     * @param int Project_id
     * @param int SQL-limit to limit the number of returned tasks
     * @return array List of criticalTasks
     */
    public function getCriticalTasks($project_id = 0, $limit = 1)
    {
        $project_id = ($project_id) ? $project_id : $this->project_id;

        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addWhere('task_project = ' . (int) $project_id . ' AND task_end_date IS NOT NULL AND task_end_date <>  \'0000-00-00 00:00:00\'');
        $q->addOrder('task_end_date DESC');
        $q->setLimit($limit);

        return $q->loadList();
    }

    public function getBudget()
    {
        $q = $this->_getQuery();
        $q->addQuery('budget_category, budget_amount');
        $q->addTable('budgets_assigned');
        $q->addWhere('budget_project =' . (int) $this->project_id);

        return $q->loadHashList('budget_category');
    }

    public function storeBudget(array $budgets)
    {
        $q = $this->_getQuery();
        $q->setDelete('budgets_assigned');
        $q->addWhere('budget_project =' . (int) $this->project_id);
        $q->exec();

        $q->clear();
        foreach ($budgets as $category => $amount) {
            $q->addTable('budgets_assigned');
            $q->addInsert('budget_project', $this->project_id);
            $q->addInsert('budget_category', $category);
            $q->addInsert('budget_amount', $amount);
            $q->exec();
            $q->clear();
        }

        return true;
    }

    public function store()
    {
        $stored = false;

        $this->w2PTrimAll();
        if (!$this->isValid()) {
            return false;
        }

        // ensure changes of state in checkboxes is captured
        $this->project_active = (int) $this->project_active;
        $this->project_private = (int) $this->project_private;

        $this->project_target_budget = filterCurrency($this->project_target_budget);

        // Make sure project_short_name is the right size (issue for languages with encoded characters)
        $this->project_short_name = mb_substr($this->project_short_name, 0, 10);
        if (empty($this->project_end_date)) {
            $this->project_end_date = null;
        }

        $this->project_id = (int) $this->project_id;
        // convert dates to SQL format first
        if ($this->project_start_date) {
            $date = new w2p_Utilities_Date($this->project_start_date);
            $this->project_start_date = $date->format(FMT_DATETIME_MYSQL);
        }
        if ($this->project_end_date) {
            $date = new w2p_Utilities_Date($this->project_end_date);
            $date->setTime(23, 59, 59);
            $this->project_end_date = $date->format(FMT_DATETIME_MYSQL);
        }
        if ($this->project_actual_end_date) {
            $date = new w2p_Utilities_Date($this->project_actual_end_date);
            $this->project_actual_end_date = $date->format(FMT_DATETIME_MYSQL);
        }

        // check project parents and reset them to self if they do not exist
        if (!$this->project_parent) {
            $this->project_parent = $this->project_id;
            $this->project_original_parent = $this->project_id;
        } else {
            $parent_project = new CProject();
            $parent_project->overrideDatabase($this->_query);
            $parent_project->load($this->project_parent);
            $this->project_original_parent = $parent_project->project_original_parent;
        }
        if (!$this->project_original_parent) {
            $this->project_original_parent = $this->project_id;
        }

        /*
         * TODO: I don't like the duplication on each of these two branches, but I
         *   don't have a good idea on how to fix it at the moment...
         */
        $q = $this->_getQuery();
        $this->project_updated = $q->dbfnNowWithTZ();
        if ($this->{$this->_tbl_key} && $this->canEdit()) {
            $stored = parent::store();
        }
        if (0 == $this->{$this->_tbl_key} && $this->canCreate()) {
            $this->project_created = $q->dbfnNowWithTZ();
            $stored = parent::store();
            
            if ($stored) {
                if (0 == $this->project_parent || 0 == $this->project_original_parent) {
                    $this->project_parent = $this->project_id;
                    $this->project_original_parent = $this->project_id;
//TODO: I *really* hate how we have to do the store() twice when we create the project.
                    $stored = parent::store();
                }
            }
        }

        if ($stored) {
            //split out related departments and store them seperatly.
            $q->setDelete('project_departments');
            $q->addWhere('project_id=' . (int) $this->project_id);
            $q->exec();
            $q->clear();
            $stored_departments = array();
            if ($this->project_departments) {
                foreach ($this->project_departments as $department) {
                    if ($department) {
                        $q->addTable('project_departments');
                        $q->addInsert('project_id', $this->project_id);
                        $q->addInsert('department_id', $department);
                        $stored_departments[$department] = $this->project_id;
                        $q->exec();
                        $q->clear();
                    }
                }
            }
            $this->stored_departments = $stored_departments;

            //split out related contacts and store them seperatly.
            $q->setDelete('project_contacts');
            $q->addWhere('project_id=' . (int) $this->project_id);
            $q->exec();
            $q->clear();
            $stored_contacts = array();
            if ($this->project_contacts) {
                foreach ($this->project_contacts as $contact) {
                    if ($contact) {
                        $q->addTable('project_contacts');
                        $q->addInsert('project_id', $this->project_id);
                        $q->addInsert('contact_id', $contact);
                        $stored_contacts[$contact] = $this->project_id;
                        $q->exec();
                        $q->clear();
                    }
                }
            }
            $this->stored_contacts = $stored_contacts;

            $custom_fields = new w2p_Core_CustomFields('projects', 'addedit', $this->project_id, 'edit');
            $custom_fields->bind($_POST);
            $sql = $custom_fields->store($this->project_id); // Store Custom Fields

            CTask::storeTokenTask($this->_AppUI, $this->project_id);
        }
        return $stored;
    }

    public function notifyOwner($isNotNew)
    {
        global $w2Pconfig, $locale_char_set;

        $mail = new w2p_Utilities_Mail;

        $subject = (intval($isNotNew)) ? "Project Updated: $this->project_name " : "Project Submitted: $this->project_name ";

        $user = new CUser();
        $user->overrideDatabase($this->_query);
        $user->loadFull($this->project_owner);

        if ($user && $mail->ValidEmail($user->user_email)) {
            $emailManager = new w2p_Output_EmailManager($this->_AppUI);
            $body = $emailManager->getProjectNotifyOwner($this, $isNotNew);

            $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
            $mail->To($user->user_email, true);
            $mail->Send();
        }
    }

    public function notifyContacts($isNotNew)
    {
        global $w2Pconfig, $locale_char_set;

        $subject = (intval($isNotNew)) ? "Project Updated: $this->project_name " : "Project Submitted: $this->project_name ";

        $users = CProject::getContacts($this->_AppUI, $this->project_id);
        if (count($users)) {
            $emailManager = new w2p_Output_EmailManager($this->_AppUI);
            $body = $emailManager->getProjectNotifyContacts($this, $isNotNew);

            foreach ($users as $row) {
                $mail = new w2p_Utilities_Mail;
                $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
                $mail->Subject($subject, $locale_char_set);

                if ($mail->ValidEmail($row['contact_email'])) {
                    $mail->To($row['contact_email'], true);
                    $mail->Send();
                }
            }
        }
        return '';
    }

    public function getAllowedProjects($userId, $activeOnly = true)
    {

        $q = $this->_getQuery();
        $q->addTable('projects', 'pr');
        $q->addQuery('pr.project_id, project_color_identifier, project_name, project_start_date, project_end_date, project_company, project_parent');
        if ($activeOnly) {
            $q->addWhere('project_active = 1');
        }
        $q->addGroup('pr.project_id');
        $q->addOrder('project_name');
        $this->setAllowedSQL($userId, $q, null, 'pr');

        return $q->loadHashList('project_id');
    }

    public function getContactList()
    {
        if ($this->_AppUI->isActiveModule('contacts') && canView('contacts')) {
            $q = $this->getQuery();
            $q->addTable('contacts', 'c');
            $q->addQuery('c.*, d.dept_id');
            $q->addQuery('contact_display_name as contact_name');

            $q->leftJoin('departments', 'd', 'd.dept_id = c.contact_department');
            $q->addQuery('dept_name');

            $q->addJoin('project_contacts', 'pc', 'pc.contact_id = c.contact_id', 'inner');
            $q->addWhere('pc.project_id = ' . (int) $this->project_id);

            $q->addWhere('
				(contact_private=0
					OR (contact_private=1 AND contact_owner=' . $this->_AppUI->user_id . ')
					OR contact_owner IS NULL OR contact_owner = 0
				)');

            $department = new CDepartment;
            $department->overrideDatabase($this->_query);
            //TODO: We need to convert this from static to use ->overrideDatabase() for testing.
            $department->setAllowedSQL($this->_AppUI->user_id, $q);

            return $q->loadHashList('contact_id');
        }
    }

    public static function getContacts($AppUI = null, $projectId)
    {
        trigger_error("CProject::getContacts has been deprecated in v3.0 and will be removed by v4.0. Please use CProject->getContactList() instead.", E_USER_NOTICE);

        $project = new CProject();
        //TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $project->project_id = $projectId;

        return $project->getContactList();
    }

    public function getDepartmentList()
    {
        if ($this->_AppUI->isActiveModule('departments') && canView('departments')) {
            $q = $this->_getQuery();
            $q->addTable('departments', 'a');
            $q->addTable('project_departments', 'b');
            $q->addQuery('a.dept_id, a.dept_name, a.dept_phone');
            $q->addWhere('a.dept_id = b.department_id and b.project_id = ' . (int) $this->project_id);

            $department = new CDepartment();
            $department->overrideDatabase($this->_query);
            $department->setAllowedSQL($this->_AppUI->user_id, $q);

            return $q->loadHashList('dept_id');
        }
    }

    public static function getDepartments($AppUI = null, $projectId)
    {
        trigger_error("CProject::getDepartments has been deprecated in v3.0 and will be removed by v4.0. Please use CProject->getDepartmentList() instead.", E_USER_NOTICE);

        $project = new CProject();
        //TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $project->project_id = $projectId;

        return $project->getDepartmentList();
    }

    public function getForumList()
    {
		if ($this->_AppUI->isActiveModule('forums') && canView('forums')) {
			$q = $this->_getQuery();
			$q->addTable('forums');
			$q->addQuery('forum_id, forum_project, forum_description, forum_owner,
                forum_name, forum_message_count, forum_create_date, forum_last_date,
				project_name, project_color_identifier, project_id');
            $q->addJoin('projects', 'p', 'project_id = forum_project', 'inner');
            $q->addWhere('forum_project = ' . (int) $projectId);
            $q->addOrder('forum_project, forum_name');

            return $q->loadHashList('forum_id');
        }
    }

    public static function getForums($AppUI = null, $projectId)
    {
        trigger_error("CProject::getForums has been deprecated in v3.0 and will be removed by v4.0. Please use CProject->getForumList() instead.", E_USER_NOTICE);

        $project = new CProject();
        //TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $project->project_id = $projectId;

        return $project->getForumList();
    }

    public static function getCompany($projectId)
    {

        $q = new w2p_Database_Query();
        $q->addQuery('project_company');
        $q->addTable('projects');
        $q->addWhere('project_id = ' . (int) $projectId);

        return $q->loadResult();
    }

    public static function getBillingCodes($companyId, $all = false)
    {

        $q = new w2p_Database_Query();
        $q->addTable('billingcode');
        $q->addQuery('billingcode_id, billingcode_name');
        $q->addOrder('billingcode_name');
        $q->addWhere('billingcode_status = 0');
        $q->addWhere('(billingcode_company = 0 OR billingcode_company = ' . (int) $companyId . ')');
        $task_log_costcodes = $q->loadHashList();

        if ($all) {
            $q->clear();
            $q->addTable('billingcode');
            $q->addQuery('billingcode_id, billingcode_name');
            $q->addOrder('billingcode_name');
            $q->addWhere('billingcode_status = 1');
            $q->addWhere('(billingcode_company = 0 OR billingcode_company = ' . (int) $companyId . ')');

            $billingCodeList = $q->loadHashList();
            foreach ($billingCodeList as $id => $code) {
                $task_log_costcodes[$id] = $code;
            }
        }

        return $task_log_costcodes;
    }

    public static function getOwners()
    {

        $q = new w2p_Database_Query();
        $q->addTable('projects', 'p');
        $q->addQuery('user_id, contact_display_name');
        $q->leftJoin('users', 'u', 'u.user_id = p.project_owner');
        $q->leftJoin('contacts', 'c', 'c.contact_id = u.user_contact');
        $q->addOrder('contact_first_name, contact_last_name');
        $q->addWhere('user_id > 0');
        $q->addWhere('p.project_owner IS NOT NULL');

        return $q->loadHashList();
    }

    public static function updateStatus($AppUI = null, $projectId, $statusId)
    {
        trigger_error("CProject::updateStatus has been deprecated in v2.3 and will be removed by v4.0.", E_USER_NOTICE);

        global $AppUI;

        $perms = $AppUI->acl();
        if ($perms->checkModuleItem('projects', 'edit', $projectId) && $projectId > 0 && $statusId >= 0) {
            $q = new w2p_Database_Query();
            $q->addTable('projects');
            $q->addUpdate('project_status', $statusId);
            $q->addWhere('project_id   = ' . (int) $projectId);
            $q->exec();
        }
    }

    public static function updateTaskCache($project_id, $task_id, $project_actual_end_date, $project_task_count)
    {

        if ($project_id && $task_id) {
            $q = new w2p_Database_Query();
            $q->addTable('projects');
            $q->addUpdate('project_last_task', $task_id);
            $q->addUpdate('project_actual_end_date', $project_actual_end_date);
            $q->addUpdate('project_task_count', $project_task_count);
            $q->addWhere('project_id   = ' . (int) $project_id);
            $q->exec();
            self::updatePercentComplete($project_id);
        }
    }

    public static function updateTaskCount($projectId, $taskCount)
    {

        trigger_error("CProject::updateTaskCount has been deprecated in v2.3 and will be removed by v4.0. Please use CProject::updateTaskCache instead.", E_USER_NOTICE);

        if (intval($projectId) > 0 && intval($taskCount)) {
            $q = new w2p_Database_Query();
            $q->addTable('projects');
            $q->addUpdate('project_task_count', intval($taskCount));
            $q->addWhere('project_id   = ' . (int) $projectId);
            $q->exec();
            self::updatePercentComplete($projectId);
        }
    }

    public function hasChildProjects($projectId = 0)
    {
        // Note that this returns the *count* of projects.  If this is zero, it
        //   is evaluated as false, otherwise it is considered true.
        $project_id = ($projectId) ? ($this->project_original_parent ? $this->project_original_parent : $this->project_id) : $projectId;

        $q = $this->_getQuery();
        $q->addTable('projects');
        $q->addQuery('COUNT(project_id)');
        $q->addWhere('project_original_parent = ' . (int) $project_id);
        $q->addWhere('project_id <> ' . (int) $project_id);

        return $q->loadResult();
    }

    public static function hasTasks($projectId, $override = null)
    {
        trigger_error("CProject::hasTasks() has been deprecated in v3.0 and will be removed in v4.0. Please use CTask->getTaskCount() instead.", E_USER_NOTICE);

        $task = new CTask();
        $task->overrideDatabase($override);
        return $task->getTaskCount($projectId);
    }

    public static function updateHoursWorked($project_id)
    {

        $q = new w2p_Database_Query();
        $q->addTable('task_log');
        $q->addTable('tasks');
        $q->addQuery('ROUND(SUM(task_log_hours),2)');
        $q->addWhere('task_log_task = task_id AND task_project = ' . (int) $project_id);
        $worked_hours = 0 + $q->loadResult();
        $worked_hours = rtrim($worked_hours, '.');
        $q->clear();

        $q->addTable('projects');
        $q->addUpdate('project_worked_hours', $worked_hours);
        $q->addWhere('project_id  = ' . (int) $project_id);
        $q->exec();
        self::updatePercentComplete($project_id);
    }

    public static function updatePercentComplete($project_id)
    {
        $working_hours = (w2PgetConfig('daily_working_hours') ? w2PgetConfig('daily_working_hours') : 8);

        $q = new w2p_Database_Query();
        $q->addTable('projects');
        $q->addQuery('SUM(t1.task_duration * t1.task_percent_complete * IF(t1.task_duration_type = 24, ' . $working_hours . ', t1.task_duration_type)) / SUM(t1.task_duration * IF(t1.task_duration_type = 24, ' . $working_hours . ', t1.task_duration_type)) AS project_percent_complete');
        $q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project', 'inner');
        $q->addWhere('project_id = ' . $project_id . ' AND t1.task_id = t1.task_parent');
        $project_percent_complete = $q->loadResult();
        $q->clear();

        $q->addTable('projects');
        $q->addUpdate('project_percent_complete', $project_percent_complete);
        $q->addWhere('project_id  = ' . (int) $project_id);
        $q->exec();

        global $AppUI;
        CTask::storeTokenTask($AppUI, $project_id);
    }

    /*
     * This is an unnecessary function as of v2.x. Instead of calculating this on
     *   demand every single time, we calculate it when a Task is created or deleted
     *   and then store it on the projects table. Then we can just return that column.
     *
     * Also, we have to do the check below just in case the object hasn't been
     *   loaded.
     *
     * @deprecated
     */
    public function getTotalProjectHours()
    {
        trigger_error("CProject->getTotalProjectHours() has been deprecated in v3.0 and will be removed in v4.0. Please use the project_scheduled_hours column instead.", E_USER_NOTICE);

        if ('' == $this->project_name)
        {
            $this->load($this->project_id);
        }

        return $this->project_scheduled_hours;
    }

    //TODO: this method should be moved to CTaskLog
    public function getTaskLogs($AppUI = null, $projectId, $user_id = 0, $hide_inactive = false, $hide_complete = false, $cost_code = 0)
    {

        $q = $this->_getQuery();
		$q->addTable('task_log');
		$q->addQuery('DISTINCT task_log.*, user_username, t.*');
//BEGIN: We can probably drop these lines, the fields are unneeded
		$q->addQuery("contact_display_name AS real_name");
        $q->addJoin('users', 'u', 'user_id = task_log_creator');
        $q->addJoin('contacts', 'ct', 'contact_id = user_contact');
//END: We can probably drop these lines, the fields are unneeded
        $q->addQuery('contact_display_name as contact_name');
        $q->addQuery('contact_display_name as task_log_creator');
		$q->addQuery('billingcode_name as task_log_costcode, billingcode_category');
		$q->addJoin('tasks', 't', 'task_log_task = t.task_id');
		
		$q->addJoin('billingcode', 'b', 'task_log.task_log_costcode = billingcode_id');

		$q->addWhere('task_project = ' . (int) $projectId);
		if ($user_id > 0) {
			$q->addWhere('task_log_creator=' . $user_id);
		}
		if ($hide_inactive) {
			$q->addWhere('task_status>=0');
		}
		if ($hide_complete) {
			$q->addWhere('task_percent_complete < 100');
		}
		if ($cost_code > 0) {
			$q->addWhere("billingcode_id = $cost_code");
		}
		$q->addOrder('task_log_date');
		$q->addOrder('task_log_created');
		$this->setAllowedSQL($this->_AppUI->user_id, $q, 'task_project');

		return $q->loadList();
	}

    public function hook_search() {
        $search['table'] = 'projects';
        $search['table_alias'] = 'p';
        $search['table_module'] = 'projects';
        $search['table_key'] = 'p.project_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=projects&a=view&project_id='; // first part of link
        $search['table_title'] = 'Projects';
        $search['table_orderby'] = 'project_name';
        $search['search_fields'] = array(
            'p.project_id', 'p.project_name',
            'p.project_short_name', 'p.project_location', 'p.project_description',
            'p.project_url', 'p.project_demo_url'
        );
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(
            array(
                'table' => 'project_contacts',
                'alias' => 'pc',
                'join' => 'p.project_id = pc.project_id'
            )
        );

        return $search;
    }

    /*
     * TODO: Everything below this is UGLY and needs cleanup.
     */
    public function getStructuredProjects($active_only = false)
    {
        //global $st_projects_arr;
        $st_projects = array(0 => '');

        $q = $this->getQuery();
        $q->addTable('projects');
        $q->addJoin('companies', '', 'projects.project_company = company_id', 'inner');
        $q->addQuery('DISTINCT(projects.project_id), project_name, project_parent');
        if ($this->project_original_parent) {
            $q->addWhere('project_original_parent = ' . (int) $this->project_original_parent);
        }
        if ($this->project_status >= 0) {
            $q->addWhere('project_status = ' . (int) $this->project_status);
        }
        if ($active_only) {
            $q->addWhere('project_active = 1');
        }
        $q->addOrder('project_start_date, project_end_date');

        $obj = new CCompany();
        $obj->overrideDatabase($this->_query);
        $obj->setAllowedSQL($this->_AppUI->user_id, $q);

        $dpt = new CDepartment();
        $dpt->overrideDatabase($this->_query);
        $dpt->setAllowedSQL($this->_AppUI->user_id, $q);

        $q->leftJoin('project_departments', 'pd', 'pd.project_id = projects.project_id' );
        $q->leftJoin('departments', 'd', 'd.dept_id = pd.department_id' );

        $st_projects = $q->loadList();
        $tnums = count($st_projects);
        for ($i = 0; $i < $tnums; $i++) {
            $st_project = $st_projects[$i];
            if (($st_project['project_parent'] == $st_project['project_id'])) {
                $this->show_st_project($st_project);
                $this->find_proj_child($st_projects, $st_project['project_id']);
            }
        }

        return $this->st_projects_arr;
    }

    public function find_proj_child(&$tarr, $parent, $level = 0) {
        $level = $level + 1;
        $n = count($tarr);
        for ($x = 0; $x < $n; $x++) {
            if ($tarr[$x]['project_parent'] == $parent && $tarr[$x]['project_parent'] != $tarr[$x]['project_id']) {
                $this->show_st_project($tarr[$x], $level);
                $this->find_proj_child($tarr, $tarr[$x]['project_id'], $level);
            }
        }
    }

    protected function show_st_project(&$a, $level = 0) {
        $this->st_projects_arr[] = array($a, $level);
    }

    public function getProjects()
    {
        $st_projects = array(0 => '');

        $q = $this->_getQuery();
        $q->addTable('projects');
        $q->addQuery('project_id, project_name, project_parent');
        $q->addOrder('project_name');
        $st_projects = $q->loadHashList('project_id');

        $this->reset_project_parents($st_projects);
        return $st_projects;
    }

    protected function reset_project_parents(&$projects)
    {
        foreach ($projects as $key => $project) {
            if ($project['project_id'] == $project['project_parent'])
                $projects[$key][2] = '';
        }
    }
}
