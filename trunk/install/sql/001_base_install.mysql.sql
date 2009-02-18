-- WEB2PROJECT DATABASE CONVERSION SCRIPT
-- USE THIS FILE FOR TESTING PURPOSES ONLY!
-- WITH A NORMAL WEB2PROJECT INSTALL YOU WILL NOT NEED TO USE THIS FILE 
-- BECAUSE ALL DATABASE CREATION PROCEDURES SHOULD BE HANDLED BY WEB2PROJECT 
-- INSTALLER.

-- HOW TO USE THIS FILE:
-- 1) DON'T.  PLEASE USE THE INSTALLER INSTEAD.
--
-- PLEASE PROVIDE US WITH FEEDBACK ON OUR FORUMS AT:
-- http://forums.web2project.net
--
-- AND HELP US SPREAD THE WORD,
-- THANK YOU VERY MUCH.

--
-- (C) 2009 WEB2PROJECT DEVELOPMENT TEAM
--

-- --------------------------------------------------------

--
-- Table structure for table `billingcode`
--

CREATE TABLE `billingcode` (
  `billingcode_id` int(10) NOT NULL auto_increment,
  `billingcode_name` varchar(25) NOT NULL default '',
  `billingcode_value` float NOT NULL default '0',
  `billingcode_desc` varchar(255) NOT NULL default '',
  `billingcode_status` int(1) NOT NULL default '0',
  `company_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`billingcode_id`),
  UNIQUE KEY `billingcode_name` (`billingcode_name`,`company_id`),
  KEY `billingcode_name_2` (`billingcode_name`),
  KEY `billingcode_status` (`billingcode_status`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(10) NOT NULL auto_increment,
  `company_module` int(10) NOT NULL default '0',
  `company_name` varchar(100) default '',
  `company_phone1` varchar(30) default '',
  `company_phone2` varchar(30) default '',
  `company_fax` varchar(30) default '',
  `company_address1` varchar(50) default '',
  `company_address2` varchar(50) default '',
  `company_city` varchar(30) default '',
  `company_state` varchar(30) default '',
  `company_zip` varchar(11) default '',
  `company_country` varchar(100) NOT NULL default '',
  `company_primary_url` varchar(255) default '',
  `company_owner` int(10) NOT NULL default '0',
  `company_description` text,
  `company_type` int(3) NOT NULL default '0',
  `company_email` varchar(255) default NULL,
  `company_custom` longtext,
  `company_private` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`company_id`),
  KEY `idx_cpy1` (`company_owner`),
  KEY `company_name` (`company_name`),
  KEY `company_type` (`company_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `config_id` int(10) NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL default '',
  `config_value` varchar(255) NOT NULL default '',
  `config_group` varchar(255) NOT NULL default '',
  `config_type` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`config_id`),
  UNIQUE KEY `config_name` (`config_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('host_locale', 'en', 'admin_system', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('check_overallocation', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('currency_symbol', '$', 'budgeting', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('host_style', 'web2project', 'admin_system', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('company_name', 'web2Project Development', 'admin_system', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('page_title', 'web2Project', 'admin_system', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('site_domain', 'web2project.net', 'admin_system', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('email_prefix', '[web2Project]', 'mail', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('admin_username', 'admin', 'admin_users', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('username_min_len', '4', 'admin_users', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('password_min_len', '4', 'admin_users', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('enable_gantt_charts', 'true', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('log_changes', 'true', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('check_task_dates', 'true', 'tasks', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('locale_warn', 'false', 'locales', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('locale_alert', '^', 'locales', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('daily_working_hours', '8.0', 'calendar', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('display_debug', 'false', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('show_all_task_assignees', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('direct_edit_assignment', 'true', 'tasks', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('restrict_color_selection', 'false', 'projects', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('cal_day_view_show_minical', 'true', 'calendar', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('cal_day_start', '8', 'calendar', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('cal_day_end', '17', 'calendar', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('cal_day_increment', '15', 'calendar', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('cal_working_days', '1,2,3,4,5', 'calendar', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('restrict_task_time_editing', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('default_view_m', 'calendar', 'startup', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('default_view_a', 'day_view', 'startup', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('default_view_tab', '1', 'startup', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('index_max_file_size', '-1', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('session_handling', 'app', 'session', 'select');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('session_idle_time', '2d', 'session', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('session_max_lifetime', '1m', 'session', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('debug', '1', 'admin_system', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('parser_default', '/usr/bin/strings', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('parser_application/msword', '/usr/bin/strings', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('parser_text/html', '/usr/bin/strings', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('parser_application/pdf', '/usr/bin/pdftotext', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('files_ci_preserve_attr', 'true', 'files', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('files_show_versions_edit', 'false', 'files', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('reset_memory_limit', '64M', 'admin_system', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('auth_method', 'sql', 'auth', 'select');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_host', 'localhost', 'ldap', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_port', '389', 'ldap', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_version', '3', 'ldap', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_base_dn', 'dc=web2project,dc=net', 'ldap', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_user_filter', '(uid=%USERNAME%)', 'ldap', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('postnuke_allow_login', 'true', 'auth', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_transport', 'smtp', 'mail', 'select');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_host', 'mail.yourdomain.com', 'mail', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_port', '25', 'mail', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_auth', 'true', 'mail', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_user', 'smtpuser', 'mail', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_pass', 'smtppasswd', 'mail', 'password');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_defer', 'false', 'mail', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_timeout', '30', 'mail', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('task_reminder_control', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('task_reminder_days_before', '1', 'tasks', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('task_reminder_repeat', '100', 'tasks', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('session_gc_scan_queue', 'false', 'session', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_search_user', 'Manager', 'ldap', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_search_pass', 'secret', 'ldap', 'password');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('ldap_allow_login', 'true', 'ldap', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('activate_external_user_creation', 'true', 'admin_users', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('projectdesigner_view_project', 'false', 'projects', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_secure', '', 'mail', 'select');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('mail_debug', 'false', 'mail', 'checkbox');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES ('template_projects_status_id', '6', 'projects', 'text');

-- --------------------------------------------------------

--
-- Table structure for table `config_list`
--

CREATE TABLE `config_list` (
  `config_list_id` int(10) NOT NULL auto_increment,
  `config_id` int(10) NOT NULL default '0',
  `config_list_name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`config_list_id`),
  KEY `config_id` (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `config_list`
--

INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'auth_method'), 'sql');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'auth_method'), 'ldap');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'auth_method'), 'pn');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'session_handling'), 'app');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'session_handling'), 'php');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_transport'), 'php');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_transport'), 'smtp');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_secure'), '');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_secure'), 'tls');
INSERT INTO `config_list` (`config_id`, `config_list_name`) VALUES 
	((SELECT `config_id` FROM `config` WHERE `config_name` = 'mail_secure'), 'ssl');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(10) NOT NULL auto_increment,
  `contact_first_name` varchar(30) default NULL,
  `contact_last_name` varchar(30) default NULL,
  `contact_order_by` varchar(30) NOT NULL default '',
  `contact_title` varchar(50) default NULL,
  `contact_birthday` date default NULL,
  `contact_job` varchar(255) default NULL,
  `contact_company` int(10) NOT NULL default '0',
  `contact_department` int(10) NOT NULL default '0',
  `contact_type` varchar(20) default NULL,
  `contact_email` varchar(255) default NULL,
  `contact_email2` varchar(255) default NULL,
  `contact_url` varchar(255) default NULL,
  `contact_phone` varchar(30) default NULL,
  `contact_phone2` varchar(30) default NULL,
  `contact_fax` varchar(30) default NULL,
  `contact_mobile` varchar(30) default NULL,
  `contact_address1` varchar(60) default NULL,
  `contact_address2` varchar(60) default NULL,
  `contact_city` varchar(30) default NULL,
  `contact_state` varchar(30) default NULL,
  `contact_zip` varchar(11) default NULL,
  `contact_country` varchar(30) default NULL,
  `contact_jabber` varchar(255) default NULL,
  `contact_icq` varchar(20) default NULL,
  `contact_msn` varchar(255) default NULL,
  `contact_yahoo` varchar(255) default NULL,
  `contact_aol` varchar(30) default NULL,
  `contact_notes` text,
  `contact_project` int(10) NOT NULL default '0',
  `contact_icon` varchar(20) default 'obj/contact',
  `contact_owner` int(10) unsigned default '0',
  `contact_private` tinyint(3) unsigned default '0',
  `contact_updatekey` varchar(32) default NULL,
  `contact_lastupdate` datetime default NULL,
  `contact_updateasked` datetime default NULL,
  `contact_skype` varchar(100) default NULL,
  `contact_google` varchar(255) default NULL,
  PRIMARY KEY  (`contact_id`),
  KEY `idx_oby` (`contact_order_by`),
  KEY `idx_co` (`contact_company`),
  KEY `idx_prp` (`contact_project`),
  KEY `contact_first_name` (`contact_first_name`),
  KEY `contact_last_name` (`contact_last_name`),
  KEY `contact_updatekey` (`contact_updatekey`),
  KEY `contact_email` (`contact_email`),
  KEY `contact_private` (`contact_private`),
  KEY `contact_department` (`contact_department`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`contact_id`, `contact_first_name`, `contact_last_name`, `contact_order_by`, `contact_title`, `contact_birthday`, `contact_job`, `contact_company`, `contact_department`, `contact_type`, `contact_email`, `contact_email2`, `contact_url`, `contact_phone`, `contact_phone2`, `contact_fax`, `contact_mobile`, `contact_address1`, `contact_address2`, `contact_city`, `contact_state`, `contact_zip`, `contact_country`, `contact_jabber`, `contact_icq`, `contact_msn`, `contact_yahoo`, `contact_aol`, `contact_notes`, `contact_project`, `contact_icon`, `contact_owner`, `contact_private`, `contact_updatekey`, `contact_lastupdate`, `contact_updateasked`, `contact_skype`, `contact_google`) VALUES (1, 'Admin', 'Person', '', NULL, NULL, NULL, 0, 0, NULL, 'admin@localhost', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'obj/contact', 0, 0, NULL, NULL, NULL, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_lists`
--

CREATE TABLE `custom_fields_lists` (
  `field_id` int(10) default NULL,
  `list_option_id` int(10) default NULL,
  `list_value` varchar(250) default NULL,
  KEY `field_id` (`field_id`),
  KEY `list_value` (`list_value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_struct`
--

CREATE TABLE `custom_fields_struct` (
  `field_id` int(10) NOT NULL auto_increment,
  `field_module` varchar(30) default NULL,
  `field_page` varchar(30) default NULL,
  `field_htmltype` varchar(20) default NULL,
  `field_datatype` varchar(20) default NULL,
  `field_order` int(10) default NULL,
  `field_name` varchar(100) default NULL,
  `field_extratags` varchar(250) default NULL,
  `field_description` varchar(250) default NULL,
  `field_tab` int(10) NOT NULL default '0',
  `field_published` tinyint(1) default '0',
  PRIMARY KEY  (`field_id`),
  KEY `cfs_field_order` (`field_order`),
  KEY `field_module` (`field_module`),
  KEY `field_page` (`field_page`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_values`
--

CREATE TABLE `custom_fields_values` (
  `value_id` int(10) NOT NULL auto_increment,
  `value_module` varchar(30) default NULL,
  `value_object_id` int(10) default NULL,
  `value_field_id` int(10) default NULL,
  `value_charvalue` varchar(250) default NULL,
  `value_intvalue` int(10) default NULL,
  PRIMARY KEY  (`value_id`),
  KEY `value_field_id` (`value_field_id`),
  KEY `value_object_id` (`value_object_id`),
  KEY `value_charvalue` (`value_charvalue`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `custom_fields_values`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(10) unsigned NOT NULL auto_increment,
  `dept_parent` int(10) unsigned NOT NULL default '0',
  `dept_company` int(10) unsigned NOT NULL default '0',
  `dept_name` varchar(255) NOT NULL default '',
  `dept_phone` varchar(30) default NULL,
  `dept_fax` varchar(30) default NULL,
  `dept_address1` varchar(30) default NULL,
  `dept_address2` varchar(30) default NULL,
  `dept_city` varchar(30) default NULL,
  `dept_state` varchar(30) default NULL,
  `dept_zip` varchar(11) default NULL,
  `dept_url` varchar(25) default NULL,
  `dept_desc` text,
  `dept_owner` int(10) unsigned NOT NULL default '0',
  `dept_country` varchar(100) NOT NULL,
  `dept_email` varchar(255) NOT NULL default '',
  `dept_type` int(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`dept_id`),
  KEY `dept_parent` (`dept_parent`),
  KEY `dept_name` (`dept_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Department heirarchy under a company';

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(10) NOT NULL auto_increment,
  `event_title` varchar(255) NOT NULL default '',
  `event_start_date` datetime default NULL,
  `event_end_date` datetime default NULL,
  `event_parent` int(10) unsigned NOT NULL default '0',
  `event_description` text,
  `event_url` varchar(255) default NULL,
  `event_times_recuring` int(10) unsigned NOT NULL default '0',
  `event_recurs` int(10) unsigned NOT NULL default '0',
  `event_remind` int(10) unsigned NOT NULL default '0',
  `event_icon` varchar(20) default 'obj/event',
  `event_owner` int(10) default '0',
  `event_project` int(10) default '0',
  `event_task` int(10) default NULL,
  `event_private` tinyint(3) default '0',
  `event_type` tinyint(3) default '0',
  `event_cwd` tinyint(3) default '0',
  `event_notify` tinyint(3) NOT NULL default '0',
  `event_location` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`event_id`),
  KEY `id_esd` (`event_start_date`),
  KEY `id_eed` (`event_end_date`),
  KEY `id_evp` (`event_parent`),
  KEY `idx_ev1` (`event_owner`),
  KEY `idx_ev2` (`event_project`),
  KEY `event_recurs` (`event_recurs`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `event_contacts`
--

CREATE TABLE `event_contacts` (
  `event_id` int(10) NOT NULL default '0',
  `contact_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`event_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `event_contacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `event_queue`
--

CREATE TABLE `event_queue` (
  `queue_id` int(10) NOT NULL auto_increment,
  `queue_start` int(10) NOT NULL default '0',
  `queue_type` varchar(40) NOT NULL default '',
  `queue_repeat_interval` int(10) NOT NULL default '0',
  `queue_repeat_count` int(10) NOT NULL default '0',
  `queue_data` longblob NOT NULL,
  `queue_callback` varchar(127) NOT NULL default '',
  `queue_owner` int(10) NOT NULL default '0',
  `queue_origin_id` int(10) NOT NULL default '0',
  `queue_module` varchar(40) NOT NULL default '',
  `queue_module_type` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`queue_id`),
  KEY `queue_start` (`queue_start`),
  KEY `queue_module` (`queue_module`),
  KEY `queue_type` (`queue_type`),
  KEY `queue_origin_id` (`queue_origin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `event_queue`
--


-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `file_id` int(10) NOT NULL auto_increment,
  `file_real_filename` varchar(255) NOT NULL default '',
  `file_project` int(10) NOT NULL default '0',
  `file_task` int(10) NOT NULL default '0',
  `file_name` varchar(255) NOT NULL default '',
  `file_parent` int(10) default '0',
  `file_description` text,
  `file_type` varchar(100) default NULL,
  `file_owner` int(10) default '0',
  `file_date` datetime default NULL,
  `file_size` int(10) default '0',
  `file_version` float NOT NULL default '0',
  `file_icon` varchar(20) default 'obj/',
  `file_category` int(10) default '0',
  `file_checkout` varchar(16) NOT NULL,
  `file_co_reason` text,
  `file_version_id` int(10) NOT NULL default '0',
  `file_folder` int(10) NOT NULL default '0',
  `file_helpdesk_item` int(10) NOT NULL default '0',
  PRIMARY KEY  (`file_id`),
  KEY `idx_file_task` (`file_task`),
  KEY `idx_file_project` (`file_project`),
  KEY `idx_file_parent` (`file_parent`),
  KEY `idx_file_vid` (`file_version_id`),
  KEY `file_name` (`file_name`),
  KEY `file_folder` (`file_folder`),
  KEY `file_category` (`file_category`),
  KEY `file_checkout` (`file_checkout`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `files_index`
--

CREATE TABLE `files_index` (
  `file_id` int(10) NOT NULL default '0',
  `word` varchar(50) NOT NULL default '',
  `word_placement` int(10) NOT NULL default '0',
  PRIMARY KEY  (`file_id`,`word`,`word_placement`),
  KEY `idx_fwrd` (`word`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `file_folders`
--

CREATE TABLE `file_folders` (
  `file_folder_id` int(10) NOT NULL auto_increment,
  `file_folder_parent` int(10) NOT NULL default '0',
  `file_folder_name` varchar(255) NOT NULL default '',
  `file_folder_description` text,
  PRIMARY KEY  (`file_folder_id`),
  KEY `file_folder_parent` (`file_folder_parent`),
  KEY `file_folder_name` (`file_folder_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

CREATE TABLE `forums` (
  `forum_id` int(10) NOT NULL auto_increment,
  `forum_project` int(10) NOT NULL default '0',
  `forum_status` tinyint(4) NOT NULL default '-1',
  `forum_owner` int(10) NOT NULL default '0',
  `forum_name` varchar(50) NOT NULL default '',
  `forum_create_date` datetime default '0000-00-00 00:00:00',
  `forum_last_date` datetime default '0000-00-00 00:00:00',
  `forum_last_id` int(10) unsigned NOT NULL default '0',
  `forum_message_count` int(10) NOT NULL default '0',
  `forum_description` varchar(255) default NULL,
  `forum_moderated` int(10) NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `idx_fproject` (`forum_project`),
  KEY `idx_fowner` (`forum_owner`),
  KEY `forum_status` (`forum_status`),
  KEY `forum_name` (`forum_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_messages`
--

CREATE TABLE `forum_messages` (
  `message_id` int(10) NOT NULL auto_increment,
  `message_forum` int(10) NOT NULL default '0',
  `message_parent` int(10) NOT NULL default '0',
  `message_author` int(10) NOT NULL default '0',
  `message_editor` int(10) NOT NULL default '0',
  `message_title` varchar(255) NOT NULL default '',
  `message_date` datetime default '0000-00-00 00:00:00',
  `message_body` text,
  `message_published` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`message_id`),
  KEY `idx_mparent` (`message_parent`),
  KEY `idx_mdate` (`message_date`),
  KEY `idx_mforum` (`message_forum`),
  KEY `message_author` (`message_author`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_visits`
--

CREATE TABLE `forum_visits` (
  `visit_user` int(10) NOT NULL default '0',
  `visit_forum` int(10) NOT NULL default '0',
  `visit_message` int(10) NOT NULL default '0',
  `visit_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY ( `visit_user` , `visit_forum`, `visit_message` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_watch`
--

CREATE TABLE `forum_watch` (
  `watch_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `watch_user` int(10) unsigned NOT NULL default '0',
  `watch_forum` int(10) unsigned default NULL,
  `watch_topic` int(10) unsigned default NULL,
  PRIMARY KEY (`watch_id`),
  KEY `idx_fw1` (`watch_user`,`watch_forum`),
  KEY `idx_fw2` (`watch_user`,`watch_topic`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Links users to the forums/messages they are watching';

-- --------------------------------------------------------

--
-- Table structure for table `gacl_acl`
--

CREATE TABLE `gacl_acl` (
  `id` int(10) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default 'system',
  `allow` int(10) NOT NULL default '0',
  `enabled` int(10) NOT NULL default '0',
  `return_value` longtext,
  `note` longtext,
  `updated_date` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `gacl_enabled_acl` (`enabled`),
  KEY `gacl_section_value_acl` (`section_value`),
  KEY `gacl_updated_date_acl` (`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_acl`
--

INSERT INTO `gacl_acl` (`id`, `section_value`, `allow`, `enabled`, `return_value`, `note`, `updated_date`) VALUES (10, 'user', 1, 1, '', '', 1195510857);
INSERT INTO `gacl_acl` (`id`, `section_value`, `allow`, `enabled`, `return_value`, `note`, `updated_date`) VALUES (11, 'user', 1, 1, '', '', 1195510857);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_acl_sections`
--

CREATE TABLE `gacl_acl_sections` (
  `id` int(10) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(10) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_acl_sections` (`value`),
  KEY `gacl_hidden_acl_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_acl_sections`
--

INSERT INTO `gacl_acl_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (1, 'system', 1, 'System', 0);
INSERT INTO `gacl_acl_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (2, 'user', 2, 'User', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_acl_seq`
--

CREATE TABLE `gacl_acl_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_acl_seq`
--

INSERT INTO `gacl_acl_seq` (`id`) VALUES (29);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aco`
--

CREATE TABLE `gacl_aco` (
  `id` int(10) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_aco` (`section_value`,`value`),
  KEY `gacl_hidden_aco` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aco`
--

INSERT INTO `gacl_aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'system', 'login', 1, 'Login', 0);
INSERT INTO `gacl_aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (11, 'application', 'access', 1, 'Access', 0);
INSERT INTO `gacl_aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (12, 'application', 'view', 2, 'View', 0);
INSERT INTO `gacl_aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (13, 'application', 'add', 3, 'Add', 0);
INSERT INTO `gacl_aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (14, 'application', 'edit', 4, 'Edit', 0);
INSERT INTO `gacl_aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (15, 'application', 'delete', 5, 'Delete', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aco_map`
--

CREATE TABLE `gacl_aco_map` (
  `acl_id` int(10) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aco_map`
--

INSERT INTO `gacl_aco_map` (`acl_id`, `section_value`, `value`) VALUES (10, 'system', 'login');
INSERT INTO `gacl_aco_map` (`acl_id`, `section_value`, `value`) VALUES (11, 'application', 'access');
INSERT INTO `gacl_aco_map` (`acl_id`, `section_value`, `value`) VALUES (11, 'application', 'add');
INSERT INTO `gacl_aco_map` (`acl_id`, `section_value`, `value`) VALUES (11, 'application', 'delete');
INSERT INTO `gacl_aco_map` (`acl_id`, `section_value`, `value`) VALUES (11, 'application', 'edit');
INSERT INTO `gacl_aco_map` (`acl_id`, `section_value`, `value`) VALUES (11, 'application', 'view');

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aco_sections`
--

CREATE TABLE `gacl_aco_sections` (
  `id` int(10) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(10) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_aco_sections` (`value`),
  KEY `gacl_hidden_aco_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aco_sections`
--

INSERT INTO `gacl_aco_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'system', 1, 'System', 0);
INSERT INTO `gacl_aco_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (11, 'application', 2, 'Application', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aco_sections_seq`
--

CREATE TABLE `gacl_aco_sections_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aco_sections_seq`
--

INSERT INTO `gacl_aco_sections_seq` (`id`) VALUES (11);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aco_seq`
--

CREATE TABLE `gacl_aco_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aco_seq`
--

INSERT INTO `gacl_aco_seq` (`id`) VALUES (15);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro`
--

CREATE TABLE `gacl_aro` (
  `id` int(10) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_aro` (`section_value`,`value`),
  KEY `gacl_hidden_aro` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro`
--

INSERT INTO `gacl_aro` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'user', '1', 1, 'admin', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro_groups`
--

CREATE TABLE `gacl_aro_groups` (
  `id` int(10) NOT NULL default '0',
  `parent_id` int(10) NOT NULL default '0',
  `lft` int(10) NOT NULL default '0',
  `rgt` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`,`value`),
  KEY `gacl_parent_id_aro_groups` (`parent_id`),
  KEY `gacl_value_aro_groups` (`value`),
  KEY `gacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro_groups`
--

INSERT INTO `gacl_aro_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (10, 0, 1, 12, 'Roles', 'role');
INSERT INTO `gacl_aro_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (11, 10, 2, 3, 'Administrator', 'admin');
INSERT INTO `gacl_aro_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (12, 10, 4, 5, 'Anonymous', 'anon');
INSERT INTO `gacl_aro_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (13, 10, 6, 7, 'Guest', 'guest');
INSERT INTO `gacl_aro_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (14, 10, 8, 9, 'Project worker', 'normal');
INSERT INTO `gacl_aro_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (16, 10, 10, 11, 'Empty Role', 'empty');

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro_groups_id_seq`
--

CREATE TABLE `gacl_aro_groups_id_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro_groups_id_seq`
--

INSERT INTO `gacl_aro_groups_id_seq` (`id`) VALUES (16);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro_groups_map`
--

CREATE TABLE `gacl_aro_groups_map` (
  `acl_id` int(10) NOT NULL default '0',
  `group_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro_groups_map`
--

INSERT INTO `gacl_aro_groups_map` (`acl_id`, `group_id`) VALUES (10, 10);
INSERT INTO `gacl_aro_groups_map` (`acl_id`, `group_id`) VALUES (11, 11);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro_map`
--

CREATE TABLE `gacl_aro_map` (
  `acl_id` int(10) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro_map`
--

INSERT INTO `gacl_aro_map` (`acl_id`, `section_value`, `value`) VALUES (23, 'user', '2');

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro_sections`
--

CREATE TABLE `gacl_aro_sections` (
  `id` int(10) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(10) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_aro_sections` (`value`),
  KEY `gacl_hidden_aro_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro_sections`
--

INSERT INTO `gacl_aro_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'user', 1, 'Users', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro_sections_seq`
--

CREATE TABLE `gacl_aro_sections_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro_sections_seq`
--

INSERT INTO `gacl_aro_sections_seq` (`id`) VALUES (10);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_aro_seq`
--

CREATE TABLE `gacl_aro_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_aro_seq`
--

INSERT INTO `gacl_aro_seq` (`id`) VALUES (10);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo`
--

CREATE TABLE `gacl_axo` (
  `id` int(10) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_axo` (`section_value`,`value`),
  KEY `gacl_hidden_axo` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo`
--

INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'sys', 'acl', 1, 'ACL Administration', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (11, 'app', 'admin', 1, 'User Administration', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (12, 'app', 'calendar', 2, 'Calendar', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (13, 'app', 'events', 2, 'Events', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (14, 'app', 'companies', 3, 'Companies', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (15, 'app', 'contacts', 4, 'Contacts', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (16, 'app', 'departments', 5, 'Departments', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (17, 'app', 'files', 6, 'Files', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (18, 'app', 'forums', 7, 'Forums', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (19, 'app', 'help', 8, 'Help', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (20, 'app', 'projects', 9, 'Projects', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (21, 'app', 'system', 10, 'System Administration', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (22, 'app', 'tasks', 11, 'Tasks', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (23, 'app', 'task_log', 11, 'Task Logs', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (25, 'app', 'public', 13, 'Public', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (26, 'app', 'roles', 14, 'Roles Administration', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (27, 'app', 'users', 15, 'User Table', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (28, 'app', 'smartsearch', 1, 'SmartSearch', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (55, 'app', 'links', 1, 'Links', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (51, 'app', 'projectdesigner', 1, 'ProjectDesigner', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (31, 'departments', '1', 0, '1', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (36, 'app', 'history', 1, 'History', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (39, 'app', 'reports', 1, 'Reports', 0);
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (54, 'app', 'resources', 1, 'Resources', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo_groups`
--

CREATE TABLE `gacl_axo_groups` (
  `id` int(10) NOT NULL default '0',
  `parent_id` int(10) NOT NULL default '0',
  `lft` int(10) NOT NULL default '0',
  `rgt` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`,`value`),
  KEY `gacl_parent_id_axo_groups` (`parent_id`),
  KEY `gacl_value_axo_groups` (`value`),
  KEY `gacl_lft_rgt_axo_groups` (`lft`,`rgt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo_groups`
--

INSERT INTO `gacl_axo_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (10, 0, 1, 8, 'Modules', 'mod');
INSERT INTO `gacl_axo_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (11, 10, 2, 3, 'All Modules', 'all');
INSERT INTO `gacl_axo_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (12, 10, 4, 5, 'Admin Modules', 'admin');
INSERT INTO `gacl_axo_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES (13, 10, 6, 7, 'Non-Admin Modules', 'non_admin');

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo_groups_id_seq`
--

CREATE TABLE `gacl_axo_groups_id_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo_groups_id_seq`
--

INSERT INTO `gacl_axo_groups_id_seq` (`id`) VALUES (13);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo_groups_map`
--

CREATE TABLE `gacl_axo_groups_map` (
  `acl_id` int(10) NOT NULL default '0',
  `group_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo_groups_map`
--

INSERT INTO `gacl_axo_groups_map` (`acl_id`, `group_id`) VALUES (11, 11);
INSERT INTO `gacl_axo_groups_map` (`acl_id`, `group_id`) VALUES (13, 13);
INSERT INTO `gacl_axo_groups_map` (`acl_id`, `group_id`) VALUES (14, 13);
INSERT INTO `gacl_axo_groups_map` (`acl_id`, `group_id`) VALUES (15, 13);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo_map`
--

CREATE TABLE `gacl_axo_map` (
  `acl_id` int(10) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo_map`
--

INSERT INTO `gacl_axo_map` (`acl_id`, `section_value`, `value`) VALUES (12, 'sys', 'acl');

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo_sections`
--

CREATE TABLE `gacl_axo_sections` (
  `id` int(10) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(10) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_axo_sections` (`value`),
  KEY `gacl_hidden_axo_sections` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo_sections`
--

INSERT INTO `gacl_axo_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'sys', 1, 'System', 0);
INSERT INTO `gacl_axo_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (11, 'app', 2, 'Application', 0);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo_sections_seq`
--

CREATE TABLE `gacl_axo_sections_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo_sections_seq`
--

INSERT INTO `gacl_axo_sections_seq` (`id`) VALUES (11);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_axo_seq`
--

CREATE TABLE `gacl_axo_seq` (
  `id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_axo_seq`
--

INSERT INTO `gacl_axo_seq` (`id`) VALUES (55);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_groups_aro_map`
--

CREATE TABLE `gacl_groups_aro_map` (
  `group_id` int(10) NOT NULL default '0',
  `aro_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`aro_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_groups_aro_map`
--

INSERT INTO `gacl_groups_aro_map` (`group_id`, `aro_id`) VALUES (11, 10);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_groups_axo_map`
--

CREATE TABLE `gacl_groups_axo_map` (
  `group_id` int(10) NOT NULL default '0',
  `axo_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`axo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_groups_axo_map`
--

INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 11);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 12);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 13);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 14);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 15);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 16);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 17);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 18);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 19);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 20);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 21);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 22);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 23);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 24);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 25);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 26);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 27);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 28);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 36);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 39);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 51);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 54);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 55);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (12, 11);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (12, 21);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (12, 26);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (12, 27);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 12);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 13);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 14);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 15);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 16);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 17);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 18);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 19);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 20);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 22);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 23);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 24);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 25);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 28);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 36);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 39);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 51);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 54);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 55);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_permissions`
--

CREATE TABLE `gacl_permissions` (
  `user_id` int(10) NOT NULL default '0',
  `user_name` varchar(255) NOT NULL default '',
  `module` varchar(64) NOT NULL default '',
  `item_id` int(10) NOT NULL default '0',
  `action` varchar(32) NOT NULL default '',
  `access` int(1) NOT NULL default '0',
  `acl_id` int(10) NOT NULL default '0',
  KEY `user_id` (`user_id`),
  KEY `module` (`module`),
  KEY `item_id` (`item_id`),
  KEY `acl_id` (`acl_id`),
  KEY `user_name` (`user_name`),
  KEY `action` (`action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_permissions`
--

INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'history', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'resources', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'access', 1, 12);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'history', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'resources', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'add', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'history', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'resources', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'delete', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'history', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'resources', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'edit', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'history', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'resources', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'view', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'access', 1, 11);

-- --------------------------------------------------------

--
-- Table structure for table `gacl_phpgacl`
--

CREATE TABLE `gacl_phpgacl` (
  `name` varchar(230) NOT NULL default '',
  `value` varchar(230) NOT NULL default '',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gacl_phpgacl`
