
-- This addition adds support for token tasks to represent subprojects. The
--   best part about this method is that you get dependencies and other
--   planning aspects for "free".

# 2026 Update - eliminating '0000-00-00 00:00:00' as defaults
ALTER TABLE `tasks`
    MODIFY `task_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `task_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

# 2026 Update - eliminating '0000-00-00 00:00:00' as values
UPDATE `tasks` SET `task_created` = NOW() WHERE CAST(`task_created` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_updated` = NOW() WHERE CAST(`task_updated` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_start_date` = NOW() WHERE CAST(`task_start_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_end_date` = NOW() WHERE CAST(`task_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';

ALTER TABLE `tasks` ADD `task_represents_project` INT( 10 ) NOT NULL ;
ALTER TABLE `tasks` ADD INDEX ( `task_represents_project` ) ;