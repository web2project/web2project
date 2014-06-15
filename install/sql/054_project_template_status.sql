
-- Resetting the default project template status in case there isn't one already set.

UPDATE `config` SET `config_value` = 6 WHERE `config_name` = 'template_projects_status_id' AND
    (`config_value` = 0 OR `config_value` = '');