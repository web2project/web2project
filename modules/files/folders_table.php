<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit, $allowed_folders_ary,
    $denied_folders_ary, $tab, $folder, $cfObj, $m, $a, $company_id,
    $allowed_companies, $showProject, $current_uri;

$canEdit = canEdit($m);
$canRead = canView($m);

$folder_id = (int) $folder;
if ($folder_id) {
    $cfObj->load($folder);
    $msg = '';
    $canDelete = $cfObj->canDelete($msg, $folder);
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

$myFolder = new CFile_Folder();
$xpg_totalrecs = $myFolder->getFileCountByFolder(null, $folder_id, $task_id, $project_id, $company_id, $allowed_companies);
?>
<script language="javascript" type="text/javascript">
function doSubmit() {
	if (confirm('<?php echo $AppUI->_('Are you sure you wish to apply the options on the selected files?') ?>')) {
		// Let's compose the file list
		var f = document.frm_bulk;
		var files = new Array();
	        // harvest all checked checkboxes (files to process)
		var inputs = document.getElementsByName('file_sel');
	        for (var i=0, i_cmp=inputs.length; i < i_cmp; i++) {
	                var el1 = inputs[i];
	                // only if it's a checkbox.
	                if(el1.checked == true)
	                {
				files.push(el1.value);
			}
		}
		f.bulk_selected_files.value = files.join(',');
		f.submit();
	}
}

function expand(id){
	var element = document.getElementById(id);
	element.style.display = (element.style.display == '' || element.style.display == 'none') ? 'block' : 'none';
}
</script>

<table id="tblFolders" class="tbl list">
    <tr>
        <td nowrap="nowrap" colspan="20">
            <ul>
                <?php if ($folder_id) { ?>
                <li><a href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=0"><?php echo w2PshowImage('home.png', '22', '22', 'back to root folder', '', 'files'); ?></a></li>
                <?php if (array_key_exists($cfObj->file_folder_parent, $allowed_folders_ary)): ?>
                    <li><a href="./index.php?m=<?php echo $m; ?>&amp;&a=<?php echo $a; ?>&amp;tab=<?php echo $tab; ?>&folder=<?php echo $cfObj->file_folder_parent; ?>"><?php echo w2PshowImage('back.png', '22', '22', 'folder icon', 'back to parent folder', 'files'); ?></a></li>
        		<?php endif; ?>
                <li><a href="./index.php?m=<?php echo $m; ?>&amp;tab=<?php echo $tab; ?>&a=addedit_folder&folder=<?php echo $cfObj->file_folder_id; ?>" title="edit the <?php echo $cfObj->file_folder_name; ?> folder"><?php echo w2PshowImage('filesaveas.png', '22', '22', 'folder icon', 'edit folder', 'files'); ?></a></li>
                <?php } ?>
                <li class="info-text"><?php echo w2PshowImage('folder5_small.png', '22', '22', '', '', 'files'); ?> <strong><?php echo (isset($cfObj) && $cfObj->file_folder_name) ? $cfObj->file_folder_name : "Root"; ?></strong></li>
                <?php if (isset($cfObj) && $cfObj->file_folder_description != '') { ?>
                    <li class="info-text"><?php echo w2p_textarea($cfObj->file_folder_description); ?></li>
                <?php } ?>
            </ul>
        </td>
    </tr>
    <?php
    if (countFiles($folder) > 0) {
        echo displayFiles($AppUI, $folder_id, $task_id, $project_id, $company_id, $canEdit);
    } elseif ((!empty($limited) && !$limited) or $folder_id != 0) {
        echo '<tr><td colspan="20">' . $AppUI->_('no files') . '</td></tr>';
    }

    echo getFolders($folder_id, $canEdit);

    if ($canEdit) {

	    //Lets add our bulk form
	    $folders_avail = getFolderSelectList();
	    //used O (uppercase 0)instead of 0 (zero) to keep things in place
	    $folders = array('-1' => array(0 => 'O', 1 => '(Move to Folder)', 2 => -1)) + array('0' => array(0 => 0, 1 => 'Root', 2 => -1)) + $folders_avail;

	    $project = new CProject();
	    $sprojects = $project->getAllowedProjects($AppUI->user_id, false);
	    foreach ($sprojects as $prj_id => $proj_info) {
	        $sprojects[$prj_id] = $idx_companies[$prj_id] . ': ' . $proj_info['project_name'];
	    }
	    asort($sprojects);
	    $sprojects = array('O' => '(' . $AppUI->_('Move to Project', UI_OUTPUT_RAW) . ')') + array('0' => '(' . $AppUI->_('All Projects', UI_OUTPUT_RAW) . ')') + $sprojects;
	    ?>
		<tr>
		    <form name="frm_bulk" method="post" action="?m=files&a=do_files_bulk_aed" accept-charset="utf-8">
	                <input type="hidden" name="redirect" value="<?php echo $current_uri; ?>" />
	                <input type="hidden" name="bulk_selected_files" value="" />
		    	<td colspan="50" align="right">
		            <?php echo arraySelect($sprojects, 'bulk_file_project', 'style="width:180px" class="text"', -1); ?>
		            <?php echo arraySelectTree($folders, 'bulk_file_folder', 'style="width:180px;" class="text"', -1); ?>
		            <input type="button" class="button" value="<?php echo $AppUI->_('Go'); ?>" onclick="doSubmit();" />
			</td>
		    </form>
		</tr>
	<?php } ?>
</table>

