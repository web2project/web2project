<?php 

	// Function to scan the event queue and execute any functions required.	
	require_once 'base.php';
	require_once W2P_BASE_DIR . '/includes/config.php';
	require_once W2P_BASE_DIR . '/includes/main_functions.php';
	require_once W2P_BASE_DIR . '/includes/db_adodb.php';
	require_once W2P_BASE_DIR . '/classes/ui.class.php';
	require_once W2P_BASE_DIR . '/classes/event_queue.class.php';
	require_once W2P_BASE_DIR . '/classes/query.class.php';
	
	$AppUI = new CAppUI;
	$AppUI->setUserLocale();

	$queue = new EventQueue;
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
		include_once ($AppUI->getModuleClass($module['mod_directory']));
		$object = new $module['mod_main_class']();
		
		if (method_exists($object, 'cron_hook')) {
			$itemList = $object->cron_hook();
		}
	}
?>