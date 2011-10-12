<?php
/* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
global $AppUI, $canRead, $canEdit, $allowed_folders_ary,
$tab, $folder, $cfObj, $m, $a, $company_id,
 $allowed_companies, $showProject;

/*
 * Fetched from cleanup_functions.php since this is the ONLY place where
 * getFolders is used.
 * 
 * $parent is the parent of the children we want to see
 * $level is increased when we go deeper into the tree, used to display a nice indented tree
 */

function getFolders($parent) {
    global $AppUI, $allowed_folders_ary, $tab, $m, $a, $company_id, $project_id, $task_id;
    // retrieve all children of $parent

    $file_folder = new CFileFolder();
    $folders = $file_folder->getFoldersByParent($parent);

    $s = '';
    // display each child
    foreach ($folders as $row) {
        if (array_key_exists($row['file_folder_id'], $allowed_folders_ary) or array_key_exists($parent, $allowed_folders_ary)) {
            $file_count = countFiles($row['file_folder_id']);

            $s .= '<tr><td colspan="20">';
            $s .= '<a href="./index.php?m=' . $m . '&a=' . $a . '&tab=' . $tab . '&folder_id=' . $row['file_folder_id'] . '&project_id=' . $project_id . '">';
            $s .= '<img src="' . w2PfindImage('folder5_small.png', 'files') . '" width="16" height="16" style="float: left; border: 0px;" />';
            $s .= $row['file_folder_name'];
            $s .= '</a>';
            if ($file_count > 0) {
                $s .= ' <a href="" id="folder_' . $row['file_folder_id'] . '" class="has-files">(' . $file_count . ' files) +</a>';
            }
            $s .= '<form name="frm_remove_folder_' . $row['file_folder_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                    <input type="hidden" name="dosql" value="do_folder_aed" />
                    <input type="hidden" name="del" value="1" />
                    <input type="hidden" name="file_folder_id" value="' . $row['file_folder_id'] . '" />
                    </form>';
            $s .= '<a style="float:left;" href="./index.php?m=files&amp;a=addedit_folder&amp;folder=' . $row['file_folder_id'] . '">' . w2PshowImage('filesaveas.png', '16', '16', 'Edit Folder', 'Edit this folder', 'files') . '</a>' .
                    '<a style="float:left;" href="./index.php?m=files&amp;a=addedit_folder&amp;file_folder_parent=' . $row['file_folder_id'] . '&amp;file_folder_id=0">' . w2PshowImage('edit_add.png', '', '', 'New Folder', 'Add a new subfolder', 'files') . '</a>' .
                    '<a style="float:right;" href="javascript: void(0);" onclick="if (confirm(\'Are you sure you want to delete this folder?\')) {document.frm_remove_folder_' . $row['file_folder_id'] . '.submit()}">' . w2PshowImage('remove.png', '', '', 'Delete Folder', 'Delete this folder', 'files') . '</a>' .
                    '<a style="float:left;" href="./index.php?m=files&amp;a=addedit&amp;folder=' . $row['file_folder_id'] . '&amp;project_id=' . $project_id . '&amp;file_id=0">' . w2PshowImage('folder_new.png', '', '', 'new file', 'add new file to this folder', 'files') . '</a>';
            $s .= '</td></tr>';
            if ($file_count > 0) {
                $s .= '<tr><td colspan="20">' . displayFiles($AppUI, $row['file_folder_id'], $task_id, $project_id, $company_id, false, true) . '</td></tr>';
            }
        }
    }

    return $s;
}

function displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id, $skip_headers = false, $skip_projects = false, $display_contents = true) {
    global $xpg_min, $xpg_pagesize, $showProject, $file_types,
    $company_id, $current_uri, $w2Pconfig, $canEdit;

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
        $q->addWhere('file_project = ' . (int) $project_id);
    }
    if ($task_id) {
        $q->addWhere('file_task = ' . (int) $task_id);
    }
    if ($company_id) {
        $q->addWhere('project_company = ' . (int) $company_id);
    }
    $q->setLimit($xpg_pagesize, $xpg_min);
    $q->addWhere('file_folder = ' . (int) $folder_id);
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
        $qv->addWhere('file_project = ' . (int) $project_id);
    }
    if ($task_id) {
        $qv->addWhere('file_task = ' . (int) $task_id);
    }
    if ($company_id) {
        $qv->addWhere('project_company = ' . (int) $company_id);
    }
    $qv->leftJoin('users', 'cu', 'cu.user_id = file_checkout');
    $qv->leftJoin('contacts', 'co', 'co.contact_id = cu.user_contact');
    $qv->addWhere('file_folder = ' . (int) $folder_id);

    $files = array();
    $file_versions = array();
    $files = $q->loadList();
    $file_versions = $qv->loadHashList('file_id');
    $q->clear();
    $qv->clear();

    if ($files === array()) {
        return 0;
    }
    $dispFiles = ($display_contents) ? "show-files" : "";
    $s = '<table width="100%" border="0" cellpadding="2" cellspacing="1" id="filestbl_' . $folder_id . '" class="tbl filestbl ' . $dispFiles . '">';
    if (!$skip_headers) {
        $s .= '<tr>
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
    }
    $fp = -1;
    $file_date = new w2p_Utilities_Date();

    foreach ($files as $row) {
        $latest_file = $file_versions[$row['latest_id']];
        $file_date = new w2p_Utilities_Date($latest_file['file_date']);

        if ($fp != $latest_file['file_project']) {
            if (!$latest_file['file_project']) {
                $latest_file['project_name'] = $AppUI->_('Not attached to a project');
                $latest_file['project_color_identifier'] = 'f4efe3';
            }
            if ($showProject && !$skip_projects) {
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
                    <form name="frm_remove_file_' . $latest_file['file_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                        <input type="hidden" name="dosql" value="do_file_aed" />
                        <input type="hidden" name="del" value="1" />
                        <input type="hidden" name="file_id" value="' . $latest_file['file_id'] . '" />
                        <input type="hidden" name="redirect" value="' . $current_uri . '" />
                    </form>
                    <form name="frm_duplicate_file_' . $latest_file['file_id'] . '" action="?m=files" method="post" accept-charset="utf-8">
                        <input type="hidden" name="dosql" value="do_file_aed" />
                        <input type="hidden" name="duplicate" value="1" />
                        <input type="hidden" name="file_id" value="' . $latest_file['file_id'] . '" />
                        <input type="hidden" name="redirect" value="' . $current_uri . '" />
                    </form>
                ';
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
                    $hidden_table .= '</table></td><tr>';
                }
            }
            $hidden_table .= '';
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
        $s .= '</table>';
        $hidden_table = '';
    }
    return $s;
}

