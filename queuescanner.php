<?php

require_once 'bootstrap.php';

// Function to scan the event queue and execute any functions required.
$queue = new w2p_System_EventQueue();
$queue->scan();

/**
 * This is the first piece of a simple hook system to allow for regularly scheduled maintenance tasks to occur.  This
 *   could be data validation and cleanup, sending email notifications, or workflow related tasks.
 */

$hooks = new w2p_System_HookHandler($AppUI);
$hooks->process('cron');