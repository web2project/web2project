
-- Updating column names to leverage our naming conventions and simplify the templating

-- events
ALTER TABLE `events` CHANGE `event_start_date` `event_start_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `events` CHANGE `event_end_date` `event_end_datetime` DATETIME NULL DEFAULT NULL;
UPDATE `module_config` SET `module_config_value` = 'event_start_datetime' WHERE `module_config_value` = 'event_start_date'
UPDATE `module_config` SET `module_config_value` = 'event_end_datetime' WHERE `module_config_value` = 'event_end_date'