$canEdit = canEdit($m);
$canRead = canView($m);

$folder_id = (int) $folder;
if ($folder_id) {
    $cfObj->load($folder_id);
}
if (!$folder_id && isset($_GET['folder_id'])) {
    $folder_id = w2PgetParam($_REQUEST, 'folder_id', 0);
    $cfObj = new CFileFolder();
    $cfObj->load($folder_id);
}
if (!$folder_id) {
    $folder_id = 0;
}

// Files modules: index page re-usable sub-table
// add to allow for returning to other modules besides Files
$current_uriArray = parse_url($_SERVER['REQUEST_URI']);
$current_uri = $current_uriArray['query'];

if (!isset($project_id)) {
    $project_id = (int) w2PgetParam($_REQUEST, 'project_id', 0);
}
if (!$project_id) {
    $showProject = true;
}

if (!isset($company_id)) {
    $company_id = (int) w2PgetParam($_REQUEST, 'company_id', 0);
}

$obj = new CCompany();
$allowed_companies_ary = $obj->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$allowed_companies = implode(',', array_keys($allowed_companies_ary));

if (!isset($task_id)) {
    $task_id = (int) w2PgetParam($_REQUEST, 'task_id', 0);
}

?>
<script language="javascript" type="text/javascript">
    $('body').ready(function(){
        $('.filestbl,not:(show-files)').hide();
    });
    $('body').delegate('a.has-files', 'click', function(e){
        var folderId = $(this).attr('id');
        var fileTableId = '#filestbl_' + folderId.slice(folderId.lastIndexOf("_")+1);
        var elem = $(fileTableId);
        elem.toggle();
        if(elem.is(':visible')){
            elem.html(elem.html().replace('-', '+'));
        } else {
            elem.html(elem.html().replace('+', '-'));
        }
        return false;
    });
    function addBulkComponent(li) {
        //IE
        if (document.all || navigator.appName == 'Microsoft Internet Explorer') {
            var form = document.frm_bulk;
            var ni = document.getElementById('tbl_bulk');
            var newitem = document.createElement('input');
            var htmltxt = '';
            newitem.id = 'bulk_selected_file['+li+']';
            newitem.name = 'bulk_selected_file['+li+']';
            newitem.type = 'hidden';
            ni.appendChild(newitem);
        } else {
            //Non IE
            var form = document.frm_bulk;
            var ni = document.getElementById('tbl_bulk');
            var newitem = document.createElement('input');
            newitem.setAttribute('id', 'bulk_selected_file['+li+']');
            newitem.setAttribute('name', 'bulk_selected_file['+li+']');
            newitem.setAttribute('type', 'hidden');
            ni.appendChild(newitem);
        }
    }

    function removeBulkComponent(li) {
        var t = document.getElementById('tbl_bulk');
        var old = document.getElementById('bulk_selected_file['+li+']');
        t.removeChild(old);
    }
