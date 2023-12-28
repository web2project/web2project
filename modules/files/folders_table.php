<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $deny1, $canRead, $canEdit, $allowed_folders_ary,
    $denied_folders_ary, $tab, $folder, $cfObj, $m, $a, $company_id,
    $allowed_companies, $showProject, $folder, $folder_id;

$canEdit = canEdit($m);
$canRead = canView($m);

if ($folder_id && !$folder->load($folder_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

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

$myFolder = new CFile_Folder();
$xpg_totalrecs = 0;//$myFolder->getFileCountByFolder(null, $folder_id, $task_id, $project_id, $company_id, $allowed_companies);
?>
<script language="javascript" type="text/javascript">
function expand(id){
	var element = document.getElementById(id);
	element.style.display = (element.style.display == '' || element.style.display == 'none') ? 'block' : 'none';
}
function addBulkComponent(li) {
//IE
	if (document.all || navigator.appName == 'Microsoft Internet Explorer') {
		var ni = document.getElementById('frm_bulk');
		var newitem = document.createElement('input');
		newitem.id = 'bulk_selected_file['+li+']';
		newitem.name = 'bulk_selected_file['+li+']';
		newitem.type = 'hidden';
		ni.appendChild(newitem);
	} else {
//Non IE
		var ni = document.getElementById('frm_bulk');
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

<table id="tblFolders" class="tbl list">
    <tr>
        <td nowrap="nowrap" colspan="20">
            <ul>
                <?php if ($folder_id) { ?>
                <li><a href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=0"><?php echo w2PshowImage('home.png', '22', '22', 'back to root folder', '', 'files'); ?></a></li>
                <?php if (array_key_exists($folder->file_folder_parent, $allowed_folders_ary)): ?>
                    <li><a href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=<?php echo $folder->file_folder_parent; ?>"><?php echo w2PshowImage('back.png', '22', '22', 'folder icon', 'back to parent folder', 'files'); ?></a></li>
        		<?php endif; ?>
                <li><a href="./index.php?m=<?php echo $m; ?>&amp;tab=<?php echo $tab; ?>&a=addedit_folder&folder=<?php echo $folder->file_folder_id; ?>" title="edit the <?php echo $folder->file_folder_name; ?> folder"><?php echo w2PshowImage('filesaveas.png', '22', '22', 'folder icon', 'edit folder', 'files'); ?></a></li>
                <?php } ?>
                <li class="info-text"><?php echo w2PshowImage('folder5_small.png', '22', '22', '', '', 'files'); ?> <strong><?php echo (isset($folder) && $folder->file_folder_name) ? $folder->file_folder_name : "Root"; ?></strong></li>
                <?php if (isset($folder) && $folder->file_folder_description != '') { ?>
                    <li class="info-text"><?php echo w2p_textarea($folder->file_folder_description); ?></li>
                <?php } ?>
            </ul>
        </td>
    </tr>
    </tr>
    <?php
    $fileCount = $folder->getFiles();
    if (count($fileCount) > 0) {
        echo displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id);
    } elseif ((!empty($limited) && !$limited) or $folder_id != 0) {
        echo '<tr><td colspan="20">' . $AppUI->_('no files') . '</td></tr>';
    }

    echo getFolders($folder_id);
?>
</table>