--

INSERT INTO `gacl_phpgacl` (`name`, `value`) VALUES ('version', '3.3.7');
INSERT INTO `gacl_phpgacl` (`name`, `value`) VALUES ('schema_version', '0.95');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `history_id` int(10) unsigned NOT NULL auto_increment,
  `history_date` datetime NOT NULL default '0000-00-00 00:00:00',
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `link_id` int(10) NOT NULL auto_increment,
  `link_url` varchar(255) NOT NULL default '',
  `link_project` int(10) NOT NULL default '0',
  `link_task` int(10) NOT NULL default '0',
  `link_name` varchar(255) NOT NULL default '',
  `link_parent` int(10) default '0',
  `link_description` text,
  `link_owner` int(10) default '0',
  `link_date` datetime default NULL,
  `link_icon` varchar(20) default 'obj/',
  `link_category` int(10) NOT NULL default '0',
  PRIMARY KEY  (`link_id`),
  KEY `idx_link_task` (`link_task`),
  KEY `idx_link_project` (`link_project`),
  KEY `idx_link_parent` (`link_parent`),
  KEY `link_name` (`link_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `mod_id` int(10) NOT NULL auto_increment,
  `mod_name` varchar(64) NOT NULL default '',
  `mod_directory` varchar(64) NOT NULL default '',
  `mod_version` varchar(10) NOT NULL default '',
  `mod_setup_class` varchar(64) NOT NULL default '',
  `mod_type` varchar(64) NOT NULL default '',
  `mod_active` int(1) unsigned NOT NULL default '0',
  `mod_ui_name` varchar(20) NOT NULL default '',
  `mod_ui_icon` varchar(64) NOT NULL default '',
  `mod_ui_order` tinyint(3) NOT NULL default '0',
  `mod_ui_active` int(1) unsigned NOT NULL default '0',
  `mod_description` varchar(255) NOT NULL default '',
  `permissions_item_table` varchar(100) default NULL,
  `permissions_item_field` varchar(100) default NULL,
  `permissions_item_label` varchar(100) default NULL,
  `mod_main_class` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`mod_id`,`mod_directory`),
  KEY `mod_ui_order` (`mod_ui_order`),
  KEY `mod_active` (`mod_active`),
  KEY `mod_directory` (`mod_directory`),
  KEY `permissions_item_table` (`permissions_item_table`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (1, 'Companies', 'companies', '1.0.0', '', 'core', 1, 'Companies', 'handshake.png', 1, 1, '', 'companies', 'company_id', 'company_name', 'CCompany');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (2, 'Projects', 'projects', '1.0.0', '', 'core', 1, 'Projects', 'applet3-48.png', 2, 1, '', 'projects', 'project_id', 'project_name', 'CProject');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (3, 'Tasks', 'tasks', '1.0.0', '', 'core', 1, 'Tasks', 'applet-48.png', 3, 1, '', 'tasks', 'task_id', 'task_name', 'CTask');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (4, 'Calendar', 'calendar', '1.0.0', '', 'core', 1, 'Calendar', 'myevo-appointments.png', 4, 1, '', 'events', 'event_id', 'event_title', 'CEvent');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (5, 'Files', 'files', '1.0.0', '', 'core', 1, 'Files', 'folder5.png', 5, 1, '', 'files', 'file_id', 'file_name', 'CFile');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (6, 'Contacts', 'contacts', '1.0.0', '', 'core', 1, 'Contacts', 'monkeychat-48.png', 6, 1, '', 'contacts', 'contact_id', 'contact_first_name', 'CContact');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (7, 'Forums', 'forums', '1.0.0', '', 'core', 1, 'Forums', 'support.png', 7, 1, '', 'forums', 'forum_id', 'forum_name', 'CForum');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (9, 'User Administration', 'admin', '1.0.0', '', 'core', 1, 'User Admin', 'helix-setup-users.png', 18, 1, '', 'users', 'user_id', 'user_username', '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (10, 'System Administration', 'system', '1.0.0', '', 'core', 1, 'System Admin', '48_my_computer.png', 19, 1, '', '', '', '', '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (12, 'Help', 'help', '1.0.0', '', 'core', 1, 'Help', 'w2p.gif', 21, 0, '', '', '', '', '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (13, 'Public', 'public', '1.0.0', '', 'core', 1, 'Public', 'users.gif', 21, 0, '', '', '', '', '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (14, 'SmartSearch', 'smartsearch', '2.0', 'SSearchNS', 'user', 1, 'SmartSearch', 'kfind.png', 9, 0, 'A module to search keywords and find the needle in the haystack', NULL, NULL, NULL, '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (37, 'ProjectDesigner', 'projectdesigner', '1.0', 'projectDesigner', 'user', 1, 'ProjectDesigner', 'projectdesigner.jpg', 25, 0, 'A module to design projects', NULL, NULL, NULL, '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (17, 'Departments', 'departments', '1.0', '', 'core', 1, 'Departments', '', 0, 0, '', 'departments', 'dept_id', 'dept_name', 'CDepartment');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (22, 'History', 'history', '0.32', 'CSetupHistory', 'user', 1, 'History', '', 12, 0, 'A module for tracking changes', NULL, NULL, NULL, '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (25, 'Reports', 'reports', '0.1', 'CSetupReports', 'user', 1, 'Reports', 'printer.png', 24, 0, 'A module for reports', NULL, NULL, NULL, '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (41, 'Links', 'links', '1.0', 'CSetupLinks', 'user', 1, 'Links', 'communicate.gif', 27, 1, 'Links related to tasks', 'links', 'link_id', 'link_name', 'CLink');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (40, 'Resources', 'resources', '1.0.1', 'SResource', 'user', 1, 'Resources', 'helpdesk.png', 26, 1, '', 'resources', 'resource_id', 'resource_name', 'CResource');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(10) NOT NULL auto_increment,
  `project_company` int(10) NOT NULL default '0',
  `project_department` int(10) NOT NULL default '0',
  `project_name` varchar(255) default NULL,
  `project_short_name` varchar(10) default NULL,
  `project_owner` int(10) default '0',
  `project_url` varchar(255) default NULL,
  `project_demo_url` varchar(255) default NULL,
  `project_start_date` datetime default NULL,
  `project_end_date` datetime default NULL,
  `project_actual_end_date` datetime default NULL,
  `project_status` int(10) default '0',
  `project_percent_complete` tinyint(4) default '0',
  `project_color_identifier` varchar(6) default 'eeeeee',
  `project_description` text,
  `project_target_budget` decimal(10,2) default '0.00',
  `project_actual_budget` decimal(10,2) default '0.00',
  `project_creator` int(10) default '0',
  `project_private` tinyint(3) unsigned default '0',
  `project_departments` varchar(100) default NULL,
  `project_contacts` varchar(100) default NULL,
  `project_priority` tinyint(4) default '0',
  `project_type` smallint(6) NOT NULL default '0',
  `project_keydate` datetime default NULL,
  `project_keydate_pos` tinyint(1) default '0',
  `project_keytask` int(10) default '0',
  `project_active` int(1) NOT NULL default '1',
  `project_original_parent` int(10) unsigned NOT NULL default '0',
  `project_parent` int(10) unsigned NOT NULL default '0',
  `project_empireint_special` int(1) NOT NULL default '0',
  `project_updator` int(10) NOT NULL default '0',
  `project_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `project_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `project_status_comment` varchar(255) NOT NULL default '',
  `project_subpriority` tinyint(4) default '0',
  `project_end_date_adjusted` datetime NOT NULL default '0000-00-00 00:00:00',
  `project_end_date_adjusted_user` int(10) NOT NULL default '0',
  `project_location` varchar(128) NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_contacts`
--

CREATE TABLE `project_contacts` (
  `project_id` int(10) NOT NULL default '0',
  `contact_id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `project_id` , `contact_id` ),
  KEY `project_id` (`project_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_departments`
--

CREATE TABLE `project_departments` (
  `project_id` int(10) NOT NULL default '0',
  `department_id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `project_id` , `department_id` ),
  KEY `project_id` (`project_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_designer_options`
--

CREATE TABLE `project_designer_options` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(10) NOT NULL auto_increment,
  `resource_name` varchar(255) NOT NULL default '',
  `resource_key` varchar(64) NOT NULL default '',
  `resource_type` int(10) NOT NULL default '0',
  `resource_note` text NOT NULL,
  `resource_max_allocation` int(10) NOT NULL default '100',
  PRIMARY KEY  (`resource_id`),
  KEY `resource_name` (`resource_name`),
  KEY `resource_type` (`resource_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resource_tasks`
--

CREATE TABLE `resource_tasks` (
  `resource_id` int(10) NOT NULL default '0',
  `task_id` int(10) NOT NULL default '0',
  `percent_allocated` int(10) NOT NULL default '100',
  PRIMARY KEY ( `resource_id` , `task_id` ),
  KEY `resource_id` (`resource_id`),
  KEY `task_id` (`task_id`,`resource_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `resource_tasks`
--


-- --------------------------------------------------------

--
-- Table structure for table `resource_types`
--

CREATE TABLE `resource_types` (
  `resource_type_id` int(10) NOT NULL auto_increment,
  `resource_type_name` varchar(255) NOT NULL default '',
  `resource_type_note` text,
  PRIMARY KEY  (`resource_type_id`),
  KEY `resource_type_name` (`resource_type_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `resource_types`
--

INSERT INTO `resource_types` (`resource_type_id`, `resource_type_name`, `resource_type_note`) VALUES (1, 'Equipment', NULL);
INSERT INTO `resource_types` (`resource_type_id`, `resource_type_name`, `resource_type_note`) VALUES (2, 'Tool', NULL);
INSERT INTO `resource_types` (`resource_type_id`, `resource_type_name`, `resource_type_note`) VALUES (3, 'Venue', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL default '',
  `session_user` int(10) NOT NULL default '0',
  `session_data` longblob,
  `session_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `session_created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`session_id`),
  KEY `session_updated` (`session_updated`),
  KEY `session_created` (`session_created`),
  KEY `session_user` (`session_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `syskeys`
--

CREATE TABLE `syskeys` (
  `syskey_id` int(10) unsigned NOT NULL auto_increment,
  `syskey_name` varchar(48) NOT NULL default '',
  `syskey_label` varchar(255) NOT NULL default '',
  `syskey_type` int(1) unsigned NOT NULL default '0',
  `syskey_sep1` char(2) default '\n',
  `syskey_sep2` char(2) NOT NULL default '|',
  PRIMARY KEY  (`syskey_id`),
  UNIQUE KEY `syskey_name` (`syskey_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `syskeys`
--

INSERT INTO `syskeys` (`syskey_id`, `syskey_name`, `syskey_label`, `syskey_type`, `syskey_sep1`, `syskey_sep2`) VALUES (1, 'SelectList', 'Enter values for list', 0, '\n', '|');
INSERT INTO `syskeys` (`syskey_id`, `syskey_name`, `syskey_label`, `syskey_type`, `syskey_sep1`, `syskey_sep2`) VALUES (2, 'CustomField', 'Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Field''s name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>', 0, '\n', '|');
INSERT INTO `syskeys` (`syskey_id`, `syskey_name`, `syskey_label`, `syskey_type`, `syskey_sep1`, `syskey_sep2`) VALUES (3, 'ColorSelection', 'Hex color values for type=>color association.', 0, '', '|');

-- --------------------------------------------------------

--
-- Table structure for table `sysvals`
--

CREATE TABLE `sysvals` (
  `sysval_id` int(10) unsigned NOT NULL auto_increment,
  `sysval_key_id` int(10) unsigned NOT NULL default '0',
  `sysval_title` varchar(48) NOT NULL default '',
  `sysval_value` text NOT NULL,
  `sysval_value_id` varchar(128) default '0',
  PRIMARY KEY  (`sysval_id`),
  KEY `sysval_value_id` (`sysval_value_id`),
  KEY `sysval_title` (`sysval_title`),
  KEY `sysval_key_id` (`sysval_key_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sysvals`
--

INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (43, 1, 'CompanyType', 'Supplier', '3');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (44, 1, 'CompanyType', 'Consultant', '4');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (513, 1, 'GlobalCountries', 'Saint Kitts & Nevis Anguilla', 'KN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (42, 1, 'CompanyType', 'Vendor', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (70, 1, 'ProjectRequiredFields', '<3', 'f.project_name.value.length');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (512, 1, 'GlobalCountries', 'Comoros', 'KM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (511, 1, 'GlobalCountries', 'Kiribati', 'KI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (94, 1, 'TaskPriority', 'high', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (96, 1, 'TaskStatus', 'Inactive', '-1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (71, 1, 'ProjectRequiredFields', '<3', 'f.project_color_identifier.value.length');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (113, 1, 'UserType', 'Default User', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (114, 1, 'UserType', 'Administrator', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (115, 1, 'UserType', 'CEO', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (40, 1, 'CompanyType', 'Not Applicable', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (84, 1, 'TaskDurationType', 'hours', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (47, 1, 'EventType', 'General', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (48, 1, 'EventType', 'Appointment', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (95, 1, 'TaskStatus', 'Active', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (97, 1, 'TaskType', 'Unknown', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (98, 1, 'TaskType', 'Administrative', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (81, 1, 'ProjectType', 'Unknown', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (82, 1, 'ProjectType', 'Administrative', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (63, 3, 'ProjectColors', 'FFE0AE', 'Web');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (62, 3, 'ProjectColors', 'FFAEAE', 'System Administration');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (53, 1, 'FileType', 'Unknown', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (92, 1, 'TaskPriority', 'low', '-1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (93, 1, 'TaskPriority', 'normal', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (86, 1, 'TaskLogReference', 'Not Defined', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (64, 1, 'ProjectPriority', 'low', '-1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (67, 1, 'ProjectPriorityColor', '#E5F7FF', '-1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (87, 1, 'TaskLogReference', 'Email', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (510, 1, 'GlobalCountries', 'Cambodia, Kingdom of', 'KH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (509, 1, 'GlobalCountries', 'Kyrgyz Republic (Kyrgyzstan)', 'KG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (41, 1, 'CompanyType', 'Client', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (39, 1, 'GlobalYesNo', 'Yes', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (38, 1, 'GlobalYesNo', 'No', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (45, 1, 'CompanyType', 'Government', '5');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (46, 1, 'CompanyType', 'Internal', '6');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (49, 1, 'EventType', 'Meeting', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (50, 1, 'EventType', 'All Day Event', '3');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (51, 1, 'EventType', 'Anniversary', '4');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (52, 1, 'EventType', 'Reminder', '5');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (54, 1, 'FileType', 'Document', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (55, 1, 'FileType', 'Application', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (61, 3, 'ProjectColors', 'FFFCAE', 'HelpDesk');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (60, 3, 'ProjectColors', 'AEFFB2', 'Engineering');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (65, 1, 'ProjectPriority', 'normal', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (66, 1, 'ProjectPriority', 'high', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (68, 1, 'ProjectPriorityColor', '', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (69, 1, 'ProjectPriorityColor', '#FFDCB3', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (72, 1, 'ProjectRequiredFields', '<1', 'f.project_company.options[f.project_company.selectedIndex].value');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (508, 1, 'GlobalCountries', 'Kenya', 'KE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (507, 1, 'GlobalCountries', 'Japan', 'JP');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (506, 1, 'GlobalCountries', 'Jordan', 'JO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (505, 1, 'GlobalCountries', 'Jamaica', 'JM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (83, 1, 'ProjectType', 'Operative', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (85, 1, 'TaskDurationType', 'days', '24');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (88, 1, 'TaskLogReference', 'Helpdesk', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (89, 1, 'TaskLogReference', 'Phone Call', '3');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (90, 1, 'TaskLogReference', 'Fax', '4');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (99, 1, 'TaskType', 'Operative', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (504, 1, 'GlobalCountries', 'Italy', 'IT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (155, 1, 'TaskLogReferenceImage', 'a', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (154, 1, 'TaskLogReferenceImage', 'i', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (116, 1, 'UserType', 'Director', '3');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (117, 1, 'UserType', 'Branch Manager', '4');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (118, 1, 'UserType', 'Manager', '5');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (119, 1, 'UserType', 'Supervisor', '6');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (120, 1, 'UserType', 'Employee', '7');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (503, 1, 'GlobalCountries', 'Iceland', 'IS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (502, 1, 'GlobalCountries', 'Iran', 'IR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (501, 1, 'GlobalCountries', 'Iraq', 'IQ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (500, 1, 'GlobalCountries', 'British Indian Ocean Territory', 'IO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (499, 1, 'GlobalCountries', 'India', 'IN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (498, 1, 'GlobalCountries', 'Israel', 'IL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (497, 1, 'GlobalCountries', 'Ireland', 'IE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (496, 1, 'GlobalCountries', 'Indonesia', 'ID');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (495, 1, 'GlobalCountries', 'Hungary', 'HU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (153, 1, 'ProjectStatus', 'Template', '6');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (152, 1, 'ProjectStatus', 'Complete', '5');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (151, 1, 'ProjectStatus', 'On Hold', '4');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (150, 1, 'ProjectStatus', 'In Progress', '3');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (149, 1, 'ProjectStatus', 'In Planning', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (148, 1, 'ProjectStatus', 'Proposed', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (147, 1, 'ProjectStatus', 'Not Defined', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (494, 1, 'GlobalCountries', 'Haiti', 'HT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (493, 1, 'GlobalCountries', 'Croatia', 'HR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (492, 1, 'GlobalCountries', 'Honduras', 'HN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (491, 1, 'GlobalCountries', 'Heard and McDonald Islands', 'HM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (490, 1, 'GlobalCountries', 'Hong Kong', 'HK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (489, 1, 'GlobalCountries', 'Guyana', 'GY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (488, 1, 'GlobalCountries', 'Guinea Bissau', 'GW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (487, 1, 'GlobalCountries', 'Guam (USA)', 'GU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (486, 1, 'GlobalCountries', 'Guatemala', 'GT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (485, 1, 'GlobalCountries', 'S. Georgia & S. Sandwich Isls.', 'GS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (484, 1, 'GlobalCountries', 'Greece', 'GR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (483, 1, 'GlobalCountries', 'Equatorial Guinea', 'GQ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (482, 1, 'GlobalCountries', 'Guadeloupe (French)', 'GP');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (481, 1, 'GlobalCountries', 'Guinea', 'GN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (480, 1, 'GlobalCountries', 'Gambia', 'GM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (479, 1, 'GlobalCountries', 'Greenland', 'GL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (478, 1, 'GlobalCountries', 'Gibraltar', 'GI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (477, 1, 'GlobalCountries', 'Ghana', 'GH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (476, 1, 'GlobalCountries', 'French Guyana', 'GF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (475, 1, 'GlobalCountries', 'Georgia', 'GE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (474, 1, 'GlobalCountries', 'Grenada', 'GD');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (473, 1, 'GlobalCountries', 'Great Britain', 'GB');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (472, 1, 'GlobalCountries', 'Gabon', 'GA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (471, 1, 'GlobalCountries', 'France', 'FR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (470, 1, 'GlobalCountries', 'Faroe Islands', 'FO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (469, 1, 'GlobalCountries', 'Micronesia', 'FM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (468, 1, 'GlobalCountries', 'Falkland Islands', 'FK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (467, 1, 'GlobalCountries', 'Fiji', 'FJ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (466, 1, 'GlobalCountries', 'Finland', 'FI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (465, 1, 'GlobalCountries', 'Ethiopia', 'ET');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (464, 1, 'GlobalCountries', 'Spain', 'ES');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (463, 1, 'GlobalCountries', 'Eritrea', 'ER');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (462, 1, 'GlobalCountries', 'Western Sahara', 'EH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (461, 1, 'GlobalCountries', 'Egypt', 'EG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (460, 1, 'GlobalCountries', 'Estonia', 'EE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (459, 1, 'GlobalCountries', 'Ecuador', 'EC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (458, 1, 'GlobalCountries', 'Algeria', 'DZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (457, 1, 'GlobalCountries', 'Dominican Republic', 'DO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (456, 1, 'GlobalCountries', 'Dominica', 'DM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (455, 1, 'GlobalCountries', 'Denmark', 'DK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (454, 1, 'GlobalCountries', 'Djibouti', 'DJ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (453, 1, 'GlobalCountries', 'Germany', 'DE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (452, 1, 'GlobalCountries', 'Czech Republic', 'CZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (451, 1, 'GlobalCountries', 'Cyprus', 'CY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (450, 1, 'GlobalCountries', 'Christmas Island', 'CX');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (449, 1, 'GlobalCountries', 'Cape Verde', 'CV');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (448, 1, 'GlobalCountries', 'Cuba', 'CU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (447, 1, 'GlobalCountries', 'Former Czechoslovakia', 'CS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (446, 1, 'GlobalCountries', 'Costa Rica', 'CR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (445, 1, 'GlobalCountries', 'Colombia', 'CO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (444, 1, 'GlobalCountries', 'China', 'CN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (443, 1, 'GlobalCountries', 'Cameroon', 'CM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (442, 1, 'GlobalCountries', 'Chile', 'CL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (441, 1, 'GlobalCountries', 'Cook Islands', 'CK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (440, 1, 'GlobalCountries', 'Ivory Coast (Cote D''Ivoire)', 'CI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (439, 1, 'GlobalCountries', 'Switzerland', 'CH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (438, 1, 'GlobalCountries', 'Congo', 'CG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (437, 1, 'GlobalCountries', 'Congo, The Democratic Republic of the', 'CD');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (436, 1, 'GlobalCountries', 'Central African Republic', 'CF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (435, 1, 'GlobalCountries', 'Cocos (Keeling) Islands', 'CC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (434, 1, 'GlobalCountries', 'Canada', 'CA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (433, 1, 'GlobalCountries', 'Belize', 'BZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (432, 1, 'GlobalCountries', 'Belarus', 'BY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (431, 1, 'GlobalCountries', 'Botswana', 'BW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (430, 1, 'GlobalCountries', 'Bouvet Island', 'BV');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (429, 1, 'GlobalCountries', 'Bhutan', 'BT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (428, 1, 'GlobalCountries', 'Bahamas', 'BS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (427, 1, 'GlobalCountries', 'Brazil', 'BR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (426, 1, 'GlobalCountries', 'Bolivia', 'BO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (425, 1, 'GlobalCountries', 'Brunei Darussalam', 'BN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (424, 1, 'GlobalCountries', 'Bermuda', 'BM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (423, 1, 'GlobalCountries', 'Benin', 'BJ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (422, 1, 'GlobalCountries', 'Burundi', 'BI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (421, 1, 'GlobalCountries', 'Bahrain', 'BH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (420, 1, 'GlobalCountries', 'Bulgaria', 'BG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (419, 1, 'GlobalCountries', 'Burkina Faso', 'BF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (418, 1, 'GlobalCountries', 'Belgium', 'BE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (417, 1, 'GlobalCountries', 'Bangladesh', 'BD');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (416, 1, 'GlobalCountries', 'Barbados', 'BB');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (415, 1, 'GlobalCountries', 'Bosnia-Herzegovina', 'BA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (414, 1, 'GlobalCountries', 'Azerbaidjan', 'AZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (413, 1, 'GlobalCountries', 'Aruba', 'AW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (412, 1, 'GlobalCountries', 'Australia', 'AU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (411, 1, 'GlobalCountries', 'Austria', 'AT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (410, 1, 'GlobalCountries', 'American Samoa', 'AS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (409, 1, 'GlobalCountries', 'Argentina', 'AR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (408, 1, 'GlobalCountries', 'Antarctica', 'AQ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (407, 1, 'GlobalCountries', 'Angola', 'AO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (406, 1, 'GlobalCountries', 'Netherlands Antilles', 'AN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (405, 1, 'GlobalCountries', 'Armenia', 'AM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (404, 1, 'GlobalCountries', 'Albania', 'AL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (403, 1, 'GlobalCountries', 'Anguilla', 'AI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (402, 1, 'GlobalCountries', 'Antigua and Barbuda', 'AG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (401, 1, 'GlobalCountries', 'Afghanistan, Islamic State of', 'AF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (400, 1, 'GlobalCountries', 'United Arab Emirates', 'AE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (399, 1, 'GlobalCountries', 'Andorra, Principality of', 'AD');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (514, 1, 'GlobalCountries', 'North Korea', 'KP');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (515, 1, 'GlobalCountries', 'South Korea', 'KR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (516, 1, 'GlobalCountries', 'Kuwait', 'KW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (517, 1, 'GlobalCountries', 'Cayman Islands', 'KY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (518, 1, 'GlobalCountries', 'Kazakhstan', 'KZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (519, 1, 'GlobalCountries', 'Laos', 'LA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (520, 1, 'GlobalCountries', 'Lebanon', 'LB');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (521, 1, 'GlobalCountries', 'Saint Lucia', 'LC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (522, 1, 'GlobalCountries', 'Liechtenstein', 'LI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (523, 1, 'GlobalCountries', 'Sri Lanka', 'LK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (524, 1, 'GlobalCountries', 'Liberia', 'LR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (525, 1, 'GlobalCountries', 'Lesotho', 'LS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (526, 1, 'GlobalCountries', 'Lithuania', 'LT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (527, 1, 'GlobalCountries', 'Luxembourg', 'LU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (528, 1, 'GlobalCountries', 'Latvia', 'LV');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (529, 1, 'GlobalCountries', 'Libya', 'LY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (530, 1, 'GlobalCountries', 'Morocco', 'MA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (531, 1, 'GlobalCountries', 'Monaco', 'MC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (532, 1, 'GlobalCountries', 'Moldavia', 'MD');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (533, 1, 'GlobalCountries', 'Madagascar', 'MG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (534, 1, 'GlobalCountries', 'Marshall Islands', 'MH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (535, 1, 'GlobalCountries', 'Macedonia', 'MK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (536, 1, 'GlobalCountries', 'Mali', 'ML');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (537, 1, 'GlobalCountries', 'Myanmar', 'MM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (538, 1, 'GlobalCountries', 'Mongolia', 'MN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (539, 1, 'GlobalCountries', 'Macau', 'MO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (540, 1, 'GlobalCountries', 'Northern Mariana Islands', 'MP');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (541, 1, 'GlobalCountries', 'Martinique (French)', 'MQ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (542, 1, 'GlobalCountries', 'Mauritania', 'MR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (543, 1, 'GlobalCountries', 'Montserrat', 'MS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (544, 1, 'GlobalCountries', 'Malta', 'MT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (545, 1, 'GlobalCountries', 'Mauritius', 'MU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (546, 1, 'GlobalCountries', 'Maldives', 'MV');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (547, 1, 'GlobalCountries', 'Malawi', 'MW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (548, 1, 'GlobalCountries', 'Mexico', 'MX');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (549, 1, 'GlobalCountries', 'Malaysia', 'MY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (550, 1, 'GlobalCountries', 'Mozambique', 'MZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (551, 1, 'GlobalCountries', 'Namibia', 'NA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (552, 1, 'GlobalCountries', 'New Caledonia (French)', 'NC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (553, 1, 'GlobalCountries', 'Niger', 'NE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (554, 1, 'GlobalCountries', 'Norfolk Island', 'NF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (555, 1, 'GlobalCountries', 'Nigeria', 'NG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (556, 1, 'GlobalCountries', 'Nicaragua', 'NI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (557, 1, 'GlobalCountries', 'Netherlands', 'NL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (558, 1, 'GlobalCountries', 'Norway', 'NO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (559, 1, 'GlobalCountries', 'Nepal', 'NP');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (560, 1, 'GlobalCountries', 'Nauru', 'NR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (561, 1, 'GlobalCountries', 'Neutral Zone', 'NT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (562, 1, 'GlobalCountries', 'Niue', 'NU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (563, 1, 'GlobalCountries', 'New Zealand', 'NZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (564, 1, 'GlobalCountries', 'Oman', 'OM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (565, 1, 'GlobalCountries', 'Panama', 'PA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (566, 1, 'GlobalCountries', 'Peru', 'PE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (567, 1, 'GlobalCountries', 'Polynesia (French)', 'PF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (568, 1, 'GlobalCountries', 'Papua New Guinea', 'PG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (569, 1, 'GlobalCountries', 'Philippines', 'PH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (570, 1, 'GlobalCountries', 'Pakistan', 'PK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (571, 1, 'GlobalCountries', 'Poland', 'PL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (572, 1, 'GlobalCountries', 'Saint Pierre and Miquelon', 'PM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (573, 1, 'GlobalCountries', 'Pitcairn Island', 'PN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (574, 1, 'GlobalCountries', 'Puerto Rico', 'PR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (575, 1, 'GlobalCountries', 'Portugal', 'PT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (576, 1, 'GlobalCountries', 'Palau', 'PW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (577, 1, 'GlobalCountries', 'Paraguay', 'PY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (578, 1, 'GlobalCountries', 'Qatar', 'QA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (579, 1, 'GlobalCountries', 'Reunion (French)', 'RE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (580, 1, 'GlobalCountries', 'Romania', 'RO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (581, 1, 'GlobalCountries', 'Russian Federation', 'RU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (582, 1, 'GlobalCountries', 'Rwanda', 'RW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (583, 1, 'GlobalCountries', 'Saudi Arabia', 'SA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (584, 1, 'GlobalCountries', 'Solomon Islands', 'SB');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (585, 1, 'GlobalCountries', 'Seychelles', 'SC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (586, 1, 'GlobalCountries', 'Sudan', 'SD');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (587, 1, 'GlobalCountries', 'Sweden', 'SE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (588, 1, 'GlobalCountries', 'Singapore', 'SG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (589, 1, 'GlobalCountries', 'Saint Helena', 'SH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (590, 1, 'GlobalCountries', 'Slovenia', 'SI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (591, 1, 'GlobalCountries', 'Svalbard and Jan Mayen Islands', 'SJ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (592, 1, 'GlobalCountries', 'Slovak Republic', 'SK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (593, 1, 'GlobalCountries', 'Sierra Leone', 'SL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (594, 1, 'GlobalCountries', 'San Marino', 'SM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (595, 1, 'GlobalCountries', 'Senegal', 'SN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (596, 1, 'GlobalCountries', 'Somalia', 'SO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (597, 1, 'GlobalCountries', 'Suriname', 'SR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (598, 1, 'GlobalCountries', 'Saint Tome (Sao Tome) and Principe', 'ST');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (599, 1, 'GlobalCountries', 'Former USSR', 'SU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (600, 1, 'GlobalCountries', 'El Salvador', 'SV');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (601, 1, 'GlobalCountries', 'Syria', 'SY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (602, 1, 'GlobalCountries', 'Swaziland', 'SZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (603, 1, 'GlobalCountries', 'Turks and Caicos Islands', 'TC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (604, 1, 'GlobalCountries', 'Chad', 'TD');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (605, 1, 'GlobalCountries', 'French Southern Territories', 'TF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (606, 1, 'GlobalCountries', 'Togo', 'TG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (607, 1, 'GlobalCountries', 'Thailand', 'TH');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (608, 1, 'GlobalCountries', 'Tadjikistan', 'TJ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (609, 1, 'GlobalCountries', 'Tokelau', 'TK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (610, 1, 'GlobalCountries', 'Turkmenistan', 'TM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (611, 1, 'GlobalCountries', 'Tunisia', 'TN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (612, 1, 'GlobalCountries', 'Tonga', 'TO');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (613, 1, 'GlobalCountries', 'East Timor', 'TL');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (614, 1, 'GlobalCountries', 'Turkey', 'TR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (615, 1, 'GlobalCountries', 'Trinidad and Tobago', 'TT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (616, 1, 'GlobalCountries', 'Tuvalu', 'TV');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (617, 1, 'GlobalCountries', 'Taiwan', 'TW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (618, 1, 'GlobalCountries', 'Tanzania', 'TZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (619, 1, 'GlobalCountries', 'Ukraine', 'UA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (620, 1, 'GlobalCountries', 'Uganda', 'UG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (621, 1, 'GlobalCountries', 'United Kingdom', 'UK');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (622, 1, 'GlobalCountries', 'USA Minor Outlying Islands', 'UM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (623, 1, 'GlobalCountries', 'United States', 'US');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (624, 1, 'GlobalCountries', 'Uruguay', 'UY');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (625, 1, 'GlobalCountries', 'Uzbekistan', 'UZ');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (626, 1, 'GlobalCountries', 'Holy See (Vatican City State)', 'VA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (627, 1, 'GlobalCountries', 'Saint Vincent & Grenadines', 'VC');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (628, 1, 'GlobalCountries', 'Venezuela', 'VE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (629, 1, 'GlobalCountries', 'Virgin Islands (British)', 'VG');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (630, 1, 'GlobalCountries', 'Virgin Islands (USA)', 'VI');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (631, 1, 'GlobalCountries', 'Vietnam', 'VN');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (632, 1, 'GlobalCountries', 'Vanuatu', 'VU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (633, 1, 'GlobalCountries', 'Wallis and Futuna Islands', 'WF');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (634, 1, 'GlobalCountries', 'Samoa', 'WS');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (635, 1, 'GlobalCountries', 'Yemen', 'YE');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (636, 1, 'GlobalCountries', 'Mayotte', 'YT');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (637, 1, 'GlobalCountries', 'Yugoslavia', 'YU');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (638, 1, 'GlobalCountries', 'South Africa', 'ZA');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (639, 1, 'GlobalCountries', 'Zambia', 'ZM');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (640, 1, 'GlobalCountries', 'Zaire', 'ZR');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (641, 1, 'GlobalCountries', 'Zimbabwe', 'ZW');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (657, 1, 'LinkType', 'Application', '2');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (656, 1, 'LinkType', 'Document', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (655, 1, 'LinkType', 'Unknown', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (658, 1, 'DepartmentType', 'Not Defined', '0');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (659, 1, 'DepartmentType', 'Profit', '1');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (660, 1, 'DepartmentType', 'Cost', '2');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(10) NOT NULL auto_increment,
  `task_name` varchar(255) default NULL,
  `task_parent` int(10) default '0',
  `task_milestone` tinyint(1) default '0',
  `task_project` int(10) NOT NULL default '0',
  `task_owner` int(10) NOT NULL default '0',
  `task_start_date` datetime default NULL,
  `task_duration` float unsigned default '0',
  `task_duration_type` int(10) NOT NULL default '1',
  `task_hours_worked` float unsigned default '0',
  `task_end_date` datetime default NULL,
  `task_status` int(10) default '0',
  `task_priority` tinyint(4) default '0',
  `task_percent_complete` tinyint(4) default '0',
  `task_description` text,
  `task_target_budget` decimal(10,2) default '0.00',
  `task_related_url` varchar(255) default NULL,
  `task_creator` int(10) NOT NULL default '0',
  `task_order` int(10) NOT NULL default '0',
  `task_client_publish` tinyint(1) NOT NULL default '0',
  `task_dynamic` tinyint(1) NOT NULL default '0',
  `task_access` int(10) NOT NULL default '0',
  `task_notify` int(10) NOT NULL default '0',
  `task_departments` varchar(100) default NULL,
  `task_contacts` varchar(100) default NULL,
  `task_custom` longtext,
  `task_type` smallint(6) NOT NULL default '0',
  `task_updator` int(10) NOT NULL default '0',
  `task_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `task_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `task_dep_reset_dates` tinyint(1) default '0',
  PRIMARY KEY  (`task_id`),
  KEY `idx_task_parent` (`task_parent`),
  KEY `idx_task_project` (`task_project`),
  KEY `idx_task_owner` (`task_owner`),
  KEY `idx_task_order` (`task_order`),
  KEY `idx_task1` (`task_start_date`),
  KEY `idx_task2` (`task_end_date`),
  KEY `task_priority` (`task_priority`),
  KEY `task_name` (`task_name`),
  KEY `task_status` (`task_status`),
  KEY `task_percent_complete` (`task_percent_complete`),
  KEY `task_creator` (`task_creator`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tasks_critical`
--

CREATE TABLE `tasks_critical` (
  `task_project` int(10) default NULL,
  `critical_task` int(10) default NULL,
  `project_actual_end_date` datetime default NULL,
  PRIMARY KEY (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tasks_problems`
--

CREATE TABLE `tasks_problems` (
  `task_project` int(10) default NULL,
  `task_log_problem` tinyint(1) default NULL,
  PRIMARY KEY (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tasks_sum`
--

CREATE TABLE `tasks_sum` (
  `task_project` int(10) default NULL,
  `total_tasks` int(6) default NULL,
  `project_percent_complete` float default NULL,
  `project_duration` float default NULL,
  PRIMARY KEY (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tasks_summy`
--

CREATE TABLE `tasks_summy` (
  `task_project` int(10) default NULL,
  `my_tasks` varchar(10) default NULL,
  PRIMARY KEY (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tasks_total`
--

CREATE TABLE `tasks_total` (
  `task_project` int(10) default NULL,
  `total_tasks` int(10) default NULL,
  PRIMARY KEY (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tasks_users`
--

CREATE TABLE `tasks_users` (
  `task_project` int(10) default NULL,
  `user_id` int(10) default NULL,
  PRIMARY KEY (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_contacts`
--

CREATE TABLE `task_contacts` (
  `task_id` int(10) NOT NULL default '0',
  `contact_id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `task_id` , `contact_id` ),
  KEY `task_id` (`task_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_departments`
--

CREATE TABLE `task_departments` (
  `task_id` int(10) NOT NULL default '0',
  `department_id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `task_id` , `department_id` ),
  KEY `task_id` (`task_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_dependencies`
--

CREATE TABLE `task_dependencies` (
  `dependencies_task_id` int(10) NOT NULL default '0',
  `dependencies_req_task_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`dependencies_task_id`,`dependencies_req_task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_log`
--

CREATE TABLE `task_log` (
  `task_log_id` int(10) NOT NULL auto_increment,
  `task_log_task` int(10) NOT NULL default '0',
  `task_log_help_desk_id` int(10) NOT NULL default '0',
  `task_log_name` varchar(255) default NULL,
  `task_log_description` text,
  `task_log_creator` int(10) NOT NULL default '0',
  `task_log_hours` float NOT NULL default '0',
  `task_log_date` datetime default NULL,
  `task_log_costcode` varchar(8) NOT NULL default '',
  `task_log_problem` tinyint(1) default '0',
  `task_log_reference` tinyint(4) default '0',
  `task_log_related_url` varchar(255) default NULL,
  `task_log_project` int(10) unsigned NOT NULL default '0',
  `task_log_company` int(10) unsigned NOT NULL default '0',
  `task_log_changelog` int(1) unsigned NOT NULL default '0',
  `task_log_changelog_servers` varchar(255) NOT NULL default '',
  `task_log_changelog_whom` int(10) NOT NULL default '0',
  `task_log_changelog_datetime` datetime default NULL,
  `task_log_changelog_duration` varchar(50) NOT NULL default '',
  `task_log_changelog_expected_downtime` int(1) unsigned NOT NULL default '0',
  `task_log_changelog_description` text,
  `task_log_changelog_backout_plan` text,
  PRIMARY KEY  (`task_log_id`),
  KEY `idx_log_task` (`task_log_task`),
  KEY `task_log_date` (`task_log_date`),
  KEY `task_log_creator` (`task_log_creator`),
  KEY `task_log_problem` (`task_log_problem`),
  KEY `task_log_costcode` (`task_log_costcode`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL auto_increment,
  `user_contact` int(10) NOT NULL default '0',
  `user_username` varchar(255) NOT NULL default '',
  `user_password` varchar(32) NOT NULL default '',
  `user_parent` int(10) NOT NULL default '0',
  `user_type` tinyint(3) NOT NULL default '0',
  `user_signature` text,
  `user_empireint_special` int(1) NOT NULL default '0',
  `user_department` int(10) unsigned NOT NULL default '0',
  `user_company` int(10) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  KEY `idx_uid` (`user_username`),
  KEY `idx_pwd` (`user_password`),
  KEY `user_contact` (`user_contact`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_contact`, `user_username`, `user_password`, `user_parent`, `user_type`, `user_signature`, `user_empireint_special`, `user_department`, `user_company`) VALUES (1, 1, 'admin', md5('[ADMINPASS]'), 0, 1, '', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_access_log`
--

CREATE TABLE `user_access_log` (
  `user_access_log_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `user_ip` varchar(15) NOT NULL default '',
  `date_time_in` datetime default '0000-00-00 00:00:00',
  `date_time_out` datetime default '0000-00-00 00:00:00',
  `date_time_last_action` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`user_access_log_id`),
  KEY `date_time_last_action` (`date_time_last_action`),
  KEY `date_time_in` (`date_time_in`),
  KEY `date_time_out` (`date_time_out`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_events`
--

CREATE TABLE `user_events` (
  `user_id` int(10) NOT NULL default '0',
  `event_id` int(10) NOT NULL default '0',
  PRIMARY KEY ( `user_id` , `event_id` ),
  KEY `uek2` (`event_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `pref_user` varchar(12) NOT NULL default '',
  `pref_name` varchar(72) NOT NULL default '',
  `pref_value` varchar(32) NOT NULL default '',
  PRIMARY KEY ( `pref_user` , `pref_name` ),
  KEY `pref_user_2` (`pref_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'CURRENCYFORM', 'en');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'EVENTFILTER', 'all');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'LOCALE', 'en');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'MAILALL', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'SHDATEFORMAT', '%d/%m/%Y');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'TABVIEW', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'TASKASSIGNMAX', '100');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'TASKLOGEMAIL', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'TASKLOGNOTE', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'TASKLOGSUBJ', '');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'TIMEFORMAT', '%I:%M %p');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'UISTYLE', 'web2project');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'USERFORMAT', 'user');

INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'CURRENCYFORM', 'en');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'EVENTFILTER', 'my');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'LOCALE', 'en');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'MAILALL', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'SHDATEFORMAT', '%d/%b/%Y');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TABVIEW', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TASKASSIGNMAX', '100');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TASKLOGEMAIL', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TASKLOGNOTE', '0');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TASKLOGSUBJ', '');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TIMEFORMAT', '%I:%M %p');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'UISTYLE', 'web2project');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'USERFORMAT', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `user_tasks`
--

CREATE TABLE `user_tasks` (
  `user_id` int(10) NOT NULL default '0',
  `user_type` tinyint(4) NOT NULL default '0',
  `task_id` int(10) NOT NULL default '0',
  `perc_assignment` int(10) NOT NULL default '100',
  `user_task_priority` tinyint(4) default '0',
  PRIMARY KEY  (`user_id`,`task_id`),
  KEY `index_ut_to_tasks` (`task_id`),
  KEY `perc_assignment` (`perc_assignment`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_task_pin`
--

CREATE TABLE `user_task_pin` (
  `user_id` int(10) NOT NULL default '0',
  `task_id` int(10) NOT NULL default '0',
  `task_pinned` tinyint(2) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`task_id`),
  KEY `task_id` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `w2pversion`
--

CREATE TABLE `w2pversion` (
  `code_revision` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `code_version` varchar(10) NOT NULL default '',
  `db_version` int(10) NOT NULL default '0',
  `last_db_update` date NOT NULL default '0000-00-00',
  `last_code_update` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`code_revision`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `w2pversion`
--

INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) 
	VALUES (199, '0.9.9', 1, now(), now());