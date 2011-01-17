-- This sets a few default values for pagination and admin email. These
--   resolve a pair of requests/issues for v2.2

DELETE FROM `config` WHERE `config_name` LIKE 'system_update%';

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_check', 'true', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_last_check', '2001-01-01 00:00:00', 'admin_system', 'text');

DELETE FROM `config` WHERE `config_name` IN ('calendar', 'jpLocale',
    'projects', 'system', 'tasks');