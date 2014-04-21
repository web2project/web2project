<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// pull all the key types
$perms = &$AppUI->acl();

// Get the permissions for this module
$canAccess = canAccess('roles');
if (!$canAccess) {
	$AppUI->redirect(ACCESS_DENIED);
}
$canRead = canView('roles');
$canAdd = canAdd('roles');
$canEdit = canEdit('roles');
$canDelete = canDelete('roles');

$crole = new CSystem_Role;
$roles = $crole->getRoles();

$role_id = (int) w2PgetParam($_GET, 'role_id', 0);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Roles', 'main-settings.png', $m);
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();

$crumbs = array();
$crumbs['?m=system'] = 'System Admin';

?>

<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.roleFrm;
		f.del.value = 1;
		f.role_id.value = id;
		f.submit();
	}
}
<?php } ?>
</script>

<table class="tbl list">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Role ID'); ?></th>
	<th><?php echo $AppUI->_('Description'); ?></th>
</tr>
<?php

// do the modules that are installed on the system
$s = '';
foreach ($roles as $row) {
	echo showRoleRow($row);
}
// add in the new key row:
if ($role_id == 0) {
	echo showRoleRow();
}
?>
</table>