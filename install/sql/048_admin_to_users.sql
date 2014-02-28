-- Changing the Admin Module to be called the Users Module to more accurately reflect its purpose
UPDATE `modules` SET `mod_directory` = 'users' WHERE `mod_directory` = 'admin';

UPDATE `gacl_axo` SET `value` = 'users' WHERE `value` = 'admin';
UPDATE `gacl_axo` SET `section_value` = 'users' WHERE `section_value` = 'admin';

UPDATE `gacl_axo_map` SET `value` = 'users' WHERE `value` = 'admin';
UPDATE `gacl_axo_map` SET `section_value` = 'users' WHERE `section_value` = 'admin';

UPDATE `gacl_permissions` SET `module` = 'users' WHERE `module` = 'admin';
UPDATE `module_config` SET `module_name` = 'users' WHERE `module_name` = 'admin';