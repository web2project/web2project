
-- Adds fields to the Tasks table to store the original end of task date and percentage.

ALTER TABLE `tasks` ADD (`task_original_percent_complete` TINYINT( 4 ) DEFAULT '0', `task_original_end_date` DATETIME DEFAULT NULL);

-- Mirror the current value of the end data and percentage into the new field.

UPDATE `tasks` SET `task_original_percent_complete` = `task_percent_complete`, `task_original_end_date` = `task_end_date`;