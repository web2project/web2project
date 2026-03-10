-- WEB2PROJECT DATABASE CONVERSION SCRIPT
-- USE THIS FILE FOR TESTING PURPOSES ONLY!
-- WITH A NORMAL WEB2PROJECT INSTALL YOU WILL NOT NEED TO USE THIS FILE 
-- BECAUSE ALL DATABASE CREATION PROCEDURES SHOULD BE HANDLED BY WEB2PROJECT 
-- INSTALLER.

-- HOW TO USE THIS FILE:
-- 1) DON'T.  PLEASE USE THE CONVERTER INSTEAD.

-- PLEASE PROVIDE US WITH FEEDBACK ON OUR FORUMS AT:
-- http://support.web2project.net
--
-- AND HELP US SPREAD THE WORD,
-- THANK YOU VERY MUCH.

--
-- (C) 2014 WEB2PROJECT DEVELOPMENT TEAM
--

# 2026 Update - setting to handle non-null datetimes
TRUNCATE TABLE `sessions`;
ALTER TABLE `sessions` CHANGE `session_created` `session_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

# 0060809
ALTER TABLE `sessions` CHANGE `session_user` `session_user` INT NOT NULL DEFAULT '0';

# 2026 Update - setting to eliminate '0000-00-00 00:00:00' datetimes
UPDATE `contacts` SET `contact_birthday` = NULL WHERE CAST(`contact_birthday` AS CHAR(20)) = '0000-00-00 00:00:00';
ALTER TABLE `forum_messages` CHANGE `message_date` `message_date` DATETIME NULL DEFAULT NULL;
UPDATE `forum_messages` SET `message_date` = NULL WHERE CAST(`message_date` AS CHAR(20)) = '0000-00-00 00:00:00';
ALTER TABLE `projects` CHANGE `project_end_date` `project_end_date` DATETIME NULL;
UPDATE `projects` SET `project_end_date` = NULL WHERE CAST(`project_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_end_date` = NULL WHERE CAST(`task_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `tasks` SET `task_start_date` = NULL WHERE CAST(`task_start_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `users` SET `user_birthday` = NULL WHERE CAST(`user_birthday` AS CHAR(20)) = '0000-00-00 00:00:00';

# 20061119
# archived status, do the second line only if project_status name matches 'Archived'
ALTER TABLE `projects` ADD `project_active` INT(1) DEFAULT 1 NOT NULL;
UPDATE `projects` SET `project_active` = 0 WHERE `project_status` = 7;

# 20061129
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'ProjectRequiredFields', 'f.project_name.value.length|<3\r\nf.project_color_identifier.value.length|<3\r\nf.project_company.options[f.project_company.selectedIndex].value|<1' );

# 20070106
# Adding Index to the custom fields value
ALTER TABLE `custom_fields_values` ADD INDEX `idx_cfv_id` ( `value_id` );

# 20070126
ALTER TABLE `files` ADD `file_folder` INT(11) DEFAULT '0' NOT NULL;

# 20070126
#
# Table structure for table `file_folders`
#
CREATE TABLE IF NOT EXISTS `file_folders` (
    `file_folder_id` int(11) NOT NULL auto_increment,
    `file_folder_parent` int(11) NOT NULL default '0',
    `file_folder_name` varchar(255) NOT NULL default '',
    `file_folder_description` text,
    PRIMARY KEY  (`file_folder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# 20071113
# Remove the NOT NULL clause from company_description to avoid issues on win plaforms
ALTER TABLE `companies` MODIFY `company_description` text;

# 20070728
#altered the data type to prevent the 99.99% misrounding issue
ALTER TABLE `tasks` MODIFY `task_percent_complete` tinyint(4) DEFAULT '0';

-- 
-- Table structure for table 'gacl_permissions'
-- 

CREATE TABLE IF NOT EXISTS `gacl_permissions` (
  `user_id` int(11) NOT NULL default '0',
  `user_name` varchar(255) NOT NULL default '',
  `module` varchar(64) NOT NULL default '',
  `item_id` int(11) NOT NULL default '0',
  `action` varchar(32) NOT NULL default '',
  `access` int(1) NOT NULL default '0',
  `acl_id` int(11) NOT NULL default '0',
  KEY `user_id` (`user_id`),
  KEY `module` (`module`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `user_preferences` SET `pref_value` = "web2project" WHERE `pref_name` = "UISTYLE";

ALTER TABLE `sysvals` ADD `sysval_value_id` VARCHAR(128) DEFAULT '0' NULL;

# 20090813
# Updated the database structure to handle some oddball dotProject 2.1.2 items
CREATE TABLE IF NOT EXISTS `event_contacts` (
  `event_id` int(10) NOT NULL default '0',
  `contact_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`event_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `project_designer_options`
-- 

CREATE TABLE IF NOT EXISTS `project_designer_options` (
  `pd_option_id` int(10) NOT NULL auto_increment,
  `pd_option_user` int(10) NOT NULL default '0',
  `pd_option_view_project` int(1) NOT NULL default '1',
  `pd_option_view_gantt` int(1) NOT NULL default '1',
  `pd_option_view_tasks` int(1) NOT NULL default '1',
  `pd_option_view_actions` int(1) NOT NULL default '1',
  `pd_option_view_addtasks` int(1) NOT NULL default '1',
  `pd_option_view_files` int(1) NOT NULL default '1',
  PRIMARY KEY  (`pd_option_id`),
  UNIQUE KEY `pd_option_user` (`pd_option_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;