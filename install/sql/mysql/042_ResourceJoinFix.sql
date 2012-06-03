-- Added by Korkonius
-- Short fix to enable the other_resources view to function with all resources
INSERT INTO `resource_types`(`resource_type_id`,`resource_type_name`,`resource_type_note`) VALUES(0, 'All resources', 'All resources');
UPDATE `resource_types` SET `resource_type_id` = 0 WHERE `resource_type_id` = LAST_INSERT_ID();