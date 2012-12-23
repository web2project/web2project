<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $m, $a;

$user_id = (int) w2PgetParam($_POST, 'user', $AppUI->user_id);
$module = w2PgetParam($_POST, 'module', 'all');
$action = w2PgetParam($_POST, 'action', 'all');

$canView = canView('system');
if (!$canView) { // let's see if the user has sys access
	$AppUI->redirect(ACCESS_DENIED);
}

$perms = &$AppUI->acl();
$avail_modules = $perms->getModuleList();
$modules = array('all' => 'All Modules');
foreach ($avail_modules as $avail_module) {
	$modules[$avail_module['value']] = $avail_module['value'];
}
$module = isset($modules[$module]) ? $module : 'all';

$actions = array('all' => 'All Actions', 'access' => 'access', 'add' => 'add', 'delete' => 'delete', 'edit' => 'edit', 'view' => 'view');
$action = isset($actions[$action]) ? $action : 'all';

$users = array('' => '(' . $AppUI->_('Select User') . ')') + w2PgetUsers();

$q = new w2p_Database_Query;
$q->addTable($perms->_db_acl_prefix . 'permissions', 'gp');
$q->addQuery('gp.*');
$q->addWhere('user_id = ' . $user_id);
if ('all' != $module) {
    $q->addWhere("module = '$module'");
}
if ('all' != $action) {
    $q->addWhere("action = '$action'");
}

$q->addOrder('user_name');
$q->addOrder('module');
$q->addOrder('action');
$q->addOrder('item_id');
$q->addOrder('acl_id');
$permissions = $q->loadList();

$titleBlock = new w2p_Theme_TitleBlock('Permission Result Table', '48_my_computer.png', $m, $m . '.' . $a);
$titleBlock->addCell('
    <form action="?m=system&a=acls_view" method="post" name="pickUser" accept-charset="utf-8">' .
        $AppUI->_('View Users Permissions') . ': ' . arraySelect($users, 'user', 'class="text" onchange="javascript:document.pickUser.submit()"', $user_id) .
        $AppUI->_('View by Module') . ': ' . arraySelect($modules, 'module', 'class="text" onchange="javascript:document.pickUser.submit()"', $module) .
        $AppUI->_('View by Action') . ': ' . arraySelect($actions, 'action', 'class="text" onchange="javascript:document.pickUser.submit()"', $action) .
    '</form>', '', '', '');

$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&u=roles', 'user roles');
$titleBlock->show();

$fieldNames = array('UserID', 'User', 'Display Name', 'Module', 'Item', 'Item Name', 'Action', 'Allow', 'ACL_ID');
?>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php
foreach ($permissions as $permission) {
	$item = '';
	if ($permission['item_id']) {
		$q = new w2p_Database_Query;
		$q->addTable('modules');
		$q->addQuery('permissions_item_field,permissions_item_label');
		$q->addWhere('mod_directory = \'' . $permission['module'] . '\'');
		$field = $q->loadHash();

		$q = new w2p_Database_Query;
		$q->addTable($permission['module']);
		$q->addQuery($field['permissions_item_label']);
		$q->addWhere($field['permissions_item_field'] . ' = \'' . $permission['item_id'] . '\'');
		$item = $q->loadResult();
	}
	if (!($permission['item_id'] && !$permission['acl_id'])) {
		$table .= '<tr>' . '<td class="data _id">' . $permission['user_id'] . '</td>' . '<td>' . $permission['user_name'] . '</td>' . '<td>' . $users[$permission['user_id']] . '</td>' . '<td>' . $permission['module'] . '</td>' . '<td style="text-align:right;">' . ($permission['item_id'] ? $permission['item_id'] : '') . '</td>' . '<td>' . ($item ? $item : 'ALL') . '</td>' . '<td>' . $permission['action'] . '</td>' . '<td ' . (!$permission['access'] ? 'style="text-align:right;background-color:red"' : 'style="text-align:right;background-color:green"') . '>' . $permission['access'] . '</td>' . '<td ' . ($permission['acl_id'] ? '' : 'style="background-color:gray"') . '>' . ($permission['acl_id'] ? $permission['acl_id'] : 'soft-denial') . '</td>' . '</tr>';
	}
}
$table .= '</table>';
echo $table;