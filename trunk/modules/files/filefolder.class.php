<?php

/**
 * File Folder Class
 */
class CFileFolder extends CW2pObject {
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

        $q = new DBQuery();
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

	public function delete($oid = null) {
        global $AppUI;

        $k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		if (!$this->canDelete($msg, ($oid ? $oid : $this->file_folder_id))) {
			return $msg;
		}
		$this->$k = $this->$k ? $this->$k : intval(($oid ? $oid : $this->file_folder_id));

		$q = new DBQuery();
		$q->setDelete($this->_tbl);
		$q->addWhere($this->_tbl_key . ' = ' . $this->$k);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		} else {
			$q->clear();
			return null;
		}
	}

	public function canDelete(&$msg, $oid = null, $joins = null) {
		global $AppUI;

		$q = new DBQuery();
		$q->addTable('file_folders');
		$q->addQuery('COUNT(DISTINCT file_folder_id) AS num_of_subfolders');
		$q->addWhere('file_folder_parent=' . $oid);
		$res1 = $q->loadResult();
		$q->clear();

		$q = new DBQuery();
		$q->addTable('files');
		$q->addQuery('COUNT(DISTINCT file_id) AS num_of_files');
		$q->addWhere('file_folder=' . $oid);
		$res2 = $q->loadResult();
		$q->clear();
		if (($res1 > 0) || ($res2 > 0)) {
			$msg[] = 'File Folders';
			$msg = $AppUI->_('Can\'t delete folder, it has files and/or subfolders.') . ': ' . implode(', ', $msg);
			return false;
		}
		return true;
	}

	/**
 	@return string Returns the name of the parent folder or null if no parent was found **/
	public function getParentFolderName() {
		$q = new DBQuery();
		$q->addTable('file_folders');
		$q->addQuery('file_folder_name');
		$q->addWhere('file_folder_id=' . $this->file_folder_parent);
		return $q->loadResult();
	}

	public function countFolders() {
		$q = new DBQuery();
		$q->addTable($this->_tbl);
		$q->addQuery('COUNT(' . $this->_tbl_key. ' )');
		$result = $q->loadResult();
		return $result;
	}
}
