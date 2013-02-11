<?php
global $AppUI, $user_id, $user_name, $canEdit, $canDelete, $tab;

if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();
$user_roles = $perms->getUserRoles($user_id);
$crole = new CSystem_Role;
$roles = $crole->getRoles();
// Format the roles for use in arraySelect
$roles_arr = array();
foreach ($roles as $role) {
	if ($role['name'] != 'Administrator') {
        $roles_arr[$role['id']] = $role['name'];
    } else {
        if ($perms->checkModuleItem('system', 'edit')) {
            $roles_arr[$role['id']] = $role['name'];
        }
    }
}

?>

<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this role?' )) {
		var f = document.frmRoles;
		f.del.value = 1;
		f.role_id.value = id;
		f.submit();
	}
}
function clearIt(){
	var f = document.frmRoles;
	f.sqlaction2.value = "<?php echo $AppUI->_('add'); ?>";
	f.user_role.selectedIndex = 0;
}
<?php
} ?>

</script>

<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td width="50%" valign="top">
<table class="tbl list">
<tr>
	<th width="100%"><?php echo $AppUI->_('Role'); ?></th>
	<th>&nbsp;</th>
</tr>

<?php
foreach ($user_roles as $row) {
	$buf = '';

	$style = '';
	$buf .= "<td>" . $row['name'] . "</td>";

	$buf .= '<td nowrap>';
	if ($canEdit) {
		$buf .= "<a href=\"javascript:delIt({$row['id']});\" title=\"" . $AppUI->_('delete') . "\">" . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
	}
	$buf .= '</td>';

	echo "<tr>$buf</tr>";
}
?>
</table>

</td><td width="50%" valign="top">

<?php if ($canEdit) { ?>

<form name="frmRoles" method="post" action="?m=admin" accept-charset="utf-8">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_userrole_aed" />
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="user_name" value="<?php echo $user_name; ?>" />
	<input type="hidden" name="role_id" value="" />
	<table cellspacing="1" cellpadding="2" border="0" class="std" width="100%">
		<tr>
			<th colspan='2'><?php echo $AppUI->_('Add Role'); ?></th>
		</tr>
		<tr>
			<td colspan='2' width="100%"><?php echo arraySelect($roles_arr, 'user_role', 'size="1" class="text"', '', true); ?></td>
		</tr>
		<tr>
			<td>
				<input type="reset" value="<?php echo $AppUI->_('clear'); ?>" class="button" name="sqlaction" onclick="clearIt();" />
			</td>
			<td align="right">
				<?php
					if (!count($user_roles)) {
						echo $AppUI->_('Notify New User Activation');
						?> <input type='checkbox' name='notify_new_user' />&nbsp;&nbsp;&nbsp;&nbsp;<?php
					}
				?>
				<input type="submit" value="<?php echo $AppUI->_('add'); ?>" class="button" name="sqlaction2" />
			</td>
		</tr>
	</table>
</form>

<?php } ?>

</td>
</tr>
</table>