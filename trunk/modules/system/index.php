<?php /* SYSTEM $Id$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$perms = &$AppUI->acl();
if (!$perms->checkModule('system', 'view')) { // let's see if the user has sys access
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();

$titleBlock = new CTitleBlock('System Administration', '48_my_computer.png', $m, $m . '.' . $a);
$titleBlock->show();
?>
<table class="std" width="100%" border="0" cellpadding="0" cellspacing="5">
<tr>
	<td width="42">
		<?php echo w2PshowImage('rdf2.png', 42, 42, ''); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Language Support'); ?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=translate"><?php echo $AppUI->_('Translation Management'); ?></a>
	</td>
</tr>
<tr>
	<td>
		<?php echo w2PshowImage('myevo-weather.png', 42, 42, ''); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Preferences'); ?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=systemconfig"><?php echo $AppUI->_('System Configuration'); ?></a>
		<br /><a href="?m=system&a=addeditpref"><?php echo $AppUI->_('Default User Preferences'); ?></a>
		<br /><a href="?m=system&u=syskeys&a=keys"><?php echo $AppUI->_('System Lookup Keys'); ?></a>
		<br /><a href="?m=system&u=syskeys"><?php echo $AppUI->_('System Lookup Values'); ?></a>
		<br /><a href="?m=system&a=custom_field_editor"><?php echo $AppUI->_('Custom Field Editor'); ?></a>
                <br /><a href="?m=system&a=billingcode"><?php echo $AppUI->_('Billing Code Table'); ?></a>
	</td>
</tr>
<tr>
	<td>
		<?php echo w2PshowImage('power-management.png', 42, 42, ''); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Modules'); ?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=viewmods"><?php echo $AppUI->_('View Modules'); ?></a>
	</td>
</tr>
<tr>
	<td>
		<?php echo w2PshowImage('main-settings.png', 42, 42, ''); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Administration'); ?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&u=roles"><?php echo $AppUI->_('User Roles'); ?></a>
		<br /><a href="?m=system&a=acls_view"><?php echo $AppUI->_('Users Permissions Information'); ?></a>
		<br /><a href="?m=system&a=contacts_ldap"><?php echo $AppUI->_('Import Contacts'); ?></a>
	</td>
</tr>
</table>