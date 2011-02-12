<?php 
// Function to scan the event queue and execute any functions required.	
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$defaultTZ = w2PgetConfig('system_timezone', 'Europe/London');
date_default_timezone_set($defaultTZ);

$AppUI = new CAppUI;
$AppUI->setUserLocale();

$queue = new w2p_Core_EventQueue();
$queue->scan();

/*
 This is the first piece of a simple hook system to allow for regularly
 scheduled maintenance tasks to occur.  This could be data validation and
 cleanup, sending email notifications, or workflow related tasks.

 The model for this functionality was based on Drupal's methods for laying
 out and interacting with hooks.  It should not be considered complete at
 this time.
*/
$moduleList = $AppUI->getLoadableModuleList();

foreach ($moduleList as $module) {
	if (!in_array($module['mod_main_class'], get_declared_classes())) {
		require_once ($AppUI->getModuleClass($module['mod_directory']));
	}
	$object = new $module['mod_main_class']();
    if (is_callable(array($object, 'hook_cron'))) {
        $object->hook_cron();
    }
}