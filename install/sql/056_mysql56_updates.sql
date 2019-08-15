
-- 0000-00-00 is not a valid date format for mysql 5.6+
-- Unfortunately when you're changing the table structure, mysql validates the other columns/values.. therefore:
--     if you have a table that previously supported 0000-00-00 in a single column, we can update no problem;
--     if you have multiple fields, it errors out because the others fail validation.
-- Doh.
-- Therefore, this script creates new tables with the columns configured properly, updates the old data,
--     extracts it from the old table, inserts it into the new, renames the old table, and renames the new to replace it.
-- Fun times.

ALTER TABLE `forum_messages`  CHANGE `message_date` `message_date`                    DATETIME DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `history`         CHANGE `history_date` `history_date`                    DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `sessions`        CHANGE `session_created` `session_created`              DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `tasks`           CHANGE `task_created` `task_created`                    DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `tasks`           CHANGE `task_updated` `task_updated`                    DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `task_log`        CHANGE `task_log_created` `task_log_created`            DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `task_log`        CHANGE `task_log_updated` `task_log_updated`            DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';

CREATE TABLE `forums2` (
  `forum_id` int(10) NOT NULL auto_increment,
  `forum_project` int(10) NOT NULL default '0',
  `forum_status` tinyint(4) NOT NULL default '-1',
  `forum_owner` int(10) NOT NULL default '0',
  `forum_name` varchar(50) NOT NULL default '',
  `forum_create_date` datetime default '1000-01-01 00:00:00',
  `forum_last_date` datetime default '1000-01-01 00:00:00',
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

UPDATE `forums` SET `forum_create_date` = '1000-01-01 00:00:00' where `forum_create_date` < '1000-01-01 00:00:00';
UPDATE `forums` SET `forum_last_date` = '1000-01-01 00:00:00' where `forum_last_date` < '1000-01-01 00:00:00';
INSERT INTO `forums2` SELECT * from `forums`;
RENAME TABLE `forums` TO `old_forums`;
RENAME TABLE `forums2` TO `forums`;


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
  `project_created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `project_updated` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
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
UPDATE `projects` SET `project_start_date` = '1000-01-01 00:00:00' where `project_start_date` < '1000-01-01 00:00:00';
UPDATE `projects` SET `project_end_date` = '1000-01-01 00:00:00' where `project_end_date` < '1000-01-01 00:00:00';
UPDATE `projects` SET `project_created` = '1000-01-01 00:00:00' where `project_created` < '1000-01-01 00:00:00';
UPDATE `projects` SET `project_updated` = '1000-01-01 00:00:00' where `project_updated` < '1000-01-01 00:00:00';
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
  `user_access_log_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_ip` varchar(15) NOT NULL DEFAULT '',
  `date_time_in` datetime DEFAULT '1000-01-01 00:00:00',
  `date_time_out` datetime DEFAULT '1000-01-01 00:00:00',
  `date_time_last_action` datetime DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY  (`user_access_log_id`),
  KEY `date_time_last_action` (`date_time_last_action`),
  KEY `date_time_in` (`date_time_in`),
  KEY `date_time_out` (`date_time_out`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
UPDATE `user_access_log` SET `date_time_in` = '1000-01-01 00:00:00' where `date_time_in` < '1000-01-01 00:00:00';
UPDATE `user_access_log` SET `date_time_out` = '1000-01-01 00:00:00' where `date_time_out` < '1000-01-01 00:00:00';
UPDATE `user_access_log` SET `date_time_last_action` = '1000-01-01 00:00:00' where `date_time_last_action` < '1000-01-01 00:00:00';
INSERT INTO `user_access_log2` SELECT * from `user_access_log`;
RENAME TABLE `user_access_log` TO `old_user_access_log`;
RENAME TABLE `user_access_log2` TO `user_access_log`;

CREATE TABLE `w2pversion2` (
  `code_revision` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `code_version` varchar(10) NOT NULL DEFAULT '',
  `db_version` int(10) NOT NULL DEFAULT '0',
  `last_db_update` date NOT NULL DEFAULT '1000-01-01',
  `last_code_update` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY  (`db_version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
UPDATE `w2pversion` SET `last_db_update` = '1000-01-01' where `last_db_update` < '1000-01-01';
UPDATE `w2pversion` SET `last_code_update` = '1000-01-01' where `last_code_update` < '1000-01-01';
INSERT INTO `w2pversion2` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`)
  SELECT 0, `code_version`, `db_version`, `last_db_update`, `last_code_update` from `w2pversion`;
RENAME TABLE `w2pversion` TO `old_w2pversion`;
RENAME TABLE `w2pversion2` TO `w2pversion`;
