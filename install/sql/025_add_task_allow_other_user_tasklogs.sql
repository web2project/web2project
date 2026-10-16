-- This will add a field to the tasks table indicating if users with the
-- proper permissions can add tasks for other users

# 2026 Update - eliminating '0000-00-00 00:00:00' as defaults
ALTER TABLE `tasks`
    MODIFY task_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY task_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

# 2026 Update - eliminating '0000-00-00 00:00:00' as values
UPDATE `tasks` SET `task_created` = NOW() WHERE CAST(`task_created` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_updated` = NOW() WHERE CAST(`task_updated` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_start_date` = NOW() WHERE CAST(`task_start_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_end_date` = NOW() WHERE CAST(`task_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';

ALTER TABLE `tasks` ADD COLUMN `task_allow_other_user_tasklogs` int(1) NOT NULL DEFAULT '0';

-- This will add a field that indicates who created/updated the task log
ALTER TABLE `task_log` ADD COLUMN `task_log_record_creator` int(10) unsigned NOT NULL;
