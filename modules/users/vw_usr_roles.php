<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $user_id, $user_name, $canDelete, $tab;
 $canEdit = canEdit('users');
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

<div class="row g-3">
        <div class="col-lg-6">
            <table class="tbl list">
                <tr>
                    <th width="100%"><?php echo $AppUI->_('Role'); ?></th>
                    <th>&nbsp;</th>
                </tr>
                <?php
                foreach ($user_roles as $row) {
                    $buf = "<td>" . $row['name'] . "</td>";
                    $buf .= '<td nowrap>';
                    if ($canEdit) {
                        $buf .= "<a href=\"javascript:delIt({$row['id']});\" title=\"" . $AppUI->_('delete') . "\">" . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
                    }
                    $buf .= '</td>';

                    echo "<tr>$buf</tr>";
                }
                ?>
            </table>
        </div>
        <div class="col-lg-6">

<?php if ($canEdit) { ?>

<form name="frmRoles" method="post" action="?m=users" accept-charset="utf-8">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_userrole_aed" />
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="user_name" value="<?php echo $user_name; ?>" />
	<input type="hidden" name="role_id" value="" />
	<div class="std addedit">
		<div class="column left w2p-wide">
			<h3 class="h6 border-bottom pb-1"><?php echo $AppUI->_('Add Role'); ?></h3>
			<p><?php echo arraySelect($roles_arr, 'user_role', 'size="1" class="text"', '', true); ?></p>
		</div>
		<div class="w2p-form-actions align-items-center">
			<input type="reset" value="<?php echo $AppUI->_('clear'); ?>" class="button btn btn-danger" name="sqlaction" onclick="clearIt();" />
			<span class="d-inline-flex align-items-center gap-2">
				<?php
					if (!count($user_roles)) {
						echo $AppUI->_('Notify New User Activation');
						?> <input type='checkbox' name='notify_new_user' /><?php
					}
				?>
				<input type="submit" value="<?php echo $AppUI->_('add'); ?>" class="button btn btn-primary" name="sqlaction2" />
			</span>
		</div>
	</div>
</form>

<?php } ?>

        </div>
</div>