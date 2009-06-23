<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once ($AppUI->getSystemClass('libmail'));
require_once ($AppUI->getSystemClass('w2p'));
require_once ($AppUI->getSystemClass('date'));
require_once ($AppUI->getModuleClass('tasks'));
require_once ($AppUI->getModuleClass('projects'));
global $helpdesk_available;

if ($helpdesk_available = $AppUI->isActiveModule('helpdesk')) {
	require_once ($AppUI->getModuleClass('helpdesk'));
}
/**
 * File Class
 */
class CFile extends CW2pObject {

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
	public $file_date = null;
	public $file_size = null;
	public $file_version = null;
	public $file_category = null;
	public $file_folder = null;
	public $file_checkout = null;
	public $file_co_reason = null;

	// This "breaks" check-in/upload if helpdesk is not present class variable needs to be added "dymanically"
	//public $file_helpdesk_item = NULL;

	function CFile() {
		global $AppUI, $helpdesk_available;
		if ($helpdesk_available) {
			$this->file_helpdesk_item = null;
		}
		$this->CW2pObject('files', 'file_id');
	}

	function store() {
		global $helpdesk_available;
		if ($helpdesk_available && $this->file_helpdesk_item != 0) {
			$this->addHelpDeskTaskLog();
		}
		parent::store();
	}

	function addHelpDeskTaskLog() {
		global $AppUI, $helpdesk_available;
		if ($helpdesk_available && $this->file_helpdesk_item != 0) {

			// create task log with information about the file that was uploaded
			$task_log = new CHDTaskLog();
			$task_log->task_log_help_desk_id = $this->_hditem->item_id;
			if ($this->_message != 'deleted') {
				$task_log->task_log_name = 'File ' . $this->file_name . ' uploaded';
			} else {
				$task_log->task_log_name = 'File ' . $this->file_name . ' deleted';
			}
			$task_log->task_log_description = $this->file_description;
			$task_log->task_log_creator = $AppUI->user_id;
			$date = new CDate();
			$task_log->task_log_date = $date->format(FMT_DATETIME_MYSQL);
			if ($msg = $task_log->store()) {
				$AppUI->setMsg($msg, UI_MSG_ERROR);
			}
		}
		return null;
	}

	function canAdmin() {
		global $AppUI;

		if (!$this->file_project) {
			return false;
		}
		if (!$this->file_id) {
			return false;
		}

		$result = false;
		$this->_query->clear();
		$this->_query->addTable('projects');
		$this->_query->addQuery('project_owner');
		$this->_query->addWhere('project_id = ' . (int)$this->file_project);
		$res = $this->_query->exec(ADODB_FETCH_ASSOC);
		if ($res && $row = $this->_query->fetchRow()) {
			if ($row['project_owner'] == $AppUI->user_id) {
				$result = true;
			}
		}
		$this->_query->clear();
		return $result;
	}

	function check() {
		// ensure the integrity of some variables
		$this->file_id = intval($this->file_id);
		$this->file_version_id = intval($this->file_version_id);
		$this->file_parent = intval($this->file_parent);
		$this->file_task = intval($this->file_task);
		$this->file_project = intval($this->file_project);

		return null; // object is ok
	}

	function checkout($userId, $fileId, $coReason) {
		$q = new DBQuery;
		$q->addTable('files');
		$q->addUpdate('file_checkout', $userId);
		$q->addUpdate('file_co_reason', $coReason);
		$q->addWhere('file_id = ' . (int)$fileId);
		$q->exec();
		$q->clear();

		return true;
	}

