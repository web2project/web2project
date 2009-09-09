
-- This prepares to resolve the potential performance issue in #170.  These 
--   values are calculated as Tasks and Task Logs are updated instead of 
--   calculated at run time.  The values for these fields are in the next 
--   update.

ALTER TABLE `projects` ADD `project_scheduled_hours` 
	FLOAT NOT NULL DEFAULT '0' AFTER `project_actual_budget`;

ALTER TABLE `projects` ADD `project_worked_hours` 
	FLOAT NOT NULL DEFAULT '0' AFTER `project_scheduled_hours`;

ALTER TABLE `projects` ADD `project_task_count` 
	INT( 10 ) NOT NULL DEFAULT '0' AFTER `project_worked_hours` ;