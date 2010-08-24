<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
$canEdit = canEdit('system');
$canRead = canView('system');
if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();

$hidden_modules = array('public', 'install', );
$q = new DBQuery;
$q->addQuery('*');
$q->addTable('modules');
foreach ($hidden_modules as $no_show) {
	$q->addWhere('mod_directory <> \'' . $no_show . '\'');
}
$q->addOrder('mod_ui_order');
$modules = $q->loadList();
// get the modules actually installed on the file system
$modFiles = $AppUI->readDirs('modules');

$titleBlock = new CTitleBlock('Modules', 'power-management.png', $m, "$m.$a");
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();
?>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th colspan="2"><?php echo $AppUI->_('Module'); ?></th>
	<th><?php echo $AppUI->_('Status'); ?></th>
	<th><?php echo $AppUI->_('Type'); ?></th>
	<th><?php echo $AppUI->_('Version'); ?></th>
	<th><?php echo $AppUI->_('Menu Text'); ?></th>
	<th><?php echo $AppUI->_('Menu Icon'); ?></th>
	<th><?php echo $AppUI->_('Menu Status'); ?></th>
	<th><?php echo $AppUI->_('#'); ?></th>
</tr>
<?php
// do the modules that are installed on the system
foreach ($modules as $row) {
	// clear the file system entry
	if (isset($modFiles[$row['mod_directory']])) {
		$modFiles[$row['mod_directory']] = '';
	}
	$query_string = '?m=' . $m . '&a=domodsql&mod_id=' . $row['mod_id'];
	$s = '';
	$s .= '<td width="64" align="center">';
	if ($canEdit) {
		$s .= w2PtoolTip('Modules', 'Move to First') . '<a href="' . $query_string . '&cmd=movefirst"><img src="' . w2PfindImage('icons/2uparrow.png') . '" border="0"/></a>' . w2PendTip();
		$s .= w2PtoolTip('Modules', 'Move Up') . '<a href="' . $query_string . '&cmd=moveup"><img src="' . w2PfindImage('icons/1uparrow.png') . '" border="0"/></a>' . w2PendTip();
		$s .= w2PtoolTip('Modules', 'Move Down') . '<a href="' . $query_string . '&cmd=movedn"><img src="' . w2PfindImage('icons/1downarrow.png') . '" border="0"/></a>' . w2PendTip();
		$s .= w2PtoolTip('Modules', 'Move to Last') . '<a href="' . $query_string . '&cmd=movelast"><img src="' . w2PfindImage('icons/2downarrow.png') . '" border="0"/></a>' . w2PendTip();
	}
	$s .= '</td>';

	$s .= '<td width="1%" nowrap="nowrap">' . $AppUI->_($row['mod_name']) . '</td>';
	$s .= '<td>';
	$s .= '<img src="' . w2PfindImage('obj/dot' . ($row['mod_active'] ? 'green' : 'yellowanim') . '.gif') . '" />&nbsp;';
	if ($canEdit) {
		$s .= '<a href="' . $query_string . '&cmd=toggle&">';
	}
	$s .= ($row['mod_active'] ? $AppUI->_('active') : $AppUI->_('disabled'));
	if ($canEdit) {
		$s .= '</a>';
	}
	if ($row['mod_type'] != 'core' && $canEdit) {
		$s .= ' | <a href="' . $query_string . '&cmd=remove" onclick="return window.confirm(' . "'" . $AppUI->_('This will delete all data associated with the module!') . "\\n\\n" . $AppUI->_('Are you sure?') . "\\n" . "'" . ');">' . $AppUI->_('remove') . '</a>';
	}

	// check for upgrades
	$ok = file_exists(W2P_BASE_DIR . '/modules/' . $row['mod_directory'] . '/setup.php');
	if ($ok) {
		include_once (W2P_BASE_DIR . '/modules/' . $row['mod_directory'] . '/setup.php');
	}
	if ($ok) {
		if ($config['mod_version'] != $row['mod_version'] && $canEdit) {
			$s .= ' | <a href="' . $query_string . '&cmd=upgrade" onclick="return window.confirm(' . "'" . $AppUI->_('Are you sure?') . "'" . ');" >' . $AppUI->_('upgrade') . '</a>';
		}
	}

	// check for configuration

	if ($ok) {
		if (isset($config['mod_config']) && $config['mod_config'] == true && $canEdit) {
			$s .= ' | <a href="' . $query_string . '&cmd=configure">' . $AppUI->_('configure') . '</a>';
		}
	}

	$s .= '</td>';
	$s .= '<td>' . $row['mod_type'] . '</td>';
	$s .= '<td>' . $row['mod_version'] . '</td>';
	$s .= '<td>' . $AppUI->_($row['mod_ui_name']) . '</td>';
	$s .= '<td>' . $row['mod_ui_icon'] . '</td>';

	$s .= '<td>';
	$s .= '<img src="' . w2PfindImage('/obj/' . ($row['mod_ui_active'] ? 'dotgreen.gif' : 'dotredanim.gif')) . '" />&nbsp;';
	if ($canEdit) {
		$s .= '<a href="' . $query_string . '&cmd=toggleMenu">';
	}
	$s .= ($row['mod_ui_active'] ? $AppUI->_('visible') : $AppUI->_('hidden'));
	if ($canEdit) {
		$s .= '</a>';
	}
	$s .= '</td>';

	$s .= '<td align="right">' . $row['mod_ui_order'] . '</td>';

	echo '<tr>' . $s . '</tr>';
}

foreach ($modFiles as $v) {
	// clear the file system entry
	if ($v && !in_array($v, $hidden_modules)) {
		$s = '';
		$s .= '<td></td>';
		$s .= '<td>' . $AppUI->_($v) . '</td>';
		$s .= '<td>';
		$s .= '<img src="' . w2PfindImage('obj/dotgrey.gif') . '" />&nbsp;';
		if ($canEdit) {
			$s .= '<a href="?m=' . $m . '&a=domodsql&cmd=install&mod_directory=' . $v . '">';
		}
		$s .= $AppUI->_('install');
		if ($canEdit) {
			$s .= '</a>';
		}
		$s .= '</td>';
		echo '<tr>' . $s . '</tr>';
	}

}
?>
</table>