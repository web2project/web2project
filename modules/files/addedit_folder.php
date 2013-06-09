<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$file_folder_parent = intval(w2PgetParam($_GET, 'file_folder_parent', 0));
$folder_id = intval(w2PgetParam($_GET, 'folder', 0));

$folder = new CFile_Folder();
$folder->file_folder_id = $folder_id;

$obj = $folder;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $folder = $obj;
    $folder_id = $folder->file_folder_id;
} else {
    $obj = $folder->load($folder_id);
}
if (!$folder && $folder_id > 0) {
	$AppUI->setMsg('File Folder');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

// add to allow for returning to other modules besides Files
$referrerArray = parse_url($_SERVER['HTTP_REFERER']);
$referrer = $referrerArray['query'] . $referrerArray['fragment'];

// check permissions for this record
if ($folder_id == 0) {
	$canEdit = $canAuthor;
}
if (!$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$folders = getFolderSelectList();





// setup the title block
$ttl = $folder_id ? 'Edit File Folder' : 'Add File Folder';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'folder5.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=files', 'files list');
$canDelete = $folder->canDelete();
if ($canDelete) {
	$titleBlock->addCrumbDelete('delete file folder', $canDelete, $msg);
}
$titleBlock->show();

?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.folderFrm;
	var msg = '';
	if (f.file_folder_name.value.length < 1) {
		msg += "\n<?php echo $AppUI->_('Folder Name'); ?>";
		f.file_folder_name.focus();
	}
	if( msg.length > 0) {
		alert('<?php echo $AppUI->_('Please type'); ?>:' + msg);
	} else {
		f.submit();
	}
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('Delete Folder'); ?>" )) {
		var f = document.folderFrm;
		f.del.value='1';
		f.submit();
	}
}
</script>
<form name="folderFrm" action="?m=files" enctype="multipart/form-data" method="post">
	<input type="hidden" name="dosql" value="do_folder_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_folder_id" value="<?php echo $folder_id; ?>" />
	<input type="hidden" name="redirect" value="<?php echo $referrer; ?>" />
    <table width="100%" border="0" cellpadding="3" cellspacing="3" class="std addedit">
        <tr>
            <td width="100%" valign="top" align="center">
                <table cellspacing="1" cellpadding="2" width="100%" class="well">
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Subfolder of'); ?>:</td>
                        <td align="left">
                        <?php
                            $parent_folder = ($folder_id > 0) ? $folder->file_folder_parent : $file_folder_parent;
                            echo arraySelectTree($folders, 'file_folder_parent', 'style="width:175px;" class="text"', $parent_folder);
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Folder Name'); ?>:</td>
                        <td align="left">
                            <input type="text" class="text" id="ffn" name="file_folder_name" value="<?php echo $folder->file_folder_name; ?>" maxlength="255" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
                        <td align="left">
                            <textarea name="file_folder_description" class="textarea" rows="4" style="width:270px"><?php echo $folder->file_folder_description; ?></textarea>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <input class="button btn btn-danger" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?'); ?>')){location.href = '?<?php echo $referrer; ?>';}" />
            </td>
            <td align="right">
                <input type="button" class="button btn btn-primary" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
            </td>
        </tr>
    </table>
</form>