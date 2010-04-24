<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $m, $a;

$perms = &$AppUI->acl();
if (!canView('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$user_permissions = array();
$users = w2PgetUsers();
/*echo("<pre>");
print_r($permissions);
echo("</pre>");*/

if (isset($_POST['user']) && $_POST['user'] != '') {
	$q = new DBQuery;
	$q->addTable($perms->_db_acl_prefix . 'permissions', 'gp');
	$q->addQuery('gp.*');
	$q->addWhere('user_id IN (' . implode(',', array_keys($users)) . ')');
	if (isset($_POST['user']) && (int) $_POST['user'] > 0) {
		$q->addWhere('user_id = ' . (int)$_POST['user']);
	}
	if ($_POST['module']) {
		$q->addWhere('module = \'' . $_POST['module'] . '\'');
	}
	if ($_POST['action']) {
		$q->addWhere('action = \'' . $_POST['action'] . '\'');
	}
	$q->addOrder('user_name');
	$q->addOrder('module');
	$q->addOrder('action');
	$q->addOrder('item_id');
	$q->addOrder('acl_id');
	$permissions = $q->loadList();
} else {
	$permissions = array();
}

$avail_modules = $perms->getModuleList();
$modules = array();
foreach ($avail_modules as $avail_module) {
	$modules[$avail_module['value']] = $avail_module['value'];
}
$modules = array(0 => 'All Modules') + $modules;

$actions = array(0 => 'All Actions', 'access' => 'access', 'add' => 'add', 'delete' => 'delete', 'edit' => 'edit', 'view' => 'view');

$table = '<table class="tbl" width="100%" cellspacing="1" cellpadding="2" border="0">';
$table .= '<tr><th colspan="9"><b>Permission Result Table</b></th></tr>';
$table .= '<tr><th>UserID</th><th>User</th><th>User Name</th><th>Module</th><th>Item</th><th>Item Name</th><th>Action</th><th>Allow</th><th>ACL_ID</th></tr>';
foreach ($permissions as $permission) {
	$item = '';
	if ($permission['item_id']) {
		$q = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('permissions_item_field,permissions_item_label');
		$q->addWhere('mod_directory = \'' . $permission['module'] . '\'');
		$field = $q->loadHash();

		$q = new DBQuery;
		$q->addTable($permission['module']);
		$q->addQuery($field['permissions_item_label']);
		$q->addWhere($field['permissions_item_field'] . ' = \'' . $permission['item_id'] . '\'');
		$item = $q->loadResult();
	}
	if (!($permission['item_id'] && !$permission['acl_id'])) {
		$table .= '<tr>' . '<td style="text-align:right;">' . $permission['user_id'] . '</td>' . '<td>' . $permission['user_name'] . '</td>' . '<td>' . $users[$permission['user_id']] . '</td>' . '<td>' . $permission['module'] . '</td>' . '<td style="text-align:right;">' . ($permission['item_id'] ? $permission['item_id'] : '') . '</td>' . '<td>' . ($item ? $item : 'ALL') . '</td>' . '<td>' . $permission['action'] . '</td>' . '<td ' . (!$permission['access'] ? 'style="text-align:right;background-color:red"' : 'style="text-align:right;background-color:green"') . '>' . $permission['access'] . '</td>' . '<td ' . ($permission['acl_id'] ? '' : 'style="background-color:gray"') . '>' . ($permission['acl_id'] ? $permission['acl_id'] : 'soft-denial') . '</td>' . '</tr>';
	}
}
$table .= '</table>';
$users = array('' => '(' . $AppUI->_('Select User') . ')') + $users;
$user = (isset($_POST['user']) && $_POST['user'] != '') ?  $_POST['user'] : $AppUI->user_id;
$user_selector = arraySelect($users, 'user', 'class="text" onchange="javascript:document.pickUser.submit()"', $user);
$module = (isset($_POST['module']) && $_POST['module'] != '') ?  $_POST['module'] : '';
$module_selector = arraySelect($modules, 'module', 'class="text" onchange="javascript:document.pickUser.submit()"', $module);
$action = (isset($_POST['action']) && $_POST['action'] != '') ?  $_POST['action'] : '';
$action_selector = arraySelect($actions, 'action', 'class="text" onchange="javascript:document.pickUser.submit()"', $action);
echo $AppUI->_('View Users Permissions') . ':<form action="?m=system&a=acls_view" method="post" name="pickUser" accept-charset="utf-8">' . $user_selector . $AppUI->_('View by Module') . ':' . $module_selector . $AppUI->_('View by Action') . ':' . $action_selector . '</form><br />';
echo $table;