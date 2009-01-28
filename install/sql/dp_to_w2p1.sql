-- WEB2PROJECT DATABASE CONVERSION SCRIPT
-- USE THIS FILE FOR TESTING PURPOSES ONLY!
-- WITH A NORMAL WEB2PROJECT INSTALL YOU WILL NOT NEED TO USE THIS FILE BECAUSE ALL DATABASE CREATION PROCEDURES SHOULD BE HANDLED BY WEB2PROJECT INSTALLER.
-- USE THIS FILE AT YOUR OWN RISK AND DON'T FORGET TO BACKUP ANY IMPORTANT DATA OR FILES BEFORE USING IT.

-- HOW TO USE THIS FILE:
-- 1) YOU SHOULD CREATE A NEW EMPTY DATABASE AND THEN IMPORT THIS SCRIPT INTO IT USING PHPMYADMIN.
-- NOTE: IF YOU WANT TO USE TABLE NAMES WITH PREFIXES PLEASE REPLACE IN THIS SQL FILE ALL REFERENCES TO CREATE TABLE ` TO CREATE TABLE `yourprefix_
-- THEN YOU CAN IMPORT THE SQL SCRIPT WITH PHPMYADMIN. KEEP IN MIND THAT YOU WILL HAVE TO SET THAT PREFIX ON THE includes/config.php FILE WITH $w2Pconfig['dbprefix'] = 'yourprefix_';
-- 2) CHANGE YOUR includes/config.php TO POINT TO THE DATABASE WITH THE CORRECT SETTINGS.
-- 3) AFTER THAT YOU CAN POINT YOUR BROWSER TO YOUR WEB2PROJECT SITE AND LOGIN WITH USER admin AND PASSWORD passwd

-- PLEASE PROVIDE US WITH FEEDBACK ON OUR FORUMS AT:
-- http://forums.web2project.net
--
-- AND HELP US SPREAD THE WORD,
-- THANK YOU VERY MUCH.

--
-- (C) 2008 WEB2PROJECT DEVELOPMENT TEAM
--



# 0060809
ALTER TABLE `sessions` ADD `session_user` INT DEFAULT '0' NOT NULL AFTER `session_id`;

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
CREATE TABLE `file_folders` (
    `file_folder_id` int(11) NOT NULL auto_increment,
    `file_folder_parent` int(11) NOT NULL default '0',
    `file_folder_name` varchar(255) NOT NULL default '',
    `file_folder_description` text,
    PRIMARY KEY  (`file_folder_id`)
) TYPE=MyISAM;

#20071113
# Remove the NOT NULL clause from company_description to avoid issues on win plaforms
ALTER TABLE `companies` MODIFY `company_description` text;

#20070728
#altered the data type to prevent the 99.99% misrounding issue
ALTER TABLE `tasks` MODIFY `task_percent_complete` tinyint(4) DEFAULT '0';

-- 
-- Table structure for table 'gacl_permissions'
-- 

CREATE TABLE gacl_permissions (
  user_id int(11) NOT NULL default '0',
  user_name varchar(255) NOT NULL default '',
  module varchar(64) NOT NULL default '',
  item_id int(11) NOT NULL default '0',
  `action` varchar(32) NOT NULL default '',
  access int(1) NOT NULL default '0',
  acl_id int(11) NOT NULL default '0',
  KEY user_id (user_id),
  KEY module (module),
  KEY item_id (item_id)
) TYPE=MyISAM;

UPDATE `user_preferences` SET `pref_value` = "web2project" WHERE `pref_name` = "UISTYLE";

ALTER TABLE `sysvals` ADD `sysval_value_id` VARCHAR(128) DEFAULT '0' NULL;