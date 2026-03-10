
-- 0000-00-00 is not a valid date format for mysql 5.6+
-- Unfortunately when you're changing the table structure, mysql validates the other columns/values.. therefore:
--     if you have a table that previously supported 0000-00-00 in a single column, we can update no problem;
--     if you have multiple fields, it errors out because the others fail validation.
-- Doh.
-- Therefore, this script creates new tables with the columns configured properly, updates the old data,
--     extracts it from the old table, inserts it into the new, renames the old table, and renames the new to replace it.
-- Fun times.

# 2026 Update - eliminating '0000-00-00 00:00:00' as defaults and values
ALTER TABLE `forum_messages`  CHANGE `message_date` `message_date` DATETIME DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `sessions`
    MODIFY `session_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `session_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `tasks`
    MODIFY `task_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `task_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

# Added a create statement for history because there are potentially some upgrade paths that could have left it out
CREATE TABLE IF NOT EXISTS `history` (
  `history_id` int(10) NOT NULL AUTO_INCREMENT,
  `history_date` datetime NOT NULL default CURRENT_TIMESTAMP,
  `history_user` int(10) NOT NULL default '0',
  `history_action` varchar(20) NOT NULL default 'modify',
  `history_item` int(10) NOT NULL,
  `history_table` varchar(20) NOT NULL default '',
  `history_project` int(10) NOT NULL default '0',
  `history_name` varchar(255) default NULL,
  `history_changes` text,
  `history_description` text,
  PRIMARY KEY  (`history_id`),
  KEY `index_history_module` (`history_table`,`history_item`),
  KEY `index_history_item` (`history_item`),
  KEY `history_date` (`history_date`),
  KEY `history_table` (`history_table`),
  KEY `history_user` (`history_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# Still need the alter statement in case the history table was there but incorrect from a previous install
ALTER TABLE `history`         CHANGE `history_date` `history_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `task_log` SET `task_log_created` = `task_log_date` WHERE `task_log_created` IS NULL;
UPDATE `task_log` SET `task_log_updated` = `task_log_date` WHERE `task_log_updated` IS NULL;

ALTER TABLE `task_log`
    MODIFY `task_log_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `task_log_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

# Clean up the forums structure
CREATE TABLE `forums2` (
  `forum_id` int(10) NOT NULL auto_increment,
  `forum_project` int(10) NOT NULL default '0',
  `forum_status` tinyint(4) NOT NULL default '-1',
  `forum_owner` int(10) NOT NULL default '0',
  `forum_name` varchar(50) NOT NULL default '',
  `forum_create_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `forum_last_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `forum_last_id` int(10) unsigned NOT NULL default '0',
  `forum_message_count` int(10) NOT NULL default '0',
  `forum_description` varchar(255) default NULL,
  `forum_moderated` int(10) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `idx_fproject` (`forum_project`),
  KEY `idx_fowner` (`forum_owner`),
  KEY `forum_status` (`forum_status`),
  KEY `forum_name` (`forum_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

UPDATE `forums` SET `forum_create_date` = NOW() WHERE CAST(`forum_create_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `forums` SET `forum_create_date` = NOW() WHERE `forum_create_date` IS NULL;
UPDATE `forums` SET `forum_last_date` = `forum_create_date` WHERE CAST(`forum_last_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `forums` SET `forum_last_date` = `forum_create_date` WHERE `forum_last_date` IS NULL;
INSERT INTO `forums2` SELECT * from `forums`;
RENAME TABLE `forums` TO `old_forums`;
RENAME TABLE `forums2` TO `forums`;

# Clean up the projects structure
CREATE TABLE `projects2` (
  `project_id` int(10) NOT NULL auto_increment,
  `project_company` int(10) NOT NULL DEFAULT '0',
  `project_department` int(10) NOT NULL DEFAULT '0',
  `project_name` varchar(255) DEFAULT NULL,
  `project_short_name` varchar(10) DEFAULT NULL,
  `project_owner` int(10) DEFAULT '0',
  `project_url` varchar(255) DEFAULT NULL,
  `project_demo_url` varchar(255) DEFAULT NULL,
  `project_start_date` date DEFAULT NULL,
  `project_end_date` date DEFAULT NULL,
  `project_actual_end_date` datetime DEFAULT NULL,
  `project_status` int(10) DEFAULT '0',
  `project_percent_complete` tinyint(4) DEFAULT '0',
  `project_color_identifier` varchar(6) DEFAULT 'eeeeee',
  `project_description` mediumtext,
  `project_target_budget` decimal(10,2) DEFAULT '0.00',
  `project_actual_budget` decimal(10,2) DEFAULT '0.00',
  `project_scheduled_hours` float NOT NULL DEFAULT '0',
  `project_worked_hours` float NOT NULL DEFAULT '0',
  `project_task_count` int(10) NOT NULL DEFAULT '0',
  `project_creator` int(10) DEFAULT '0',
  `project_private` tinyint(3) UNSIGNED DEFAULT '0',
  `project_departments` varchar(100) DEFAULT NULL COMMENT 'deprecated',
  `project_contacts` varchar(100) DEFAULT NULL COMMENT 'deprecated',
  `project_priority` tinyint(4) DEFAULT '0',
  `project_type` smallint(6) NOT NULL DEFAULT '0',
  `project_keydate` datetime DEFAULT NULL,
  `project_keydate_pos` tinyint(1) DEFAULT '0',
  `project_keytask` int(10) DEFAULT '0',
  `project_active` int(1) NOT NULL DEFAULT '1',
  `project_original_parent` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `project_parent` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `project_empireint_special` int(1) NOT NULL DEFAULT '0',
  `project_updator` int(10) NOT NULL DEFAULT '0',
  `project_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `project_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `project_status_comment` varchar(255) NOT NULL DEFAULT '',
  `project_subpriority` tinyint(4) DEFAULT '0',
  `project_end_date_adjusted_user` int(10) NOT NULL DEFAULT '0',
  `project_location` varchar(255) NOT NULL DEFAULT '',
  `project_last_task` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`project_id`),
  KEY `idx_project_owner` (`project_owner`),
  KEY `idx_sdate` (`project_start_date`),
  KEY `idx_edate` (`project_end_date`),
  KEY `project_short_name` (`project_short_name`),
  KEY `idx_proj1` (`project_company`),
  KEY `project_name` (`project_name`),
  KEY `project_parent` (`project_parent`),
  KEY `project_status` (`project_status`),
  KEY `project_type` (`project_type`),
  KEY `project_original_parent` (`project_original_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `projects` SET `project_start_date` = NULL WHERE CAST(`project_start_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_end_date` = NULL WHERE CAST(`project_end_date` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_created` = NOW() WHERE CAST(`project_created` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_created` = NOW() WHERE `project_created` IS NULL;
UPDATE `projects` SET `project_updated` = NOW() WHERE CAST(`project_updated` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `projects` SET `project_updated` = NOW() WHERE `project_updated` IS NULL;

INSERT INTO `projects2` (`project_id`, `project_company`, `project_department`, `project_name`, `project_short_name`,
      `project_owner`, `project_url`, `project_demo_url`, `project_start_date`, `project_end_date`, `project_actual_end_date`,
      `project_status`, `project_percent_complete`, `project_color_identifier`, `project_description`, `project_target_budget`,
      `project_actual_budget`, `project_scheduled_hours`, `project_worked_hours`, `project_task_count`, `project_creator`,
      `project_private`, `project_departments`, `project_contacts`, `project_priority`, `project_type`, `project_keydate`,
      `project_keydate_pos`, `project_keytask`, `project_active`, `project_original_parent`, `project_parent`,
      `project_empireint_special`, `project_updator`, `project_created`, `project_updated`, `project_status_comment`,
      `project_subpriority`, `project_end_date_adjusted_user`, `project_location`, `project_last_task`
  ) SELECT
    `project_id`, `project_company`, 0, `project_name`, `project_short_name`,
    `project_owner`, `project_url`, `project_demo_url`, `project_start_date`, `project_end_date`, `project_actual_end_date`,
    `project_status`, `project_percent_complete`, `project_color_identifier`, `project_description`, `project_target_budget`,
    `project_actual_budget`, `project_scheduled_hours`, `project_worked_hours`, `project_task_count`, `project_creator`,
    `project_private`, `project_departments`, `project_contacts`, `project_priority`, `project_type`, '1000-01-01',
    0, 0, `project_active`, `project_original_parent`, `project_parent`,
    0, `project_updator`, `project_created`, `project_updated`, `project_status_comment`,
    `project_subpriority`, `project_end_date_adjusted_user`, `project_location`, `project_last_task`
  FROM `projects`;
RENAME TABLE `projects` TO `old_projects`;
RENAME TABLE `projects2` TO `projects`;

CREATE TABLE `user_access_log2` (
  `user_access_log_id` int(10) auto_increment,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_ip` varchar(15) NOT NULL DEFAULT '',
  `date_time_in` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_time_out` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_time_last_action` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_access_log_id`),
  KEY `date_time_last_action` (`date_time_last_action`),
  KEY `date_time_in` (`date_time_in`),
  KEY `date_time_out` (`date_time_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

UPDATE `user_access_log` SET `date_time_out` = NOW() WHERE CAST(`date_time_out` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `user_access_log` SET `date_time_out` = NOW() WHERE `date_time_out` IS NULL;

UPDATE `user_access_log` SET `date_time_in` = `date_time_out` WHERE CAST(`date_time_in` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `user_access_log` SET `date_time_in` = `date_time_out` WHERE `date_time_in` IS NULL;

UPDATE `user_access_log` SET `date_time_last_action` = `date_time_out` WHERE CAST(`date_time_last_action` AS CHAR(20)) = '0000-00-00 00:00:00';
UPDATE `user_access_log` SET `date_time_last_action` = `date_time_out` WHERE `date_time_last_action` IS NULL;

INSERT INTO `user_access_log2` SELECT * from `user_access_log`;
RENAME TABLE `user_access_log` TO `old_user_access_log`;
RENAME TABLE `user_access_log2` TO `user_access_log`;

CREATE TABLE `w2pversion2` (
  `code_revision` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `code_version` varchar(10) NOT NULL DEFAULT '',
  `db_version` int(10) NOT NULL DEFAULT '0',
  `last_db_update` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_code_update` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`db_version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
UPDATE `w2pversion` SET `last_db_update` = '2001-01-01' where `last_db_update` < '2001-01-01';
UPDATE `w2pversion` SET `last_code_update` = `last_db_update` where `last_code_update` < '2001-01-01';
INSERT INTO `w2pversion2` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`)
  SELECT 0, `code_version`, `db_version`, `last_db_update`, `last_code_update` from `w2pversion`;
RENAME TABLE `w2pversion` TO `old_w2pversion`;
RENAME TABLE `w2pversion2` TO `w2pversion`;
