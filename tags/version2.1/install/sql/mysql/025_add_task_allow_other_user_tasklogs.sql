-- This will add a field to the tasks table indicating if users with the
-- proper permissions can add tasks for other users
ALTER TABLE `tasks` ADD COLUMN `task_allow_other_user_tasklogs` int(1) NOT NULL DEFAULT '0';

-- This will add a field that indicates who created/updated the task log
ALTER TABLE `task_log` ADD COLUMN `task_log_record_creator` int(10) unsigned NOT NULL;

INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`)
	VALUES (1465, '2.1.0', 25, now(), now());