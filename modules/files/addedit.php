<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$folder = (int) w2PgetParam($_GET, 'folder', 0);
$object_id = (int) w2PgetParam($_GET, 'file_id', 0);
$ci = w2PgetParam($_GET, 'ci', 0) == 1 ? true : false;
$preserve = $w2Pconfig['files_ci_preserve_attr'];

$object = new CFile();
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
    $object_id = $file->getId();
} else {
    $obj = $object->load($object_id);
}
if (!$object && $object_id > 0) {
	$AppUI->setMsg('File');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

if (file_exists(W2P_BASE_DIR . '/modules/helpdesk/config.php')) {
	include (W2P_BASE_DIR . '/modules/helpdesk/config.php');
}
$canAdmin = canEdit('system');
// add to allow for returning to other modules besides Files
$referrerArray = parse_url($_SERVER['HTTP_REFERER']);
$referrer = $referrerArray['query'];

$file_task = (int) w2PgetParam($_GET, 'file_task', $object->file_task);
$file_parent = (int) w2PgetParam($_GET, 'file_parent', 0);
$file_project = (int) w2PgetParam($_GET, 'project_id', 0);

if ($object_id > 0) {
	// Check to see if the task or the project is also allowed.
    $perms = &$AppUI->acl();
	if ($object->file_task) {
		if (!$perms->checkModuleItem('tasks', 'view', $object->file_task)) {
			$AppUI->redirect(ACCESS_DENIED);
		}
	}
	if ($object->file_project) {
		if (!$perms->checkModuleItem('projects', 'view', $object->file_project)) {
			$AppUI->redirect(ACCESS_DENIED);
		}
	}
}

if ($object->file_checkout != $AppUI->user_id) {
	$ci = false;
}

if (!$canAdmin)
	$canAdmin = $object->canAdmin();

if ($object->file_checkout == 'final' && !$canAdmin) {
	$AppUI->redirect(ACCESS_DENIED);
}
// setup the title block
$ttl = $object_id ? 'Edit File' : 'Add File';
$ttl = $ci ? 'Checking in' : $ttl;
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$canDelete = $object->canDelete();

if ($canDelete && $object_id > 0 && !$ci) {
	$titleBlock->addCrumbDelete('delete file', $canDelete, $msg);
}
$titleBlock->show();

//Clear the file id if checking out so a new version is created.
if ($ci) {
	$object_id = 0;
}

if ($object->file_project) {
	$file_project = $object->file_project;
}

$task = new CTask();
$task->load($file_task);
$task_name = $task->task_name;

if (isset($object->file_helpdesk_item)) {
	$file_helpdesk_item = $object->file_helpdesk_item;
}
$folders = getFolderSelectList();
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.editFrm;
	f.submit();
}
function cancelIt() {
	var f = document.editFrm;
	f.cancel.value='1';
	f.submit();
}
function delIt() {
	if (confirm( '<?php echo $AppUI->_('filesDelete', UI_OUTPUT_JS); ?>' )) {
		var f = document.editFrm;
		f.del.value='1';
		f.submit();
	}
}
function popTask() {
	var f = document.editFrm;
	if (f.file_project.selectedIndex == 0) {
		alert( '<?php echo $AppUI->_('Please select a project first!', UI_OUTPUT_JS); ?>' );
	} else {
		window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&task_project=' + f.file_project.options[f.file_project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
	}
}

function finalCI() {
	var f = document.editFrm;
	if (f.final_ci.value == '1') {
		f.file_checkout.value = 'final';
		f.file_co_reason.value = 'Final Version';
	} else {
		f.file_checkout.value = '';
		f.file_co_reason.value = '';
	}
}

// Callback function for the generic selector
function setTask( key, val ) {
	var f = document.editFrm;
	if (val != '') {
		f.file_task.value = key;
		f.task_name.value = val;
	} else {
		f.file_task.value = '0';
		f.task_name.value = '';
	}
}
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('files/addedit');