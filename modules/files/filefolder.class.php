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

    public function getFileCountByFolder(CAppUI $AppUI, $folder_id, $task_id, $project_id, $company_id) {

        // SQL text for count the total recs from the selected option
        $q = new w2p_Database_Query();
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
        $q = new w2p_Database_Query();
        $q->addTable('file_folders');
        $q->addQuery('*');
        $q->addWhere('file_folder_parent = '. (int) $parent);
        $q->addOrder('file_folder_name');

        return $q->loadList();
    }
}

function getFolderSelectList() {
	global $AppUI;
	$folders = array(0 => '');
	$q = new w2p_Database_Query();
	$q->addTable('file_folders');
	$q->addQuery('file_folder_id, file_folder_name, file_folder_parent');
	$q->addOrder('file_folder_name');
	$folders = arrayMerge(array('0' => array(0, $AppUI->_('Root'), -1)), $q->loadHashList('file_folder_id'));
	return $folders;
}

/*
 * $parent is the parent of the children we want to see
 * $level is increased when we go deeper into the tree, used to display a nice indented tree
 */

function getFolders($parent, $level = 0) {
	global $AppUI, $allowed_folders_ary, $denied_folders_ary, $tab, $m, $a, $company_id, $allowed_companies, $project_id, $task_id, $current_uri, $file_types;
	// retrieve all children of $parent

    $file_folder = new CFileFolder();
    $folders = $file_folder->getFoldersByParent($parent);

	$s = '';
	// display each child
	foreach ($folders as $row) {
		if (array_key_exists($row['file_folder_id'], $allowed_folders_ary) or array_key_exists($parent, $allowed_folders_ary)) {
            $file_count = countFiles($row['file_folder_id']);

            $s .= '<tr><td colspan="20">';
            if ($m == 'files') {
                $s .= '<a href="./index.php?m=' . $m . '&amp;a=' . $a . '&amp;tab=' . $tab . '&folder=' . $row['file_folder_id'] . '" name="ff' . $row['file_folder_id'] . '">';
            }
            $s .= '<img src="' . w2PfindImage('folder5_small.png', 'files') . '" width="16" height="16" style="float: left; border: 0px;" />';
            $s .= $row['file_folder_name'];
            if ($m == 'files') {
                $s .= '</a>';
            }
            if ($file_count > 0) {
                $s .= ' <a href="javascript: void(0);" onClick="expand(\'files_' . $row['file_folder_id'] . '\')" class="has-files">(' . $file_count . ' files) +</a>';
            }
            $s .= '<form name="frm_remove_folder_' . $row['file_folder_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                    <input type="hidden" name="dosql" value="do_folder_aed" />
                    <input type="hidden" name="del" value="1" />
                    <input type="hidden" name="file_folder_id" value="' . $row['file_folder_id'] . '" />
                    </form>';
            $s .= '<a style="float:left;" href="./index.php?m=files&amp;a=addedit_folder&amp;folder=' . $row['file_folder_id'] . '">' . w2PshowImage('filesaveas.png', '16', '16', 'edit icon', 'edit this folder', 'files') . '</a>' .
                  '<a style="float:left;" href="./index.php?m=files&amp;a=addedit_folder&amp;file_folder_parent=' . $row['file_folder_id'] . '&amp;file_folder_id=0">' . w2PshowImage('edit_add.png', '', '', 'new folder', 'add a new subfolder', 'files') . '</a>' .
                  '<a style="float:right;" href="javascript: void(0);" onclick="if (confirm(\'Are you sure you want to delete this folder?\')) {document.frm_remove_folder_' . $row['file_folder_id'] . '.submit()}">' . w2PshowImage('remove.png', '', '', 'delete icon', 'delete this folder', 'files') . '</a>' .
                  '<a style="float:left;" href="./index.php?m=files&amp;a=addedit&amp;folder=' . $row['file_folder_id'] . '&amp;project_id=' . $project_id . '&amp;file_id=0">' . w2PshowImage('folder_new.png', '', '', 'new file', 'add new file to this folder', 'files') . '</a>';
            $s .= '</td></tr>';
            if ($file_count > 0) {
                $s .= '<div class="files-list" id="files_' . $row['file_folder_id'] . '" style="display: none;">';
                $s .= displayFiles($AppUI, $row['file_folder_id'], $task_id, $project_id, $company_id);
                $s .= "</div>";
            }
		}
		// call this function again to display this
		// child's children
		// getFolders *always* returns true, so there's no point in checking it
		//$s .= getFolders($row['file_folder_id'], $level + 1).'</li></ul>';
	}
	/*
	 *  getFolders  would *alway* return true and would echo the results.  It
	 * makes more sense to simply return the results.  Then the calling code can
	 * echo it, capture it for parsing, or whatever else needs to be done.  There
	 * should be less inadvertent actions as a result.
	 */
	return $s;
}

function countFiles($folder) {
	global $AppUI, $company_id, $allowed_companies, $tab;
	global $deny1, $deny2, $project_id, $task_id, $showProject, $file_types;

	$q = new w2p_Database_Query();
	$q->addTable('files');
	$q->addQuery('count(files.file_id)', 'file_in_folder');
	$q->addJoin('projects', 'p', 'p.project_id = file_project');
	$q->addJoin('users', 'u', 'u.user_id = file_owner');
	$q->addJoin('tasks', 't', 't.task_id = file_task');
	$q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	$q->addWhere('file_folder = ' . (int)$folder);
	if (count($deny1) > 0) {
		$q->addWhere('file_project NOT IN (' . implode(',', $deny1) . ')');
	}
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

	$files_in_folder = $q->loadResult();
	$q->clear();

	return $files_in_folder;
}

function displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id) {
	global $m, $a, $tab, $xpg_min, $xpg_pagesize, $showProject, $file_types, 
            $cfObj, $xpg_totalrecs, $xpg_total_pages, $page, $company_id,
            $allowed_companies, $current_uri, $w2Pconfig, $canEdit, $canRead;

	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');

	// SETUP FOR FILE LIST
	$q = new w2p_Database_Query();
	$q->addQuery('f.*, max(f.file_id) as latest_id, count(f.file_version) as file_versions, round(max(file_version), 2) as file_lastversion');
	$q->addQuery('ff.*');
	$q->addTable('files', 'f');
	$q->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	$q->addJoin('projects', 'p', 'p.project_id = file_project');
	$q->addJoin('tasks', 't', 't.task_id = file_task');
	$q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
	$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');

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
		$q->addWhere('project_company = ' . (int)$company_id);
	}
	$q->setLimit($xpg_pagesize, $xpg_min);
	$q->addWhere('file_folder = ' . (int)$folder_id);
	$q->addGroup('file_version_id DESC');

	$qv = new w2p_Database_Query();
	$qv->addTable('files');
	$qv->addQuery('file_id, file_version, file_project, file_name, file_task,
		file_description, u.user_username as file_owner, file_size, file_category,
		task_name, file_version_id,  file_checkout, file_co_reason, file_type,
		file_date, cu.user_username as co_user, project_name,
		project_color_identifier, project_owner, con.contact_first_name,
		con.contact_last_name, co.contact_first_name as co_contact_first_name,
		co.contact_last_name as co_contact_last_name ');
	$qv->addJoin('projects', 'p', 'p.project_id = file_project');
	$qv->addJoin('users', 'u', 'u.user_id = file_owner');
	$qv->addJoin('contacts', 'con', 'con.contact_id = u.user_contact');
	$qv->addJoin('tasks', 't', 't.task_id = file_task');
	$qv->addJoin('file_folders', 'ff', 'ff.file_folder_id = file_folder');
	if ($project_id) {
		$qv->addWhere('file_project = ' . (int)$project_id);
	}
	if ($task_id) {
		$qv->addWhere('file_task = ' . (int)$task_id);
	}
	if ($company_id) {
		$qv->addWhere('project_company = ' . (int)$company_id);
	}
	$qv->leftJoin('users', 'cu', 'cu.user_id = file_checkout');
	$qv->leftJoin('contacts', 'co', 'co.contact_id = cu.user_contact');
	$qv->addWhere('file_folder = ' . (int)$folder_id);

	$files = array();
	$file_versions = array();
    $files = $q->loadList();
    $file_versions = $qv->loadHashList('file_id');
    $q->clear();
    $qv->clear();

	if ($files === array()) {
		return 0;
	}

	$s = '
		<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
		<tr>
			<th nowrap="nowrap">' . $AppUI->_('File Name') . '</th>
			<th>' . $AppUI->_('Description') . '</th>
			<th>' . $AppUI->_('Versions') . '</th>
		    <th>' . $AppUI->_('Category') . '</th>
			<th nowrap="nowrap">' . $AppUI->_('Task Name') . '</th>
			<th>' . $AppUI->_('Owner') . '</th>
			<th>' . $AppUI->_('Size') . '</th>
			<th>' . $AppUI->_('Type') . '</a></th>
			<th>' . $AppUI->_('Date') . '</th>
	    	<th nowrap="nowrap">' . $AppUI->_('co Reason') . '</th>
	    	<th>' . $AppUI->_('co') . '</th>
			<th nowrap="nowrap" width="5%"></th>
			<th nowrap="nowrap" width="1"></th>
		</tr>';

	$fp = -1;
	$file_date = new w2p_Utilities_Date();

	$id = 0;
	foreach ($files as $row) {
		$latest_file = $file_versions[$row['latest_id']];
		$file_date = new w2p_Utilities_Date($latest_file['file_date']);

		if ($fp != $latest_file['file_project']) {
			if (!$latest_file['file_project']) {
				$latest_file['project_name'] = $AppUI->_('Not attached to a project');
				$latest_file['project_color_identifier'] = 'f4efe3';
			}
			if ($showProject) {
				$style = 'background-color:#' . $latest_file['project_color_identifier'] . ';color:' . bestColor($latest_file['project_color_identifier']);
				$s .= '<tr>';
				$s .= '<td colspan="20" style="border: outset 2px #eeeeee;' . $style . '">';
				if ($latest_file['file_project'] > 0) {
					$href = './index.php?m=projects&a=view&project_id=' . $latest_file['file_project'];
				} else {
					$href = './index.php?m=projects';
				}
				$s .= '<a href="' . $href . '">';
				$s .= '<span style="' . $style . '">' . $latest_file['project_name'] . '</span></a>';
				$s .= '</td></tr>';
			}
		}
		$fp = $latest_file['file_project'];

		$s .= '<tr>
				<td nowrap="8%">
                    <form name="frm_remove_file_' . $file['file_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                        <input type="hidden" name="dosql" value="do_file_aed" />
                        <input type="hidden" name="del" value="1" />
                        <input type="hidden" name="file_id" value="' . $file['file_id'] . '" />
                        <input type="hidden" name="redirect" value="' . $current_uri . '" />
                    </form>
                    <form name="frm_duplicate_file_' . $file['file_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                        <input type="hidden" name="dosql" value="do_file_aed" />
                        <input type="hidden" name="duplicate" value="1" />
                        <input type="hidden" name="file_id" value="' . $file['file_id'] . '" />
                        <input type="hidden" name="redirect" value="' . $current_uri . '" />
                    </form>
                ';
        $junkFile = new CFile(); // TODO: This is just to get getIcon included..
		$file_icon = getIcon($row['file_type']);
		$s .= '<a href="./fileviewer.php?file_id=' . $latest_file['file_id'] . '"><img border="0" width="16" heigth="16" src="' . w2PfindImage($file_icon, 'files') . '" alt="" />&nbsp;' . $latest_file['file_name'] . '</a></td>';
		$s .= '<td width="20%">' . w2p_textarea($latest_file['file_description']) . '</td><td width="5%" nowrap="nowrap" align="right">';
		$hidden_table = '';
		$s .= $row['file_lastversion'];
		if ($row['file_versions'] > 1) {
			$s .= ' <a href="javascript: void(0);" onClick="expand(\'versions_' . $latest_file['file_id'] . '\'); ">(' . $row['file_versions'] . ')</a>';
			$hidden_table = '<tr><td colspan="20">
							<table style="display: none" id="versions_' . $latest_file['file_id'] . '" width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
							<tr>
							        <th nowrap="nowrap">' . $AppUI->_('File Name') . '</th>
							        <th>' . $AppUI->_('Description') . '</th>
							        <th>' . $AppUI->_('Versions') . '</th>
							        <th>' . $AppUI->_('Category') . '</th>
									<th>' . $AppUI->_('Folder') . '</th>
							        <th>' . $AppUI->_('Task Name') . '</th>
							        <th>' . $AppUI->_('Owner') . '</th>
							        <th>' . $AppUI->_('Size') . '</th>
							        <th>' . $AppUI->_('Type') . '</a></th>
							        <th>' . $AppUI->_('Date') . '</th>
							</tr>';
			foreach ($file_versions as $file) {
				if ($file['file_version_id'] == $latest_file['file_version_id']) {
					$file_icon = getIcon($file['file_type']);
					$hdate = new w2p_Utilities_Date($file['file_date']);
					$hidden_table .= '<tr><td nowrap="8%"><a href="./fileviewer.php?file_id=' . $file['file_id'] . '" title="' . $file['file_description'] . '">' . '<img border="0" width="16" heigth="16" src="' . w2PfindImage($file_icon, 'files') . '" alt="" />&nbsp;' . $file['file_name'] . '
					  </a></td>
					  <td width="20%">' . $file['file_description'] . '</td>
					  <td width="5%" nowrap="nowrap" align="right">' . $file['file_version'] . '</td>
					  <td nowrap="nowrap" align="left">' . $file_types[$file['file_category']] . '</td>
					  <td nowrap="nowrap" align="left">' . (($file['file_folder_name'] != '') ? '<a href="' . W2P_BASE_URL . '/index.php?m=files&tab=' . (count($file_types) + 1) . '&folder=' . $file['file_folder_id'] . '">' . w2PshowImage('folder5_small.png', '16', '16', 'folder icon', 'show only this folder', 'files') . $file['file_folder_name'] . '</a>' : 'Root') . '</td>
					  <td nowrap="nowrap" align="left"><a href="./index.php?m=tasks&a=view&task_id=' . $file['file_task'] . '">' . $file['task_name'] . '</a></td>
					  <td nowrap="nowrap">' . $file['contact_first_name'] . ' ' . $file['contact_last_name'] . '</td>
					  <td width="5%" nowrap="nowrap" align="right">' . file_size(intval($file['file_size'])) . '</td>
					  <td nowrap="nowrap">' . $file['file_type'] . '</td>
					  <td width="5%" nowrap="nowrap" align="center">' . $AppUI->formatTZAwareTime($file['file_date'], $df . ' ' . $tf) . '</td>';
					if ($canEdit && $w2Pconfig['files_show_versions_edit']) {
						$hidden_table .= '<a href="./index.php?m=files&a=addedit&file_id=' . $file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . "</a>";
					}
					$hidden_table .= '</td><tr>';
				}
			}
			$hidden_table .= '</table>';
		}
		$s .= '</td>
				<td width="10%" nowrap="nowrap" align="left">' . $file_types[$file['file_category']] . '</td>
				<td nowrap="nowrap" align="left"><a href="./index.php?m=tasks&a=view&task_id=' . $latest_file['file_task'] . '">' . $latest_file['task_name'] . '</a></td>
				<td nowrap="nowrap">' . $latest_file['contact_first_name'] . ' ' . $latest_file['contact_last_name'] . '</td>
				<td width="5%" nowrap="nowrap" align="right">' . intval($latest_file['file_size'] / 1024) . ' kb</td>
				<td nowrap="nowrap">' . $latest_file['file_type'] . '</td>
				<td nowrap="nowrap" align="center">' . $AppUI->formatTZAwareTime($latest_file['file_date'], $df . ' ' . $tf) . '</td>
				<td width="10%">' . $latest_file['file_co_reason'] . '</td>
				<td nowrap="nowrap">';
        if (empty($row['file_checkout'])) {
        	$s .= '<a href="?m=files&a=co&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('up.png', '16', '16', 'checkout', 'checkout file', 'files') . '</a>';
        } elseif ($row['file_checkout'] == $AppUI->user_id) {
            $s .= '<a href="?m=files&a=addedit&ci=1&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('down.png', '16', '16', 'checkin', 'checkin file', 'files') . '</a>';
        } else {
			if ($latest_file['file_checkout'] == 'final') {
				$s .= 'final';
			} else {
				$s .= $latest_file['co_contact_first_name'] . ' ' . $latest_file['co_contact_last_name'] . '<br>(' . $latest_file['co_user'] . ')';
			}
		}
		$s .= '</td><td nowrap="nowrap" width="50">';
		if ($canEdit && (empty($latest_file['file_checkout']) || ($latest_file['file_checkout'] == 'final' && ($canEdit || $latest_file['project_owner'] == $AppUI->user_id)))) {
			$s .= '<a style="float: left;" href="./index.php?m=files&a=addedit&file_id=' . $latest_file['file_id'] . '">' . w2PshowImage('kedit.png', '16', '16', 'edit file', 'edit file', 'files') . '</a>';
			$s .= '<a style="float: left;" href="javascript: void(0);" onclick="document.frm_duplicate_file_' . $latest_file['file_id'] . '.submit()">' . w2PshowImage('duplicate.png', '16', '16', 'duplicate file', 'duplicate file', 'files') . '</a>';
			$s .= '<a style="float: left;" href="javascript: void(0);" onclick="if (confirm(\'Are you sure you want to delete this file?\')) {document.frm_remove_file_' . $latest_file['file_id'] . '.submit()}">' . w2PshowImage('remove.png', '16', '16', 'delete file', 'delete file', 'files') . '</a>';
		}
        $s .= '</td>';
		$s .= '<td nowrap="nowrap" align="center" width="1">';
		if ($canEdit && (empty($latest_file['file_checkout']) || ($latest_file['file_checkout'] == 'final' && ($canEdit || $latest_file['project_owner'] == $AppUI->user_id)))) {
			$bulk_op = 'onchange="(this.checked) ? addBulkComponent(' . $latest_file['file_id'] . ') : removeBulkComponent(' . $latest_file['file_id'] . ')"';
			$s .= '<input type="checkbox" ' . $bulk_op . ' name="chk_sel_file_' . $latest_file['file_id'] . '" />';
		}
		$s .= '</td></tr>';
		$s .= $hidden_table;
		$hidden_table = '';
	}
	return $s;
}
