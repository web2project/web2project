-- This is just flagging a bunch of fields for deprecation.

ALTER TABLE `contacts` CHANGE `contact_order_by` `contact_order_by` VARCHAR( 30 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'deprecated';

ALTER TABLE `projects` CHANGE `project_contacts` `project_contacts` VARCHAR( 100 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'deprecated';

ALTER TABLE `projects` CHANGE `project_departments` `project_departments` VARCHAR( 100 )
	CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'deprecated';

ALTER TABLE `tasks` CHANGE `task_contacts` `task_contacts` VARCHAR( 100 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'deprecated';

-- This field is for the task cache, also helps the
--   project_list_data and the configurable columns update.

ALTER TABLE `projects` ADD `project_last_task` INT( 10 ) NOT NULL DEFAULT '0';

UPDATE projects SET project_task_count = (
	SELECT COUNT(*) FROM tasks WHERE task_project = project_id
);

UPDATE projects SET project_last_task = (
	SELECT task_id FROM tasks WHERE task_project = project_id ORDER BY task_end_date DESC LIMIT 1
);

UPDATE projects SET project_actual_end_date = (
	SELECT task_end_date FROM tasks WHERE task_id = project_last_task
);