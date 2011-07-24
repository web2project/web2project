
-- This allows us to track the percent complete at the task_log level which lets
--   us attach it to specific dates and track progress over time.

ALTER TABLE  `task_log` ADD  `task_log_percent_complete` TINYINT( 4 ) NOT NULL DEFAULT '0' AFTER `task_log_date`;

UPDATE `task_log` SET `task_log_percent_complete` = 
(
    SELECT `task_percent_complete` FROM `tasks` 
        WHERE tasks.task_id = task_log_task
        GROUP BY tasks.task_id
);

ALTER TABLE  `task_log` ADD  `task_log_task_end_date` DATETIME NOT NULL AFTER  `task_log_percent_complete`;

UPDATE `task_log` SET `task_log_task_end_date` = 
(
    SELECT `task_end_date` FROM `tasks` 
        WHERE tasks.task_id = task_log_task
        GROUP BY tasks.task_id
);