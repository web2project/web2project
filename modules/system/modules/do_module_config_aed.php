<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = $AppUI->acl();
$canEdit = canEdit('system');
if (!$canEdit) {
	$AppUI->redirect('m=public&a=access_denied');
}

$mod_id = (int) w2PgetCleanParam($_POST, 'mod_id');
$module = new w2p_Core_Module();
$module->load($mod_id);

$moduleName		= $module->mod_directory;
$configName		= w2PgetParam($_POST, 'module_config_name', '');
$displayColumns	= w2PgetParam($_POST, 'display', array());
$displayOrder	= w2PgetParam($_POST, 'order', array());
$displayFields	= w2PgetParam($_POST, 'displayFields', array());
$displayNames	= w2PgetParam($_POST, 'displayNames', array());

$result = w2p_Core_Module::saveSettings($moduleName, $configName,
		$displayColumns, $displayOrder, $displayFields, $displayNames);

$AppUI->redirect('m=system&u=modules&a=addedit&mod_id='.$mod_id.'&v='.$configName);