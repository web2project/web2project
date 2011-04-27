UPDATE `config` SET `config_value` = '[SYSTEM_TIMEZONE]' WHERE `config_name` = 'system_timezone';

UPDATE `user_preferences` SET `pref_value` = '[USER_TIMEZONE]' WHERE `pref_name` = 'TIMEZONE';