
CREATE TABLE IF NOT EXISTS `module_config` (
    `module_config_id` int(10) NOT NULL AUTO_INCREMENT,
    `module_name` varchar(50) NOT NULL,
    `module_config_name` varchar(50) NOT NULL,
    `module_config_value` varchar(50) NOT NULL,
    `module_config_text` varchar(50) NOT NULL,
    `module_config_order` int(10) NOT NULL,
    PRIMARY KEY (`module_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`, 
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_color_identifier', 'Progress', 1);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`, 
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_priority', 'P', 2);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_name', 'Project Name', 3);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_company', 'Company', 4);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_start_date', 'Start', 5);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_end_date', 'End', 6);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_actual_end_date', 'Actual', 7);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'task_log_problem', 'LP', 8);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_owner', 'Owner', 9);
INSERT INTO `module_config` (`module_name`, `module_config_name`, `module_config_value`,
	`module_config_text`, `module_config_order`) VALUES
	('projects', 'index_list', 'project_task_count', 'Tasks', 10);