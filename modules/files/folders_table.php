<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $deny1, $canRead, $canEdit, $allowed_folders_ary,
    $denied_folders_ary, $tab, $folder, $cfObj, $m, $a, $company_id,
    $allowed_companies, $showProject, $folder_id;

$canEdit = canEdit($m);
$canRead = canView($m);

// The Files index sets these up; set them here too so the page also works
// on its own (?m=files&a=folders_table).
if (!isset($folder_id)) {
    $folder_id = (int) w2PgetParam($_GET, 'folder', 0);
}
if (!($folder instanceof CFile_Folder)) {
    $folder = new CFile_Folder();
}
if (!is_array($allowed_folders_ary)) {
    // Folders have no permissions of their own: everyone who can see Files
    // sees every folder.
    $allowed_folders_ary = $folder->getAllowedRecords($AppUI->user_id);
}
if ($folder_id && !$folder->load($folder_id)) {
    $AppUI->setMsg('File Folder');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=files');
}

// add to allow for returning to other modules besides Files
$current_uriArray = parse_url($_SERVER['REQUEST_URI']);
$current_uri = isset($current_uriArray['query']) ? $current_uriArray['query'] : '';

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

$file_types = w2PgetSysVal('FileType');
?>
<script language="javascript" type="text/javascript">
function expand(id){
	var element = document.getElementById(id);
	element.style.display = (element.style.display == '' || element.style.display == 'none') ? 'block' : 'none';
}
</script>

<div id="tblFolders" class="w2p-folders">
    <div class="d-flex flex-wrap align-items-center gap-2 border-bottom pb-1 mb-2">
        <ul>
            <?php if ($folder_id) { ?>
            <li><a href="./index.php?m=<?php echo $m; ?>&amp;a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&amp;folder=0"><?php echo w2PshowImage('home.png', '22', '22', 'back to root folder', '', 'files'); ?></a></li>
            <?php if (array_key_exists($folder->file_folder_parent, $allowed_folders_ary)): ?>
                <li><a href="./index.php?m=<?php echo $m; ?>&amp;a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&amp;folder=<?php echo $folder->file_folder_parent; ?>"><?php echo w2PshowImage('back.png', '22', '22', 'folder icon', 'back to parent folder', 'files'); ?></a></li>
            <?php endif; ?>
            <li><a href="./index.php?m=<?php echo $m; ?>&amp;tab=<?php echo $tab; ?>&amp;a=addedit_folder&amp;folder=<?php echo $folder->file_folder_id; ?>" title="<?php echo $AppUI->_('edit this folder'); ?>"><?php echo w2PshowImage('filesaveas.png', '22', '22', 'folder icon', 'edit folder', 'files'); ?></a></li>
            <?php } ?>
            <li class="info-text"><?php echo w2PshowImage('folder5_small.png', '22', '22', '', '', 'files'); ?> <strong><?php echo $folder->file_folder_name ? w2PformSafe($folder->file_folder_name) : $AppUI->_('Root'); ?></strong></li>
            <?php if ($folder->file_folder_description != '') { ?>
                <li class="info-text"><?php echo w2p_textarea($folder->file_folder_description); ?></li>
            <?php } ?>
        </ul>
    </div>
    <?php
    if ($folder->countFiles($folder_id, $task_id, $project_id, $company_id) > 0) {
        echo '<table class="tbl list">' . displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id) . '</table>';
    } else {
        echo '<p>' . $AppUI->_('no files') . '</p>';
    }

    echo getFolders($folder_id);
?>
</div>