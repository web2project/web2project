-- Clear up incorrect module configs

UPDATE `module_config` SET `module_config_value`="file_co_reason" WHERE `module_name` = "files" AND `module_config_name` = "index_list" AND `module_config_value` = "file_checkout_reason";
UPDATE `module_config` SET `module_config_text`="Assigned Users" WHERE `module_name` = "tasks" AND `module_config_name` = "projectdesigner-view" AND `module_config_value` = "task_4";