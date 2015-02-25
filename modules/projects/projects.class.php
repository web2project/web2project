<?php
/**
 *
 * @package     web2project\modules\core
 * @todo        refactor static methods
 */

class CProject extends w2p_Core_BaseObject
{
    public $project_id = null;
    public $project_company = null;
    public $project_name = null;
    // @todo convert this to project_shortname for v4.0
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
    public $project_created = null;
    public $project_updated = null;

    protected $st_projects_arr = array();
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
        if (0 == (int) $this->project_company) {
            $this->_error['project_company'] = $baseErrorMsg . 'project company is not set';
        }
        if ('' == $this->project_color_identifier) {
            $this->_error['project_color_identifier'] = $baseErrorMsg . 'project color identifier is not set';
        }
        return (count($this->_error)) ? false : true;
    }

    protected function hook_postLoad() {
        $this->budget = $this->getBudget();
    }

    /**
     * 	Returns an array, keyed by the key field, of all elements that meet
     * 	the where clause provided. Ordered by $order key.
     *
     * @param null $order
     * @param null $where
     *
     * @return Associative
     */
    public function loadAll($order = null, $where = null)
    {
        $q = $this->_getQuery();
        $q->addTable($this->_tbl);
        if ($order) {
            $q->addOrder($order);
        }
        $q->addOrder('project_name');
        if ($where) {
            $q->addWhere($where);
        }
        $where = $this->getAllowedSQL($this->_AppUI->user_id, 'projects.project_id');
        $q->addWhere($where);
        $q->addJoin('companies', 'c', 'c.company_id = project_company');

        return $q->loadHashList($this->_tbl_key);
    }

    protected function hook_postDelete()
    {
        $q = $this->_getQuery();
        $q->addTable('tasks');
        $q->addQuery('task_id');
        $q->addWhere('task_project = ' . $this->_old_key);
        $tasks_to_delete = $q->loadColumn();

        $q->clear();
        $task = new w2p_Actions_BulkTasks();
        $task->overrideDatabase($this->_query);
        foreach ($tasks_to_delete as $task_id) {
            $task->task_id = $task_id;
            $task->delete();
        }

        $q->clear();
        $q->addTable('files');
        $q->addQuery('file_id');
        $q->addWhere('file_project = ' . $this->_old_key);
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
        $q->addWhere('event_project = ' . $this->_old_key);
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
        $q->addWhere('project_id =' . $this->_old_key);
        $q->exec();

        $q->clear();
        $q->setDelete('project_departments');
        $q->addWhere('project_id =' . $this->_old_key);
        $q->exec();

        $q->clear();
        $q->setDelete('tasks');
        $q->addWhere('task_represents_project =' . $this->_old_key);

        parent::hook_preDelete();
    }

    /**
     * Import tasks from another project
     * */
    public function importTasks($from_project_id, CTask $newTask = null)
    {
        $newTask = new w2p_Actions_BulkTasks();
        $newTask->overrideDatabase($this->_query);

        return $newTask->importTasks($from_project_id, $this->project_id, $this->project_start_date);
    }

    // end of importTasks

    /**
     * *	Overload of the w2PObject::getAllowedRecords
     * *	to ensure that the allowed projects are owned by allowed companies.
     * *
     * *	@author	handco <handco@sourceforge.net>
     * *	@see	w2PObject::getAllowedRecords
     * */
    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = array(), $table_alias = '')
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
            $extra['where'] = '1 = 0';
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

    public function setAllowedSQL($uid, $query, $index = null, $key = 'pr')
    {
        $oCpy = new CCompany;
        $oCpy->overrideDatabase($this->_query);
        $query = parent::setAllowedSQL($uid, $query, $index, $key);
        $query = $oCpy->setAllowedSQL($uid, $query, ($key ? $key . '.' : '') . 'project_company');
        //Department permissions
        $oDpt = new CDepartment();
        $oDpt->overrideDatabase($this->_query);
        $query->leftJoin('project_departments', '', $key . '.project_id = project_departments.project_id');
        $query = $oDpt->setAllowedSQL($uid, $query, 'project_departments.department_id');

        return $query;
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

    /**
     * @deprecated
     */
    public function getAllowedProjectsInRows($userId)
    {
        trigger_error("CProject->getAllowedProjectsInRows() has been deprecated in v3.0 and will be removed in v4.0", E_USER_NOTICE);

        $q = $this->_getQuery();
        $q->clear();
        $q->addQuery('pr.project_id, project_status, project_name, project_description, project_short_name');
        $q->addTable('projects', 'pr');
        $q->addOrder('project_short_name');
        $q = $this->setAllowedSQL($userId, $q, null, 'pr');
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

        $this->project_target_budget = array_sum($budgets);
        $this->store();

        return true;
    }

    protected function hook_preCreate()
    {
        $q = $this->_getQuery();
        $this->project_created = $q->dbfnNowWithTZ();

        parent::hook_preCreate();
    }
    protected function hook_preStore()
    {
        $q = $this->_getQuery();
        $this->project_updated = $q->dbfnNowWithTZ();

        // ensure changes of state in checkboxes is captured
        $this->project_active = (int) $this->project_active;
        $this->project_private = (int) $this->project_private;

        $this->project_target_budget = filterCurrency($this->project_target_budget);
	if(!ctype_digit($this->project_target_budget) && !is_float($this->project_target_budget))
		$this->project_target_budget = 0.0;
        $this->project_url = str_replace(array('"', '"', '<', '>'), '', $this->project_url);
        $this->project_demo_url = str_replace(array('"', '"', '<', '>'), '', $this->project_demo_url);
        $this->project_owner = (int) $this->project_owner ? $this->project_owner : $this->_AppUI->user_id;
        $this->project_creator = (int) $this->project_creator ? $this->project_creator : $this->_AppUI->user_id;

        $this->project_priority = (int) $this->project_priority;
        $this->project_type = (int) $this->project_type;
        $this->project_status = (int) $this->project_status;

        // Make sure project_short_name is the right size (issue for languages with encoded characters)
        if ('' == $this->project_short_name) {
            $this->project_short_name = mb_substr($this->project_name, 0, 10);
        }
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
            $this->project_end_date = $date->format(FMT_DATETIME_MYSQL);
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

        parent::hook_preStore();
    }

    /**
     * @todo TODO: I *really* hate how we have to do the store() twice when we
     *   create the project.. it's all because the stupid project_original_parent
     *   has to equal its own project_id if this is a root project. Ugh.
     */
    protected function hook_postCreate()
    {
        if (0 == $this->project_parent || 0 == $this->project_original_parent) {
            $this->project_parent = $this->project_id;
            $this->project_original_parent = $this->project_id;

            parent::store();
        }

        parent::hook_postCreate();
    }

    protected function hook_postStore()
    {
        $q = $this->_getQuery();
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

        CTask::storeTokenTask($this->_AppUI, $this->project_id);

        parent::hook_postStore();
    }
    public function notifyOwner($isNotNew)
    {
        $user = new CUser();
        $user->overrideDatabase($this->_query);
        $user->loadFull($this->project_owner);

        $subject = (intval($isNotNew)) ? $this->_AppUI->_('Project updated') . ': ' . $this->project_name : $this->_AppUI->_('Project submitted') . ': ' . $this->project_name;
        $emailManager = new w2p_Output_EmailManager($this->_AppUI);
        $body = $emailManager->getProjectNotify($this, $isNotNew);

        $mail = new w2p_Utilities_Mail;
        $mail->To($user->user_email, true);
        $mail->Subject($subject);
        $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

        $mail->Send();
    }

    public function notifyContacts($isNotNew)
    {
        $subject = (intval($isNotNew)) ? "Project Updated: $this->project_name " : "Project Submitted: $this->project_name ";

        $users = CProject::getContacts($this->_AppUI, $this->project_id);
        if (count($users)) {
            $emailManager = new w2p_Output_EmailManager($this->_AppUI);
            $body = $emailManager->getProjectNotify($this, $isNotNew);

            foreach ($users as $row) {
                $mail = new w2p_Utilities_Mail;
                $mail->To($row['contact_email'], true);
                $mail->Subject($subject);
                $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
                $mail->Send();
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
        $q = $this->setAllowedSQL($userId, $q, null, 'pr');

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
            $q = $department->setAllowedSQL($this->_AppUI->user_id, $q);

            return $q->loadHashList('contact_id');
        }
    }

    /**
     * @deprecated
     */
    public static function getContacts($notUsed = null, $projectId)
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
            $q = $department->setAllowedSQL($this->_AppUI->user_id, $q);

            return $q->loadHashList('dept_id');
        }
    }

    /**
     * @deprecated
     */
    public static function getDepartments($notUsed = null, $projectId)
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
				project_name, project_color_identifier, project_id, user_id');
            $q->addJoin('projects', 'p', 'project_id = forum_project', 'inner');
            $q->addWhere('forum_project = ' . (int) $this->project_id);
            $q->addJoin('users', 'u', 'u.user_id = forum_owner');
            $q->addOrder('forum_project, forum_name');

            return $q->loadHashList('forum_id');
        }
    }

    /**
     * @deprecated
     */
    public static function getForums($notUsed = null, $projectId)
    {
        trigger_error("CProject::getForums has been deprecated in v3.0 and will be removed by v4.0. Please use CProject->getForumList() instead.", E_USER_NOTICE);

        $project = new CProject();
        //TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $project->project_id = $projectId;

        return $project->getForumList();
    }

    public function company()
    {
        $this->load();
        return $this->project_company;
    }

    /**
     * @deprecated
     */
    public static function getCompany($projectId)
    {
        trigger_error("CProject::getCompany has been deprecated in v3.1 and will be removed by v4.0. Please use CProject->company() instead.", E_USER_NOTICE);

        $project = new CProject();
        $project->project_id = $projectId;

        return $project->company();
    }

    public static function getBillingCodes($companyId, $all = false)
    {
        $q = new w2p_Database_Query();
        $q->addTable('billingcode');
        $q->addQuery('billingcode_id, billingcode_name');
        $q->addOrder('billingcode_name');
        if ($all) {
            $q->addWhere('billingcode_status = 1');
        } else {
            $q->addWhere('billingcode_status = 0');
        }
        $q->addWhere('(billingcode_company = 0 OR billingcode_company = ' . (int) $companyId . ')');

        return $q->loadHashList();
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
            $project = new CProject();
            $project->load($projectId);
            $project->project_status = $statusId;
            $project->store();
        }
    }

    public static function updateTaskCache($project_id, $task_id, $project_actual_end_date, $project_task_count)
    {
        $project_id = (int) $project_id;
        if ($project_id && $task_id) {
            $q = new w2p_Database_Query();
            $q->addTable('projects');
            $q->addUpdate('project_last_task', $task_id);
            $q->addUpdate('project_actual_end_date', $project_actual_end_date);
            $q->addUpdate('project_task_count', $project_task_count);
            $q->addWhere('project_id = ' . (int) $project_id);
            $q->exec();

            self::updatePercentComplete($project_id);
        }
    }

    /**
     * @deprecated
     */
    public static function updateTaskCount($projectId, $taskCount)
    {
        trigger_error("CProject::updateTaskCount has been deprecated in v2.3 and will be removed by v4.0. Please use CProject::updateTaskCache instead.", E_USER_NOTICE);

        if ((int) $projectId) {
            $project = new CProject();
            $project->load($projectId);
            $project->project_task_count = $taskCount;
            $project->store();

            self::updatePercentComplete($projectId);
        }
    }

    /**
     * Note that this returns the *count* of projects.  If this is zero, it is
     *   evaluated as false, otherwise it is considered true.
     *
     * @param type $projectId
     * @return type
     */
    public function hasChildProjects($projectId = 0)
    {
        $project_id = ($projectId) ? ($this->project_original_parent ? $this->project_original_parent : $this->project_id) : $projectId;

        $q = $this->_getQuery();
        $q->addTable('projects');
        $q->addQuery('COUNT(project_id)');
        $q->addWhere('project_original_parent = ' . (int) $project_id);
        $q->addWhere('project_id <> ' . (int) $project_id);

        return $q->loadResult();
    }

    /**
     * @deprecated
     */
    public static function hasTasks($projectId, $override = null)
    {
        trigger_error("CProject::hasTasks() has been deprecated in v3.0 and will be removed in v4.0. Please use CTask->getTaskCount() instead.", E_USER_NOTICE);

        $task = new CTask();
        $task->overrideDatabase($override);
        return $task->getTaskCount($projectId);
    }

    public static function updateHoursWorked($project_id)
    {
        $project_id = (int) $project_id;

        $q = new w2p_Database_Query();
        $q->addTable('task_log');
        $q->addTable('tasks');
        $q->addQuery('ROUND(SUM(task_log_hours),2)');
        $q->addWhere('task_log_task = task_id AND task_project = ' . $project_id);
        $worked_hours =  floatval($q->loadResult());
        $worked_hours = rtrim($worked_hours, '.');
        $q->clear();

        $q->addTable('projects');
        $q->addUpdate('project_worked_hours', $worked_hours);
        $q->addWhere('project_id = ' . $project_id);
        $q->exec();

        self::updatePercentComplete($project_id);
    }

    public static function updatePercentComplete($project_id)
    {
        if (!$project_id) {
            return;
        }
        $working_hours = (w2PgetConfig('daily_working_hours') ? w2PgetConfig('daily_working_hours') : 8);

        $q = new w2p_Database_Query();
        $q->addTable('projects');
        $q->addQuery('SUM(t1.task_duration * t1.task_percent_complete * IF(t1.task_duration_type = 24, ' . $working_hours . ', t1.task_duration_type)) / SUM(t1.task_duration * IF(t1.task_duration_type = 24, ' . $working_hours . ', t1.task_duration_type)) AS project_percent_complete');
        $q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project', 'inner');
        $q->addWhere('project_id = ' . $project_id . ' AND t1.task_id = t1.task_parent');
        $q->addWhere('task_status <> -1');
        $project_percent_complete = $q->loadResult();
        $q->clear();

        $task = new CTask();
        $project_scheduled_hours = $task->getHoursScheduled($project_id);

        $q->addTable('projects');
        $q->addUpdate('project_percent_complete', $project_percent_complete);
        $q->addUpdate('project_scheduled_hours', $project_scheduled_hours);
        $q->addWhere('project_id = ' . $project_id);
        $q->exec();

        global $AppUI;
        CTask::storeTokenTask($AppUI, $project_id);
    }

    /**
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
    public function getTaskLogs($notUsed = null, $projectId, $user_id = 0, $hide_inactive = false, $hide_complete = false, $cost_code = 0)
    {
        $q = $this->_getQuery();
		$q->addTable('task_log');
        $q->addTable('projects', 'pr');
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
		$q = $this->setAllowedSQL($this->_AppUI->user_id, $q, 'task_project');

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
        $q->addQuery('DISTINCT(projects.project_id), project_name, project_parent, project_company');
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
        $q = $obj->setAllowedSQL($this->_AppUI->user_id, $q);

        $dpt = new CDepartment();
        $dpt->overrideDatabase($this->_query);
        $q = $dpt->setAllowedSQL($this->_AppUI->user_id, $q);

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
        $level++;
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

    public function getProjectsByStatus($company_id = 0)
    {
        $q = $this->_getQuery();
        $q->addTable('projects', 'pr');
        $q->addQuery('project_status, count(*) as count');
        $q->addWhere('project_active = 1');
        if ($company_id > 0) {
            $q->addWhere('project_company = ' . $company_id);
        }
        $q->addGroup('project_status');
        $q = $this->setAllowedSQL($this->_AppUI->user_id, $q, 'project_company');

        $statuses = $q->loadList(-1, 'project_status');
        foreach ($statuses as $key => $array) {
            $statuses[$key] = $array['count'];
        }

        return $statuses;
    }
}
