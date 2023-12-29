
-- This changes the column to meet our naming convention to make our auto-formatting work
ALTER TABLE `files` CHANGE `file_date` `file_datetime` DATETIME NULL DEFAULT NULL;

-- The user_access_log table was recreated in update 056 and I left out the auto_increment 
ALTER TABLE `user_access_log` CHANGE `user_access_log_id` `user_access_log_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;