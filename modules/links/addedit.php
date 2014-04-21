<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$link_id    = (int) w2PgetParam($_GET, 'link_id', 0);
$task_id    = (int) w2PgetParam($_GET, 'task_id', 0);
$project_id = (int) w2PgetParam($_GET, 'project_id', 0);

$link = new CLink();
$link->link_id = $link_id;

$obj = $link;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $link = $obj;
    $link_id = $link->link_id;
} else {
    $link->load($link_id);
}
if (!$link && $link_id > 0) {
    $AppUI->setMsg('Link');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

if (0 == $link_id && ($project_id || $task_id)) {

    // We are creating a link, so if we have them lets figure out the project
    // and task id
    $link->link_project = $project_id;
    $link->link_task    = $task_id;

    if ($task_id) {
        $link_task = new CTask;
        $link_task->load($task_id);
        $link->task_name = $link_task->task_name;
    }
}

// setup the title block
$ttl = $link_id ? 'Edit Link' : 'Add Link';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$canDelete = $link->canDelete();
if ($canDelete && $link_id) {
    if (!isset($msg)) {
        $msg = '';
    }
	$titleBlock->addCrumbDelete('delete link', $canDelete, $msg);
}
$titleBlock->show();

$prj = new CProject();
$projects = $prj->getAllowedProjects($AppUI->user_id, false);
foreach ($projects as $project_id => $project_info) {
	$projects[$project_id] = $project_info['project_name'];
}
$projects = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_JS)), $projects);

$types = w2PgetSysVal('LinkType');

// Load the users
$perms = &$AppUI->acl();
$users = $perms->getPermittedUsers('links');
?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.editFrm;
	f.submit();
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('linksDelete', UI_OUTPUT_JS); ?>" )) {
		var f = document.editFrm;
		f.del.value='1';
		f.submit();
	}
}
function popTask() {
    var f = document.editFrm;
    if (f.link_project.selectedIndex == 0) {
        alert( "<?php echo $AppUI->_('Please select a project first!', UI_OUTPUT_JS); ?>" );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&task_project='
            + f.link_project.options[f.link_project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
    }
}

// Callback function for the generic selector
function setTask( key, val ) {
    var f = document.editFrm;
    if (val != '') {
        f.link_task.value = key;
        f.task_name.value = val;
    } else {
        f.link_task.value = '0';
        f.task_name.value = '';
    }
}
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('links/addedit');