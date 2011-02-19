-- This prepares the system for better update checking, etc with the main
--   web2project.net site. The previous checker made multiple requests in the
--   same hour each week instead of a unique request each week.

DELETE FROM `config` WHERE `config_name` LIKE 'system_update%';

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_check', 'true', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_last_check', '2001-01-01 00:00:00', 'admin_system', 'text');

DELETE FROM `config` WHERE `config_name` IN ('calendar', 'jpLocale',
    'projects', 'system', 'tasks');

ALTER TABLE `contacts` ADD `contact_display_name` VARCHAR( 100 )
    NOT NULL AFTER `contact_last_name`;

UPDATE contacts SET contact_display_name = contact_order_by;