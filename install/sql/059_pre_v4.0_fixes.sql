
-- A file without an upload date (a zero date from a 1.x/2.x site, or NULL)
-- makes the NOT NULL change below fail on MySQL 8. Give it the upgrade time,
-- as 056 does for tasks without a created date.
UPDATE `files` SET `file_date` = NOW() WHERE `file_date` IS NULL OR CAST(`file_date` AS CHAR(20)) LIKE '0000-00-00%';

-- This changes the column to meet our naming convention to make our auto-formatting work
ALTER TABLE `files` CHANGE `file_date` `file_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- The user_access_log table was recreated in update 056 and I left out the auto_increment 
ALTER TABLE `user_access_log` CHANGE `user_access_log_id` `user_access_log_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Setting up the defaults for the budgets table
ALTER TABLE `budgets_assigned` CHANGE `budget_project` `budget_project` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `budgets_assigned` CHANGE `budget_task` `budget_task` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `budgets_assigned` CHANGE `budget_category` `budget_category` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0';
ALTER TABLE `budgets_assigned` CHANGE `budget_amount` `budget_amount` DECIMAL(10,2) NOT NULL DEFAULT '0';