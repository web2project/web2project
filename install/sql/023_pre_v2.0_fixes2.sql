
-- Somehere along the way, the dept_url was chosen to be only 25 characters.
--   That's obviously not enough.

ALTER TABLE `departments` CHANGE `dept_url` `dept_url` VARCHAR( 255 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- On some mysql instances on Windows, not having a default value is causing
--   the insert/update queries to throw errors. This will make it explicit.
--   Resolves: http://bugs.web2project.net/view.php?id=489

ALTER TABLE `departments` CHANGE `dept_country` `dept_country` VARCHAR( 100 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

-- On some mysql instances on Windows, not having a default value is causing
--   the insert/update queries to throw errors. This will make it explicit.
--   This is a variation of the above issue.
--   Resolves: http://bugs.web2project.net/view.php?id=486

# 2026 Update - eliminating '0000-00-00 00:00:00' as defaults
ALTER TABLE `tasks`
    MODIFY task_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY task_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

# 2026 Update - eliminating '0000-00-00 00:00:00' as values
UPDATE `tasks` SET `task_created` = NOW() WHERE CAST(`task_created` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_updated` = NOW() WHERE CAST(`task_updated` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_start_date` = NOW() WHERE CAST(`task_start_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_end_date` = NOW() WHERE CAST(`task_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';

ALTER TABLE `tasks` CHANGE `task_represents_project` `task_represents_project`
    INT( 10 ) NOT NULL DEFAULT '0'