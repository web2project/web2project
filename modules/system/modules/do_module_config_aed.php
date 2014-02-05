<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = $AppUI->acl();
$canEdit = canEdit('system');
if (!$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$mod_id = (int) w2PgetParam($_POST, 'mod_id');
$module = new w2p_System_Module();
$module->load($mod_id);

$moduleName		= $module->mod_directory;
$configName		= w2PgetParam($_POST, 'module_config_name', '');
$displayColumns	= w2PgetParam($_POST, 'display', array());
$displayOrder	= w2PgetParam($_POST, 'order', array());
$displayFields	= w2PgetParam($_POST, 'displayFields', array());
$displayNames	= w2PgetParam($_POST, 'displayNames', array());

$result = w2p_System_Module::saveSettings($moduleName, $configName,
		$displayColumns, $displayOrder, $displayFields, $displayNames);

if ($result) {
    $AppUI->setMsg('The module settings were saved successfully', UI_MSG_OK, true);
} else {
    $AppUI->setMsg('There was an error saving the module settings', UI_MSG_ERROR);
}
$AppUI->redirect('m=system&u=modules&a=addedit&mod_id='.$mod_id.'&v='.$configName);