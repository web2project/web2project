
-- Updating column names to leverage our naming conventions and simplify the templating

-- events
ALTER TABLE `events` CHANGE `event_start_date` `event_start_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `events` CHANGE `event_end_date` `event_end_datetime` DATETIME NULL DEFAULT NULL;
UPDATE `module_config` SET `module_config_value` = 'event_start_datetime' WHERE `module_config_value` = 'event_start_date'
UPDATE `module_config` SET `module_config_value` = 'event_end_datetime' WHERE `module_config_value` = 'event_end_date'

-- files
ALTER TABLE `files` CHANGE `file_datetime` `file_datetime` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
UPDATE `module_config` SET `module_config_value` = 'file_datetime' WHERE `module_config_value` = 'file_date';

-- forums
ALTER TABLE `forums` CHANGE `forum_create_date` `forum_created` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `forums` CHANGE `forum_last_date` `forum_updated` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `forum_messages` CHANGE `message_date` `message_datetime` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `forum_visits` CHANGE `visit_datetime` `visit_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
UPDATE `module_config` SET `module_config_value` = 'forum_created' WHERE `module_config_value` = 'forum_create_date';
UPDATE `module_config` SET `module_config_value` = 'forum_updated' WHERE `module_config_value` = 'forum_last_date';
UPDATE `module_config` SET `module_config_value` = 'message_datetime' WHERE `module_config_value` = 'message_date';

-- history
ALTER TABLE `history` CHANGE `history_date` `history_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
UPDATE `module_config` SET `module_config_value` = 'history_datetime' WHERE `module_config_value` = 'history_date';

-- links
ALTER TABLE `links` CHANGE `link_date` `link_datetime` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
UPDATE `module_config` SET `module_config_value` = 'link_datetime' WHERE `module_config_value` = 'link_date';

-- user_access_log
ALTER TABLE `user_access_log` CHANGE `date_time_in` `date_time_in` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `user_access_log` CHANGE `date_time_out` `date_time_out` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `user_access_log` CHANGE `date_time_last_action` `date_time_last_action` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;

-- tasks: This cleans up some broken data where mysql no longer allows '0000-00-00 00:00:00' as a pseudo-null
UPDATE `tasks` SET `task_updated` = now() WHERE `task_updated` < '1900-01-01 00:00:00';
UPDATE `tasks` SET `task_created` = `task_updated` WHERE `task_created` < '1900-01-01 00:00:00';