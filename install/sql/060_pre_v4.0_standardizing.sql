
-- Updating column names to leverage our naming conventions and simplify the templating

-- events
ALTER TABLE `events` CHANGE `event_start_date` `event_start_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `events` CHANGE `event_end_date` `event_end_datetime` DATETIME NULL DEFAULT NULL;
UPDATE `module_config` SET `module_config_value` = 'event_start_datetime' WHERE `module_config_value` = 'event_start_date'
UPDATE `module_config` SET `module_config_value` = 'event_end_datetime' WHERE `module_config_value` = 'event_end_date'

-- files
ALTER TABLE `files` CHANGE `file_datetime` `file_datetime` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
UPDATE `module_config` SET `module_config_value` = 'file_datetime' WHERE `module_config_value` = 'file_date';

-- history
ALTER TABLE `history` CHANGE `history_date` `history_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `module_config` SET `module_config_value` = 'history_datetime' WHERE `module_config_value` = 'history_date';

-- links
ALTER TABLE `links` CHANGE `link_date` `link_datetime` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `module_config` SET `module_config_value` = 'link_datetime' WHERE `module_config_value` = 'link_date';

