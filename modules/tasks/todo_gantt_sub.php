<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $showEditCheckbox, $tasks, $priorities;
global $m, $a, $date, $min_view, $other_users, $showPinned, $showArcProjs, $showHoldProjs, $showDynTasks, $showLowTasks, $showEmptyDate, $user_id;
$perms = &$AppUI->acl();
$canDelete = $perms->checkModuleItem($m, 'delete');
?>
<table width="100%" border="0" cellpadding="1" cellspacing="0">
<form name="form_buttons" method="post" action="index.php?<?php echo 'm=' . $m . '&a=' . $a . '&date=' . $date; ?>" accept-charset="utf-8">
<input type="hidden" name="show_form" value="1" />
	// web2project_gii
// Erased selectedUsers comboBox: now it s handled in viewgantt.php
	// /web2project_gii

</form>
</table>
<?php
$min_view = true;
include W2P_BASE_DIR . '/modules/tasks/viewgantt.php';