<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
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
	public $file_icon = null;
	public $file_category = null;
	public $file_folder = null;
	public $file_checkout = null;
	public $file_co_reason = null;
	public $file_indexed = null;

	// This "breaks" check-in/upload if helpdesk is not present class variable needs to be added "dymanically"
	//public $file_helpdesk_item = NULL;

	public function __construct() {
        global $AppUI, $helpdesk_available;
        if ($helpdesk_available) {
          $this->file_helpdesk_item = null;
        }
        parent::__construct('files', 'file_id');
	}

	public function store(CAppUI $AppUI = null) {
        global $AppUI;
        global $helpdesk_available;

        $perms = $AppUI->acl();

        $errorMsgArray = $this->check();
        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        if ($helpdesk_available && $this->file_helpdesk_item != 0) {
            $this->addHelpDeskTaskLog();
        }
        $this->file_date = $AppUI->convertToSystemTZ($this->file_date);

        if ($this->file_id && $perms->checkModuleItem('files', 'edit', $this->file_id)) {
            $this->file_parent = $this->file_id;
            $this->file_id = 0;
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->file_id && $perms->checkModuleItem('files', 'add')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }

        return true;
	}

    public function hook_search()
    {
        $search['table'] = 'files';
        $search['table_module'] = 'files';
        $search['table_key'] = 'file_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=files&a=addedit&file_id='; // first part of link
        $search['table_title'] = 'Files';
        $search['table_orderby'] = 'file_name';
        $search['search_fields'] = array('file_name', 'file_description', 'file_type', 'file_version', 'file_co_reason');
        $search['display_fields'] = array('file_name', 'file_description', 'file_type', 'file_version', 'file_co_reason');

        return $search;        
    }

	public static function getFileList(CAppUI $AppUI = null, $company_id, $project_id, $task_id, $category_id) {
		global $AppUI;

        $q = new DBQuery();
		$q->addQuery('f.*');
		$q->addTable('files', 'f');
		$q->addJoin('projects', 'p', 'p.project_id = file_project');
		$q->addJoin('project_departments', 'pd', 'p.project_id = pd.project_id');
		$q->addJoin('departments', '', 'pd.department_id = dept_id');
		$q->addJoin('tasks', 't', 't.task_id = file_task');

		$project = new CProject();
		$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'file_project');
		if (count($allowedProjects)) {
			$q->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ') OR file_project = 0 )');
		}
		if (isset($company_id) && (int) $company_id > 0) {
			$q->addWhere('project_company = ' . (int)$company_id);
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

	public function addHelpDeskTaskLog() {
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

	public function canAdmin() {
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

	public function check() {
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ($this->file_id == 0 && '' == $this->file_real_filename) {
            $errorArray['file_real_filename'] = $baseErrorMsg . 'file real name is not set';
        }
        if ($this->file_id == 0 && '' == $this->file_name) {
            $errorArray['file_name'] = $baseErrorMsg . 'file name is not set';
        }
        if (!is_int($this->file_parent) && '' == $this->file_parent) {
            $errorArray['file_parent'] = $baseErrorMsg . 'file parent id is not set';
        }
        if ($this->file_id == 0 && !is_int($this->file_size) && '' == $this->file_size) {
            $errorArray['file_size'] = $baseErrorMsg . 'file size is not set';
        }
        if ($this->file_id == 0 && '' == $this->file_type) {
            $errorArray['file_type'] = $baseErrorMsg . 'file type is not set';
        }
        return $errorArray;
	}

	public function checkout($userId, $fileId, $coReason) {
		$q = new DBQuery;
		$q->addTable('files');
		$q->addUpdate('file_checkout', $userId);
		$q->addUpdate('file_co_reason', $coReason);
		$q->addWhere('file_id = ' . (int)$fileId);
		$q->exec();

		return true;
	}

	public function cancelCheckout($fileId) {
		$q = new DBQuery;
		$q->addTable('files');
		$q->addUpdate('file_checkout', '');
		$q->addWhere('file_id = ' . (int)$fileId);
		$q->exec();

		return true;

	}

	public function delete(CAppUI $AppUI = null) {
		global $AppUI;
        global $helpdesk_available;
        $perms = $AppUI->acl();

        if ($perms->checkModuleItem('files', 'delete', $this->file_id)) {
            // remove the file from the file system
            if (!$this->deleteFile()) {
                return false;
            }

            if ($msg = parent::delete()) {
                return $msg;
            }

            // delete any index entries
            $q = new DBQuery;
            $q->setDelete('files_index');
            $q->addQuery('*');
            $q->addWhere('file_id = ' . (int)$this->file_id);
            if (!$q->exec()) {
                $q->clear();
                return db_error();
            }
            if ($helpdesk_available && $this->file_helpdesk_item != 0) {
                $this->addHelpDeskTaskLog();
            }

            return true;
        }
		return false;
	}

	// delete File from File System
	public function deleteFile() {
		global $AppUI, $w2Pconfig;
        $perms = $AppUI->acl();

        if ($perms->checkModuleItem('files', 'delete', $this->file_id)) {
            return @unlink(W2P_BASE_DIR . '/files/' . $this->file_project . '/' . $this->file_real_filename);
        }
	}

	// move the file if the affiliated project was changed
	public function moveFile($oldProj, $realname) {
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
	public function duplicateFile($oldProj, $realname) {
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
	public function moveTemp($upload) {
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
	public function indexStrings() {
		global $AppUI, $w2Pconfig;
        $nwords_indexed = 0;

        /* Workaround for indexing large files:
        ** Based on the value defined in config data,
        ** files with file_size greater than specified limit
        ** are not indexed for searching.
        ** Negative value :<=> no filesize limit
        */
        $index_max_file_size = w2PgetConfig('index_max_file_size', 0);
        if ($index_max_file_size < 0 || $obj->file_size <= $index_max_file_size * 1024) {
            // get the parser application
            $parser = $w2Pconfig['parser_' . $this->file_type];
            if (!$parser) {
                $parser = $w2Pconfig['parser_default'];
            }
            if (!$parser) {
                return false;
            }
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
            $warr = explode(' ', $x);

            $wordarr = array();
            $nwords = count($warr);
            for ($x = 0; $x < $nwords; $x++) {
                $newword = $warr[$x];
                if (!preg_match('[!"#$%&\'()*+,\-./:;<=>?@[\\\]^_`{|}~]', $newword)
                    && mb_strlen(mb_trim($newword)) > 2
                    && !preg_match('[0-9]', $newword)) {
                        $wordarr[$newword] = $x;
                }
            }

            // filter out common strings
            $ignore = w2PgetSysVal('FileIndexIgnoreWords');
            $ignore = str_replace(' ,', ',', $ignore);
            $ignore = str_replace(', ', ',', $ignore);
            $ignore = explode(',', $ignore);
            foreach ($ignore as $w) {
                unset($wordarr[$w]);
            }
            $nwords_indexed = count($wordarr);
            // insert the strings into the table
            while (list($key, $val) = each($wordarr)) {
                $q = new DBQuery;
                $q->addTable('files_index');
                $q->addReplace('file_id', $this->file_id);
                $q->addReplace('word', $key);
                $q->addReplace('word_placement', $val);
                $q->exec();
                $q->clear();
            }
        }
		return $nwords_indexed;
	}

	//function notifies about file changing
	public function notify($notify) {
        global $AppUI, $w2Pconfig, $locale_char_set, $helpdesk_available;

        if ($notify == '1') {
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
                    $q->addQuery('t.task_id, cc.contact_first_name as
                             creator_first_name, cc.contact_last_name as creator_last_name,
                             oc.contact_first_name as owner_first_name,
                             oc.contact_last_name as owner_last_name, a.user_id as assignee_id,
                             ac.contact_first_name as assignee_first_name,
                             ac.contact_last_name as assignee_last_name');
                    $q->addJoin('user_tasks', 'u', 'u.task_id = t.task_id');

                    $q->addJoin('users', 'o', 'o.user_id = t.task_owner');
                    $q->addJoin('contacts', 'oc', 'o.user_contact = oc.contact_id');
                    $q->addQuery('oc.contact_id as owner_contact_id');

                    $q->addJoin('users', 'c', 'c.user_id = t.task_creator');
                    $q->addJoin('contacts', 'cc', 'c.user_contact = cc.contact_id');
                    $q->addQuery('cc.contact_id as creator_contact_id');

                    $q->addJoin('users', 'a', 'a.user_id = u.user_id');
                    $q->addJoin('contacts', 'ac', 'a.user_contact = ac.contact_id');
                    $q->addQuery('ac.contact_id as assignee_contact_id');

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

                $contact = new CContact();
                foreach ($this->_users as $index => $info) {
                    $contact->contact_id = $info['owner_contact_id'];
                    $email = $contact->getContactMethods(array('email_primary'));
                    $this->_users[$index]['owner_email'] = $email['email_primary'];

                    $contact->contact_id = $info['creator_contact_id'];
                    $email = $contact->getContactMethods(array('email_primary'));
                    $this->_users[$index]['creator_email'] = $email['email_primary'];

                    $contact->contact_id = $info['assignee_contact_id'];
                    $email = $contact->getContactMethods(array('email_primary'));
                    $this->_users[$index]['assignee_email'] = $email['email_primary'];
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
        }
	} //notify

	public function notifyContacts($notifyContacts) {
		global $AppUI, $w2Pconfig, $locale_char_set;

        if ($notifyContacts) {
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
                    $q->addQuery('c.contact_first_name as contact_first_name, c.contact_last_name as contact_last_name');
                    $q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
                    $q->addWhere('pc.project_id = ' . (int)$this->_project->project_id);

                    $sql = '(' . $q->prepare() . ')';
                    $q->clear();
                    $sql .= ' UNION ';
                    $q->addTable('task_contacts', 'tc');
                    $q->addQuery('c.contact_first_name as contact_first_name, c.contact_last_name as contact_last_name');
                    $q->addJoin('contacts', 'c', 'c.contact_id = tc.contact_id');
                    $q->addWhere('tc.task_id = ' . (int)$this->_task->task_id);
                } else {
                    $q = new DBQuery;
                    $q->addTable('project_contacts', 'pc');
                    $q->addQuery('pc.project_id, pc.contact_id');
                    $q->addQuery('c.contact_first_name as contact_first_name, c.contact_last_name as contact_last_name');
                    $q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
                    $q->addWhere('pc.project_id = ' . (int)$this->file_project);
                }
                $this->_users = $q->loadList();

                $body .= "\n\nFile " . $this->file_name . ' was ' . $this->_message . ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
                if ($this->_message != 'deleted') {
                    $body .= "\n" . $AppUI->_('URL') . ':     ' . W2P_BASE_URL . '/fileviewer.php?file_id=' . $this->file_id;
                    $body .= "\n" . $AppUI->_('Description') . ":\n" . $this->file_description;
                }

                $contact = new CContact();
                foreach ($this->_users as $index => $info) {
                    $contact->contact_id = $info['contact_id'];
                    $email = $contact->getContactMethods(array('email_primary'));
                    $this->_users[$index]['contact_email'] = $email['email_primary'];
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
	}

	public function getOwner() {
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

	public function getTaskName() {
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

function last_file($file_versions, $file_name, $file_project) {
	$latest = null;

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
    $mime = explode('/', $file_type);
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

function getHelpdeskFolder() {
	$q = new DBQuery();
	$q->addTable('file_folders', 'ff');
	$q->addQuery('file_folder_id');
	$q->addWhere('ff.file_folder_name = \'Helpdesk\'');
	$ffid = $q->loadResult();
	$q->clear();
	return intval($ffid);
}

function file_show_attr() {
	global $AppUI, $obj, $ci, $canAdmin, $projects, $file_project, $file_task, $task_name, $preserve, $file_helpdesk_item;

	if ($ci) {
		$str_out = '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Minor Revision') . '</td><td><input type="Radio" name="revision_type" value="minor" checked />' . '</td><tr><td align="right" nowrap="nowrap">' . $AppUI->_('Major Revision') . '</td><td><input type="Radio" name="revision_type" value="major" /></td>';
	} else {
		$str_out = '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Version') . ':</td>';
	}

	$str_out .= '<td align="left">';

	if ($ci || ($canAdmin && $obj->file_checkout == 'final')) {
		$str_out .= '<input type="hidden" name="file_checkout" value="" /><input type="hidden" name="file_co_reason" value="" />';
	}

	if ($ci) {
		$the_value = (strlen($obj->file_version) > 0 ? $obj->file_version + 0.01 : '1');
		$str_out .= '<input type="hidden" name="file_version" value="' . $the_value . '" />';
	} else {
		$the_value = (strlen($obj->file_version) > 0 ? $obj->file_version : '1');
		$str_out .= '<input type="text" name="file_version" maxlength="10" size="5" value="' . $the_value . '" />';
	}

	$str_out .= '</td>';

	$select_disabled = ' ';
	$onclick_task = ' onclick="popTask()" ';
	if ($ci && $preserve) {
		$select_disabled = ' disabled="disabled" ';
		$onclick_task = ' ';
		// need because when a html is disabled, it's value it's not sent in submit
		$str_out .= '<input type="hidden" name="file_project" value="' . $file_project . '" />';
		$str_out .= '<input type="hidden" name="file_category" value="' . $obj->file_category . '" />';
	}

	// Category
	$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Category') . ':</td>';
	$str_out .= '<td align="left">' . arraySelect(w2PgetSysVal('FileType'), 'file_category', '' . $select_disabled, $obj->file_category, true) . '<td>';

	// ---------------------------------------------------------------------------------

	if ($file_helpdesk_item) {
		$hd_item = new CHelpDeskItem();
		$hd_item->load($file_helpdesk_item);
		//Helpdesk Item
		$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Helpdesk Item') . ':</td>';
		$str_out .= '<td align="left"><strong>' . $hd_item->item_id . ' - ' . $hd_item->item_title . '</strong></td></tr>';
		// Project
		$str_out .= '<input type="hidden" name="file_project" value="' . $file_project . '" />';

		// Task
		$str_out .= '<input type="hidden" name="file_task" value="0" />';
	} else {
		// Project
		$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Project') . ':</td>';
		$str_out .= '<td align="left">' . projectSelectWithOptGroup($AppUI->user_id, 'file_project', 'size="1" class="text" style="width:270px"' . $select_disabled, $file_project) . '</td></tr>';

		// ---------------------------------------------------------------------------------

		// Task
		$str_out .= '<tr><td align="right" nowrap="nowrap">' . $AppUI->_('Task') . ':</td><td align="left" colspan="2" valign="top"><input type="hidden" name="file_task" value="' . $file_task . '" /><input type="text" class="text" name="task_name" value="' . $task_name . '" size="40" disabled /><input type="button" class="button" value="' . $AppUI->_('select task') . '..."' . $onclick_task . '/></td></tr>';
	}

	return ($str_out);
}