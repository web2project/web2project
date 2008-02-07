<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();
$perms = &$AppUI->acl();
$role_id = w2PgetParam($_GET, 'role_id', 0);
$role = $perms->getRole($role_id);

if (isset($_GET['tab'])) {
	$AppUI->setState('RoleVwTab', w2PgetParam($_GET, 'tab', null));
}
$tab = $AppUI->getState('RoleVwTab') !== null ? $AppUI->getState('RoleVwTab') : 0;

if (!is_array($role)) {
	$titleBlock = new CTitleBlock('Invalid Role', 'main-settings.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb("?m=system&u=roles", "role list");
	$titleBlock->show();
} else {
	$titleBlock = new CTitleBlock('View Role', 'main-settings.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=system&u=roles', 'role list');
	$titleBlock->show();
	// Now onto the display of the user.
?>
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Role ID'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $role["value"]; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($role["name"]); ?></td>
		</tr>
</table>
<?php
	if (function_exists('styleRenderBoxBottom')) {
		echo styleRenderBoxBottom();
	}
	$tabBox = new CTabBox('?m=system&u=roles&a=viewrole&role_id=' . $role_id, W2P_BASE_DIR . '/modules/system/roles/', $tab);
	$tabBox->add('vw_role_perms', 'Permissions');
	$tabBox->show();
} // End of check for valid role
?>