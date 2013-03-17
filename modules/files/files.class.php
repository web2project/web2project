<?php

/**
 * @package     web2project\modules\core
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
	public $file_date = null;
	public $file_size = null;
	public $file_version = null;
	public $file_icon = null;
	public $file_category = null;
	public $file_folder = null;
	public $file_checkout = null;
	public $file_co_reason = null;
	public $file_indexed = null;

    protected $_file_id = 0;
	// This "breaks" check-in/upload if helpdesk is not present class variable needs to be added "dymanically"
	//public $file_helpdesk_item = NULL;

	public function __construct() {
        global $helpdesk_available;
        if ($helpdesk_available) {
          $this->file_helpdesk_item = null;
        }
        parent::__construct('files', 'file_id');
	}

    protected function hook_preStore() {
        global $helpdesk_available;

        if ($helpdesk_available && $this->file_helpdesk_item != 0) {
            $this->addHelpDeskTaskLog();
        }

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
		$q = $this->_getQuery();
		$q->addQuery('file_id, file_name');
		$q->addTable('files');
		$q->addWhere('file_indexed = 0');
		$unindexedFiles = $q->loadList(5, 'file_id');

		foreach($unindexedFiles as $file_id => $notUsed) {
			$this->load($file_id);
			$this->indexStrings($this->_AppUI);
		}
	}

    public function hook_search()
    {
        $search['table'] = 'files';
        $search['table_alias'] = 'f';
        $search['table_module'] = 'files';
        $search['table_key'] = 'f.file_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=files&a=addedit&file_id='; // first part of link
        $search['table_title'] = 'Files';
        $search['table_orderby'] = 'file_name, word_placement';
        $search['search_fields'] = array('file_name', 'file_description',
            'file_type', 'file_version', 'file_co_reason', 'word');
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(array('table' => 'files_index',
            'alias' => 'fi', 'join' => 'f.file_id = fi.file_id'));

        return $search;
    }

	public static function getFileList($AppUI = null, $company_id = 0, $project_id = 0, $task_id = 0, $category_id = 0) {
		global $AppUI;

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
		global $helpdesk_available;
		if ($helpdesk_available && $this->file_helpdesk_item != 0) {

			// create task log with information about the file that was uploaded
			$task_log = new CHDTaskLog();
            $task_log->overrideDatabase($this->_query);
			$task_log->task_log_help_desk_id = $this->_hditem->item_id;
			if ($this->_message != 'deleted') {
				$task_log->task_log_name = 'File ' . $this->file_name . ' uploaded';
			} else {
				$task_log->task_log_name = 'File ' . $this->file_name . ' deleted';
			}
			$task_log->task_log_description = $this->file_description;
			$task_log->task_log_creator = $this->_AppUI->user_id;
			$date = new w2p_Utilities_Date();
			$task_log->task_log_date = $date->format(FMT_DATETIME_MYSQL);
			if ($msg = $task_log->store()) {
				$this->_AppUI->setMsg($msg, UI_MSG_ERROR);
			}
		}
		return null;
	}

	public function canAdmin() {
		if (!$this->file_project) {
			return false;
		}
		if (!$this->file_id) {
			return false;
		}

		$result = false;
        $q = $this->_getQuery();
		$q->clear();
		$q->addTable('projects');
		$q->addQuery('project_owner');
		$q->addWhere('project_id = ' . (int)$this->file_project);
		$res = $q->exec(ADODB_FETCH_ASSOC);
		if ($res && $row = $q->fetchRow()) {
			if ($row['project_owner'] == $this->_AppUI->user_id) {
				$result = true;
			}
		}

		return $result;
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
        if (!is_int($this->file_parent) && '' == $this->file_parent) {
            $this->_error['file_parent'] = $baseErrorMsg . 'file parent id is not set';
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

	public function delete()
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
        global $helpdesk_available;

        $this->_file_id = $this->file_id;

        if ($helpdesk_available && $this->file_helpdesk_item != 0) {
            $this->addHelpDeskTaskLog();
        }

        parent::hook_preDelete();
    }

    protected function hook_postDelete()
    {
        $q = $this->_getQuery();
        $q->setDelete('files_index');
        $q->addQuery('*');
        $q->addWhere('file_id = ' . (int) $this->_old_key);
        $q->exec();

        parent::hook_postDelete();
    }

	// delete File from File System
	public function deleteFile() {
        if ('' == $this->file_real_filename ||
                !file_exists(W2P_BASE_DIR . '/files/' . $this->file_project . '/' . $this->file_real_filename)) {
            return true;
        }
        if ($this->_perms->checkModuleItem('files', 'delete', $this->file_id)) {
            return @unlink(W2P_BASE_DIR . '/files/' . $this->file_project . '/' . $this->file_real_filename);
        }
	}

	// move the file if the affiliated project was changed
	public function moveFile($oldProj, $realname) {
		if (!is_dir(W2P_BASE_DIR . '/files/' . $this->file_project)) {
			$res = mkdir(W2P_BASE_DIR . '/files/' . $this->file_project, 0744);
			if (!$res) {
				$this->_AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
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
		if (!is_dir(W2P_BASE_DIR . '/files/0')) {
			$res = mkdir(W2P_BASE_DIR . '/files/0', 0744);
			if (!$res) {
				$this->_AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
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
		// check that directories are created
		if (!is_dir(W2P_BASE_DIR . '/files')) {
			$res = mkdir(W2P_BASE_DIR . '/files', 0744);
			if (!$res) {
				return false;
			}
		}
		if (!is_dir(W2P_BASE_DIR . '/files/' . $this->file_project)) {
			$res = mkdir(W2P_BASE_DIR . '/files/' . $this->file_project, 0744);
			if (!$res) {
				$this->_AppUI->setMsg('Upload folder not setup to accept uploads - change permission on files/ directory.', UI_MSG_ALLERT);
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
		global $w2Pconfig;
        $nwords_indexed = 0;

        /* Workaround for indexing large files:
        ** Based on the value defined in config data,
        ** files with file_size greater than specified limit
        ** are not indexed for searching.
        ** Negative value :<=> no filesize limit
        */
        $index_max_file_size = w2PgetConfig('index_max_file_size', 0);
        if ($this->file_size > 0 && ($index_max_file_size < 0 || (int) $this->file_size <= $index_max_file_size * 1024)) {
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
            if (file_exists($this->_filepath)) {
                $fp = fopen($this->_filepath, 'rb');
                $x = fread($fp, $this->file_size);
                fclose($fp);
                // parse it
                $parser = $parser . ' ' . $this->_filepath;
                $pos = strpos($parser, '/pdf');

                /*
                 * TODO: I *really* hate using error surpression here and I would
                 *   normally just detect if safe_mode is on and if it was, skip
                 *   this call. Unfortunately, safe_mode has been deprecated in
                 *   5.3 and will be removed in 5.4
                 */
                if (false !== $pos) {
                    $x = @shell_exec(`$parser -`);
                } else {
                    $x = @shell_exec(`$parser`);
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
                    $q = $this->_getQuery();
                    $q->addTable('files_index');
                    $q->addReplace('file_id', $this->file_id);
                    $q->addReplace('word', $key);
                    $q->addReplace('word_placement', $val);
                    $q->exec();
                    $q->clear();
                }
            } else {
                //TODO: if the file doesn't exist.. should we delete the db record?
            }
        }
		$q = $this->_getQuery();
		$q->addTable('files');
		$q->addUpdate('file_indexed', 1);
		$q->addWhere('file_id = '. $this->file_id);
		$q->exec();

		return $nwords_indexed;
	}

	//function notifies about file changing
	public function notify($notify) {
        global $locale_char_set, $helpdesk_available;

        if ($notify == '1') {
            // if helpdesk_item is available send notification to assigned users
            if ($helpdesk_available && $this->file_helpdesk_item != 0) {
                $this->_hditem = new CHelpDeskItem();
                $this->_hditem->overrideDatabase($this->_query);
                $this->_hditem->load($this->file_helpdesk_item);

                $task_log = new CHDTaskLog();
                $task_log->overrideDatabase($this->_query);
                // send notifcation about new log entry
                // 2 = TASK_LOG
                $this->_hditem->notify(2, $task_log->task_log_id);

            }
            //if no project specified than we will not do anything
            if ($this->file_project != 0) {
                $this->_project = new CProject();
                $this->_project->overrideDatabase($this->_query);
                $this->_project->load($this->file_project);
                $mail = new w2p_Utilities_Mail();

                if ($this->file_task == 0) { //notify all developers
                    $mail->Subject($this->_project->project_name . '::' . $this->file_name, $locale_char_set);
                } else { //notify all assigned users
                    $this->_task = new CTask();
                    $this->_task->overrideDatabase($this->_query);
                    $this->_task->load($this->file_task);
                    $mail->Subject($this->_project->project_name . '::' . $this->_task->task_name . '::' . $this->file_name, $locale_char_set);
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
                            if ($mail->ValidEmail($row['assignee_email'])) {
                                $mail->To($row['assignee_email'], true);
                                $mail->Send();
                            }
                        }
                    }
                } else { //sending mail to project owner
                    foreach ($this->_users as $row) { //there should be only one row
                        if ($row['user_id'] != $this->_AppUI->user_id) {
                            if ($mail->ValidEmail($row['owner_email'])) {
                                $mail->To($row['owner_email'], true);
                                $mail->Send();
                            }
                        }
                    }
                }
            }
        }
	} //notify

	public function notifyContacts($notifyContacts) {
		global $locale_char_set;

        if ($notifyContacts) {
            //if no project specified than we will not do anything
            if ($this->file_project != 0) {
                $this->_project = new CProject();
                $this->_project->overrideDatabase($this->_query);
                $this->_project->load($this->file_project);
                $mail = new w2p_Utilities_Mail();

                if ($this->file_task == 0) { //notify all developers
                  $mail->Subject($this->_AppUI->_('Project') . ': ' . $this->_project->project_name . '::' . $this->file_name, $locale_char_set);
                } else { //notify all assigned users
                  $this->_task = new CTask();
                  $this->_task->overrideDatabase($this->_query);
                  $this->_task->load($this->file_task);
                  $mail->Subject($this->_AppUI->_('Project') . ': ' . $this->_project->project_name . '::' . $this->_task->task_name . '::' . $this->file_name, $locale_char_set);
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

		$q = $this->_getQuery();
		$q->addTable('users', 'a');
		$q->addJoin('contacts', 'b', 'b.contact_id = a.user_contact', 'inner');
		$q->addQuery('contact_first_name, contact_last_name, contact_display_name as contact_name');
		$q->addWhere('a.user_id = ' . (int)$this->file_owner);
		if ($qid = &$q->exec()) {
			$owner = $qid->fields['contact_name'];
		}

		return $owner;
	}

	public function getTaskName() {
        trigger_error("The CTask->getTaskName method has been deprecated in v3.0
            and will be removed in v4.0. Please use just load a CTask object
            instead", E_USER_NOTICE );

        $task = new CTask();
        $task->load((int)$this->file_task);
        
        return $task->task_name;
	}
}