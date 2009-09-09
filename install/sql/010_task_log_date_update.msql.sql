
-- This adds the necessary fields to the task_log table
-- to track created and updated dates. 
-- As well it changes the type of task_log_date to date

ALTER TABLE `task_log` CHANGE `task_log_date` `task_log_date` 
	DATE NULL DEFAULT NULL;

ALTER TABLE `task_log` ADD `task_log_created` 
	DATETIME NULL AFTER `task_log_date`;

ALTER TABLE `task_log` ADD `task_log_updated` 
	DATETIME NULL AFTER `task_log_created`; 
