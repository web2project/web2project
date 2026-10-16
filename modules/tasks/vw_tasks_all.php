<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

/**
 * Project view "Tasks (All)" tab: the project's tasks of every status.
 * Based on Klaus Buecher (opto), 8db88ce5; uses vw_tasks.php instead of a copy.
 */
global $showAllTaskStatuses;

$showAllTaskStatuses = true;
include W2P_BASE_DIR . '/modules/tasks/vw_tasks.php';
$showAllTaskStatuses = false;
