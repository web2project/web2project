<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

// pull all the key types
$perms = &$AppUI->acl();

// Get the permissions for this module
$canAccess = $perms->checkModule('roles', 'access');
if (!$canAccess) {
	$AppUI->redirect('m=public&a=access_denied');
}
$canRead = $perms->checkModule('roles', 'view');
$canAdd = $perms->checkModule('roles', 'add');
$canEdit = $perms->checkModule('roles', 'edit');
$canDelete = $perms->checkModule('roles', 'delete');

$crole = &new CRole;
$roles = $crole->getRoles();

$role_id = w2PgetParam($_GET, 'role_id', 0);

$q = new DBQuery;
$q->addTable('modules');
$q->addQuery('mod_id, mod_name');
$q->addWhere('mod_active > 0');
$q->addOrder('mod_directory');
$modules = arrayMerge(array('0' => 'All'), $q->loadHashList());
$q->clear();

// setup the title block
$titleBlock = new CTitleBlock('Roles', 'main-settings.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();

$crumbs = array();
$crumbs['?m=system'] = 'System Admin';

?>

<script language="javascript">
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

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Role ID'); ?></th>
	<th><?php echo $AppUI->_('Description'); ?></th>
	<th>&nbsp;</th>
</tr>
<?php

function showRow($role = null) {
	global $canEdit, $canDelete, $role_id, $AppUI, $modules;
	$id = $role['id'];
	$name = $role['value'];
	$description = $role['name'];

	$s = '';
	if (($role_id == $id || $id == 0) && $canEdit) {
		// edit form
		$s .= '<form name="roleFrm" method="post" action="?m=system&u=roles">';
		$s .= '<input type="hidden" name="dosql" value="do_role_aed" />';
		$s .= '<input type="hidden" name="del" value="0" />';
		$s .= '<input type="hidden" name="role_id" value="' . $id . '" />';
		$s .= '<tr><td>&nbsp;</td>';
		$s .= '<td valign="top"><input type="text" name="role_name" value="' . $name . '" class="text" /></td>';
		$s .= '<td valign="top"><input type="text" name="role_description" class="text" value="' . $description . '"></td>';
		$s .= '<td><input type="submit" value="' . $AppUI->_($id ? 'edit' : 'add') . '" class="button" /></td>';
	} else {
		$s .= '<tr><td width="50" valign="top">';
		if ($canEdit) {
			$s .= '<a href="?m=system&u=roles&role_id=' . $id . '">' . w2PshowImage('icons/stock_edit-16.png') . '</a><a href="?m=system&u=roles&a=viewrole&role_id=' . $id . '" title="">' . w2PshowImage('obj/lock.gif') . '</a>';
		}
		if ($canDelete) {
			$s .= '<a href=\'javascript:delIt(' . $id . ')\'>' . w2PshowImage('icons/stock_delete-16.png') . '</a>';
		}
		$s .= '</td><td valign="top">' . $name . '</td><td valign="top">' . $AppUI->_($description) . '</td><td valign="top" width="16">&nbsp;</td>';
	}
	$s .= '</tr>';
	return $s;
}

// do the modules that are installed on the system
$s = '';
foreach ($roles as $row) {
	echo showRow($row);
}
// add in the new key row:
if ($role_id == 0) {
	echo showRow();
}
?>
</table>
<?php
// Do all the tab stuff.
?>