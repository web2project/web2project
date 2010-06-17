
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_timezone', '', 'admin_system', 'select');

UPDATE `sysvals` SET `sysval_value_id` = `sysval_value` WHERE `sysval_title` = 'Timezones';