	function delete() {
		global $helpdesk_available;
		if (!$this->canDelete($msg))
			return $msg;
		$this->_message = 'deleted';
		addHistory('files', $this->file_id, 'delete', $this->file_name, $this->file_project);
		// remove the file from the file system
		$this->deleteFile();
		// delete any index entries
		$q = new DBQuery;
		$q->setDelete('files_index');
		$q->addQuery('*');
		$q->addWhere('file_id = ' . (int)$this->file_id);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		}
		// delete the main table reference
		$q->clear();
		$q->setDelete('files');
		$q->addQuery('*');
		$q->addWhere('file_id = ' . (int)$this->file_id);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		}
		$q->clear();

		if ($helpdesk_available && $this->file_helpdesk_item != 0) {
			$this->addHelpDeskTaskLog();
		}
		return null;
	}

	// delete File from File System
	function deleteFile() {
		global $w2Pconfig;
		return @unlink(W2P_BASE_DIR . '/files/' . $this->file_project . '/' . $this->file_real_filename);
	}

	// move the file if the affiliated project was changed
	function moveFile($oldProj, $realname) {
		global $AppUI, $w2Pconfig;
		if (!is_dir(W2P_BASE_DIR . '/files/' . $this->file_project)) {
			$res = mkdir(W2P_BASE_DIR . '/files/' . $this->file_project, 0777);
			if (!$res) {
				$AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
				return false;
			}
		}
		$res = rename(W2P_BASE_DIR . '/files/' . $oldProj . '/' . $realname, W2P_BASE_DIR . '/files/' . $this->file_project . '/' . $realname);

		if (!$res) {
			return false;
		}
		return true;
	}

	// duplicate a file into root
	function duplicateFile($oldProj, $realname) {
		global $AppUI, $w2Pconfig;
		if (!is_dir(W2P_BASE_DIR . '/files/0')) {
			$res = mkdir(W2P_BASE_DIR . '/files/0', 0777);
			if (!$res) {
				$AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
				return false;
			}
		}
		$dest_realname = uniqid(rand());
		$res = copy(W2P_BASE_DIR . '/files/' . $oldProj . '/' . $realname, W2P_BASE_DIR . '/files/0/' . $dest_realname);

		if (!$res) {
			return false;
		}
		return $dest_realname;
	}

	// move a file from a temporary (uploaded) location to the file system
	function moveTemp($upload) {
		global $AppUI, $w2Pconfig;
		// check that directories are created
		if (!is_dir(W2P_BASE_DIR . '/files')) {
			$res = mkdir(W2P_BASE_DIR . '/files', 0777);
			if (!$res) {
				return false;
			}
		}
		if (!is_dir(W2P_BASE_DIR . '/files/' . $this->file_project)) {
			$res = mkdir(W2P_BASE_DIR . '/files/' . $this->file_project, 0777);
			if (!$res) {
				$AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
				return false;
			}
		}

		$this->_filepath = W2P_BASE_DIR . '/files/' . $this->file_project . '/' . $this->file_real_filename;
		// move it
		$res = move_uploaded_file($upload['tmp_name'], $this->_filepath);
		if (!$res) {
			return false;
		}
		return true;
	}

	// parse file for indexing
	function indexStrings() {
		global $AppUI, $w2Pconfig;
		// get the parser application
		$parser = $w2Pconfig['parser_' . $this->file_type];
		if (!$parser)
			$parser = $w2Pconfig['parser_default'];
		if (!$parser)
			return false;
		// buffer the file
		$this->_filepath = W2P_BASE_DIR . '/files/' . $this->file_project . '/' . $this->file_real_filename;
		$fp = fopen($this->_filepath, 'rb');
		$x = fread($fp, $this->file_size);
		fclose($fp);
		// parse it
		$parser = $parser . ' ' . $this->_filepath;
		$pos = strpos($parser, '/pdf');
		if (false !== $pos) {
			$x = `$parser -`;
		} else {
			$x = `$parser`;
		}
		// if nothing, return
		if (strlen($x) < 1) {
			return 0;
		}
		// remove punctuation and parse the strings
		$x = str_replace(array('.', ',', '!', '@', '(', ')'), ' ', $x);
		$warr = split('[[:space:]]', $x);

		$wordarr = array();
		$nwords = count($warr);
		for ($x = 0; $x < $nwords; $x++) {
			$newword = $warr[$x];
			if (!ereg('[[:punct:]]', $newword) && strlen(trim($newword)) > 2 && !ereg('[[:digit:]]', $newword)) {
				$wordarr[] = array('word' => $newword, 'wordplace' => $x);
			}
		}
		// filter out common strings
		$ignore = array();
		include W2P_BASE_DIR . '/modules/files/file_index_ignore.php';
		foreach ($ignore as $w) {
			unset($wordarr[$w]);
		}
		// insert the strings into the table
		while (list($key, $val) = each($wordarr)) {
			$q = new DBQuery;
			$q->addTable('files_index');

			$q->addReplace('file_id', $this->file_id);
			$q->addReplace('word', $wordarr[$key]['word']);
			$q->addReplace('word_placement', $wordarr[$key]['wordplace']);
			$q->exec();
			$q->clear();
		}

		return nwords;
	}

	//function notifies about file changing
	function notify() {
		global $AppUI, $w2Pconfig, $locale_char_set, $helpdesk_available;
		// if helpdesk_item is available send notification to assigned users
		if ($helpdesk_available && $this->file_helpdesk_item != 0) {
			$this->_hditem = new CHelpDeskItem();
			$this->_hditem->load($this->file_helpdesk_item);

			$task_log = new CHDTaskLog();
			$task_log_help_desk_id = $this->_hditem->item_id;
			// send notifcation about new log entry
			// 2 = TASK_LOG
			$this->_hditem->notify(2, $task_log->task_log_id);

		}
		//if no project specified than we will not do anything
		if ($this->file_project != 0) {
			$this->_project = new CProject();
			$this->_project->load($this->file_project);
			$mail = new Mail;

			if ($this->file_task == 0) { //notify all developers
				$mail->Subject($this->_project->project_name . '::' . $this->file_name, $locale_char_set);
			} else { //notify all assigned users
				$this->_task = new CTask();
				$this->_task->load($this->file_task);
				$mail->Subject($this->_project->project_name . '::' . $this->_task->task_name . '::' . $this->file_name, $locale_char_set);
			}

			$body = $AppUI->_('Project') . ': ' . $this->_project->project_name;
			$body .= "\n" . $AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id=' . $this->_project->project_id;

			if (intval($this->_task->task_id) != 0) {
				$body .= "\n\n" . $AppUI->_('Task') . ':    ' . $this->_task->task_name;
				$body .= "\n" . $AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $this->_task->task_id;
				$body .= "\n" . $AppUI->_('Description') . ':' . "\n" . $this->_task->task_description;

				//preparing users array
				$q = new DBQuery;
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
				$this->_users = $q->loadList();
			} else {
				//find project owner and notify him about new or modified file
				$q = new DBQuery;
				$q->addTable('users', 'u');
				$q->addTable('projects', 'p');
				$q->addQuery('u.*');
				$q->addWhere('p.project_owner = u.user_id');
				$q->addWhere('p.project_id = ' . (int)$this->file_project);
				$this->_users = $q->loadList();
			}
			$body .= "\n\nFile " . $this->file_name . ' was ' . $this->_message . ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			if ($this->_message != 'deleted') {
				$body .= "\n" . $AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/fileviewer.php?file_id=' . $this->file_id;
				$body .= "\n" . $AppUI->_('Description') . ':' . "\n" . $this->file_description;
			}

			//send mail
			$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

			if (intval($this->_task->task_id) != 0) {
				foreach ($this->_users as $row) {
					if ($row['assignee_id'] != $AppUI->user_id) {
						if ($mail->ValidEmail($row['assignee_email'])) {
							$mail->To($row['assignee_email'], true);
							$mail->Send();
						}
					}
				}
			} else { //sending mail to project owner
				foreach ($this->_users as $row) { //there should be only one row
					if ($row['user_id'] != $AppUI->user_id) {
						if ($mail->ValidEmail($row['user_email'])) {
							$mail->To($row['user_email'], true);
							$mail->Send();
						}
					}
				}
			}
		}
	} //notify

	function notifyContacts() {
		global $AppUI, $w2Pconfig, $locale_char_set;
		//if no project specified than we will not do anything
		if ($this->file_project != 0) {
			$this->_project = new CProject();
			$this->_project->load($this->file_project);
			$mail = new Mail;

			if ($this->file_task == 0) { //notify all developers
				$mail->Subject($AppUI->_('Project') . ': ' . $this->_project->project_name . '::' . $this->file_name, $locale_char_set);
			} else { //notify all assigned users
				$this->_task = new CTask();
				$this->_task->load($this->file_task);
				$mail->Subject($AppUI->_('Project') . ': ' . $this->_project->project_name . '::' . $this->_task->task_name . '::' . $this->file_name, $locale_char_set);
			}

			$body = $AppUI->_('Project') . ': ' . $this->_project->project_name;
			$body .= "\n" . $AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=projects&a=view&project_id=' . $this->_project->project_id;

			if (intval($this->_task->task_id) != 0) {
				$body .= "\n\n" . $AppUI->_('Task') . ':    ' . $this->_task->task_name;
				$body .= "\n" . $AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/index.php?m=tasks&a=view&task_id=' . $this->_task->task_id;
				$body .= "\n" . $AppUI->_('Description') . ":\n" . $this->_task->task_description;

				$q = new DBQuery;
				$q->addTable('project_contacts', 'pc');
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
				$sql .= '(' . $q->prepare() . ')';
				$q->clear();
				$this->_users = $q->loadList();
			} else {
				$q = new DBQuery;
				$q->addTable('project_contacts', 'pc');
				$q->addQuery('pc.project_id, pc.contact_id');
				$q->addQuery('c.contact_email as contact_email, c.contact_first_name as contact_first_name, c.contact_last_name as contact_last_name');
				$q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
				$q->addWhere('pc.project_id = ' . (int)$this->file_project);

				$this->_users = $q->loadList();
				$q->clear();
			}

			$body .= "\n\nFile " . $this->file_name . ' was ' . $this->_message . ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			if ($this->_message != 'deleted') {
				$body .= "\n" . $AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/fileviewer.php?file_id=' . $this->file_id;
				$body .= "\n" . $AppUI->_('Description') . ":\n" . $this->file_description;
			}

			//send mail
			$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');

			foreach ($this->_users as $row) {

				if ($mail->ValidEmail($row['contact_email'])) {
					$mail->To($row['contact_email'], true);
					$mail->Send();
				}
			}
			return '';
		}
	}

	function getOwner() {
		$owner = '';
		if (!$this->file_owner)
			return $owner;

		$this->_query->clear();
		$this->_query->addTable('users', 'a');
		$this->_query->addJoin('contacts', 'b', 'b.contact_id = a.user_contact', 'inner');
		$this->_query->addQuery('contact_first_name, contact_last_name');
		$this->_query->addWhere('a.user_id = ' . (int)$this->file_owner);
		if ($qid = &$this->_query->exec()) {
			$owner = $qid->fields['contact_first_name'] . ' ' . $qid->fields['contact_last_name'];
		}
		$this->_query->clear();

		return $owner;
	}

	function getTaskName() {
		$taskname = '';
		if (!$this->file_task)
			return $taskname;

		$this->_query->clear();
		$this->_query->addTable('tasks');
		$this->_query->addQuery('task_name');
		$this->_query->addWhere('task_id = ' . (int)$this->file_task);
		if ($qid = &$this->_query->exec()) {
			if ($qid->fields['task_name']) {
				$taskname = $qid->fields['task_name'];
			} else {
				$taskname = $qid->fields[0];
			}
		}
		$this->_query->clear();
		return $taskname;
	}

}

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

	function CFileFolder() {
		$this->CW2pObject('file_folders', 'file_folder_id');
	}

	function getAllowedRecords($uid) {
		$q = new DBQuery();
		$q->addTable('file_folders');
		$q->addQuery('*');
		$q->addOrder('file_folder_parent');
		$q->addOrder('file_folder_name');
		return $q->loadHashList();
	}

	function check() {
		$this->file_folder_id = intval($this->file_folder_id);
		$this->file_folder_parent = intval($this->file_folder_parent);
		return null;
	}

	function delete($oid = null) {
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

	function canDelete(&$msg, $oid) {
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
	function getParentFolderName() {
		$q = new DBQuery();
		$q->addTable('file_folders');
		$q->addQuery('file_folder_name');
		$q->addWhere('file_folder_id=' . $this->file_folder_parent);
		return $q->loadResult();
	}

	function countFolders() {
		$q = new DBQuery();
		$q->addTable($this->_tbl);
		$q->addQuery('COUNT(' . $this->_tbl_key. ' )');
		$result = $q->loadResult();
		return $result;
	}
}

function shownavbar($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page) {
	global $AppUI;
	$xpg_break = false;
	$xpg_prev_page = $xpg_next_page = 1;

	$s = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';

	if ($xpg_totalrecs > $xpg_pagesize) {
		$xpg_prev_page = $page - 1;
		$xpg_next_page = $page + 1;
		// left buttoms
		if ($xpg_prev_page > 0) {
			$s .= '<td align="left" width="15%"><a href="./index.php?m=files&amp;page=1"><img src="' . w2PfindImage('navfirst.gif') . '" border="0" Alt="First Page"></a>&nbsp;&nbsp;';
			$s .= '<a href="./index.php?m=files&amp;page=' . $xpg_prev_page . '"><img src="' . w2PfindImage('navleft.gif') . '" border="0" Alt="Previous page (' . $xpg_prev_page . ')"></a></td>';
		} else {
			$s .= '<td width="15%">&nbsp;</td>';
		}

		// central text (files, total pages, ...)
		$s .= '<td align="center" width="70%">' . $xpg_totalrecs . ' ' . $AppUI->_('File(s)') . ' (' . $xpg_total_pages . ' ' . $AppUI->_('Page(s)') . ')</td>';

		// right buttoms
		if ($xpg_next_page <= $xpg_total_pages) {
			$s .= '<td align="right" width="15%"><a href="./index.php?m=files&amp;page=' . $xpg_next_page . '"><img src="' . w2PfindImage('navright.gif') . '" border="0" Alt="Next Page (' . $xpg_next_page . ')"></a>&nbsp;&nbsp;';
			$s .= '<a href="./index.php?m=files&amp;page=' . $xpg_total_pages . '"><img src="' . w2PfindImage('navlast.gif') . '" border="0" Alt="Last Page"></a></td>';
		} else {
			$s .= '<td width="15%">&nbsp;</td></tr>';
		}
		// Page numbered list, up to 30 pages
		$s .= '<tr><td colspan="3" align="center"> [ ';

		for ($n = $page > 16 ? $page - 16 : 1; $n <= $xpg_total_pages; $n++) {
			if ($n == $page) {
				$s .= '<b>' . $n . '</b></a>';
			} else {
				$s .= '<a href="./index.php?m=files&amp;page=' . $n . '">' . $n . '</a>';
			}
			if ($n >= 30 + $page - 15) {
				$xpg_break = true;
				break;
			} else
				if ($n < $xpg_total_pages) {
					$s .= ' | ';
				}
		}

		if (!isset($xpg_break)) { // are we supposed to break ?
			if ($n == $page) {
				$s .= '<' . $n . '</a>';
			} else {
				$s .= '<a href="./index.php?m=files&amp;page=' . $xpg_total_pages . '">' . $n . '</a>';
			}
		}
		$s .= ' ] </td></tr>';
	} else { // or we dont have any files..
		$s .= '<td align="center">';
		if ($xpg_next_page > $xpg_total_pages) {
			$s .= $xpg_sqlrecs . ' ' . $AppUI->_('Files') . ' ';
		}
		$s .= '</td></tr>';
	}
	$s .= '</table>';
	echo $s;
}

function file_size($size) {
	if ($size > 1024 * 1024 * 1024)
		return round($size / 1024 / 1024 / 1024, 2) . ' Gb';
	if ($size > 1024 * 1024)
		return round($size / 1024 / 1024, 2) . ' Mb';
	if ($size > 1024)
		return round($size / 1024, 2) . ' Kb';
	return $size . ' B';
}

function last_file($file_versions, $file_name, $file_project) {
	$latest = null;
	//global $file_versions;
	if (isset($file_versions))
		foreach ($file_versions as $file_version)
			if ($file_version['file_name'] == $file_name && $file_version['file_project'] == $file_project)
				if ($latest == null || $latest['file_version'] < $file_version['file_version'])
					$latest = $file_version;

	return $latest;
}

function getIcon($file_type) {
	global $w2Pconfig, $uistyle;
	$result = '';
	$mime = str_replace('/', '-', $file_type);
	$icon = 'gnome-mime-' . $mime;
	if (is_file(W2P_BASE_DIR . '/styles/' . $uistyle . '/images/modules/files/icons/' . $icon . '.png')) {
		$result = 'icons/' . $icon . '.png';
	} else {
		$mime = split('/', $file_type);
		switch ($mime[0]) {
			case 'audio':
				$result = 'icons/wav.png';
				break;
			case 'image':
				$result = 'icons/image.png';
				break;
			case 'text':
				$result = 'icons/text.png';
				break;
			case 'video':
				$result = 'icons/video.png';
				break;
		}
		if ($mime[0] == 'application') {
			switch ($mime[1]) {
				case 'vnd.ms-excel':
					$result = 'icons/spreadsheet.png';
					break;
				case 'vnd.ms-powerpoint':
					$result = 'icons/quicktime.png';
					break;
				case 'octet-stream':
					$result = 'icons/source_c.png';
					break;
				default:
					$result = 'icons/documents.png';
			}
		}
	}

	if ($result == '') {
		switch ($obj->$file_category) {
			default: // no idea what's going on
				$result = 'icons/unknown.png';
		}
	}
	return $result;
}

function getFolderSelectList() {
	global $AppUI;
	$folders = array(0 => '');
	$q = new DBQuery();
	$q->addTable('file_folders');
	$q->addQuery('file_folder_id, file_folder_name, file_folder_parent');
	$q->addOrder('file_folder_name');
	$folders = arrayMerge(array('0' => array(0, $AppUI->_('Root'), -1)), $q->loadHashList('file_folder_id'));
	return $folders;
}