-- Clear up incorrect module configs

UPDATE `module_config` SET `module_config_text`="Watch / Email" WHERE `module_name` = "forums" AND `module_config_name` = "index_list" AND `module_config_value` = "watch_user";

UPDATE `module_config` SET `module_config_text`="Watch / Email" WHERE `module_name` = "forums" AND `module_config_name` = "view_topics" AND `module_config_value` = "watch_user";
