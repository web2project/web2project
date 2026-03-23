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
    public $file_datetime = null;
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

        $this->file_datetime = $q->dbfnNowWithTZ();
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
        $search['table_orderby'] = 'file_name';
        $search['search_fields'] = array('file_name', 'file_description',
            'file_type', 'file_version', 'file_co_reason', 'word');
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(array('table' => 'files_index',
            'alias' => 'fi', 'join' => 'f.file_id = fi.file_id'));

        return $search;
    }

    public function list($filter = [])
    {
        $q = new w2p_Database_Query();
        $q->addQuery('f.*');
        $q->addTable('files', 'f');
        $q->addQuery('project_name, project_color_identifier'); 
        $q->addJoin('projects', 'p', 'p.project_id = file_project');

        // todo: add permissions

        foreach($filter as $key => $value) {
            switch ($key) {
                case 'category':
                    if ($value > -1) {
                        $q->addWhere('file_category = ' . (int) $value);
                    }
                    break;
                // todo: add a case for folders
                default:
                    if ($value > 0) {
                        $q->addWhere("file_$key = " . $value);
                    }
            }
        }
        return $q->loadList();
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

    /**
     * @deprecated
     */
    public function checkout($userId, $fileId, $coReason) {
        return true;
    }

    /**
     * @deprecated
     */
    public function cancelCheckout($fileId) {
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

    /**
     * @deprecated
     */
    public function notify($notify) {
        return true;
    }

    /**
     * @deprecated
     */
    public function notifyContacts($notifyContacts) {
        return true;
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