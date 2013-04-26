-- Create the task delegations table

CREATE TABLE IF NOT EXISTS `user_delegations` (
  `delegation_id` int(10) NOT NULL AUTO_INCREMENT,
  `delegating_user_id` int(10) NOT NULL DEFAULT 0,
  `delegated_to_user_id` int(10) NOT NULL DEFAULT 0,
  `delegation_task` int(10) NOT NULL DEFAULT 0,
  `delegation_start_date` datetime DEFAULT NULL,
  `delegation_name` varchar(50) NOT NULL DEFAULT '',
  `delegation_description` varchar(60) NOT NULL DEFAULT '',
  `delegation_rejection_date` datetime DEFAULT NULL,
  `delegation_rejection_reason` varchar(50) NOT NULL DEFAULT '',
  `delegation_rejection_validation_date` datetime DEFAULT NULL,
  `delegation_percent_complete` decimal(10,2) NOT NULL DEFAULT 0,
  `delegation_end_date` datetime DEFAULT NULL,
  `delegation_project` int(10) NOT NULL DEFAULT 0,
  `delegation_creator` int(10) NOT NULL DEFAULT 0,
  `delegation_created` datetime DEFAULT NULL,
  `delegation_rejection_updator` int(10) NOT NULL DEFAULT 0,
  `delegation_completion_updator` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`delegation_id`),
  KEY `delegation_start_date` (`delegation_start_date`),
  KEY `delegation_end_date` (`delegation_end_date`),
  KEY `delegated_to_user_id` (`delegated_to_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Add a column to the 'task_log' table to store the associated delegation

ALTER TABLE `task_log` ADD COLUMN `task_log_related_to_delegation_id` INT(10) NOT NULL DEFAULT 0 AFTER `task_log_company`;
ALTER TABLE `task_log` ADD COLUMN `task_log_related_to_delegation_op` INT(1) NOT NULL DEFAULT 0 AFTER `task_log_related_to_delegation_id`;

-- Make room for the new 'Delegations' option on the main menu

UPDATE `modules` SET `mod_ui_order` = `mod_ui_order` + 1 WHERE `mod_ui_order` > 4;

-- Add the new Delegations module

INSERT INTO `modules` (`mod_name`, `mod_directory`, `mod_version`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) 
       VALUES ('Delegations', 'delegations', '3.0.0', 'core', 1, 'Delegations', 'delegation.png', 5, 1, 'user_delegations', 'delegation_id', 'delegation_description', 'CDelegation');

-- Add the new module to the GACL tables

INSERT INTO `gacl_axo` (`section_value`, `value`, `order_value`, `name`, `hidden`) VALUES ('app', 'delegations', 12, 'Delegations', 0);

-- Add it into the groups 'All Modules' and 'Non-Admin Modules'

INSERT INTO `gacl_groups_axo_map`(`group_id`, `axo_id`) VALUES (11,(SELECT id FROM `gacl_axo` WHERE value = 'delegations'));
INSERT INTO `gacl_groups_axo_map`(`group_id`, `axo_id`) VALUES (13,(SELECT id FROM `gacl_axo` WHERE value = 'delegations'));

-- Add the table columns definitions for the list views in the main page

INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`, `module_config_text`, `module_config_order`)
       VALUES ('delegations', 'assigned_tasks', 'task_percent_complete', 'Work', 0),
       	      ('delegations', 'assigned_tasks', 'task_priority', 'P', 1),
              ('delegations', 'assigned_tasks', 'user_task_priority', 'U', 2),
       	      ('delegations', 'assigned_tasks', 'task_name', 'Task Name', 3),
       	      ('delegations', 'assigned_tasks', 'task_description', 'Task Description', 4),
              ('delegations', 'assigned_tasks', 'project_name', 'Project Name', 5),
              ('delegations', 'assigned_tasks', 'delegations',  'Delegations', 6),
              ('delegations', 'assigned_tasks', 'task_start_date', 'Start Date', 7),
              ('delegations', 'assigned_tasks', 'task_duration', 'Duration', 8),
              ('delegations', 'assigned_tasks', 'task_end_date', 'Finish Date', 9),
              ('delegations', 'assigned_tasks', 'task_due_in', 'Due In', 10);

INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`, `module_config_text`, `module_config_order`)
       VALUES ('delegations', 'my_delegated_tasks', 'delegation_percent_complete', 'Work', 0),
              ('delegations', 'my_delegated_tasks', 'delegating_user_id', 'Delegated By', 1),
              ('delegations', 'my_delegated_tasks', 'delegation_start_date', 'Delegated On', 2),
              ('delegations', 'my_delegated_tasks', 'task_name', 'Task Name', 3),
              ('delegations', 'my_delegated_tasks', 'delegation_name_description', 'Name and Description', 4),
              ('delegations', 'my_delegated_tasks', 'task_end_date', 'Task Finish Date', 5),
              ('delegations', 'my_delegated_tasks', 'delegation_end_date', 'Finish Date', 6),
              ('delegations', 'my_delegated_tasks', 'delegation_rejection_date', 'Rejected On', 7),
              ('delegations', 'my_delegated_tasks', 'delegation_rejection_reason', 'Rejection Reason', 8),
              ('delegations', 'my_delegated_tasks', 'delegation_rejection_validation_date', 'Rejection Validated On', 9);

INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`, `module_config_text`, `module_config_order`)
       VALUES ('delegations', 'tasks_delegated_others', 'delegation_percent_complete', 'Work', 0),
              ('delegations', 'tasks_delegated_others', 'delegated_to_user_id', 'Delegated To', 1),
              ('delegations', 'tasks_delegated_others', 'delegation_start_date', 'Delegated On', 2),
              ('delegations', 'tasks_delegated_others', 'task_name', 'Task Name', 3),
              ('delegations', 'tasks_delegated_others', 'delegation_name_description', 'Name and Description', 4),
              ('delegations', 'tasks_delegated_others', 'task_end_date', 'Task Finish Date', 5),
              ('delegations', 'tasks_delegated_others', 'delegation_end_date', 'Finish Date', 6),
              ('delegations', 'tasks_delegated_others', 'delegation_rejection_date', 'Rejected On', 7),
              ('delegations', 'tasks_delegated_others', 'delegation_rejection_reason', 'Rejection Reason', 8),
              ('delegations', 'tasks_delegated_others', 'delegation_rejection_validation_date', 'Rejection Validated On', 9);

INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`, `module_config_text`, `module_config_order`)
       VALUES ('delegations', 'rejected_delegations', 'delegation_percent_complete', 'Work', 0),
              ('delegations', 'rejected_delegations', 'delegating_user_id', 'Delegated By', 1),
              ('delegations', 'rejected_delegations', 'delegation_start_date', 'Delegated On', 2),
              ('delegations', 'rejected_delegations', 'task_name', 'Task Name', 3),
              ('delegations', 'rejected_delegations', 'delegation_name_description', 'Name and Description', 4),
              ('delegations', 'rejected_delegations', 'task_end_date', 'Task Finish Date', 5),
              ('delegations', 'rejected_delegations', 'delegation_end_date', 'Finish Date', 6),
              ('delegations', 'rejected_delegations', 'delegation_rejection_date', 'Rejected On', 7),
              ('delegations', 'rejected_delegations', 'delegation_rejection_reason', 'Rejection Reason', 8),
              ('delegations', 'rejected_delegations', 'delegation_rejection_validation_date', 'Rejection Validated On', 9);
