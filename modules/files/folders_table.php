<?php
/* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit, $allowed_folders_ary,
 $denied_folders_ary, $tab, $folder, $cfObj, $m, $a, $company_id,
 $allowed_companies, $showProject;

/*
 * Fetched from cleanup_functions.php since this is the ONLY place where
 * getFolders is used.
 * 
 * $parent is the parent of the children we want to see
 * $level is increased when we go deeper into the tree, used to display a nice indented tree
 */

function getFolders($parent, $level = 0) {
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

$canEdit = canEdit($m);
$canRead = canView($m);

$folder_id = (int) $folder;
if ($folder_id) {
    $cfObj->load($folder_id);
    $msg = '';
    $canDelete = $cfObj->canDelete($msg, $folder_id);
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

$page = (int) w2PgetParam($_GET, 'page', 1);

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

$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

$file_types = w2PgetSysVal('FileType');

$myFolder = new CFileFolder();
$xpg_totalrecs = $myFolder->getFileCountByFolder($AppUI, $folder_id, $task_id, $project_id, $company_id, $allowed_companies);
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
    } elseif (!$limited or $folder_id != 0) {
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
        $sprojects[$prj_id] = $idx_companies[$prj_id] . ': ' . $proj_info['project_name'];
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