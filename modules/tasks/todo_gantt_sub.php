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
	echo $AppUI->_('Show Todo for:') . '<select name="show_user_todo" onchange="document.form_buttons.submit()">';
	if (($rows = w2PgetUsersList())) {
		foreach ($rows as $row) {
			if ($user_id == $row['user_id']) {
				echo '<option value="' . $row['user_id'] . '" selected="selected">' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
			} else {
				echo '<option value="' . $row['user_id'] . '">' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
			}
		}
	}
}
?>
		</select>
	</td>
</tr>
</form>
</table>
<?php
$min_view = true;
include W2P_BASE_DIR . '/modules/tasks/viewgantt.php';
?>