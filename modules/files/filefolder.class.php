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

        $q = new w2p_Database_Query();
		$q->addTable('file_folders');
		$q->addQuery('*');
		$q->addOrder('file_folder_parent');
		$q->addOrder('file_folder_name');
		return $q->loadHashList();
	}

	public function check() {
		$this->file_folder_id = intval($this->file_folder_id);
		$this->file_folder_parent = intval($this->file_folder_parent);
		return null;
	}

	public function delete(CAppUI $AppUI) {
        $perms = $AppUI->acl();

        $errorMsgArray = $this->canDelete(null, $this->file_folder_id);
        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
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

		$q = new w2p_Database_Query();
		$q->addTable('file_folders');
		$q->addQuery('COUNT(DISTINCT file_folder_id) AS num_of_subfolders');
		$q->addWhere('file_folder_parent=' . $oid);
		$res1 = $q->loadResult();
		if ($res1) {
			$msg[] = "Can't delete folder, it has subfolders.";//') . ': ' . implode(', ', $msg);
		}
		$q->clear();

		$q = new w2p_Database_Query();
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

        $errorMsgArray = $this->check();

        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
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
		$q = new w2p_Database_Query();
		$q->addTable('file_folders');
		$q->addQuery('file_folder_name');
		$q->addWhere('file_folder_id=' . $this->file_folder_parent);

		return $q->loadResult();
	}

	public function countFolders() {
		$q = new w2p_Database_Query();
		$q->addTable('file_folders');
		$q->addQuery('COUNT(file_folder_id)');

		return (int) $q->loadResult();
	}

    public function getFoldersByParent($parent = 0) {
        $q = new w2p_Database_Query();
        $q->addTable('file_folders');
        $q->addQuery('*');
        $q->addWhere('file_folder_parent = '. (int) $parent);
        $q->addOrder('file_folder_name');

        return $q->loadList();
    }
}
