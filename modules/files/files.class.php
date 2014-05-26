<?php
/**
 * @package     web2project\modules\core
 *
 * @todo    refactor static methods
 */

class CFile extends w2p_Core_BaseObject {

    public $file_id = null;
    public $file_version_id = null;
    public $file_project = null;
    public $file_real_filename = null;
    public $file_task = null;
    public $file_name = null;
    public $file_parent = null;
    public $file_description = null;
    public $file_type = null;
    public $file_owner = null;
    // @todo this should be file_datetime to take advantage of our templating
    public $file_date = null;
    public $file_size = null;
    public $file_version = null;
    public $file_icon = null;
    public $file_category = null;
    public $file_folder = null;
    public $file_checkout = null;
    public $file_co_reason = null;
    public $file_indexed = null;

    protected $indexer = false;
    protected $_file_id = 0;
    protected $_file_system = null;
    // This "breaks" check-in/upload if helpdesk is not present class variable needs to be added "dymanically"
    //public $file_helpdesk_item = NULL;

    public function __construct() {
        parent::__construct('files', 'file_id');
    }

    public function setFileSystem($filesystem)
    {
        $this->_file_system = $filesystem;
    }

    public function getFileSystem()
    {
        if (is_null($this->_file_system)) {
            $this->setFileSystem(new w2p_FileSystem_Local());
        }

        return $this->_file_system;
    }

    protected function hook_preStore() {
        $this->file_parent = (int) $this->file_parent;
        $this->file_owner = (int) $this->file_owner ? $this->file_owner : $this->_AppUI->user_id;

        parent::hook_preStore();
    }

    protected function hook_preCreate() {
        $q = $this->_getQuery();
        $q->addTable('files');

        $this->file_owner = $this->_AppUI->user_id;
        if (!$this->file_version_id) {
            $q->addQuery('file_version_id');
            $q->addOrder('file_version_id DESC');
            $q->setLimit(1);
            $latest_file_version = $q->loadResult();
            $this->file_version_id = $latest_file_version + 1;
        } else {
            $q->addUpdate('file_checkout', '');
            $q->addWhere('file_version_id = ' . (int)$this->file_version_id);
            $q->exec();
        }

        $this->file_date = $q->dbfnNowWithTZ();
        parent::hook_preCreate();
    }

    /*
     * If while editing a file we attach a new file, then we go ahead and set
     *   file_id to 0 so a new file object is created. We also set its owner to
     *   the current user.
     * If not then we are just editing the file information alone. So we should
     *   leave the file_id as it is.
     */
    protected function hook_preUpdate() {
        $this->file_parent = $this->file_id;
        if ((int)$this->file_size > 0) {
            $this->file_id = 0;
            $this->file_owner = $this->_AppUI->user_id;
        }
        parent::hook_preUpdate();
    }

    public function hook_cron()
    {
        $this->indexer = true;
        $q = $this->_getQuery();
        $q->addQuery('file_id, file_name');
        $q->addTable('files');
        $q->addWhere('file_indexed = 0');
        $unindexedFiles = $q->loadList(5, 'file_id');

        foreach($unindexedFiles as $file_id => $notUsed) {
            $this->load($file_id);

            $indexer = new w2p_FileSystem_Indexer($this->_getQuery());
            $indexer->index($this);
        }
        $this->indexer = false;
    }

    public function hook_search()
    {
        $search['table'] = 'files';
        $search['table_alias'] = 'f';
        $search['table_module'] = 'files';
        $search['table_key'] = 'f.file_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=files&a=view&file_id='; // first part of link
        $search['table_title'] = 'Files';
        $search['table_orderby'] = 'file_name, word_placement';
        $search['search_fields'] = array('file_name', 'file_description',
            'file_type', 'file_version', 'file_co_reason', 'word');
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(array('table' => 'files_index',
            'alias' => 'fi', 'join' => 'f.file_id = fi.file_id'));

        return $search;
    }

    public static function getFileList($AppUI = null, $notUsed = 0, $project_id = 0, $task_id = 0, $category_id = 0) {
        $q = new w2p_Database_Query();
        $q->addQuery('f.*');
        $q->addTable('files', 'f');
        $q->addJoin('projects', 'p', 'p.project_id = file_project');
        $q->addJoin('project_departments', 'pd', 'p.project_id = pd.project_id');
        $q->addJoin('departments', '', 'pd.department_id = dept_id');
        $q->addJoin('tasks', 't', 't.task_id = file_task');

        $project = new CProject();
//TODO: We need to convert this from static to use ->overrideDatabase() for testing.
        $allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'file_project');
        if (count($allowedProjects)) {
            $q->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
        }

        if (isset($project_id) && (int) $project_id > 0) {
            $q->addWhere('file_project = ' . (int)$project_id);
        }
        if (isset($task_id) && (int) $task_id > 0) {
            $q->addWhere('file_task = ' . (int)$task_id);
        }
        if ($category_id >= 0) {
            $q->addWhere('file_category = ' . (int) $category_id);
        }

