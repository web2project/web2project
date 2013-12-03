<?php
// Function to scan the event queue and execute any functions required.
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$defaultTZ = w2PgetConfig('system_timezone', 'UTC');
date_default_timezone_set($defaultTZ);

$AppUI = new w2p_Core_CAppUI();
$AppUI->setUserLocale();

$queue = new w2p_System_EventQueue();
$queue->scan();

/*
 This is the first piece of a simple hook system to allow for regularly
 scheduled maintenance tasks to occur.  This could be data validation and
 cleanup, sending email notifications, or workflow related tasks.

*/

$hooks = new w2p_System_HookHandler($AppUI);
$hooks->process('cron');