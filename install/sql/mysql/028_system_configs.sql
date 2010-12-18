-- This sets a few default values for pagination and admin email. These
--   resolve a pair of requests/issues for v2.2

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('page_size', '50', 'admin_system', 'text');

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('admin_email', 'admin@web2project.net', 'admin_system', 'text');