<?php

$moduleName		= w2PgetParam($_POST, 'module_name', '');
$configName		= w2PgetParam($_POST, 'module_config_name', '');
$displayColumns	= w2PgetParam($_POST, 'display', array());
$displayOrder	= w2PgetParam($_POST, 'order', array());
$displayFields	= w2PgetParam($_POST, 'displayFields', array());
$displayNames	= w2PgetParam($_POST, 'displayNames', array());

$result = w2p_Core_Module::saveSettings($moduleName, $configName,
		$displayColumns, $displayOrder, $displayFields, $displayNames);

$AppUI->redirect('m='.$moduleName.'&a=configure');