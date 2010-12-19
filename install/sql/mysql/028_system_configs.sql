-- This sets a few default values for pagination and admin email. These
--   resolve a pair of requests/issues for v2.2

DELETE FROM `config` WHERE `config_name` = 'page_size';
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('page_size', '50', 'admin_system', 'text');

DELETE FROM `config` WHERE `config_name` = 'admin_email';
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('admin_email', 'admin@web2project.net', 'admin_system', 'text');