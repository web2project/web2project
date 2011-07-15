
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_timezone', '', 'admin_system', 'select');

UPDATE `sysvals` SET `sysval_value_id` = `sysval_value` WHERE `sysval_title` = 'Timezones';

-- This was merged from Robert Basic's timezone patch included in
--   https://github.com/caseysoftware/web2project/pull/24 It was shifted from
--   being an independent sql statement - 034_setup_timezones.sql - to being
--   applied immediately after the timezones are added. This will prevent
--   existing systems from being corrupted.
--
--   ~ caseydk 06 May 2011

UPDATE `config` SET `config_value` = '[SYSTEM_TIMEZONE]' WHERE `config_name` = 'system_timezone';

UPDATE `user_preferences` SET `pref_value` = '[USER_TIMEZONE]' WHERE `pref_name` = 'TIMEZONE';