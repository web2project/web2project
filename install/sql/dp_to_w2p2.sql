-- WEB2PROJECT DATABASE CONVERSION SCRIPT
-- USE THIS FILE FOR TESTING PURPOSES ONLY!
-- WITH A NORMAL WEB2PROJECT INSTALL YOU WILL NOT NEED TO USE THIS FILE 
-- BECAUSE ALL DATABASE CREATION PROCEDURES SHOULD BE HANDLED BY WEB2PROJECT 
-- INSTALLER.

-- HOW TO USE THIS FILE:
-- 1) DON'T.  PLEASE USE THE CONVERTER INSTEAD.

-- PLEASE PROVIDE US WITH FEEDBACK ON OUR FORUMS AT:
-- http://forums.web2project.net
--
-- AND HELP US SPREAD THE WORD,
-- THANK YOU VERY MUCH.

--
-- (C) 2009 WEB2PROJECT DEVELOPMENT TEAM
--

ALTER TABLE `projects` ADD `project_parent` INT(10) DEFAULT 0 NOT NULL;
ALTER TABLE `projects` ADD `project_original_parent` INT(10) DEFAULT 0 NOT NULL;
ALTER TABLE `projects` ADD `project_location` VARCHAR(255) DEFAULT "" NOT NULL;
ALTER TABLE `companies` ADD `company_country` VARCHAR(100) DEFAULT '' NOT NULL;
INSERT INTO `config` VALUES (0, 'activate_external_user_creation', 'true', '', 'checkbox');
ALTER TABLE `contacts` ADD `contact_updatekey` VARCHAR( 32 ) NULL DEFAULT NULL;
ALTER TABLE `contacts` ADD `contact_lastupdate` DATETIME NULL DEFAULT NULL;
ALTER TABLE `contacts` ADD `contact_updateasked` DATETIME NULL DEFAULT NULL;
ALTER TABLE `contacts` ADD `contact_skype` VARCHAR( 100 ) NOT NULL;
ALTER TABLE `contacts` ADD `contact_google` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `departments` ADD `dept_country` VARCHAR(100) DEFAULT '' NOT NULL;
ALTER TABLE `modules` ADD `mod_main_class` varchar(30) NOT NULL default '';

-- 
-- Table structure for table `tasks_critical`
-- 

