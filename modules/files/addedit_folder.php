<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
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

$folders = getFolderSelectList();

// setup the title block
$ttl = $folder_id ? 'Edit File Folder' : 'Add File Folder';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

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
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="folderFrm" action="?m=<?php echo $m; ?>" enctype="multipart/form-data" method="post" class="addedit files-folder">
	<input type="hidden" name="dosql" value="do_folder_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_folder_id" value="<?php echo $folder_id; ?>" />
	<input type="hidden" name="redirect" value="<?php echo $referrer; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit departments">
        <div class="column left">
            <p>
                <?php $form->showLabel('Subfolder of'); ?>
                <?php
                $parent_folder = ($folder_id > 0) ? $folder->file_folder_parent : $file_folder_parent;
                echo arraySelect($folders, 'file_folder_parent', 'style="width:175px;" class="text"', $parent_folder);
                ?>
            </p>
            <p>
                <?php $form->showLabel('Folder Name'); ?>
                <?php $form->showField('file_folder_name', $folder->file_folder_name, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('file_folder_description', $folder->file_folder_description); ?>
            </p>
            <?php $form->showCancelButton(); ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>