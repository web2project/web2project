<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'forum_id', 0);



$object = new CForum();
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
    $object_id = $object->getId();
} else {
    $object->load($object_id);
}
if (!$object && $object_id > 0) {
    $AppUI->setMsg('Forum');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

$status = isset($object->forum_status) ? $object->forum_status : -1;

$prj = new CProject();
if ($object_id) {
    $projects = $prj->getAllowedProjects($AppUI->user_id, false);
} else {
    $projects = $prj->getAllowedProjects($AppUI->user_id, true);
}
foreach ($projects as $project_id => $project_info) {
	$projects[$project_id] = $project_info['project_name'];
}
$projects = arrayMerge(array(0 => $AppUI->_('All Projects')), $projects);

// check permissions for this record
$perms = &$AppUI->acl();
$users = $perms->getPermittedUsers('forums');


// setup the title block
$ttl = $object_id > 0 ? 'Edit Forum' : 'Add Forum';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$titleBlock->addViewLink('forum', $object_id, 'viewer');
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
function submitIt(){
	var form = document.editFrm;
	if(form.forum_name.value.search(/^\s*$/) >= 0 ) {
		alert("<?php echo $AppUI->_('forumName', UI_OUTPUT_JS); ?>");
		form.forum_name.focus();
	} else if(form.forum_owner.value < 1) {
		alert("<?php echo $AppUI->_('forumSelectOwner', UI_OUTPUT_JS); ?>");
		form.forum_owner.focus();
	} else {
		form.submit();
	}
}
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('forums/addedit');