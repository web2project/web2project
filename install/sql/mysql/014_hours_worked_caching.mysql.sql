
-- Added an update to allow for permissions to be applied on individual users 

UPDATE `modules` SET `permissions_item_table` = 'users', `permissions_item_field` = 'user_id', 
  `permissions_item_label` = 'user_username' WHERE `mod_directory` = 'admin';

-- This applies an update to fill in the total hours worked on a task from the
--   relevant task_log entries.

ALTER TABLE `tasks` CHANGE `task_hours_worked` `task_hours_worked` 
  FLOAT UNSIGNED NOT NULL DEFAULT '0';

UPDATE tasks SET task_hours_worked = (SELECT SUM(task_log_hours) 
	FROM task_log WHERE task_log_task = task_id);

-- This applies an update to fill in the total hours worked on a project from the
--   individual tasks 

UPDATE projects SET project_worked_hours = (SELECT SUM(task_hours_worked) 
	FROM tasks WHERE task_project = project_id);