CREATE TABLE `tasks_critical` (
  `task_project` INT(10) default NULL,
  `critical_task` INT(10) default NULL,
  `project_actual_end_date` datetime default NULL
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_problems`
-- 

CREATE TABLE `tasks_problems` (
  `task_project` INT(10) default NULL,
  `task_log_problem` tinyint(1) default NULL
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_sum`
-- 

CREATE TABLE `tasks_sum` (
  `task_project` INT(10) default NULL,
  `total_tasks` int(6) default NULL,
  `project_percent_complete` varchar(11) default NULL,
  `project_duration` varchar(11) default NULL
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_summy`
-- 

CREATE TABLE `tasks_summy` (
  `task_project` INT(10) default NULL,
  `my_tasks` varchar(10) default NULL
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_total`
-- 

CREATE TABLE `tasks_total` (
  `task_project` INT(10) default NULL,
  `total_tasks` INT(10) default NULL
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_users`
-- 

CREATE TABLE `tasks_users` (
  `task_project` INT(10) default NULL,
  `user_id` INT(10) default NULL
) ENGINE=MyISAM;

#Fix the permissions fields of the modules table to properly use the permissions system
UPDATE `modules` SET `permissions_item_table` = 'companies', `permissions_item_field` = 'company_id', `permissions_item_label` = 'company_name', `mod_main_class` = 'CCompany' WHERE  `modules`.`mod_directory` = 'companies';
UPDATE `modules` SET `permissions_item_table` = 'projects', `permissions_item_field` = 'project_id', `permissions_item_label` = 'project_name', `mod_main_class` = 'CProject' WHERE  `modules`.`mod_directory` = 'projects';
UPDATE `modules` SET `permissions_item_table` = 'tasks', `permissions_item_field` = 'task_id', `permissions_item_label` = 'task_name', `mod_main_class` = 'CTask' WHERE  `modules`.`mod_directory` = 'tasks';
UPDATE `modules` SET `permissions_item_table` = 'events', `permissions_item_field` = 'event_id', `permissions_item_label` = 'event_title', `mod_main_class` = 'CEvent' WHERE  `modules`.`mod_directory` = 'calendar';
UPDATE `modules` SET `permissions_item_table` = 'files', `permissions_item_field` = 'file_id', `permissions_item_label` = 'file_name', `mod_main_class` = 'CFile' WHERE  `modules`.`mod_directory` = 'files';
UPDATE `modules` SET `permissions_item_table` = 'contacts', `permissions_item_field` = 'contact_id', `permissions_item_label` = 'contact_first_name', `mod_main_class` = 'CContact' WHERE  `modules`.`mod_directory` = 'contacts';
UPDATE `modules` SET `permissions_item_table` = 'forums', `permissions_item_field` = 'forum_id', `permissions_item_label` = 'forum_name', `mod_main_class` = 'CForum' WHERE `modules`.`mod_directory` = 'forums';
UPDATE `modules` SET `permissions_item_table` = 'departments', `permissions_item_field` = 'dept_id', `permissions_item_label` = 'dept_name', `mod_main_class` = 'CDepartment' WHERE   `modules`.`mod_directory` = 'departments';

#Fix the host theme or the images for other themes will go beserk:
#Also shift dP name if necessary on the email prefix
UPDATE `config` SET `config_value` = "web2project" WHERE `config_name` = "host_style";
UPDATE `config` SET `config_value` = "[web2Project]" WHERE `config_name` = "email_prefix" AND `config_value` = "[dotProject]";

#Add New sysvals:
ALTER TABLE `sysvals` DROP INDEX `sysval_title`;
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalYesNo', 'No', '0');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalYesNo', 'Yes', '1');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'Default User', '0');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'Administrator', '1');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'CEO', '2');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'Director', '3');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'Branch Manager', '4');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'Manager', '5');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'Supervisor', '6');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'UserType', 'Employee', '7');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Andorra, Principality of', 'AD');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'United Arab Emirates', 'AE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Afghanistan, Islamic State of', 'AF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Antigua and Barbuda', 'AG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Anguilla', 'AI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Albania', 'AL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Armenia', 'AM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Netherlands Antilles', 'AN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Angola', 'AO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Antarctica', 'AQ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Argentina', 'AR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'American Samoa', 'AS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Austria', 'AT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Australia', 'AU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Aruba', 'AW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Azerbaidjan', 'AZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bosnia-Herzegovina', 'BA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Barbados', 'BB');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bangladesh', 'BD');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Belgium', 'BE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Burkina Faso', 'BF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bulgaria', 'BG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bahrain', 'BH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Burundi', 'BI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Benin', 'BJ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bermuda', 'BM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Brunei Darussalam', 'BN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bolivia', 'BO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Brazil', 'BR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bahamas', 'BS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bhutan', 'BT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Bouvet Island', 'BV');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Botswana', 'BW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Belarus', 'BY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Belize', 'BZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Canada', 'CA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cocos (Keeling) Islands', 'CC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Central African Republic', 'CF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Congo, The Democratic Republic of the', 'CD');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Congo', 'CG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Switzerland', 'CH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Ivory Coast (Cote D''Ivoire)', 'CI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cook Islands', 'CK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Chile', 'CL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cameroon', 'CM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'China', 'CN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Colombia', 'CO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Costa Rica', 'CR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Former Czechoslovakia', 'CS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cuba', 'CU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cape Verde', 'CV');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Christmas Island', 'CX');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cyprus', 'CY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Czech Republic', 'CZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Germany', 'DE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Djibouti', 'DJ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Denmark', 'DK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Dominica', 'DM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Dominican Republic', 'DO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Algeria', 'DZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Ecuador', 'EC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Estonia', 'EE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Egypt', 'EG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Western Sahara', 'EH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Eritrea', 'ER');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Spain', 'ES');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Ethiopia', 'ET');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Finland', 'FI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Fiji', 'FJ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Falkland Islands', 'FK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Micronesia', 'FM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Faroe Islands', 'FO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'France', 'FR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Gabon', 'GA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Great Britain', 'GB');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Grenada', 'GD');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Georgia', 'GE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'French Guyana', 'GF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Ghana', 'GH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Gibraltar', 'GI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Greenland', 'GL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Gambia', 'GM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Guinea', 'GN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Guadeloupe (French)', 'GP');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Equatorial Guinea', 'GQ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Greece', 'GR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'S. Georgia & S. Sandwich Isls.', 'GS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Guatemala', 'GT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Guam (USA)', 'GU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Guinea Bissau', 'GW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Guyana', 'GY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Hong Kong', 'HK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Heard and McDonald Islands', 'HM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Honduras', 'HN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Croatia', 'HR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Haiti', 'HT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Hungary', 'HU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Indonesia', 'ID');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Ireland', 'IE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Israel', 'IL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'India', 'IN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'British Indian Ocean Territory', 'IO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Iraq', 'IQ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Iran', 'IR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Iceland', 'IS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Italy', 'IT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Jamaica', 'JM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Jordan', 'JO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Japan', 'JP');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Kenya', 'KE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Kyrgyz Republic (Kyrgyzstan)', 'KG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cambodia, Kingdom of', 'KH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Kiribati', 'KI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Comoros', 'KM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Saint Kitts & Nevis Anguilla', 'KN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'North Korea', 'KP');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'South Korea', 'KR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Kuwait', 'KW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Cayman Islands', 'KY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Kazakhstan', 'KZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Laos', 'LA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Lebanon', 'LB');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Saint Lucia', 'LC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Liechtenstein', 'LI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Sri Lanka', 'LK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Liberia', 'LR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Lesotho', 'LS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Lithuania', 'LT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Luxembourg', 'LU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Latvia', 'LV');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Libya', 'LY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Morocco', 'MA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Monaco', 'MC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Moldavia', 'MD');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Madagascar', 'MG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Marshall Islands', 'MH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Macedonia', 'MK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Mali', 'ML');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Myanmar', 'MM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Mongolia', 'MN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Macau', 'MO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Northern Mariana Islands', 'MP');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Martinique (French)', 'MQ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Mauritania', 'MR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Montserrat', 'MS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Malta', 'MT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Mauritius', 'MU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Maldives', 'MV');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Malawi', 'MW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Mexico', 'MX');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Malaysia', 'MY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Mozambique', 'MZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Namibia', 'NA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'New Caledonia (French)', 'NC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Niger', 'NE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Norfolk Island', 'NF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Nigeria', 'NG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Nicaragua', 'NI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Netherlands', 'NL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Norway', 'NO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Nepal', 'NP');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Nauru', 'NR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Neutral Zone', 'NT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Niue', 'NU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'New Zealand', 'NZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Oman', 'OM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Panama', 'PA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Peru', 'PE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Polynesia (French)', 'PF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Papua New Guinea', 'PG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Philippines', 'PH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Pakistan', 'PK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Poland', 'PL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Saint Pierre and Miquelon', 'PM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Pitcairn Island', 'PN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Puerto Rico', 'PR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Portugal', 'PT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Palau', 'PW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Paraguay', 'PY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Qatar', 'QA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Reunion (French)', 'RE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Romania', 'RO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Russian Federation', 'RU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Rwanda', 'RW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Saudi Arabia', 'SA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Solomon Islands', 'SB');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Seychelles', 'SC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Sudan', 'SD');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Sweden', 'SE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Singapore', 'SG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Saint Helena', 'SH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Slovenia', 'SI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Svalbard and Jan Mayen Islands', 'SJ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Slovak Republic', 'SK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Sierra Leone', 'SL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'San Marino', 'SM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Senegal', 'SN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Somalia', 'SO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Suriname', 'SR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Saint Tome (Sao Tome) and Principe', 'ST');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Former USSR', 'SU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'El Salvador', 'SV');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Syria', 'SY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Swaziland', 'SZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Turks and Caicos Islands', 'TC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Chad', 'TD');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'French Southern Territories', 'TF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Togo', 'TG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Thailand', 'TH');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Tadjikistan', 'TJ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Tokelau', 'TK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Turkmenistan', 'TM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Tunisia', 'TN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Tonga', 'TO');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'East Timor', 'TL');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Turkey', 'TR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Trinidad and Tobago', 'TT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Tuvalu', 'TV');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Taiwan', 'TW');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Tanzania', 'TZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Ukraine', 'UA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Uganda', 'UG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'United Kingdom', 'UK');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'USA Minor Outlying Islands', 'UM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'United States', 'US');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Uruguay', 'UY');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Uzbekistan', 'UZ');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Holy See (Vatican City State)', 'VA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Saint Vincent & Grenadines', 'VC');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Venezuela', 'VE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Virgin Islands (British)', 'VG');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Virgin Islands (USA)', 'VI');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Vietnam', 'VN');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Vanuatu', 'VU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Wallis and Futuna Islands', 'WF');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Samoa', 'WS');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Yemen', 'YE');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Mayotte', 'YT');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Yugoslavia', 'YU');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'South Africa', 'ZA');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Zambia', 'ZM');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Zaire', 'ZR');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'GlobalCountries', 'Zimbabwe', 'ZW');

#To make project designer the default viewer of projects, false by default so that normal view is still used instead.
INSERT INTO `config` VALUES (0, 'projectdesigner_view_project', 'false', '', 'checkbox');

#Extra Indexes for Order By
ALTER TABLE `custom_fields_struct` ADD INDEX ( `field_order` );
ALTER TABLE `custom_fields_lists` ADD INDEX ( `field_id` );
ALTER TABLE `custom_fields_lists` ADD INDEX ( `list_value` );
ALTER TABLE `gacl_permissions` ADD INDEX ( `acl_id` );
ALTER TABLE `modules` ADD INDEX ( `mod_ui_order` );
ALTER TABLE `companies` ADD INDEX ( `company_name` );
ALTER TABLE `projects` ADD INDEX ( `project_name` );
ALTER TABLE `contacts` ADD INDEX ( `contact_first_name` );
ALTER TABLE `contacts` ADD INDEX ( `contact_last_name` );
ALTER TABLE `sysvals` ADD INDEX ( `sysval_value_id` );
ALTER TABLE `file_folders` ADD INDEX ( `file_folder_parent` );
ALTER TABLE `file_folders` ADD INDEX ( `file_folder_name` );
ALTER TABLE `files` ADD INDEX ( `file_name` );
ALTER TABLE `tasks` ADD INDEX ( `task_priority` );
ALTER TABLE `user_tasks` ADD INDEX ( `perc_assignment` );
ALTER TABLE `tasks` ADD INDEX ( `task_name` );
ALTER TABLE `forums` ADD INDEX ( `forum_name` );
ALTER TABLE `task_log` ADD INDEX ( `task_log_date` );
ALTER TABLE `billingcode` ADD INDEX ( `billingcode_name` );
ALTER TABLE `task_log` ADD INDEX ( `task_log_creator` );
ALTER TABLE `gacl_permissions` ADD INDEX ( `user_name` );
ALTER TABLE `gacl_permissions` ADD INDEX ( `action` );
ALTER TABLE `sysvals` ADD INDEX ( `sysval_title` );
ALTER TABLE `projects` ADD INDEX ( `project_parent` );

#Extra Indexes for Group By
ALTER TABLE `user_access_log` ADD INDEX ( `date_time_last_action` );
ALTER TABLE `files` ADD INDEX ( `file_folder` );

#Extra Indexes for Where
ALTER TABLE `contacts` ADD INDEX ( `contact_updatekey` );
ALTER TABLE `contacts` ADD INDEX ( `contact_email` );
ALTER TABLE `custom_fields_values` ADD INDEX ( `value_field_id` );
ALTER TABLE `custom_fields_values` ADD INDEX ( `value_object_id` );
ALTER TABLE `custom_fields_values` ADD INDEX ( `value_charvalue` );
ALTER TABLE `custom_fields_struct` ADD INDEX ( `field_module` );
ALTER TABLE `custom_fields_struct` ADD INDEX ( `field_page` );
ALTER TABLE `custom_fields_struct` ADD INDEX ( `field_page` );
ALTER TABLE `modules` ADD INDEX ( `mod_active` );
ALTER TABLE `modules` ADD INDEX ( `mod_directory` );
ALTER TABLE `user_preferences` ADD INDEX ( `pref_user` );
ALTER TABLE `user_access_log` ADD INDEX ( `date_time_in` );
ALTER TABLE `user_access_log` ADD INDEX ( `date_time_out` );
ALTER TABLE `modules` ADD INDEX ( `permissions_item_table` );
ALTER TABLE `users` ADD INDEX ( `user_contact` );
ALTER TABLE `events` ADD INDEX ( `event_recurs` );
ALTER TABLE `companies` ADD INDEX ( `company_type` );
ALTER TABLE `contacts` ADD INDEX ( `contact_private` );
ALTER TABLE `files` ADD INDEX ( `file_category` );
ALTER TABLE `tasks` ADD INDEX ( `task_status` );
ALTER TABLE `tasks` ADD INDEX ( `task_percent_complete` );
ALTER TABLE `projects` ADD INDEX ( `project_status` );
ALTER TABLE `task_log` ADD INDEX ( `task_log_problem` );
ALTER TABLE `projects` ADD INDEX ( `project_type` );
ALTER TABLE `projects` ADD INDEX ( `project_original_parent` );
ALTER TABLE `task_log` ADD INDEX ( `task_log_costcode` );
ALTER TABLE `billingcode` ADD INDEX ( `billingcode_status` );
ALTER TABLE `sysvals` ADD INDEX ( `sysval_key_id` );

#Extra Indexes for Joins
ALTER TABLE `sessions` ADD INDEX ( `session_user` );
ALTER TABLE `tasks` ADD INDEX ( `task_creator` );
ALTER TABLE `forum_messages` ADD INDEX ( `message_author` );
ALTER TABLE `project_departments` ADD INDEX ( `project_id` );
ALTER TABLE `project_departments` ADD INDEX ( `department_id` );
ALTER TABLE `tasks_critical` ADD INDEX ( `task_project` );
ALTER TABLE `tasks_problems` ADD INDEX ( `task_project` );
ALTER TABLE `tasks_sum` ADD INDEX ( `task_project` );
ALTER TABLE `tasks_summy` ADD INDEX ( `task_project` );
ALTER TABLE `tasks_total` ADD INDEX ( `task_project` );
ALTER TABLE `tasks_users` ADD INDEX ( `task_project` );
ALTER TABLE `files` ADD INDEX ( `file_checkout` );
ALTER TABLE `project_contacts` ADD INDEX ( `project_id` );
ALTER TABLE `user_tasks` ADD INDEX ( `user_id` );
ALTER TABLE `project_contacts` ADD INDEX ( `contact_id` );
ALTER TABLE `task_contacts` ADD INDEX ( `task_id` );
ALTER TABLE `task_contacts` ADD INDEX ( `contact_id` );

#Further improvements
ALTER TABLE `contacts` CHANGE `contact_company` `contact_company` INT( 11 ) NOT NULL DEFAULT "0";
ALTER TABLE `contacts` CHANGE `contact_department` `contact_department` INT( 11 ) NOT NULL DEFAULT "0";
ALTER TABLE `contacts` ADD INDEX ( `contact_department` );
ALTER TABLE `files` CHANGE `file_checkout` `file_checkout` VARCHAR( 16 ) NOT NULL DEFAULT "";
ALTER TABLE `departments` CHANGE `dept_name` `dept_name` VARCHAR( 255 ) NOT NULL DEFAULT "";
ALTER TABLE `departments` ADD INDEX ( `dept_name` );
ALTER TABLE `task_departments` ADD INDEX ( `task_id` );
ALTER TABLE `task_departments` ADD INDEX ( `department_id` );
ALTER TABLE `user_task_pin` ADD INDEX ( `task_id` );

#Deprecated tables
DROP TABLE IF EXISTS `custom_fields_option_id`;
DROP TABLE IF EXISTS `custom_fields_struct_id`;
DROP TABLE IF EXISTS `custom_fields_values_id`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `webcal_projects`;
DROP TABLE IF EXISTS `webcal_resources`;

#Table renames
RENAME TABLE dpversion TO w2pversion;

#Deprecated indexes
ALTER TABLE `syskeys` DROP INDEX `idx_syskey_name`;
ALTER TABLE `users` DROP INDEX `idx_user_parent`;
ALTER TABLE `user_tasks` DROP INDEX `user_type`;

#Delete the project_departments records when the department record has been deleted
DELETE FROM project_departments USING project_departments LEFT JOIN departments ON department_id = dept_id WHERE dept_id IS NULL;

#22/02/2008
#New fields needed for departments and user deletion check
ALTER TABLE `departments` ADD `dept_email` VARCHAR(255) DEFAULT '' NOT NULL;
ALTER TABLE `departments` ADD `dept_type` INT(3) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `projects` ADD `project_updator` INT(10) DEFAULT 0 NOT NULL;
ALTER TABLE `tasks` ADD `task_updator` INT(10) DEFAULT 0 NOT NULL;

#new PHPMailer SMTP options
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_secure', '', 'mail', 'select');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_debug', 'false', 'mail', 'checkbox');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_secure'), '');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_secure'), 'tls');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_secure'), 'ssl');

#Department types
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'DepartmentType', 'Not Defined', '0');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'DepartmentType', 'Profit', '1');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, 'DepartmentType', 'Cost', '2');

#Company Description fix 20080304
ALTER TABLE `companies` CHANGE `company_description` `company_description` TEXT NULL;

#Tasks Collpase/Expand User Default Value
#We should have one for each user
INSERT INTO `user_preferences` ( `pref_user` , `pref_name` , `pref_value` ) VALUES ('0', 'TASKSEXPANDED', '1');

#Add config key to set the Template status id, so we can remove them from calculations
INSERT INTO `config` VALUES (0, 'template_projects_status_id', '', 'projects', 'text');

#Add the reset_memory_limit for converted systems
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('reset_memory_limit', '64M', 'admin_system', 'text');

#Fix Config Groupings in a more readeable way:
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'company_name';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'debug';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'display_debug';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'enable_gantt_charts';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'host_locale';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'host_style';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'log_changes';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'page_title';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'reset_memory_limit';
UPDATE `config` SET `config_group` = 'admin_system' WHERE `config_name` = 'site_domain';
UPDATE `config` SET `config_group` = 'admin_users' WHERE `config_name` = 'activate_external_user_creation';
UPDATE `config` SET `config_group` = 'admin_users' WHERE `config_name` = 'admin_username';
UPDATE `config` SET `config_group` = 'admin_users' WHERE `config_name` = 'password_min_len';
UPDATE `config` SET `config_group` = 'admin_users' WHERE `config_name` = 'username_min_len';
UPDATE `config` SET `config_group` = 'auth' WHERE `config_name` = 'auth_method';
UPDATE `config` SET `config_group` = 'auth' WHERE `config_name` = 'postnuke_allow_login';
UPDATE `config` SET `config_group` = 'budgeting' WHERE `config_name` = 'currency_symbol';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_day_end';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_day_increment';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_day_start';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_day_view_show_minical';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'cal_working_days';
UPDATE `config` SET `config_group` = 'calendar' WHERE `config_name` = 'daily_working_hours';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'files_ci_preserve_attr';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'files_show_versions_edit';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'index_max_file_size';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_application/msword';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_application/pdf';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_default';
UPDATE `config` SET `config_group` = 'files' WHERE `config_name` = 'parser_text/html';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_allow_login';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_base_dn';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_host';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_port';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_search_pass';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_search_user';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_user_filter';
UPDATE `config` SET `config_group` = 'ldap' WHERE `config_name` = 'ldap_version';
UPDATE `config` SET `config_group` = 'locales' WHERE `config_name` = 'locale_alert';
UPDATE `config` SET `config_group` = 'locales' WHERE `config_name` = 'locale_warn';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'email_prefix';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_auth';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_debug';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_defer';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_host';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_pass';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_port';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_secure';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_timeout';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_transport';
UPDATE `config` SET `config_group` = 'mail' WHERE `config_name` = 'mail_user';
UPDATE `config` SET `config_group` = 'projects' WHERE `config_name` = 'projectdesigner_view_project';
UPDATE `config` SET `config_group` = 'projects' WHERE `config_name` = 'restrict_color_selection';
UPDATE `config` SET `config_group` = 'projects' WHERE `config_name` = 'template_projects_status_id';
UPDATE `config` SET `config_group` = 'session' WHERE `config_name` = 'session_gc_scan_queue';
UPDATE `config` SET `config_group` = 'session' WHERE `config_name` = 'session_handling';
UPDATE `config` SET `config_group` = 'session' WHERE `config_name` = 'session_idle_time';
UPDATE `config` SET `config_group` = 'session' WHERE `config_name` = 'session_max_lifetime';
UPDATE `config` SET `config_group` = 'startup' WHERE `config_name` = 'default_view_a';
UPDATE `config` SET `config_group` = 'startup' WHERE `config_name` = 'default_view_m';
UPDATE `config` SET `config_group` = 'startup' WHERE `config_name` = 'default_view_tab';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'check_overallocation';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'check_task_dates';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'direct_edit_assignment';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'restrict_task_time_editing';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'show_all_task_assignees';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'task_reminder_control';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'task_reminder_days_before';
UPDATE `config` SET `config_group` = 'tasks' WHERE `config_name` = 'task_reminder_repeat';
DELETE FROM `config` WHERE `config_name` = 'link_tickets_kludge';

#20/07/2008
# Trying to avoid the error:
# "Data too long for column 'project_percent_complete'" and probably to 'project_duration' too.
# By changing those fields data types from VARCHAR to FLOAT.
ALTER TABLE `tasks_sum` CHANGE `project_percent_complete` `project_percent_complete` FLOAT NULL DEFAULT NULL;
ALTER TABLE `tasks_sum` CHANGE `project_duration` `project_duration` FLOAT NULL DEFAULT NULL; 

#20090128
ALTER TABLE `w2pversion` ADD `code_revision` INT( 10 ) NOT NULL FIRST ;
ALTER TABLE `w2pversion` ADD PRIMARY KEY ( `code_revision` );
TRUNCATE TABLE `w2pversion`;
INSERT INTO `w2pversion` (`code_revision` ,`code_version` ,`db_version` ,`last_db_update` ,`last_code_update`)
	VALUES ('250', '0.9.9', '1', now(), now());

#20090224
ALTER TABLE `custom_fields_struct` ADD `field_published` TINYINT( 1 ) NOT NULL DEFAULT '0';
UPDATE `custom_fields_struct` SET field_published = 1;