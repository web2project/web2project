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
<form name="form_buttons" method="post" action="index.php?<?php echo 'm=' . $m . '&a=' . $a . '&date=' . $date; ?>">
<input type="hidden" name="show_form" value="1" />
<tr>
	<td width="50%">
		<?php
			if ($other_users) {
				$selectedUser = w2PgetParam($_POST, 'show_user_todo', $AppUI->user_id);
				$users = $perms->getPermittedUsers('tasks');
				echo arraySelect($users, 'show_user_todo', 'class="text" onchange="document.form_buttons.submit()"', $selectedUser);
			}
		?>
	</td>
</tr>
</form>
</table>
<?php
$min_view = true;
include W2P_BASE_DIR . '/modules/tasks/viewgantt.php';
?>