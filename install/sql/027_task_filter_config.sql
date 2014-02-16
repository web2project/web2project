
-- Makes the behavior for the company filter on the Task List screen configurable

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) 
    VALUES ('company_filter_default', 'all', 'tasks', 'select');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'company_filter_default'), 'allcompanies');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'company_filter_default'), 'user');

-- Makes the behavior for the task filter on the Task List screen configurable

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`)
    VALUES ('task_filter_default', 'myunfinished', 'tasks', 'select');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'my');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'myunfinished');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'allunfinished');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'myproj');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'mycomp');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'unassigned');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'taskowned');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'taskcreated');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'all');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'allfinished7days');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'task_filter_default'), 'myfinished7days');
