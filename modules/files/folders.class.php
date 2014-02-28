<?php
/**
 * @package     web2project\modules\core
 */

class CFile_Folder extends w2p_Core_BaseObject
{
	public $file_folder_id = null;
	public $file_folder_parent = null;
	public $file_folder_name = null;
	public $file_folder_description = null;

	public function __construct() {
        parent::__construct('file_folders', 'file_folder_id', 'files');
	}

	public function getAllowedRecords($uid, $fields = '*',
          $orderby = 'file_folder_parent, file_folder_name', $unused = null, $unused2 = null, $unused3 = '')
    {
        $q = $this->_getQuery();
		$q->addTable('file_folders');
		$q->addQuery($fields);
        $orderby = (property_exists($this, $orderby)) ? $orderby : 'file_folder_name';
		$q->addOrder($orderby);
		return $q->loadHashList();
	}

    public function canDelete($notUsed = null, $notUsed2 = null, $notUsed3 = null)
    {
		$q = $this->_getQuery();
		$q->addTable('file_folders');
		$q->addQuery('COUNT(DISTINCT file_folder_id) AS num_of_subfolders');
		$q->addWhere('file_folder_parent=' . (int) $this->file_folder_id);
		$res1 = $q->loadResult();
		if ($res1) {
            $this->_error['subfolders'] = "Can't delete folder, it has subfolders.";
		}

		$q = $this->_getQuery();
		$q->addTable('files');
		$q->addQuery('COUNT(DISTINCT file_id) AS num_of_files');
		$q->addWhere('file_folder=' . (int) $this->file_folder_id);
		$res2 = $q->loadResult();
		if ($res2) {
            $this->_error['files'] = "Can't delete folder, it has files within it.";
		}

		return (count($this->_error)) ? false : true;
	}

    /**
     * This needs a separate canEdit and canView instead of the BaseObject one because the CFile_Folder object
     *   doesn't support separate permissions from the Files module itself.
     *
     * @return boolean
     */
    public function canEdit()
    {
        return $this->_perms->checkModuleItem($this->_tbl_module, 'edit');
    }
    public function canView()
    {
        return $this->canAccess();
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->file_folder_name)) {
            $this->_error['file_folder_name'] = $baseErrorMsg . 'folder name is not set';
        }

        return (count($this->_error)) ? false : true;
    }

    protected function  hook_preStore() {
        $this->file_folder_id = (int) $this->file_folder_id;
		$this->file_folder_parent = (int) $this->file_folder_parent;

        parent::hook_preStore();
    }

	/**
 	@return string Returns the name of the parent folder or null if no parent was found **/
	public function getParentFolderName() {

        $q = $this->_getQuery();
		$q->addTable('file_folders');
		$q->addQuery('file_folder_name');
		$q->addWhere('file_folder_id=' . $this->file_folder_parent);

		return $q->loadResult();
	}

	public function countFolders() {

        $q = $this->_getQuery();
		$q->addTable('file_folders');
		$q->addQuery('COUNT(file_folder_id)');

		return (int) $q->loadResult();
	}

    public function getFileCountByFolder($notUsed = null, $folder_id,
            $task_id, $project_id, $company_id, $allowed_companies) {

        // SQL text for count the total recs from the selected option
        $q = $this->_getQuery();
        $q->addTable('files');
        $q->addQuery('count(files.file_id)');
        $q->addJoin('projects', 'p', 'p.project_id = file_project');
        $q->addJoin('users', 'u', 'u.user_id = file_owner');
        $q->addJoin('tasks', 't', 't.task_id = file_task');
        $q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
        $q->addWhere('file_folder = ' . (int)$folder_id);

        //TODO: apply permissions properly
        $project = new CProject();
        $project->overrideDatabase($this->_query);
        $deny1 = $project->getDeniedRecords($this->_AppUI->user_id);
        if (count($deny1) > 0) {
            $q->addWhere('file_project NOT IN (' . implode(',', $deny1) . ')');
        }
        //TODO: apply permissions properly
        $task = new CTask();
        $task->overrideDatabase($this->_query);
        $deny2 = $task->getDeniedRecords($this->_AppUI->user_id);
        if (count($deny2) > 0) {
            $q->addWhere('file_task NOT IN (' . implode(',', $deny2) . ')');
        }
        if ($project_id) {
            $q->addWhere('file_project = ' . (int)$project_id);
        }
        if ($task_id) {
            $q->addWhere('file_task = ' . (int)$task_id);
        }
        if ($company_id) {
            $q->innerJoin('companies', 'co', 'co.company_id = p.project_company');
            $q->addWhere('company_id = ' . (int)$company_id);
            $q->addWhere('company_id IN (' . $allowed_companies . ')');
        }

        $q->addGroup('file_folder_name');
        $q->addGroup('project_name');
        $q->addGroup('file_name');

        // counts total recs from selection
        return count($q->loadList());
    }

    public function getFoldersByParent($parent = 0) {

        $q = $this->_getQuery();
        $q->addTable('file_folders');
        $q->addQuery('*');
        $q->addWhere('file_folder_parent = '. (int) $parent);
        $q->addOrder('file_folder_name');

        return $q->loadList();
    }
}