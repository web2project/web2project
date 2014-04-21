<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

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

$permissions = getPermissions($perms, $user_id, $module, $action);

$titleBlock = new w2p_Theme_TitleBlock('Permission Result Table', 'icon.png', $m);
$titleBlock->addCell('
    <form action="?m=system&a=acls_view" method="post" name="pickUser" accept-charset="utf-8">' .
        $AppUI->_('View Users Permissions') . ': ' . arraySelect($users, 'user', 'class="text" onchange="javascript:document.pickUser.submit()"', $user_id) .
        $AppUI->_('View by Module') . ': ' . arraySelect($modules, 'module', 'class="text" onchange="javascript:document.pickUser.submit()"', $module) .
        $AppUI->_('View by Action') . ': ' . arraySelect($actions, 'action', 'class="text" onchange="javascript:document.pickUser.submit()"', $action) .
    '</form>', '', '', '');

$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&u=roles', 'user roles');
$titleBlock->show();

$fieldNames = array('UserID', 'User', 'Display Name', 'Module', 'Item',
    'Item Name', 'Action', 'Allow', 'ACL_ID');

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php

foreach ($permissions as $row) {
	$item = '';
	if ($row['item_id']) {
        $field = getPermissionField($row);

        $item = getPermissionItem($row, $field);
	}
	if (!($row['item_id'] && !$row['acl_id'])) {
		$table .= '<tr>' .
                $htmlHelper->createCell('user_id', $row['user_id']) .
                $htmlHelper->createCell('na', $row['user_name']) .
                '<td>' . $users[$row['user_id']] . '</td>' .
                $htmlHelper->createCell('module', $row['module']) .
                '<td style="text-align:right;">' . ($row['item_id'] ? $row['item_id'] : '') . '</td>' .
                '<td>' . ($item ? $item : 'ALL') . '</td>' .
                $htmlHelper->createCell('action', $row['action']) .
                '<td ' . (!$row['access'] ? 'style="text-align:right;background-color:red"' : 'style="text-align:right;background-color:green"') . '>' . $row['access'] . '</td>' . '<td ' . ($row['acl_id'] ? '' : 'style="background-color:gray"') . '>' . ($row['acl_id'] ? $row['acl_id'] : 'soft-denial') . '</td>' .
                '</tr>';
	}
}
$table .= '</table>';
echo $table;