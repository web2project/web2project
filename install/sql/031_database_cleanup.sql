-- This is just flagging a bunch of fields for deprecation.

# 2026 Update - eliminating '0000-00-00 00:00:00' as values
UPDATE `contacts` SET `contact_birthday` = NULL WHERE CAST(`contact_birthday` AS CHAR(20)) = '0000-00-00 00:00:00';

ALTER TABLE `contacts` CHANGE `contact_order_by` `contact_order_by` VARCHAR( 30 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'deprecated';

# 2026 Update - eliminating '0000-00-00 00:00:00' as defaults
ALTER TABLE `projects`
    MODIFY `project_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `project_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	MODIFY `project_end_date_adjusted` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `projects` CHANGE `project_contacts` `project_contacts` VARCHAR( 100 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'deprecated';
ALTER TABLE `projects` CHANGE `project_departments` `project_departments` VARCHAR( 100 )
	CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'deprecated';
# 2026 Update - eliminating '0000-00-00 00:00:00' as values
UPDATE `projects` SET `project_start_date` = NOW() WHERE CAST(`project_start_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_end_date` = NOW() WHERE CAST(`project_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_created` = NOW() WHERE CAST(`project_created` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_updated` = NOW() WHERE CAST(`project_updated` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_end_date_adjusted` = NOW() WHERE CAST(`project_end_date_adjusted` AS CHAR(20)) = '0000-00-00 00:00:00';

# 2026 Update - eliminating '0000-00-00 00:00:00' as defaults
ALTER TABLE `tasks`
    MODIFY `task_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `task_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
# 2026 Update - eliminating '0000-00-00 00:00:00' as values
UPDATE `tasks` SET `task_created` = NOW() WHERE CAST(`task_created` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_updated` = NOW() WHERE CAST(`task_updated` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_start_date` = NOW() WHERE CAST(`task_start_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_end_date` = NOW() WHERE CAST(`task_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';
ALTER TABLE `tasks` CHANGE `task_contacts` `task_contacts` VARCHAR( 100 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'deprecated';

-- This field is for the task cache, also helps the
--   project_list_data and the configurable columns update.

ALTER TABLE `projects` ADD `project_last_task` INT( 10 ) NOT NULL DEFAULT '0';

UPDATE projects SET project_task_count = (
	SELECT COUNT(*) FROM tasks WHERE task_project = project_id
);

UPDATE projects SET project_last_task = IFNULL(
	(SELECT task_id FROM tasks WHERE task_project = project_id
		AND task_dynamic <> 1
		ORDER BY task_end_date DESC LIMIT 1), 0
);

UPDATE projects SET project_actual_end_date = (
	SELECT task_end_date FROM tasks WHERE task_id = project_last_task
);