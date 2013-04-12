-- Add a new field to the forum_messages table, to hold the related task

ALTER TABLE `forum_messages` ADD COLUMN `message_task` INT(10) NOT NULL DEFAULT 0 AFTER `message_editor`;

-- Change the topics view

UPDATE `module_config` SET `module_config_order` = 4 WHERE `module_name` = "forums" AND `module_config_name` = "view_topics" AND `module_config_value` = "replies";
UPDATE `module_config` SET `module_config_order` = 5 WHERE `module_name` = "forums" AND `module_config_name` = "view_topics" AND `module_config_value` = "latest_reply";
INSERT `module_config` (`module_name`, `module_config_name`, `module_config_value`, `module_config_text`, `module_config_order`) VALUES ("forums", "view_topics", "message_task", "Related Task", 3);
