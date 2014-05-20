-- Changing the Calendar Module to be called the Events Module to more accurately reflect its purpose
UPDATE `modules` SET `mod_directory` = 'events' WHERE `mod_directory` = 'calendar';

UPDATE `gacl_axo` SET `value` = 'events' WHERE `value` = 'calendar';
UPDATE `gacl_axo` SET `section_value` = 'events' WHERE `section_value` = 'calendar';

UPDATE `gacl_axo_map` SET `value` = 'events' WHERE `value` = 'calendar';
UPDATE `gacl_axo_map` SET `section_value` = 'events' WHERE `section_value` = 'calendar';

UPDATE `gacl_permissions` SET `module` = 'events' WHERE `module` = 'calendar';
UPDATE `module_config` SET `module_name` = 'events' WHERE `module_name` = 'calendar';

UPDATE `config` SET `config_value` = 'events' WHERE `config_value` = 'calendar';