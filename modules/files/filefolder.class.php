<?php

/**
 * File Folder Class
 */
class CFileFolder extends w2p_Core_BaseObject {
	/**
 	@param int file_folder_id **/
	public $file_folder_id = null;
	/**
 	@param int file_folder_parent The id of the parent folder **/
	public $file_folder_parent = null;
	/**
 	@param string file_folder_name The folder's name **/
	public $file_folder_name = null;
	/**
 	@param string file_folder_description The folder's description **/
	public $file_folder_description = null;

	public function __construct() {
        parent::__construct('file_folders', 'file_folder_id');
	}

	public function getAllowedRecords($uid) {
		global $AppUI;

        $q = $this->_query;
		$q->addTable('file_folders');
		$q->addQuery('*');
		$q->addOrder('file_folder_parent');
		$q->addOrder('file_folder_name');
		return $q->loadHashList();
	}

	public function delete(CAppUI $AppUI) {
        $perms = $AppUI->acl();

        $this->_error = $this->canDelete(null, $this->file_folder_id);
        if (count($this->_error)) {
            return $this->_error;
        }

        if ($perms->checkModuleItem('files', 'edit')) {
            if ($msg = parent::delete()) {
                return $msg;
            }
            return true;
        }
        return false;
	}

	public function canDelete($msg, $oid = 0, $joins = null) {
        $msg = array();

		$q = $this->_query;
		$q->addTable('file_folders');
		$q->addQuery('COUNT(DISTINCT file_folder_id) AS num_of_subfolders');
		$q->addWhere('file_folder_parent=' . $oid);
		$res1 = $q->loadResult();
		if ($res1) {
			$msg[] = "Can't delete folder, it has subfolders.";//') . ': ' . implode(', ', $msg);
		}
		$q->clear();

		$q = $this->_query;
		$q->addTable('files');
		$q->addQuery('COUNT(DISTINCT file_id) AS num_of_files');
		$q->addWhere('file_folder=' . $oid);
		$res2 = $q->loadResult();
		if ($res2) {
			$msg[] = "Can't delete folder, it has files within it.";//') . ': ' . implode(', ', $msg);
		}

		return $msg;
	}


    public function store(CAppUI $AppUI) {
        $perms = $AppUI->acl();
        $stored = false;
        $this->file_folder_id = (int) $this->file_folder_id;
		$this->file_folder_parent = (int) $this->file_folder_parent;

        $this->_error = $this->check();

        if (count($this->_error)) {
            return $this->_error;
        }

        /*
         * TODO: I don't like the duplication on each of these two branches, but I
         *   don't have a good idea on how to fix it at the moment...
         */
        if ($this->file_folder_id && $perms->checkModuleItem('files', 'edit')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->file_folder_id && $perms->checkModuleItem('files', 'add')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
    }

	/**
 	@return string Returns the name of the parent folder or null if no parent was found **/
	public function getParentFolderName() {

        $q = $this->_query;
		$q->addTable('file_folders');
		$q->addQuery('file_folder_name');
		$q->addWhere('file_folder_id=' . $this->file_folder_parent);

		return $q->loadResult();
	}

	public function countFolders() {

        $q = $this->_query;
		$q->addTable('file_folders');
		$q->addQuery('COUNT(file_folder_id)');

		return (int) $q->loadResult();
	}

    public function getFileCountByFolder(CAppUI $AppUI, $folder_id, $task_id, $project_id, $company_id) {

        // SQL text for count the total recs from the selected option
        $q = $this->_query;
        $q->addTable('files');
        $q->addQuery('count(files.file_id)');
        $q->addJoin('projects', 'p', 'p.project_id = file_project');
        $q->addJoin('users', 'u', 'u.user_id = file_owner');
        $q->addJoin('tasks', 't', 't.task_id = file_task');
        $q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
        $q->addWhere('file_folder = ' . (int)$folder_id);

        //TODO: apply permissions properly
        $project = new CProject();
        $deny1 = $project->getDeniedRecords($AppUI->user_id);
        if (count($deny1) > 0) {
            $q->addWhere('file_project NOT IN (' . implode(',', $deny1) . ')');
        }
        //TODO: apply permissions properly
        $task = new CTask();
        $deny2 = $task->getDeniedRecords($AppUI->user_id);
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

        $q = $this->_query;
        $q->addTable('file_folders');
        $q->addQuery('*');
        $q->addWhere('file_folder_parent = '. (int) $parent);
        $q->addOrder('file_folder_name');

        return $q->loadList();
    }
}