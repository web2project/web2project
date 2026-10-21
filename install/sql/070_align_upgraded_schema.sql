-- Bring databases upgraded from 4.x in line with a fresh 5.0 install.
--
-- During 5.0 several old migrations (022-060) were edited instead of adding
-- new ones: zero dates were removed and created/updated columns got
-- CURRENT_TIMESTAMP defaults. A 4.x site is already at db_version 62, so it
-- never runs those edits. Two old migrations also never worked on upgrades,
-- and the errors were hidden at the time:
--   046  a missing semicolon joined two statements, so user_preferences.pref_user
--        stayed VARCHAR(12)
--   060  renamed forum_visits.visit_datetime to itself instead of renaming
--        visit_date, so recording forum visits fails on upgraded sites
-- And 036 now makes custom_fields_values.value_charvalue VARCHAR(255) on fresh
-- installs, while upgraded sites have TEXT, which textarea fields need.
--
-- Every statement here also runs on fresh 5.0 installs, where it changes
-- nothing (or only widens value_charvalue back to TEXT).

-- Values first: strict servers (MySQL 8's NO_ZERO_DATE) refuse to ALTER a
-- table that still holds a zero date in any of its date columns.
UPDATE `tasks` SET `task_start_date` = NULL WHERE CAST(`task_start_date` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `tasks` SET `task_end_date` = NULL WHERE CAST(`task_end_date` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `tasks` SET `task_created` = NOW() WHERE CAST(`task_created` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `tasks` SET `task_updated` = `task_created` WHERE CAST(`task_updated` AS CHAR(20)) LIKE '0000-00-00%';

UPDATE `projects` SET `project_start_date` = NULL WHERE (CAST(`project_start_date` AS CHAR(20)) LIKE '0000-00-00%' OR CAST(`project_start_date` AS CHAR(20)) LIKE '1000-01-01%');
UPDATE `projects` SET `project_end_date` = NULL WHERE (CAST(`project_end_date` AS CHAR(20)) LIKE '0000-00-00%' OR CAST(`project_end_date` AS CHAR(20)) LIKE '1000-01-01%');
UPDATE `projects` SET `project_actual_end_date` = NULL WHERE CAST(`project_actual_end_date` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `projects` SET `project_keydate` = NULL WHERE CAST(`project_keydate` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `projects` SET `project_created` = NOW() WHERE CAST(`project_created` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `projects` SET `project_updated` = `project_created` WHERE CAST(`project_updated` AS CHAR(20)) LIKE '0000-00-00%';

UPDATE `task_log` SET `task_log_date` = NULL WHERE CAST(`task_log_date` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `task_log` SET `task_log_created` = IFNULL(`task_log_date`, NOW()) WHERE CAST(`task_log_created` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `task_log` SET `task_log_updated` = `task_log_created` WHERE CAST(`task_log_updated` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `task_log` SET `task_log_task_end_date` = `task_log_created` WHERE CAST(`task_log_task_end_date` AS CHAR(20)) LIKE '0000-00-00%';

UPDATE `sessions` SET `session_created` = NOW() WHERE CAST(`session_created` AS CHAR(20)) LIKE '0000-00-00%';

UPDATE `contacts` SET `contact_birthday` = NULL WHERE CAST(`contact_birthday` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `contacts` SET `contact_lastupdate` = NULL WHERE CAST(`contact_lastupdate` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `contacts` SET `contact_updateasked` = NULL WHERE CAST(`contact_updateasked` AS CHAR(20)) LIKE '0000-00-00%';

UPDATE `w2pversion` SET `last_db_update` = '2001-01-01' WHERE CAST(`last_db_update` AS CHAR(20)) LIKE '0000-00-00%';
UPDATE `w2pversion` SET `last_code_update` = `last_db_update` WHERE CAST(`last_code_update` AS CHAR(20)) LIKE '0000-00-00%';

-- Defaults, as on a fresh install.
ALTER TABLE `tasks`
    MODIFY `task_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `task_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `projects`
    MODIFY `project_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `project_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `task_log`
    MODIFY `task_log_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `task_log_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `task_log_task_end_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- session_updated was a TIMESTAMP that changed on every write. The session
-- handler sets it explicitly.
ALTER TABLE `sessions`
    MODIFY `session_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `session_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `w2pversion`
    MODIFY `last_db_update` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `last_code_update` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 046: pref_user holds user ids. Rows that aren't a number can't belong to
-- anyone, and they would collide with the defaults (user 0) on conversion.
DELETE FROM `user_preferences` WHERE `pref_user` NOT REGEXP '^[0-9]+$';
ALTER TABLE `user_preferences` MODIFY `pref_user` INT(10) NOT NULL DEFAULT '0';

-- 060: rename forum_visits.visit_date only where it is still there.
SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'forum_visits' AND column_name = 'visit_date') > 0,
    'ALTER TABLE `forum_visits` CHANGE `visit_date` `visit_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

-- 036: textarea custom fields keep their text here, so it stays TEXT.
ALTER TABLE `custom_fields_values` MODIFY `value_charvalue` TEXT NULL DEFAULT NULL;
