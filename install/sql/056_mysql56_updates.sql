
-- 0000-00-00 is not a valid date format for mysql 5.6+ so this updates the defaults to valid values

ALTER TABLE `forums`          CHANGE `forum_create_date` `forum_create_date`          DATETIME DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `forums`          CHANGE `forum_last_date` `forum_last_date`              DATETIME DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `forum_messages`  CHANGE `message_date` `message_date`                    DATETIME DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `history`         CHANGE `history_date` `history_date`                    DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `projects`        CHANGE `project_created` `project_created`              DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `projects`        CHANGE `project_updated` `project_updated`              DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `projects`        CHANGE `project_end_date_adjusted` `project_end_date_adjusted` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `sessions`        CHANGE `session_created` `session_created`              DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `tasks`           CHANGE `task_created` `task_created`                    DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `tasks`           CHANGE `task_updated` `task_updated`                    DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `task_log`        CHANGE `task_log_created` `task_log_created`            DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `task_log`        CHANGE `task_log_updated` `task_log_updated`            DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `user_access_log` CHANGE `date_time_in` `date_time_in`                    DATETIME NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `user_access_log` CHANGE `date_time_out` `date_time_out`                  DATETIME NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `user_access_log` CHANGE `date_time_last_action` `date_time_last_action`  DATETIME NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `w2pversion`      CHANGE `last_db_update` `last_db_update`                DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `w2pversion`      CHANGE `last_code_update` `last_code_update`            DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