        return $q->loadList();
    }

    public function addHelpDeskTaskLog()
    {
        trigger_error("The CFiles->addHelpDeskTaskLog method has been deprecated in 3.2 and will be removed in v5.0. There is no replacement in core.", E_USER_NOTICE );

        return null;
    }

    public function canView()
    {
        return ($this->indexer || parent::canView());
    }

    public function canAdmin() {
        if (!$this->file_project) {
            return false;
        }
        if (!$this->file_id) {
            return false;
        }

        $project = new CProject();
        $project->project_id = $this->file_project;
        $project->load();

        return ($project->project_owner == $this->_AppUI->user_id);
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ($this->file_id == 0 && '' == $this->file_real_filename) {
            $this->_error['file_real_filename'] = $baseErrorMsg . 'file real name is not set';
        }
        if ($this->file_id == 0 && '' == $this->file_name) {
            $this->_error['file_name'] = $baseErrorMsg . 'file name is not set';
        }
        if ($this->file_id == 0 && !is_int($this->file_size) && '' == $this->file_size) {
            $this->_error['file_size'] = $baseErrorMsg . 'file size is not set';
        }
        if ($this->file_id == 0 && '' == $this->file_type) {
            $this->_error['file_type'] = $baseErrorMsg . 'file type is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    public function checkout($userId, $fileId, $coReason) {
        $q = $this->_getQuery();
        $q->addTable('files');
        $q->addUpdate('file_checkout', $userId);
        $q->addUpdate('file_co_reason', $coReason);
        $q->addWhere('file_id = ' . (int)$fileId);
        $q->exec();

        return true;
    }

    public function cancelCheckout($fileId) {
        $q = $this->_getQuery();
        $q->addTable('files');
        $q->addUpdate('file_checkout', '');
        $q->addWhere('file_id = ' . (int)$fileId);
        $q->exec();

        return true;

    }

    public function delete($unused = null)
    {
        $result = false;

        $this->_error = array();

        if ($this->canDelete()) {
            // remove the file from the file system
            if (!$this->deleteFile()) {
                $this->_error['file-delete'] = 'file-delete';
                return false;
            }

            $result = parent::delete();
        }
        return $result;
    }

    protected function hook_preDelete()
    {
        $this->_file_id = $this->file_id;

        parent::hook_preDelete();
    }

    protected function hook_postDelete()
    {
        $indexer = new w2p_FileSystem_Indexer($this->_getQuery());
        $indexer->clear($this->_old_key);

        parent::hook_postDelete();
    }

    //function notifies about file changing
    public function notify($notify) {
        if ($notify == '1') {
            //if no project specified than we will not do anything
            if ($this->file_project != 0) {
                $this->_project = new CProject();
                $this->_project->overrideDatabase($this->_query);
                $this->_project->load($this->file_project);
                $mail = new w2p_Utilities_Mail();

                if ($this->file_task == 0) { //notify all developers
                    $mail->Subject($this->_project->project_name . '::' . $this->file_name);
                } else { //notify all assigned users
                    $this->_task = new CTask();
                    $this->_task->overrideDatabase($this->_query);
                    $this->_task->load($this->file_task);
                    $mail->Subject($this->_project->project_name . '::' . $this->_task->task_name . '::' . $this->file_name);
                }

                $emailManager = new w2p_Output_EmailManager($this->_AppUI);
                $body = $emailManager->getFileNotify($this);
                $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

                $q = $this->_getQuery();
                if (intval($this->_task->task_id) != 0) {
                    //preparing users array
                    $q->addTable('tasks', 't');
                    $q->addQuery('t.task_id, cc.contact_email as creator_email, cc.contact_first_name as
                            creator_first_name, cc.contact_last_name as creator_last_name,
                            oc.contact_email as owner_email, oc.contact_first_name as owner_first_name,
                            oc.contact_last_name as owner_last_name, a.user_id as assignee_id,
                            ac.contact_email as assignee_email, ac.contact_first_name as
                            assignee_first_name, ac.contact_last_name as assignee_last_name');
                    $q->addJoin('user_tasks', 'u', 'u.task_id = t.task_id');
                    $q->addJoin('users', 'o', 'o.user_id = t.task_owner');
                    $q->addJoin('contacts', 'oc', 'o.user_contact = oc.contact_id');
                    $q->addJoin('users', 'c', 'c.user_id = t.task_creator');
                    $q->addJoin('contacts', 'cc', 'c.user_contact = cc.contact_id');
                    $q->addJoin('users', 'a', 'a.user_id = u.user_id');
                    $q->addJoin('contacts', 'ac', 'a.user_contact = ac.contact_id');
                    $q->addWhere('t.task_id = ' . (int)$this->_task->task_id);

                } else {
                    //find project owner and notify him about new or modified file
                    $q->addTable('users', 'u');
                    $q->addTable('projects', 'p');
                    $q->addQuery('u.user_id, u.user_contact AS owner_contact_id');
                    $q->addWhere('p.project_owner = u.user_id');
                    $q->addWhere('p.project_id = ' . (int)$this->file_project);
                }
                $this->_users = $q->loadList();

                if (intval($this->_task->task_id) != 0) {
                    foreach ($this->_users as $row) {
                        if ($row['assignee_id'] != $this->_AppUI->user_id) {
                            $mail->To($row['assignee_email'], true);
                            $mail->Send();
                        }
                    }
                } else { //sending mail to project owner
                    foreach ($this->_users as $row) { //there should be only one row
                        if ($row['user_id'] != $this->_AppUI->user_id) {
                            $mail->To($row['owner_email'], true);
                            $mail->Send();
                        }
                    }
                }
            }
        }
    } //notify

    public function notifyContacts($notifyContacts) {
        if ($notifyContacts) {
            //if no project specified than we will not do anything
            if ($this->file_project != 0) {
                $this->_project = new CProject();
                $this->_project->overrideDatabase($this->_query);
                $this->_project->load($this->file_project);
                $mail = new w2p_Utilities_Mail();

                if ($this->file_task == 0) { //notify all developers
                    $mail->Subject($this->_AppUI->_('Project') . ': ' . $this->_project->project_name . '::' . $this->file_name);
                } else { //notify all assigned users
                    $this->_task = new CTask();
                    $this->_task->overrideDatabase($this->_query);
                    $this->_task->load($this->file_task);
                    $mail->Subject($this->_AppUI->_('Project') . ': ' . $this->_project->project_name . '::' . $this->_task->task_name . '::' . $this->file_name);
                }

                $emailManager = new w2p_Output_EmailManager($this->_AppUI);
                $body = $emailManager->getFileNotifyContacts($this);
                $mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

                $q = $this->_getQuery();
                $q->addTable('project_contacts', 'pc');
                if (intval($this->_task->task_id) != 0) {
                    $q->addQuery('c.contact_email as contact_email, c.contact_first_name as contact_first_name, c.contact_last_name as contact_last_name');
                    $q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
                    $q->addWhere('pc.project_id = ' . (int)$this->_project->project_id);
                    $sql = '(' . $q->prepare() . ')';
                    $q->clear();
                    $sql .= ' UNION ';
                    $q->addTable('task_contacts', 'tc');
                    $q->addQuery('c.contact_email as contact_email, c.contact_first_name as contact_first_name, c.contact_last_name as contact_last_name');
                    $q->addJoin('contacts', 'c', 'c.contact_id = tc.contact_id');
                    $q->addWhere('tc.task_id = ' . (int)$this->_task->task_id);
                } else {
                    $q->addQuery('pc.project_id, pc.contact_id');
                    $q->addQuery('c.contact_email as contact_email, c.contact_first_name as contact_first_name, c.contact_last_name as contact_last_name');
                    $q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
                    $q->addWhere('pc.project_id = ' . (int)$this->file_project);
                }
                $this->_users = $q->loadList();

                foreach ($this->_users as $row) {
                    $mail->To($row['contact_email'], true);
                    $mail->Send();
                }
            }
        }
    }

    public function getOwner()
    {
        trigger_error("The CFile->getOwner method has been deprecated in v3.2 and will be removed in v5.0. Please use just load a CContact object instead", E_USER_NOTICE );

        $contact = new CContact();
        $contact->findContactByUserid((int) $this->file_owner);

        return $contact->contact_display_name;
    }

    /** @deprecated */
    public function getTaskName() {
        trigger_error("The CFile->getTaskName method has been deprecated in v3.0 and will be removed in v4.0. Please use just load a CTask object instead", E_USER_NOTICE );

        $task = new CTask();
        $task->load((int)$this->file_task);

        return $task->task_name;
    }

    /** @deprecated */
    public function indexStrings()
    {
        trigger_error("CFile->indexStrings() has been deprecated in v3.2 and will be removed by v5.0. Please use w2p_FileSystem_Indexer->index() instead.", E_USER_NOTICE);

        $indexer = new w2p_FileSystem_Indexer($this->_getQuery());
        $indexer->index($this);
    }

    /** @deprecated */
    public function isWritable()
    {
        return $this->getFileSystem()->isWritable();
    }

    /** @deprecated */
    public function deleteFile()
    {
        $this->load();
        return $this->getFileSystem()->delete($this);
    }

    /** @deprecated */
    public function moveFile($oldProj, $realname)
    {
        return $this->getFileSystem()->move($this, $oldProj, $realname);
    }

    /** @deprecated */
    public function duplicateFile($oldProj, $realname)
    {
        return $this->getFileSystem()->duplicate($oldProj, $realname, $this->_AppUI);
    }

    /** @deprecated */
    public function moveTemp($upload)
    {
        return $this->getFileSystem()->moveTemp($this, $upload, $this->_AppUI);
    }
}