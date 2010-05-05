
UPDATE `modules` SET `mod_main_class` = 'CSystem'
    WHERE `mod_directory` = 'system';

UPDATE `modules` SET `mod_main_class` = 'CProjectDesignerOptions'
    WHERE `mod_directory` = 'projectdesigner';

UPDATE `modules` SET `mod_main_class` = 'smartsearch'
    WHERE `mod_directory` = 'smartsearch';

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_day', '0', 'admin_system', 'text');

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_hour', '3', 'admin_system', 'text');