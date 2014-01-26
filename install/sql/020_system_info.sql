
-- Setting the mod_main_class for each of these modules makes them callable via
--   the different hook methods.

UPDATE `modules` SET `mod_main_class` = 'CSystem'
    WHERE `mod_directory` = 'system';

UPDATE `modules` SET `mod_main_class` = 'CProjectDesignerOptions'
    WHERE `mod_directory` = 'projectdesigner';

UPDATE `modules` SET `mod_main_class` = 'smartsearch'
    WHERE `mod_directory` = 'smartsearch';

-- These parameters configure the system to communicate with the core
--   web2project data collection system. Some of the details on the
--   implementation are here: http://forums.web2project.net/viewtopic.php?t=1960

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_day', '0', 'admin_system', 'text');

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('system_update_hour', '3', 'admin_system', 'text');