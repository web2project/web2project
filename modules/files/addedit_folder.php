<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$file_folder_parent = intval(w2PgetParam($_GET, 'file_folder_parent', 0));
$object_id = intval(w2PgetParam($_GET, 'folder', 0));


$object = new CFile_Folder();
$object->setId($object_id);

$obj = $object;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $object = $obj;
    $object_id = $object->file_folder_id;
} else {
    $obj = $object->load($object_id);
}
if (!$object && $object_id > 0) {
	$AppUI->setMsg('File Folder');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect('m=' . $m);
}

$folders = getFolderSelectList();

// setup the title block
$ttl = $object_id ? 'Edit File Folder' : 'Add File Folder';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

$canDelete = $object->canDelete();
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
    if (confirm( '<?php echo $AppUI->_('doDelete') . ' ' . $AppUI->_('Folder') . '?'; ?>' )) {
        $.post("?m=companies",
            {dosql: "do_folder_aed", del: 1, file_folder_id: <?php echo $object_id; ?>},
            window.location = "?m=companies"
        );
    }
}
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('files/addedit_folder');