</script>
<style>
    #folder-list {
        /*  margin-left: -25px;*/
    }
    #folder-list ul {
        padding: 0;
        margin: 0;
    }
    #folder-list ul li {
        list-style: none;
        margin-top: -1px;
        margin-bottom: 0px;
        border: 0px solid #CCC;
    }
    #folder-list ul li ul li {
        margin-left: 25px;
    }

    .folder-name {
        display: block;
        height: 16px;
        padding-top: 0px;
        background: white;
        border-bottom: 1px solid #333;
        border-right: 1px solid #333;
        margin-bottom: 0px;
    }

    .folder-name-current {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        border-bottom: 1px solid #333;
    }

    .has-files {
        font-weight: bold;
    }

    #folder-list .tbl {
        margin-top: 2px;
    }
    #folder-list .tbl th {
        border: none;
    }

    #folder-list p {
        padding: 3px 5px;
        margin-top: -5px;
        margin-left: 25px;
        margin-right: 25px;
        border: 1px solid #CCC;
        border-top: none;
        background: #F9F9F9;
    }
</style>
<table id="tblFolders" width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
    <tr>
        <td colspan="20">
            <?php
            /*             * ** Main Program *** */
            if ($folder_id) {
                ?>
                <a style="float: left" href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=0"><?php echo w2PshowImage('home.png', '22', '22', 'folder icon', 'back to root folder', 'files'); ?></a>
                <?php if (array_key_exists($cfObj->file_folder_parent, $allowed_folders_ary)): ?>
                    <a href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=<?php echo $cfObj->file_folder_parent; ?>"><?php echo w2PshowImage('back.png', '22', '22', 'folder icon', 'back to parent folder', 'files'); ?></a>
                <?php endif; ?>
                <a style="float: left; margin-right: 1em" href="./index.php?m=<?php echo $m; ?>&amp;tab=<?php echo $tab; ?>&a=addedit_folder&folder=<?php echo $cfObj->file_folder_id; ?>" title="edit the <?php echo $cfObj->file_folder_name; ?> folder"><?php echo w2PshowImage('filesaveas.png', '22', '22', 'folder icon', 'edit folder', 'files'); ?></a>
            <?php } ?>
            <img src="<?php echo w2PfindImage('folder5_small.png', 'files'); ?>" width="16" height="16" style="float: left;" />
            <span class="folder-name-current" style="float: left;"><?php echo (isset($cfObj) && $cfObj->file_folder_name) ? $cfObj->file_folder_name : "Root"; ?></span>
            <?php if (isset($cfObj) && $cfObj->file_folder_description != '') { ?>
                <p><?php echo w2p_textarea($cfObj->file_folder_description); ?></p>
            <?php } ?>
        </td>
    </tr>
    <?php
    if (countFiles($folder_id) > 0) {
        echo '<tr><td colspan="20">' . displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id, true, true, false) . '</td></tr>';
    } else {
        echo '<tr><td colspan="20">' . $AppUI->_('no files') . '</td></tr>';
    }

    echo getFolders($folder_id);

    //Lets add our bulk form
    $folders_avail = getFolderSelectList();
    //used O (uppercase 0)instead of 0 (zero) to keep things in place
    $folders = array('-1' => array(0 => 'O', 1 => '(Move to Folder)', 2 => -1)) + array('0' => array(0 => 0, 1 => 'Root', 2 => -1)) + $folders_avail;

    $project = new CProject();
    $sprojects = $project->getAllowedProjects($AppUI, false);
    foreach ($sprojects as $prj_id => $proj_info) {
        $sprojects[$prj_id] = $proj_info['project_name'];
    }
    asort($sprojects);
    $sprojects = array('O' => '(' . $AppUI->_('Move to Project', UI_OUTPUT_RAW) . ')') + array('0' => '(' . $AppUI->_('All Projects', UI_OUTPUT_RAW) . ')') + $sprojects;
    ?>
    <tr>
        <td colspan="50" align="right">
            <form name="frm_bulk" method="post" action="?m=files&a=do_files_bulk_aed" accept-charset="utf-8">
                <input type="hidden" name="redirect" value="<?php echo $current_uri; ?>" />
                <?php echo arraySelect($sprojects, 'bulk_file_project', 'style="width:180px" class="text"', 'O'); ?>
                <?php echo arraySelectTree($folders, 'bulk_file_folder', 'style="width:180px;" class="text"', 'O'); ?>
                <input type="button" class="button" value="<?php echo $AppUI->_('Go'); ?>" onclick="if (confirm('Are you sure you wish to apply the options on the selected files?')) document.frm_bulk.submit();" />
            </form>
        </td>
    </tr>
</table>