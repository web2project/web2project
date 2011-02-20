<?php

$moduleName   = w2PgetParam($_POST, 'module_name', '');
$configName   = w2PgetParam($_POST, 'module_config_name', '');
$display      = w2PgetParam($_POST, 'display', array());
$displayFields= w2PgetParam($_POST, 'displayFields', array());
$displayNames = w2PgetParam($_POST, 'displayNames', array());

$result = w2p_Core_Module::saveSettings($moduleName, $configName,
		$display, $displayFields, $displayNames);

$AppUI->redirect('m='.$moduleName.'&a=configure');