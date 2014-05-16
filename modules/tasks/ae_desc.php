<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $object_id, $object, $users, $task_access, $department_selection_list;
global $task_parent_options, $w2Pconfig, $projects, $task_project, $can_edit_time_information, $tab;
global $form;

$task_access = array(CTask::ACCESS_PUBLIC => 'Public', CTask::ACCESS_PROTECTED => 'Protected', CTask::ACCESS_PARTICIPANT => 'Participant', CTask::ACCESS_PRIVATE => 'Private');

/*
 * TODO: when we have an error and bouce back to this screen for the flash
 *   message, the arrays - task_access and others - are not being reset to
 *   good/safe values. I'm not sure of the best approach at the moment.
 *   ~ caseydk - 25 Nov 2011
 */
$perms = &$AppUI->acl();

include $AppUI->getTheme()->resolveTemplate('tasks/addedit_desc');
?>
<script language="javascript" type="text/javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.detailFrm, checkDetail, saveDetail));
</script>
