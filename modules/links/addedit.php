<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$object_id    = (int) w2PgetParam($_GET, 'link_id', 0);
$task_id    = (int) w2PgetParam($_GET, 'task_id', 0);
$project_id = (int) w2PgetParam($_GET, 'project_id', 0);

$object = new CLink();
$object->setId($object_id);
$object->link_owner = $AppUI->user_id;

$obj = $object;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
$canDelete = $object->canDelete();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject($object);
if ($obj) {
    $object = $obj;
    $object_id = $object->getId();
} else {
    $object->load($object_id);
}
if (!$object && $object_id > 0) {
    $AppUI->setMsg('Link');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

if (0 == $object_id && ($project_id || $task_id)) {

    // We are creating a link, so if we have them lets figure out the project
    // and task id
    $object->link_project = $project_id;
    $object->link_task    = $task_id;
}

// The read-only Task field shows the name of the link's task, for a new link
// opened from a task and for an existing link alike, when the user may see
// that task. A new link opened from a task without a project takes the
// task's project, so the task selector has a project to list.
$task_name = '';
if ((int) $object->link_task) {
    $link_task = new CTask();
    if ($link_task->load((int) $object->link_task) && $link_task->canView()
        && $link_task->canAccess($AppUI->user_id, false)) {
        $task_name = (string) $link_task->task_name;
        if (!(int) $object->link_project) {
            $object->link_project = (int) $link_task->task_project;
        }
    }
}

// setup the title block
$ttl = $object_id ? 'Edit Link' : 'Add Link';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($canDelete && $object_id) {
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

$view = new w2p_Controllers_View($AppUI, $object, 'Link');
echo $view->renderDelete();
?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.editFrm;
	f.submit();
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