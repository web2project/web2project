-- This resolves the potential performance issue in #170.  These values are 
--   calculated as Tasks and Task Logs are updated instead of calculated at 
--   run time.

UPDATE projects SET project_task_count = (
	SELECT COUNT(*) FROM tasks WHERE task_project = project_id
);

-- This sets up the ability to call the hook_cron to clear old or null user sessions.

UPDATE modules SET mod_main_class = 'CUser' WHERE mod_directory = 'admin';

-- This adds some columns that are now being used.  For new installations, 
--   they were in place.  For conversions from dotProject, not necessarily.

ALTER TABLE `projects` ADD `project_created` DATETIME NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `projects` ADD `project_updated` DATETIME NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `projects` ADD `project_status_comment` VARCHAR(255) NOT NULL default '';
ALTER TABLE `projects` ADD `project_subpriority` TINYINT(4) NOT NULL default 0;
ALTER TABLE `projects` ADD `project_end_date_adjusted` DATETIME NOT NULL default '0000-00-00 00:00:00;
ALTER TABLE `projects` ADD `project_end_date_adjusted_user` INT(10) NOT NULL default 0;