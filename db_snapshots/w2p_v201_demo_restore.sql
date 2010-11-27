-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 03, 2010 at 08:30 PM
-- Server version: 5.1.37
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `w2p_demo`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `billingcode`
-- 

DROP TABLE IF EXISTS `billingcode`;
CREATE TABLE `billingcode` (
  `billingcode_id` int(10) NOT NULL AUTO_INCREMENT,
  `billingcode_name` varchar(25) NOT NULL DEFAULT '',
  `billingcode_value` float NOT NULL DEFAULT '0',
  `billingcode_desc` varchar(255) NOT NULL DEFAULT '',
  `billingcode_status` int(1) NOT NULL DEFAULT '0',
  `company_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`billingcode_id`),
  KEY `billingcode_name_2` (`billingcode_name`),
  KEY `billingcode_name` (`billingcode_name`),
  KEY `billingcode_status` (`billingcode_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `billingcode`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `companies`
-- 

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `company_id` int(10) NOT NULL AUTO_INCREMENT,
  `company_module` int(10) NOT NULL DEFAULT '0',
  `company_name` varchar(100) DEFAULT '',
  `company_phone1` varchar(30) DEFAULT '',
  `company_phone2` varchar(30) DEFAULT '',
  `company_fax` varchar(30) DEFAULT '',
  `company_address1` varchar(50) DEFAULT '',
  `company_address2` varchar(50) DEFAULT '',
  `company_city` varchar(30) DEFAULT '',
  `company_state` varchar(30) DEFAULT '',
  `company_zip` varchar(11) DEFAULT '',
  `company_country` varchar(100) NOT NULL DEFAULT '',
  `company_primary_url` varchar(255) DEFAULT '',
  `company_owner` int(10) NOT NULL DEFAULT '0',
  `company_description` mediumtext,
  `company_type` int(3) NOT NULL DEFAULT '0',
  `company_email` varchar(255) DEFAULT NULL,
  `company_custom` longtext,
  `company_private` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`company_id`),
  KEY `idx_cpy1` (`company_owner`),
  KEY `company_name` (`company_name`),
  KEY `company_type` (`company_type`),
  KEY `company_owner` (`company_owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `companies`
-- 

INSERT INTO `companies` (`company_id`, `company_module`, `company_name`, `company_phone1`, `company_phone2`, `company_fax`, `company_address1`, `company_address2`, `company_city`, `company_state`, `company_zip`, `company_country`, `company_primary_url`, `company_owner`, `company_description`, `company_type`, `company_email`, `company_custom`, `company_private`) VALUES (1, 0, 'Example Company #1', '703.555.1212', '202.555.1212', '', '123 Some Address Ave', 'Suite 123', 'Beverly Hills', 'CA', '90210', 'US', 'web2project.net', 1, 'This is a bit of useful information about this company.', 0, 'example@example.org', NULL, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `config`
-- 

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `config_id` int(10) NOT NULL AUTO_INCREMENT,
  `config_name` varchar(255) NOT NULL DEFAULT '',
  `config_value` varchar(255) NOT NULL DEFAULT '',
  `config_group` varchar(255) NOT NULL DEFAULT '',
  `config_type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`config_id`),
  KEY `config_name` (`config_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=73 ;

-- 
-- Dumping data for table `config`
-- 

INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (1, 'activate_external_user_creation', 'true', 'admin_users', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (2, 'admin_username', 'admin', 'admin_users', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (3, 'auth_method', 'sql', 'auth', 'select');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (4, 'cal_day_end', '17', 'calendar', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (5, 'cal_day_increment', '15', 'calendar', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (6, 'cal_day_start', '8', 'calendar', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (7, 'cal_day_view_show_minical', 'true', 'calendar', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (8, 'cal_working_days', '1,2,3,4,5', 'calendar', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (9, 'check_overallocation', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (10, 'check_task_dates', 'true', 'tasks', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (11, 'company_name', 'web2Project Demo Site', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (12, 'currency_symbol', '$', 'budgeting', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (13, 'daily_working_hours', '8.0', 'calendar', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (14, 'debug', '0', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (15, 'default_view_a', 'day_view', 'startup', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (16, 'default_view_m', 'calendar', 'startup', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (17, 'default_view_tab', '1', 'startup', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (18, 'direct_edit_assignment', 'true', 'tasks', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (19, 'display_debug', 'false', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (20, 'email_prefix', '[w2P]', 'mail', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (21, 'enable_gantt_charts', 'true', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (22, 'files_ci_preserve_attr', 'true', 'files', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (23, 'files_show_versions_edit', 'false', 'files', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (24, 'host_locale', 'en', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (25, 'host_style', 'web2project', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (26, 'index_max_file_size', '-1', 'files', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (27, 'ldap_allow_login', 'true', 'ldap', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (28, 'ldap_base_dn', 'dc=web2project,dc=net', 'ldap', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (29, 'ldap_host', 'localhost', 'ldap', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (30, 'ldap_port', '389', 'ldap', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (31, 'ldap_search_pass', 'secret', 'ldap', 'password');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (32, 'ldap_search_user', 'Manager', 'ldap', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (33, 'ldap_user_filter', '(uid=%USERNAME%)', 'ldap', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (34, 'ldap_version', '3', 'ldap', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (35, 'locale_alert', '^', 'locales', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (36, 'locale_warn', 'false', 'locales', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (37, 'log_changes', 'true', 'admin_system', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (38, 'mail_auth', 'true', 'mail', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (39, 'mail_debug', 'false', 'mail', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (40, 'mail_defer', 'false', 'mail', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (41, 'mail_host', 'mail.yourdomain.com', 'mail', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (42, 'mail_pass', 'smtppasswd', 'mail', 'password');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (43, 'mail_port', '25', 'mail', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (44, 'mail_secure', '', 'mail', 'select');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (45, 'mail_timeout', '30', 'mail', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (46, 'mail_transport', 'smtp', 'mail', 'select');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (47, 'mail_user', 'smtpuser', 'mail', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (48, 'page_title', 'web2Project', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (49, 'parser_application/msword', '/usr/bin/strings', 'files', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (50, 'parser_application/pdf', '/usr/bin/pdftotext', 'files', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (51, 'parser_default', '/usr/bin/strings', 'files', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (52, 'parser_text/html', '/usr/bin/strings', 'files', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (53, 'password_min_len', '4', 'admin_users', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (54, 'postnuke_allow_login', 'false', 'auth', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (55, 'projectdesigner_view_project', 'false', 'projects', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (56, 'reset_memory_limit', '64M', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (57, 'restrict_color_selection', 'false', 'projects', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (58, 'restrict_task_time_editing', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (59, 'session_gc_scan_queue', 'false', 'session', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (60, 'session_handling', 'app', 'session', 'select');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (61, 'session_idle_time', '1d', 'session', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (62, 'session_max_lifetime', '7d', 'session', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (63, 'show_all_task_assignees', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (64, 'site_domain', 'web2project.net', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (65, 'task_reminder_control', 'false', 'tasks', 'checkbox');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (66, 'task_reminder_days_before', '1', 'tasks', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (67, 'task_reminder_repeat', '100', 'tasks', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (68, 'template_projects_status_id', '6', 'projects', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (69, 'username_min_len', '4', 'admin_users', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (70, 'system_timezone', 'America/Chicago', 'admin_system', 'select');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (71, 'system_update_day', '0', 'admin_system', 'text');
INSERT INTO `config` (`config_id`, `config_name`, `config_value`, `config_group`, `config_type`) VALUES (72, 'system_update_hour', '3', 'admin_system', 'text');

-- --------------------------------------------------------

-- 
-- Table structure for table `config_list`
-- 

DROP TABLE IF EXISTS `config_list`;
CREATE TABLE `config_list` (
  `config_list_id` int(10) NOT NULL AUTO_INCREMENT,
  `config_id` int(10) NOT NULL DEFAULT '0',
  `config_list_name` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`config_list_id`),
  KEY `config_id` (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- 
-- Dumping data for table `config_list`
-- 

INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (1, 3, 'sql');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (2, 3, 'ldap');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (3, 3, 'pn');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (4, 60, 'app');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (5, 60, 'php');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (6, 46, 'php');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (7, 46, 'smtp');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (8, 44, '');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (9, 44, 'tls');
INSERT INTO `config_list` (`config_list_id`, `config_id`, `config_list_name`) VALUES (10, 44, 'ssl');

-- --------------------------------------------------------

-- 
-- Table structure for table `contacts`
-- 

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `contact_id` int(10) NOT NULL AUTO_INCREMENT,
  `contact_first_name` varchar(30) DEFAULT NULL,
  `contact_last_name` varchar(30) DEFAULT NULL,
  `contact_order_by` varchar(30) NOT NULL DEFAULT '',
  `contact_title` varchar(50) DEFAULT NULL,
  `contact_birthday` date DEFAULT NULL,
  `contact_job` varchar(255) DEFAULT NULL,
  `contact_company` int(10) NOT NULL DEFAULT '0',
  `contact_department` int(10) NOT NULL DEFAULT '0',
  `contact_type` varchar(20) DEFAULT NULL,
  `contact_address1` varchar(60) DEFAULT NULL,
  `contact_address2` varchar(60) DEFAULT NULL,
  `contact_city` varchar(30) DEFAULT NULL,
  `contact_state` varchar(30) DEFAULT NULL,
  `contact_zip` varchar(11) DEFAULT NULL,
  `contact_country` varchar(30) DEFAULT NULL,
  `contact_notes` mediumtext,
  `contact_project` int(10) NOT NULL DEFAULT '0',
  `contact_icon` varchar(20) DEFAULT 'obj/contact',
  `contact_owner` int(10) unsigned DEFAULT '0',
  `contact_private` tinyint(3) unsigned DEFAULT '0',
  `contact_updatekey` varchar(32) DEFAULT NULL,
  `contact_lastupdate` datetime DEFAULT NULL,
  `contact_updateasked` datetime DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `contact_first_name` (`contact_first_name`),
  KEY `contact_last_name` (`contact_last_name`),
  KEY `contact_company` (`contact_company`),
  KEY `contact_department` (`contact_department`),
  KEY `contact_project` (`contact_project`),
  KEY `contact_owner` (`contact_owner`),
  KEY `contact_updatekey` (`contact_updatekey`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `contacts`
-- 

INSERT INTO `contacts` (`contact_id`, `contact_first_name`, `contact_last_name`, `contact_order_by`, `contact_title`, `contact_birthday`, `contact_job`, `contact_company`, `contact_department`, `contact_type`, `contact_address1`, `contact_address2`, `contact_city`, `contact_state`, `contact_zip`, `contact_country`, `contact_notes`, `contact_project`, `contact_icon`, `contact_owner`, `contact_private`, `contact_updatekey`, `contact_lastupdate`, `contact_updateasked`) VALUES (1, 'Admin', 'Person', 'Admin Person', 'CEO', '0000-00-00', 'Guy in Charge', 0, 0, '', '123 Fake Street', 'Suite 204', 'Alexandria', 'VA', '22042', 'US', 'This is some interesting information about this person.', 0, 'obj/contact', 1, 0, '', NULL, NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `contacts_methods`
-- 

DROP TABLE IF EXISTS `contacts_methods`;
CREATE TABLE `contacts_methods` (
  `method_id` int(10) NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) NOT NULL,
  `method_name` varchar(20) NOT NULL,
  `method_value` varchar(255) NOT NULL,
  PRIMARY KEY (`method_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- 
-- Dumping data for table `contacts_methods`
-- 

INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (1, 1, 'email_primary', 'admin@example.org');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (4, 1, 'phone_primary', '703.555.1212');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (5, 1, 'phone_alt', '202.555.1212');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (7, 1, 'im_jabber', 'jabber_id');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (8, 1, 'im_icq', 'icq_id');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (9, 1, 'im_msn', 'msn_id');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (10, 1, 'im_yahoo', 'yahoo_id');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (11, 1, 'im_skype', 'skype_id');
INSERT INTO `contacts_methods` (`method_id`, `contact_id`, `method_name`, `method_value`) VALUES (12, 1, 'im_google', 'google_id');

-- --------------------------------------------------------

-- 
-- Table structure for table `custom_fields_lists`
-- 

DROP TABLE IF EXISTS `custom_fields_lists`;
CREATE TABLE `custom_fields_lists` (
  `field_id` int(10) DEFAULT NULL,
  `list_option_id` int(10) DEFAULT NULL,
  `list_value` varchar(250) DEFAULT NULL,
  KEY `list_value` (`list_value`),
  KEY `field_id` (`field_id`),
  KEY `list_option_id` (`list_option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `custom_fields_lists`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `custom_fields_struct`
-- 

DROP TABLE IF EXISTS `custom_fields_struct`;
CREATE TABLE `custom_fields_struct` (
  `field_id` int(10) NOT NULL AUTO_INCREMENT,
  `field_module` varchar(30) DEFAULT NULL,
  `field_page` varchar(30) DEFAULT NULL,
  `field_htmltype` varchar(20) DEFAULT NULL,
  `field_datatype` varchar(20) DEFAULT NULL,
  `field_order` int(10) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `field_extratags` varchar(250) DEFAULT NULL,
  `field_description` varchar(250) DEFAULT NULL,
  `field_tab` int(10) NOT NULL DEFAULT '0',
  `field_published` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`field_id`),
  KEY `cfs_field_order` (`field_order`),
  KEY `field_module` (`field_module`),
  KEY `field_page` (`field_page`),
  KEY `field_order` (`field_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `custom_fields_struct`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `custom_fields_values`
-- 

DROP TABLE IF EXISTS `custom_fields_values`;
CREATE TABLE `custom_fields_values` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `value_module` varchar(30) DEFAULT NULL,
  `value_object_id` int(10) DEFAULT NULL,
  `value_field_id` int(10) DEFAULT NULL,
  `value_charvalue` varchar(250) DEFAULT NULL,
  `value_intvalue` int(10) DEFAULT NULL,
  PRIMARY KEY (`value_id`),
  KEY `value_charvalue` (`value_charvalue`),
  KEY `value_module` (`value_module`),
  KEY `value_object_id` (`value_object_id`),
  KEY `value_field_id` (`value_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `custom_fields_values`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `departments`
-- 

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `dept_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dept_parent` int(10) unsigned NOT NULL DEFAULT '0',
  `dept_company` int(10) unsigned NOT NULL DEFAULT '0',
  `dept_name` varchar(255) NOT NULL DEFAULT '',
  `dept_phone` varchar(30) DEFAULT NULL,
  `dept_fax` varchar(30) DEFAULT NULL,
  `dept_address1` varchar(30) DEFAULT NULL,
  `dept_address2` varchar(30) DEFAULT NULL,
  `dept_city` varchar(30) DEFAULT NULL,
  `dept_state` varchar(30) DEFAULT NULL,
  `dept_zip` varchar(11) DEFAULT NULL,
  `dept_url` varchar(255) DEFAULT NULL,
  `dept_desc` mediumtext,
  `dept_owner` int(10) unsigned NOT NULL DEFAULT '0',
  `dept_country` varchar(100) NOT NULL DEFAULT '',
  `dept_email` varchar(255) NOT NULL DEFAULT '',
  `dept_type` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`dept_id`),
  KEY `dept_parent` (`dept_parent`),
  KEY `dept_company` (`dept_company`),
  KEY `dept_name` (`dept_name`),
  KEY `dept_owner` (`dept_owner`),
  KEY `dept_type` (`dept_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Department heirarchy under a company' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `departments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `events`
-- 

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `event_id` int(10) NOT NULL AUTO_INCREMENT,
  `event_title` varchar(255) NOT NULL DEFAULT '',
  `event_start_date` datetime DEFAULT NULL,
  `event_end_date` datetime DEFAULT NULL,
  `event_parent` int(10) unsigned NOT NULL DEFAULT '0',
  `event_description` mediumtext,
  `event_url` varchar(255) DEFAULT NULL,
  `event_times_recuring` int(10) unsigned NOT NULL DEFAULT '0',
  `event_recurs` int(10) unsigned NOT NULL DEFAULT '0',
  `event_remind` int(10) unsigned NOT NULL DEFAULT '0',
  `event_icon` varchar(20) DEFAULT 'obj/event',
  `event_owner` int(10) DEFAULT '0',
  `event_project` int(10) DEFAULT '0',
  `event_task` int(10) DEFAULT NULL,
  `event_private` tinyint(3) DEFAULT '0',
  `event_type` tinyint(3) DEFAULT '0',
  `event_cwd` tinyint(3) DEFAULT '0',
  `event_notify` tinyint(3) NOT NULL DEFAULT '0',
  `event_location` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`event_id`),
  KEY `id_esd` (`event_start_date`),
  KEY `id_eed` (`event_end_date`),
  KEY `id_evp` (`event_parent`),
  KEY `idx_ev1` (`event_owner`),
  KEY `idx_ev2` (`event_project`),
  KEY `event_recurs` (`event_recurs`),
  KEY `event_start_date` (`event_start_date`),
  KEY `event_end_date` (`event_end_date`),
  KEY `event_parent` (`event_parent`),
  KEY `event_owner` (`event_owner`),
  KEY `event_project` (`event_project`),
  KEY `event_type` (`event_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `events`
-- 

INSERT INTO `events` (`event_id`, `event_title`, `event_start_date`, `event_end_date`, `event_parent`, `event_description`, `event_url`, `event_times_recuring`, `event_recurs`, `event_remind`, `event_icon`, `event_owner`, `event_project`, `event_task`, `event_private`, `event_type`, `event_cwd`, `event_notify`, `event_location`) VALUES (1, 'My Event', '2010-08-03 19:24:20', '2010-08-04 03:24:20', 0, 'This is a great event that we''ll all attend.', NULL, 0, 0, 0, 'obj/event', 1, 0, NULL, 0, 0, 0, 0, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `event_contacts`
-- 

DROP TABLE IF EXISTS `event_contacts`;
CREATE TABLE `event_contacts` (
  `event_id` int(10) NOT NULL DEFAULT '0',
  `contact_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `event_contacts`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `event_queue`
-- 

DROP TABLE IF EXISTS `event_queue`;
CREATE TABLE `event_queue` (
  `queue_id` int(10) NOT NULL AUTO_INCREMENT,
  `queue_start` int(10) NOT NULL DEFAULT '0',
  `queue_type` varchar(40) NOT NULL DEFAULT '',
  `queue_repeat_interval` int(10) NOT NULL DEFAULT '0',
  `queue_repeat_count` int(10) NOT NULL DEFAULT '0',
  `queue_data` longblob NOT NULL,
  `queue_callback` varchar(127) NOT NULL DEFAULT '',
  `queue_owner` int(10) NOT NULL DEFAULT '0',
  `queue_origin_id` int(10) NOT NULL DEFAULT '0',
  `queue_module` varchar(40) NOT NULL DEFAULT '',
  `queue_module_type` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`queue_id`),
  KEY `queue_start` (`queue_start`),
  KEY `queue_type` (`queue_type`),
  KEY `queue_origin_id` (`queue_origin_id`),
  KEY `queue_module` (`queue_module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `event_queue`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `files`
-- 

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `file_id` int(10) NOT NULL AUTO_INCREMENT,
  `file_real_filename` varchar(255) NOT NULL DEFAULT '',
  `file_project` int(10) NOT NULL DEFAULT '0',
  `file_task` int(10) NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `file_parent` int(10) DEFAULT '0',
  `file_description` mediumtext,
  `file_type` varchar(100) DEFAULT NULL,
  `file_owner` int(10) DEFAULT '0',
  `file_date` datetime DEFAULT NULL,
  `file_size` int(10) DEFAULT '0',
  `file_version` float NOT NULL DEFAULT '0',
  `file_icon` varchar(20) DEFAULT 'obj/',
  `file_category` int(10) DEFAULT '0',
  `file_checkout` varchar(16) NOT NULL DEFAULT '',
  `file_co_reason` mediumtext,
  `file_version_id` int(10) NOT NULL DEFAULT '0',
  `file_folder` int(10) NOT NULL DEFAULT '0',
  `file_helpdesk_item` int(10) NOT NULL DEFAULT '0',
  `file_indexed` tinyint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`),
  KEY `idx_file_task` (`file_task`),
  KEY `idx_file_project` (`file_project`),
  KEY `idx_file_parent` (`file_parent`),
  KEY `idx_file_vid` (`file_version_id`),
  KEY `file_checkout` (`file_checkout`),
  KEY `file_project` (`file_project`),
  KEY `file_task` (`file_task`),
  KEY `file_name` (`file_name`),
  KEY `file_parent` (`file_parent`),
  KEY `file_type` (`file_type`),
  KEY `file_owner` (`file_owner`),
  KEY `file_category` (`file_category`),
  KEY `file_folder` (`file_folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `files`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `files_index`
-- 

DROP TABLE IF EXISTS `files_index`;
CREATE TABLE `files_index` (
  `file_id` int(10) NOT NULL DEFAULT '0',
  `word` varchar(50) NOT NULL DEFAULT '',
  `word_placement` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`,`word`,`word_placement`),
  KEY `word` (`word`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `files_index`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `file_folders`
-- 

DROP TABLE IF EXISTS `file_folders`;
CREATE TABLE `file_folders` (
  `file_folder_id` int(10) NOT NULL AUTO_INCREMENT,
  `file_folder_parent` int(10) NOT NULL DEFAULT '0',
  `file_folder_name` varchar(255) NOT NULL DEFAULT '',
  `file_folder_description` mediumtext,
  PRIMARY KEY (`file_folder_id`),
  KEY `file_folder_parent` (`file_folder_parent`),
  KEY `file_folder_name` (`file_folder_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `file_folders`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `forums`
-- 

DROP TABLE IF EXISTS `forums`;
CREATE TABLE `forums` (
  `forum_id` int(10) NOT NULL AUTO_INCREMENT,
  `forum_project` int(10) NOT NULL DEFAULT '0',
  `forum_status` tinyint(4) NOT NULL DEFAULT '-1',
  `forum_owner` int(10) NOT NULL DEFAULT '0',
  `forum_name` varchar(50) NOT NULL DEFAULT '',
  `forum_create_date` datetime DEFAULT '0000-00-00 00:00:00',
  `forum_last_date` datetime DEFAULT '0000-00-00 00:00:00',
  `forum_last_id` int(10) unsigned NOT NULL DEFAULT '0',
  `forum_message_count` int(10) NOT NULL DEFAULT '0',
  `forum_description` varchar(255) DEFAULT NULL,
  `forum_moderated` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`forum_id`),
  KEY `forum_project` (`forum_project`),
  KEY `forum_status` (`forum_status`),
  KEY `forum_owner` (`forum_owner`),
  KEY `forum_name` (`forum_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `forums`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `forum_messages`
-- 

DROP TABLE IF EXISTS `forum_messages`;
CREATE TABLE `forum_messages` (
  `message_id` int(10) NOT NULL AUTO_INCREMENT,
  `message_forum` int(10) NOT NULL DEFAULT '0',
  `message_parent` int(10) NOT NULL DEFAULT '0',
  `message_author` int(10) NOT NULL DEFAULT '0',
  `message_editor` int(10) NOT NULL DEFAULT '0',
  `message_title` varchar(255) NOT NULL DEFAULT '',
  `message_date` datetime DEFAULT '0000-00-00 00:00:00',
  `message_body` mediumtext,
  `message_published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`message_id`),
  KEY `message_forum` (`message_forum`),
  KEY `message_parent` (`message_parent`),
  KEY `message_author` (`message_author`),
  KEY `message_date` (`message_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `forum_messages`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `forum_visits`
-- 

DROP TABLE IF EXISTS `forum_visits`;
CREATE TABLE `forum_visits` (
  `visit_user` int(10) NOT NULL DEFAULT '0',
  `visit_forum` int(10) NOT NULL DEFAULT '0',
  `visit_message` int(10) NOT NULL DEFAULT '0',
  `visit_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`visit_user`,`visit_forum`,`visit_message`),
  KEY `visit_user` (`visit_user`),
  KEY `visit_forum` (`visit_forum`),
  KEY `visit_message` (`visit_message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `forum_visits`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `forum_watch`
-- 

DROP TABLE IF EXISTS `forum_watch`;
CREATE TABLE `forum_watch` (
  `watch_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `watch_user` int(10) unsigned NOT NULL DEFAULT '0',
  `watch_forum` int(10) unsigned DEFAULT NULL,
  `watch_topic` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`watch_id`),
  KEY `watch_user` (`watch_user`),
  KEY `watch_forum` (`watch_forum`),
  KEY `watch_topic` (`watch_topic`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Links users to the forums/messages they are watching' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `forum_watch`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_acl`
-- 

DROP TABLE IF EXISTS `gacl_acl`;
CREATE TABLE `gacl_acl` (
  `id` int(10) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT 'system',
  `allow` int(10) NOT NULL DEFAULT '0',
  `enabled` int(10) NOT NULL DEFAULT '0',
  `return_value` longtext,
  `note` longtext,
  `updated_date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `section_value` (`section_value`),
  KEY `enabled` (`enabled`),
  KEY `updated_date` (`updated_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_acl`
-- 

INSERT INTO `gacl_acl` (`id`, `section_value`, `allow`, `enabled`, `return_value`, `note`, `updated_date`) VALUES (10, 'user', 1, 1, '', '', 1195510857);
INSERT INTO `gacl_acl` (`id`, `section_value`, `allow`, `enabled`, `return_value`, `note`, `updated_date`) VALUES (11, 'user', 1, 1, '', '', 1195510857);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_acl_sections`
-- 

DROP TABLE IF EXISTS `gacl_acl_sections`;
CREATE TABLE `gacl_acl_sections` (
  `id` int(10) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(10) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_acl_sections`
-- 

INSERT INTO `gacl_acl_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (1, 'system', 1, 'System', 0);
INSERT INTO `gacl_acl_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (2, 'user', 2, 'User', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_acl_seq`
-- 

DROP TABLE IF EXISTS `gacl_acl_seq`;
CREATE TABLE `gacl_acl_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_acl_seq`
-- 

INSERT INTO `gacl_acl_seq` (`id`) VALUES (29);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aco`
-- 

DROP TABLE IF EXISTS `gacl_aco`;
CREATE TABLE `gacl_aco` (
  `id` int(10) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `hidden` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `section_value` (`section_value`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

DROP TABLE IF EXISTS `gacl_aco_map`;
CREATE TABLE `gacl_aco_map` (
  `acl_id` int(10) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

DROP TABLE IF EXISTS `gacl_aco_sections`;
CREATE TABLE `gacl_aco_sections` (
  `id` int(10) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(10) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aco_sections`
-- 

INSERT INTO `gacl_aco_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'system', 1, 'System', 0);
INSERT INTO `gacl_aco_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (11, 'application', 2, 'Application', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aco_sections_seq`
-- 

DROP TABLE IF EXISTS `gacl_aco_sections_seq`;
CREATE TABLE `gacl_aco_sections_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aco_sections_seq`
-- 

INSERT INTO `gacl_aco_sections_seq` (`id`) VALUES (11);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aco_seq`
-- 

DROP TABLE IF EXISTS `gacl_aco_seq`;
CREATE TABLE `gacl_aco_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aco_seq`
-- 

INSERT INTO `gacl_aco_seq` (`id`) VALUES (15);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aro`
-- 

DROP TABLE IF EXISTS `gacl_aro`;
CREATE TABLE `gacl_aro` (
  `id` int(10) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `hidden` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aro`
-- 

INSERT INTO `gacl_aro` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'user', '1', 1, 'admin', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aro_groups`
-- 

DROP TABLE IF EXISTS `gacl_aro_groups`;
CREATE TABLE `gacl_aro_groups` (
  `id` int(10) NOT NULL DEFAULT '0',
  `parent_id` int(10) NOT NULL DEFAULT '0',
  `lft` int(10) NOT NULL DEFAULT '0',
  `rgt` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`value`),
  KEY `parent_id` (`parent_id`),
  KEY `lft_rgt` (`lft`,`rgt`),
  KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

DROP TABLE IF EXISTS `gacl_aro_groups_id_seq`;
CREATE TABLE `gacl_aro_groups_id_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aro_groups_id_seq`
-- 

INSERT INTO `gacl_aro_groups_id_seq` (`id`) VALUES (16);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aro_groups_map`
-- 

DROP TABLE IF EXISTS `gacl_aro_groups_map`;
CREATE TABLE `gacl_aro_groups_map` (
  `acl_id` int(10) NOT NULL DEFAULT '0',
  `group_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acl_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aro_groups_map`
-- 

INSERT INTO `gacl_aro_groups_map` (`acl_id`, `group_id`) VALUES (10, 10);
INSERT INTO `gacl_aro_groups_map` (`acl_id`, `group_id`) VALUES (11, 11);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aro_map`
-- 

DROP TABLE IF EXISTS `gacl_aro_map`;
CREATE TABLE `gacl_aro_map` (
  `acl_id` int(10) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aro_map`
-- 

INSERT INTO `gacl_aro_map` (`acl_id`, `section_value`, `value`) VALUES (23, 'user', '2');

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aro_sections`
-- 

DROP TABLE IF EXISTS `gacl_aro_sections`;
CREATE TABLE `gacl_aro_sections` (
  `id` int(10) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(10) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aro_sections`
-- 

INSERT INTO `gacl_aro_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'user', 1, 'Users', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aro_sections_seq`
-- 

DROP TABLE IF EXISTS `gacl_aro_sections_seq`;
CREATE TABLE `gacl_aro_sections_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aro_sections_seq`
-- 

INSERT INTO `gacl_aro_sections_seq` (`id`) VALUES (10);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_aro_seq`
-- 

DROP TABLE IF EXISTS `gacl_aro_seq`;
CREATE TABLE `gacl_aro_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_aro_seq`
-- 

INSERT INTO `gacl_aro_seq` (`id`) VALUES (10);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_axo`
-- 

DROP TABLE IF EXISTS `gacl_axo`;
CREATE TABLE `gacl_axo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `hidden` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

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
INSERT INTO `gacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES (39, 'app', 'reports', 1, 'Reports', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_axo_groups`
-- 

DROP TABLE IF EXISTS `gacl_axo_groups`;
CREATE TABLE `gacl_axo_groups` (
  `id` int(10) NOT NULL DEFAULT '0',
  `parent_id` int(10) NOT NULL DEFAULT '0',
  `lft` int(10) NOT NULL DEFAULT '0',
  `rgt` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`value`),
  KEY `parent_id` (`parent_id`),
  KEY `lft_rgt` (`lft`,`rgt`),
  KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

DROP TABLE IF EXISTS `gacl_axo_groups_id_seq`;
CREATE TABLE `gacl_axo_groups_id_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_axo_groups_id_seq`
-- 

INSERT INTO `gacl_axo_groups_id_seq` (`id`) VALUES (13);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_axo_groups_map`
-- 

DROP TABLE IF EXISTS `gacl_axo_groups_map`;
CREATE TABLE `gacl_axo_groups_map` (
  `acl_id` int(10) NOT NULL DEFAULT '0',
  `group_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acl_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

DROP TABLE IF EXISTS `gacl_axo_map`;
CREATE TABLE `gacl_axo_map` (
  `acl_id` int(10) NOT NULL DEFAULT '0',
  `section_value` varchar(80) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`acl_id`,`section_value`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_axo_map`
-- 

INSERT INTO `gacl_axo_map` (`acl_id`, `section_value`, `value`) VALUES (12, 'sys', 'acl');

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_axo_sections`
-- 

DROP TABLE IF EXISTS `gacl_axo_sections`;
CREATE TABLE `gacl_axo_sections` (
  `id` int(10) NOT NULL DEFAULT '0',
  `value` varchar(80) NOT NULL DEFAULT '',
  `order_value` int(10) NOT NULL DEFAULT '0',
  `name` varchar(230) NOT NULL DEFAULT '',
  `hidden` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_axo_sections`
-- 

INSERT INTO `gacl_axo_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (10, 'sys', 1, 'System', 0);
INSERT INTO `gacl_axo_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES (11, 'app', 2, 'Application', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_axo_sections_seq`
-- 

DROP TABLE IF EXISTS `gacl_axo_sections_seq`;
CREATE TABLE `gacl_axo_sections_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_axo_sections_seq`
-- 

INSERT INTO `gacl_axo_sections_seq` (`id`) VALUES (11);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_axo_seq`
-- 

DROP TABLE IF EXISTS `gacl_axo_seq`;
CREATE TABLE `gacl_axo_seq` (
  `id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_axo_seq`
-- 

INSERT INTO `gacl_axo_seq` (`id`) VALUES (55);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_groups_aro_map`
-- 

DROP TABLE IF EXISTS `gacl_groups_aro_map`;
CREATE TABLE `gacl_groups_aro_map` (
  `group_id` int(10) NOT NULL DEFAULT '0',
  `aro_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`aro_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_groups_aro_map`
-- 

INSERT INTO `gacl_groups_aro_map` (`group_id`, `aro_id`) VALUES (11, 10);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_groups_axo_map`
-- 

DROP TABLE IF EXISTS `gacl_groups_axo_map`;
CREATE TABLE `gacl_groups_axo_map` (
  `group_id` int(10) NOT NULL DEFAULT '0',
  `axo_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`axo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 39);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (11, 51);
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
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 39);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 51);
INSERT INTO `gacl_groups_axo_map` (`group_id`, `axo_id`) VALUES (13, 55);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_permissions`
-- 

DROP TABLE IF EXISTS `gacl_permissions`;
CREATE TABLE `gacl_permissions` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `user_name` varchar(255) NOT NULL DEFAULT '',
  `module` varchar(64) NOT NULL DEFAULT '',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `action` varchar(32) NOT NULL DEFAULT '',
  `access` int(1) NOT NULL DEFAULT '0',
  `acl_id` int(10) NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `user_name` (`user_name`),
  KEY `module` (`module`),
  KEY `item_id` (`item_id`),
  KEY `action` (`action`),
  KEY `acl_id` (`acl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_permissions`
-- 

INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'reports', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projectdesigner', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'links', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'smartsearch', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'users', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'roles', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'public', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'task_log', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'tasks', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'system', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'projects', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'help', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'forums', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'files', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'departments', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'contacts', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'companies', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'events', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'calendar', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'delete', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'edit', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'add', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'view', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'admin', 0, 'access', 1, 11);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'delete', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'edit', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'add', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'view', 0, 0);
INSERT INTO `gacl_permissions` (`user_id`, `user_name`, `module`, `item_id`, `action`, `access`, `acl_id`) VALUES (1, 'admin', 'acl', 0, 'access', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `gacl_phpgacl`
-- 

DROP TABLE IF EXISTS `gacl_phpgacl`;
CREATE TABLE `gacl_phpgacl` (
  `name` varchar(230) NOT NULL DEFAULT '',
  `value` varchar(230) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `gacl_phpgacl`
-- 

INSERT INTO `gacl_phpgacl` (`name`, `value`) VALUES ('version', '3.3.7');
INSERT INTO `gacl_phpgacl` (`name`, `value`) VALUES ('schema_version', '0.95');

-- --------------------------------------------------------

-- 
-- Table structure for table `history`
-- 

DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `history_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `history_user` int(10) NOT NULL DEFAULT '0',
  `history_action` varchar(20) NOT NULL DEFAULT 'modify',
  `history_item` int(10) NOT NULL,
  `history_table` varchar(20) NOT NULL DEFAULT '',
  `history_project` int(10) NOT NULL DEFAULT '0',
  `history_name` varchar(255) DEFAULT NULL,
  `history_changes` mediumtext,
  `history_description` mediumtext,
  PRIMARY KEY (`history_id`),
  KEY `index_history_module` (`history_table`,`history_item`),
  KEY `index_history_item` (`history_item`),
  KEY `history_date` (`history_date`),
  KEY `history_table` (`history_table`),
  KEY `history_user` (`history_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61 ;

-- 
-- Dumping data for table `history`
-- 

INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (1, '2010-08-04 00:26:45', 1, 'login', 1, 'login', 0, NULL, NULL, 'Admin Person');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (2, '2010-08-04 02:01:00', 1, 'update', 11, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 11');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (3, '2010-08-04 02:01:00', 1, 'update', 14, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 14');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (4, '2010-08-04 02:01:00', 1, 'update', 21, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 21');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (5, '2010-08-04 02:01:00', 1, 'update', 24, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 24');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (6, '2010-08-04 02:01:00', 1, 'update', 25, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 25');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (7, '2010-08-04 02:01:00', 1, 'update', 37, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 37');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (8, '2010-08-04 02:01:00', 1, 'update', 48, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 48');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (9, '2010-08-04 02:01:00', 1, 'update', 56, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 56');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (10, '2010-08-04 02:01:00', 1, 'update', 64, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 64');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (11, '2010-08-04 02:01:00', 1, 'update', 70, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 70');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (12, '2010-08-04 02:01:00', 1, 'update', 71, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 71');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (13, '2010-08-04 02:01:00', 1, 'update', 72, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 72');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (14, '2010-08-04 02:01:00', 1, 'update', 1, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 1');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (15, '2010-08-04 02:01:00', 1, 'update', 2, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 2');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (16, '2010-08-04 02:01:00', 1, 'update', 53, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 53');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (17, '2010-08-04 02:01:00', 1, 'update', 69, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 69');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (18, '2010-08-04 02:01:00', 1, 'update', 3, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 3');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (19, '2010-08-04 02:01:00', 1, 'update', 12, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 12');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (20, '2010-08-04 02:01:00', 1, 'update', 4, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 4');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (21, '2010-08-04 02:01:00', 1, 'update', 5, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 5');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (22, '2010-08-04 02:01:00', 1, 'update', 6, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 6');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (23, '2010-08-04 02:01:00', 1, 'update', 7, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 7');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (24, '2010-08-04 02:01:00', 1, 'update', 8, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 8');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (25, '2010-08-04 02:01:00', 1, 'update', 13, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 13');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (26, '2010-08-04 02:01:00', 1, 'update', 22, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 22');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (27, '2010-08-04 02:01:00', 1, 'update', 26, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 26');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (28, '2010-08-04 02:01:00', 1, 'update', 49, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 49');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (29, '2010-08-04 02:01:00', 1, 'update', 50, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 50');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (30, '2010-08-04 02:01:00', 1, 'update', 51, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 51');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (31, '2010-08-04 02:01:00', 1, 'update', 52, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 52');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (32, '2010-08-04 02:01:00', 1, 'update', 27, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 27');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (33, '2010-08-04 02:01:00', 1, 'update', 28, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 28');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (34, '2010-08-04 02:01:00', 1, 'update', 29, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 29');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (35, '2010-08-04 02:01:00', 1, 'update', 30, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 30');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (36, '2010-08-04 02:01:00', 1, 'update', 31, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 31');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (37, '2010-08-04 02:01:00', 1, 'update', 32, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 32');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (38, '2010-08-04 02:01:00', 1, 'update', 33, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 33');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (39, '2010-08-04 02:01:00', 1, 'update', 34, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 34');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (40, '2010-08-04 02:01:00', 1, 'update', 35, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 35');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (41, '2010-08-04 02:01:00', 1, 'update', 20, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 20');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (42, '2010-08-04 02:01:00', 1, 'update', 38, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 38');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (43, '2010-08-04 02:01:00', 1, 'update', 41, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 41');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (44, '2010-08-04 02:01:00', 1, 'update', 42, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 42');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (45, '2010-08-04 02:01:00', 1, 'update', 43, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 43');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (46, '2010-08-04 02:01:00', 1, 'update', 44, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 44');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (47, '2010-08-04 02:01:00', 1, 'update', 45, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 45');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (48, '2010-08-04 02:01:00', 1, 'update', 46, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 46');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (49, '2010-08-04 02:01:00', 1, 'update', 47, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 47');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (50, '2010-08-04 02:01:00', 1, 'update', 68, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 68');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (51, '2010-08-04 02:01:00', 1, 'update', 60, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 60');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (52, '2010-08-04 02:01:00', 1, 'update', 61, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 61');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (53, '2010-08-04 02:01:00', 1, 'update', 62, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 62');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (54, '2010-08-04 02:01:00', 1, 'update', 15, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 15');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (55, '2010-08-04 02:01:00', 1, 'update', 16, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 16');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (56, '2010-08-04 02:01:00', 1, 'update', 17, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 17');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (57, '2010-08-04 02:01:00', 1, 'update', 10, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 10');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (58, '2010-08-04 02:01:00', 1, 'update', 18, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 18');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (59, '2010-08-04 02:01:00', 1, 'update', 66, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 66');
INSERT INTO `history` (`history_id`, `history_date`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`) VALUES (60, '2010-08-04 02:01:00', 1, 'update', 67, 'config', 0, NULL, NULL, 'ACTION: update TABLE: config ID: 67');

-- --------------------------------------------------------

-- 
-- Table structure for table `links`
-- 

DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `link_id` int(10) NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) NOT NULL DEFAULT '',
  `link_project` int(10) NOT NULL DEFAULT '0',
  `link_task` int(10) NOT NULL DEFAULT '0',
  `link_name` varchar(255) NOT NULL DEFAULT '',
  `link_parent` int(10) DEFAULT '0',
  `link_description` mediumtext,
  `link_owner` int(10) DEFAULT '0',
  `link_date` datetime DEFAULT NULL,
  `link_icon` varchar(20) DEFAULT 'obj/',
  `link_category` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`link_id`),
  KEY `link_name` (`link_name`),
  KEY `link_project` (`link_project`),
  KEY `link_task` (`link_task`),
  KEY `link_parent` (`link_parent`),
  KEY `link_owner` (`link_owner`),
  KEY `link_category` (`link_category`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `links`
-- 

INSERT INTO `links` (`link_id`, `link_url`, `link_project`, `link_task`, `link_name`, `link_parent`, `link_description`, `link_owner`, `link_date`, `link_icon`, `link_category`) VALUES (1, 'http://web2project.net', 0, 0, 'web2project Homepage', 0, NULL, 1, '2010-01-12 23:43:13', 'obj/', 0);
INSERT INTO `links` (`link_id`, `link_url`, `link_project`, `link_task`, `link_name`, `link_parent`, `link_description`, `link_owner`, `link_date`, `link_icon`, `link_category`) VALUES (2, 'http://wiki.web2project.net', 0, 0, 'web2project Wiki', 0, NULL, 1, '2010-01-12 23:43:35', 'obj/', 0);
INSERT INTO `links` (`link_id`, `link_url`, `link_project`, `link_task`, `link_name`, `link_parent`, `link_description`, `link_owner`, `link_date`, `link_icon`, `link_category`) VALUES (3, 'http://forums.web2project.net', 0, 0, 'web2project Forum', 0, NULL, 1, '2010-01-12 23:43:54', 'obj/', 0);
INSERT INTO `links` (`link_id`, `link_url`, `link_project`, `link_task`, `link_name`, `link_parent`, `link_description`, `link_owner`, `link_date`, `link_icon`, `link_category`) VALUES (4, 'http://bugs.web2project.net', 1, 0, 'web2project Issue Tracker', 0, NULL, 1, '2010-01-12 23:44:14', 'obj/', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `modules`
-- 

DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `mod_id` int(10) NOT NULL AUTO_INCREMENT,
  `mod_name` varchar(64) NOT NULL DEFAULT '',
  `mod_directory` varchar(64) NOT NULL DEFAULT '',
  `mod_version` varchar(10) NOT NULL DEFAULT '',
  `mod_setup_class` varchar(64) NOT NULL DEFAULT '',
  `mod_type` varchar(64) NOT NULL DEFAULT '',
  `mod_active` int(1) unsigned NOT NULL DEFAULT '0',
  `mod_ui_name` varchar(20) NOT NULL DEFAULT '',
  `mod_ui_icon` varchar(64) NOT NULL DEFAULT '',
  `mod_ui_order` tinyint(3) NOT NULL DEFAULT '0',
  `mod_ui_active` int(1) unsigned NOT NULL DEFAULT '0',
  `mod_description` varchar(255) NOT NULL DEFAULT '',
  `permissions_item_table` varchar(100) DEFAULT NULL,
  `permissions_item_field` varchar(100) DEFAULT NULL,
  `permissions_item_label` varchar(100) DEFAULT NULL,
  `mod_main_class` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`mod_id`,`mod_directory`),
  KEY `mod_directory` (`mod_directory`),
  KEY `mod_type` (`mod_type`),
  KEY `mod_ui_order` (`mod_ui_order`),
  KEY `mod_active` (`mod_active`),
  KEY `permissions_item_table` (`permissions_item_table`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

-- 
-- Dumping data for table `modules`
-- 

INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (1, 'Companies', 'companies', '1.0.0', '', 'core', 1, 'Companies', 'handshake.png', 1, 1, '', 'companies', 'company_id', 'company_name', 'CCompany');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (2, 'Projects', 'projects', '1.0.0', '', 'core', 1, 'Projects', 'applet3-48.png', 4, 1, '', 'projects', 'project_id', 'project_name', 'CProject');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (3, 'Tasks', 'tasks', '1.0.0', '', 'core', 1, 'Tasks', 'applet-48.png', 5, 1, '', 'tasks', 'task_id', 'task_name', 'CTask');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (4, 'Calendar', 'calendar', '1.0.0', '', 'core', 1, 'Calendar', 'myevo-appointments.png', 6, 1, '', 'events', 'event_id', 'event_title', 'CEvent');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (5, 'Files', 'files', '1.0.0', '', 'core', 1, 'Files', 'folder5.png', 7, 1, '', 'files', 'file_id', 'file_name', 'CFile');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (6, 'Contacts', 'contacts', '1.0.0', '', 'core', 1, 'Contacts', 'monkeychat-48.png', 8, 1, '', 'contacts', 'contact_id', 'contact_first_name', 'CContact');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (7, 'Forums', 'forums', '1.0.0', '', 'core', 1, 'Forums', 'support.png', 9, 0, '', 'forums', 'forum_id', 'forum_name', 'CForum');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (9, 'User Administration', 'admin', '1.0.0', '', 'core', 1, 'User Admin', 'helix-setup-users.png', 13, 1, '', 'users', 'user_id', 'user_username', 'CUser');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (10, 'System Administration', 'system', '1.0.0', '', 'core', 1, 'System Admin', '48_my_computer.png', 14, 1, '', '', '', '', 'CSystem');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (12, 'Help', 'help', '1.0.0', '', 'core', 1, 'Help', 'w2p.gif', 15, 0, '', '', '', '', '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (13, 'Public', 'public', '1.0.0', '', 'core', 1, 'Public', 'users.gif', 16, 0, '', '', '', '', '');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (14, 'SmartSearch', 'smartsearch', '2.0', 'SSearchNS', 'user', 1, 'SmartSearch', 'kfind.png', 11, 1, 'A module to search keywords and find the needle in the haystack', NULL, NULL, NULL, 'smartsearch');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (37, 'ProjectDesigner', 'projectdesigner', '1.0', 'projectDesigner', 'user', 1, 'ProjectDesigner', 'projectdesigner.jpg', 3, 1, 'A module to design projects', NULL, NULL, NULL, 'CProjectDesignerOptions');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (17, 'Departments', 'departments', '1.0', '', 'core', 1, 'Departments', '', 2, 1, '', 'departments', 'dept_id', 'dept_name', 'CDepartment');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (25, 'Reports', 'reports', '0.1', 'CSetupReports', 'user', 1, 'Reports', 'printer.png', 12, 1, 'A module for reports', NULL, NULL, NULL, 'CReport');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (41, 'Links', 'links', '1.0', 'CSetupLinks', 'user', 1, 'Links', 'communicate.gif', 10, 1, 'Links related to tasks', 'links', 'link_id', 'link_name', 'CLink');
INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`, `permissions_item_table`, `permissions_item_field`, `permissions_item_label`, `mod_main_class`) VALUES (42, 'History', 'history', '0.32', 'CSetupHistory', 'user', 1, 'History', '', 17, 0, 'A module for tracking changes', NULL, NULL, NULL, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `projects`
-- 

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `project_id` int(10) NOT NULL AUTO_INCREMENT,
  `project_company` int(10) NOT NULL DEFAULT '0',
  `project_department` int(10) NOT NULL DEFAULT '0',
  `project_name` varchar(255) DEFAULT NULL,
  `project_short_name` varchar(10) DEFAULT NULL,
  `project_owner` int(10) DEFAULT '0',
  `project_url` varchar(255) DEFAULT NULL,
  `project_demo_url` varchar(255) DEFAULT NULL,
  `project_start_date` datetime DEFAULT NULL,
  `project_end_date` datetime DEFAULT NULL,
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
  `project_private` tinyint(3) unsigned DEFAULT '0',
  `project_departments` varchar(100) DEFAULT NULL,
  `project_contacts` varchar(100) DEFAULT NULL,
  `project_priority` tinyint(4) DEFAULT '0',
  `project_type` smallint(6) NOT NULL DEFAULT '0',
  `project_keydate` datetime DEFAULT NULL,
  `project_keydate_pos` tinyint(1) DEFAULT '0',
  `project_keytask` int(10) DEFAULT '0',
  `project_active` int(1) NOT NULL DEFAULT '1',
  `project_original_parent` int(10) unsigned NOT NULL DEFAULT '0',
  `project_parent` int(10) unsigned NOT NULL DEFAULT '0',
  `project_empireint_special` int(1) NOT NULL DEFAULT '0',
  `project_updator` int(10) NOT NULL DEFAULT '0',
  `project_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `project_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `project_status_comment` varchar(255) NOT NULL DEFAULT '',
  `project_subpriority` tinyint(4) DEFAULT '0',
  `project_end_date_adjusted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `project_end_date_adjusted_user` int(10) NOT NULL DEFAULT '0',
  `project_location` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`project_id`),
  KEY `idx_project_owner` (`project_owner`),
  KEY `idx_proj1` (`project_company`),
  KEY `project_company` (`project_company`),
  KEY `project_name` (`project_name`),
  KEY `project_short_name` (`project_short_name`),
  KEY `project_start_date` (`project_start_date`),
  KEY `project_end_date` (`project_end_date`),
  KEY `project_status` (`project_status`),
  KEY `project_creator` (`project_creator`),
  KEY `project_priority` (`project_priority`),
  KEY `project_type` (`project_type`),
  KEY `project_parent` (`project_parent`),
  KEY `project_original_parent` (`project_original_parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `projects`
-- 

INSERT INTO `projects` (`project_id`, `project_company`, `project_department`, `project_name`, `project_short_name`, `project_owner`, `project_url`, `project_demo_url`, `project_start_date`, `project_end_date`, `project_actual_end_date`, `project_status`, `project_percent_complete`, `project_color_identifier`, `project_description`, `project_target_budget`, `project_actual_budget`, `project_scheduled_hours`, `project_worked_hours`, `project_task_count`, `project_creator`, `project_private`, `project_departments`, `project_contacts`, `project_priority`, `project_type`, `project_keydate`, `project_keydate_pos`, `project_keytask`, `project_active`, `project_original_parent`, `project_parent`, `project_empireint_special`, `project_updator`, `project_created`, `project_updated`, `project_status_comment`, `project_subpriority`, `project_end_date_adjusted`, `project_end_date_adjusted_user`, `project_location`) VALUES (1, 1, 0, 'My Fantastic Project', 'FantProj', 1, '', '', '2010-08-03 19:24:23', NULL, NULL, 3, 0, '3300CC', 'This is some descriptive information about my project.', 0.00, 0.00, 0, 3, 4, 1, 0, '', '', 0, 0, NULL, 0, 0, 1, 1, 1, 0, 0, '2010-01-12 23:34:29', '2010-01-12 23:34:52', '', 0, '0000-00-00 00:00:00', 0, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `project_contacts`
-- 

DROP TABLE IF EXISTS `project_contacts`;
CREATE TABLE `project_contacts` (
  `project_id` int(10) NOT NULL DEFAULT '0',
  `contact_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`project_id`,`contact_id`),
  KEY `project_id` (`project_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `project_contacts`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `project_departments`
-- 

DROP TABLE IF EXISTS `project_departments`;
CREATE TABLE `project_departments` (
  `project_id` int(10) NOT NULL DEFAULT '0',
  `department_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`project_id`,`department_id`),
  KEY `project_id` (`project_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `project_departments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `project_designer_options`
-- 

DROP TABLE IF EXISTS `project_designer_options`;
CREATE TABLE `project_designer_options` (
  `pd_option_id` int(10) NOT NULL AUTO_INCREMENT,
  `pd_option_user` int(10) NOT NULL DEFAULT '0',
  `pd_option_view_project` int(1) NOT NULL DEFAULT '1',
  `pd_option_view_gantt` int(1) NOT NULL DEFAULT '1',
  `pd_option_view_tasks` int(1) NOT NULL DEFAULT '1',
  `pd_option_view_actions` int(1) NOT NULL DEFAULT '1',
  `pd_option_view_addtasks` int(1) NOT NULL DEFAULT '1',
  `pd_option_view_files` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pd_option_id`),
  UNIQUE KEY `pd_option_user` (`pd_option_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `project_designer_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `sessions`
-- 

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '',
  `session_user` int(10) NOT NULL DEFAULT '0',
  `session_data` longblob,
  `session_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`session_id`),
  KEY `session_updated` (`session_updated`),
  KEY `session_created` (`session_created`),
  KEY `session_user` (`session_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `sessions`
-- 

INSERT INTO `sessions` (`session_id`, `session_user`, `session_data`, `session_updated`, `session_created`) VALUES ('4d4184bfa773014ecb0fe004d82fd5f5', 15, 0x41707055497c4f3a363a22434170705549223a32373a7b733a353a227374617465223b613a31363a7b733a31323a22436f6e666967496478546162223b693a303b733a31323a225341564544504c4143452d31223b733a383a226d3d73797374656d223b733a31303a225341564544504c414345223b733a31393a226d3d73797374656d26613d766965776d6f6473223b733a31303a2250726f6a496478546162223b693a313b733a31343a2250726f6a496478436f6d70616e79223b733a313a2230223b733a31353a2250726f6a4964784f72646572446972223b733a333a22617363223b733a31353a226f776e65725f66696c7465725f6964223b693a303b733a31353a22436f6d70616e696573496478546162223b693a303b733a393a2250726f6a5677546162223b693a303b733a32323a225461736b4c69737453686f77496e636f6d706c657465223b693a303b733a31323a225461736b4165546162496478223b693a303b733a31323a225461736b4c6f675677546162223b693a303b733a31333a2243616c496478436f6d70616e79223b693a303b733a31323a2243616c49647846696c746572223b733a303a22223b733a31303a224c696e6b496478546162223b693a303b733a31353a225265736f7572636573496478546162223b693a303b7d733a373a22757365725f6964223b733a313a2231223b733a31353a22757365725f66697273745f6e616d65223b733a353a2241646d696e223b733a31343a22757365725f6c6173745f6e616d65223b733a363a22506572736f6e223b733a31323a22757365725f636f6d70616e79223b733a313a2230223b733a31353a22757365725f6465706172746d656e74223b733a313a2230223b733a31303a22757365725f656d61696c223b733a31353a2261646d696e406c6f63616c686f7374223b733a393a22757365725f74797065223b733a313a2231223b733a31303a22757365725f7072656673223b613a31333a7b733a31323a2243555252454e4359464f524d223b733a323a22656e223b733a31313a224556454e5446494c544552223b733a323a226d79223b733a363a224c4f43414c45223b733a323a22656e223b733a373a224d41494c414c4c223b733a313a2230223b733a31323a22534844415445464f524d4154223b733a383a2225642f25622f2559223b733a373a2254414256494557223b733a313a2230223b733a31333a225441534b41535349474e4d4158223b733a333a22313030223b733a31323a225441534b4c4f47454d41494c223b733a313a2230223b733a31313a225441534b4c4f474e4f5445223b733a313a2230223b733a31313a225441534b4c4f475355424a223b733a303a22223b733a31303a2254494d45464f524d4154223b733a383a2225493a254d202570223b733a373a2255495354594c45223b733a31313a227765623270726f6a656374223b733a31303a2255534552464f524d4154223b733a343a2275736572223b7d733a31323a226461795f73656c6563746564223b4e3b733a31313a22757365725f6c6f63616c65223b733a323a22656e223b733a393a22757365725f6c616e67223b613a343a7b693a303b733a31333a22656e2e49534f383835392d3135223b693a313b733a333a22656e75223b693a323b733a323a22656e223b693a333b733a323a22656e223b7d733a31313a22626173655f6c6f63616c65223b733a323a22656e223b733a333a226d7367223b733a303a22223b733a353a226d73674e6f223b693a303b733a31353a2264656661756c745265646972656374223b733a303a22223b733a333a22636667223b613a313a7b733a31313a226c6f63616c655f7761726e223b623a303b7d733a31333a2276657273696f6e5f6d616a6f72223b693a313b733a31333a2276657273696f6e5f6d696e6f72223b693a323b733a31333a2276657273696f6e5f7061746368223b693a323b733a31343a2276657273696f6e5f737472696e67223b733a353a22312e322e32223b733a31343a226c6173745f696e736572745f6964223b733a323a223135223b733a31303a22757365725f7374796c65223b4e3b733a31333a22757365725f69735f61646d696e223b693a313b733a31363a2200434170705549006f626a53746f7265223b4e3b733a31303a2270726f6a6563745f6964223b693a303b733a31343a22626f78546f7052656e6465726564223b623a313b7d4c414e4755414745537c613a373a7b733a323a22656e223b613a353a7b693a303b733a323a22656e223b693a313b733a373a22456e676c697368223b693a323b733a373a22456e676c697368223b693a333b733a333a22656e75223b693a343b733a31303a2249534f383835392d3135223b7d733a323a227074223b613a343a7b693a303b733a323a227074223b693a313b733a31353a22506f72747567756573652028505429223b693a323b733a31353a22506f72747567756573612028505429223b693a333b733a31393a22506f72747567756573655f506f72747567616c223b7d733a353a2270745f6272223b613a343a7b693a303b733a353a2270745f6272223b693a313b733a31353a22506f72747567756573652028425229223b693a323b733a31343a22506f727475677565732028425229223b693a333b733a353a2250542d4252223b7d733a323a226573223b613a343a7b693a303b733a323a226573223b693a313b733a373a225370616e697368223b693a323b733a31303a2243617374656c6c616e6f223b693a333b733a333a22657370223b7d733a353a2266725f4652223b613a343a7b693a303b733a323a226672223b693a313b733a363a224672656e6368223b693a323b733a383a224672616ee7616973223b693a333b733a333a22667261223b7d733a353a2264655f4445223b613a353a7b693a303b733a323a226465223b693a313b733a31323a224765726d616e202847657229223b693a323b733a31323a22446575747363682028444529223b693a333b733a323a226465223b693a343b733a353a227574662d38223b7d733a323a22706f223b613a353a7b693a303b733a323a22706f223b693a313b733a31323a22506f6c6973682028506f6c29223b693a323b733a31313a22506f6c736b692028504c29223b693a333b733a323a22706f223b693a343b733a353a227574662d38223b7d7d616c6c5f746162737c613a393a7b733a363a2273797374656d223b613a303a7b7d733a383a2270726f6a65637473223b613a313a7b733a343a2276696577223b613a343a7b693a303b613a333a7b733a343a226e616d65223b733a363a224576656e7473223b733a343a2266696c65223b733a37363a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f63616c656e6461722f70726f6a656374735f7461622e766965772e6576656e7473223b733a363a226d6f64756c65223b733a383a2263616c656e646172223b7d693a313b613a333a7b733a343a226e616d65223b733a353a2246696c6573223b733a343a2266696c65223b733a37323a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f66696c65732f70726f6a656374735f7461622e766965772e66696c6573223b733a363a226d6f64756c65223b733a353a2266696c6573223b7d693a323b613a333a7b733a343a226e616d65223b733a373a22486973746f7279223b733a343a2266696c65223b733a37363a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f686973746f72792f70726f6a656374735f7461622e766965772e686973746f7279223b733a363a226d6f64756c65223b733a373a22686973746f7279223b7d693a333b613a333a7b733a343a226e616d65223b733a353a224c696e6b73223b733a343a2266696c65223b733a37323a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f6c696e6b732f70726f6a656374735f7461622e766965772e6c696e6b73223b733a363a226d6f64756c65223b733a353a226c696e6b73223b7d7d7d733a393a22636f6d70616e696573223b613a313a7b733a343a2276696577223b613a323a7b693a303b613a333a7b733a343a226e616d65223b733a363a224576656e7473223b733a343a2266696c65223b733a37373a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f63616c656e6461722f636f6d70616e6965735f7461622e766965772e6576656e7473223b733a363a226d6f64756c65223b733a383a2263616c656e646172223b7d693a313b613a333a7b733a343a226e616d65223b733a353a2246696c6573223b733a343a2266696c65223b733a37333a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f66696c65732f636f6d70616e6965735f7461622e766965772e66696c6573223b733a363a226d6f64756c65223b733a353a2266696c6573223b7d7d7d733a363a227075626c6963223b613a303a7b7d733a353a227461736b73223b613a333a7b693a303b613a333a7b733a343a226e616d65223b733a353a2246696c6573223b733a343a2266696c65223b733a36343a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f66696c65732f7461736b735f7461622e66696c6573223b733a363a226d6f64756c65223b733a353a2266696c6573223b7d733a343a2276696577223b613a333a7b693a303b613a333a7b733a343a226e616d65223b733a353a2246696c6573223b733a343a2266696c65223b733a36393a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f66696c65732f7461736b735f7461622e766965772e66696c6573223b733a363a226d6f64756c65223b733a353a2266696c6573223b7d693a313b613a333a7b733a343a226e616d65223b733a353a224c696e6b73223b733a343a2266696c65223b733a36393a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f6c696e6b732f7461736b735f7461622e766965772e6c696e6b73223b733a363a226d6f64756c65223b733a353a226c696e6b73223b7d693a323b613a333a7b733a343a226e616d65223b733a31353a224f74686572207265736f7572636573223b733a343a2266696c65223b733a38333a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f7265736f75726365732f7461736b735f7461622e766965772e6f746865725f7265736f7572636573223b733a363a226d6f64756c65223b733a393a227265736f7572636573223b7d7d733a373a2261646465646974223b613a313a7b693a303b613a333a7b733a343a226e616d65223b733a31353a224f74686572207265736f7572636573223b733a343a2266696c65223b733a38363a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f7265736f75726365732f7461736b735f7461622e616464656469742e6f746865725f7265736f7572636573223b733a363a226d6f64756c65223b733a393a227265736f7572636573223b7d7d7d733a383a2263616c656e646172223b613a303a7b7d733a383a22636f6e7461637473223b613a303a7b7d733a353a226c696e6b73223b613a303a7b7d733a393a227265736f7572636573223b613a303a7b7d7d616c6c5f6372756d62737c613a393a7b733a363a2273797374656d223b613a303a7b7d733a383a2270726f6a65637473223b613a313a7b733a343a2276696577223b613a323a7b693a303b613a333a7b733a343a226e616d65223b733a31353a2250726f6a65637464657369676e6572223b733a343a2266696c65223b733a39343a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f70726f6a65637464657369676e65722f70726f6a656374735f6372756d622e766965772e70726f6a65637464657369676e6572223b733a363a226d6f64756c65223b733a31353a2270726f6a65637464657369676e6572223b7d693a313b613a333a7b733a343a226e616d65223b733a373a225265706f727473223b733a343a2266696c65223b733a37383a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f7265706f7274732f70726f6a656374735f6372756d622e766965772e7265706f727473223b733a363a226d6f64756c65223b733a373a227265706f727473223b7d7d7d733a393a22636f6d70616e696573223b613a303a7b7d733a363a227075626c6963223b613a303a7b7d733a353a227461736b73223b613a303a7b7d733a383a2263616c656e646172223b613a303a7b7d733a383a22636f6e7461637473223b613a323a7b733a373a2261646465646974223b613a313a7b693a303b613a333a7b733a343a226e616d65223b733a31383a224e65777573657266726f6d636f6e74616374223b733a343a2266696c65223b733a39303a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f61646d696e2f636f6e74616374735f6372756d622e616464656469742e6e65777573657266726f6d636f6e74616374223b733a363a226d6f64756c65223b733a353a2261646d696e223b7d7d733a343a2276696577223b613a313a7b693a303b613a333a7b733a343a226e616d65223b733a31383a224e65777573657266726f6d636f6e74616374223b733a343a2266696c65223b733a38373a222f686f6d652f6361736579646b2f776f726b73706163652f7732702d76312e322e782f6d6f64756c65732f61646d696e2f636f6e74616374735f6372756d622e766965772e6e65777573657266726f6d636f6e74616374223b733a363a226d6f64756c65223b733a353a2261646d696e223b7d7d7d733a353a226c696e6b73223b613a303a7b7d733a393a227265736f7572636573223b613a303a7b7d7d7265736f757263655f747970655f6c6973747c613a343a7b693a303b613a323a7b733a31363a227265736f757263655f747970655f6964223b693a303b733a31383a227265736f757263655f747970655f6e616d65223b733a31333a22416c6c205265736f7572636573223b7d693a313b613a323a7b733a31363a227265736f757263655f747970655f6964223b733a313a2231223b733a31383a227265736f757263655f747970655f6e616d65223b733a393a2245717569706d656e74223b7d693a323b613a323a7b733a31363a227265736f757263655f747970655f6964223b733a313a2232223b733a31383a227265736f757263655f747970655f6e616d65223b733a343a22546f6f6c223b7d693a333b613a323a7b733a31363a227265736f757263655f747970655f6964223b733a313a2233223b733a31383a227265736f757263655f747970655f6e616d65223b733a353a2256656e7565223b7d7d, '2010-01-12 23:48:35', '2010-01-12 23:31:34');
INSERT INTO `sessions` (`session_id`, `session_user`, `session_data`, `session_updated`, `session_created`) VALUES ('ce905f0e2bbd68b2e36fc033cb0dde3e', 16, 0x4c414e4755414745537c613a373a7b733a353a2264655f4445223b613a353a7b693a303b733a323a226465223b693a313b733a31323a224765726d616e202847657229223b693a323b733a31323a22446575747363682028444529223b693a333b733a323a226465223b693a343b733a353a227574662d38223b7d733a323a22656e223b613a353a7b693a303b733a323a22656e223b693a313b733a373a22456e676c697368223b693a323b733a373a22456e676c697368223b693a333b733a333a22656e75223b693a343b733a31303a2249534f383835392d3135223b7d733a323a226573223b613a343a7b693a303b733a323a226573223b693a313b733a373a225370616e697368223b693a323b733a31303a2243617374656c6c616e6f223b693a333b733a333a22657370223b7d733a353a2266725f4652223b613a343a7b693a303b733a323a226672223b693a313b733a363a224672656e6368223b693a323b733a393a224672616ec3a7616973223b693a333b733a333a22667261223b7d733a323a22706f223b613a353a7b693a303b733a323a22706f223b693a313b733a31323a22506f6c6973682028506f6c29223b693a323b733a31313a22506f6c736b692028504c29223b693a333b733a323a22706f223b693a343b733a353a227574662d38223b7d733a323a227074223b613a343a7b693a303b733a323a227074223b693a313b733a31353a22506f72747567756573652028505429223b693a323b733a31353a22506f72747567756573612028505429223b693a333b733a31393a22506f72747567756573655f506f72747567616c223b7d733a353a2270745f6272223b613a343a7b693a303b733a353a2270745f6272223b693a313b733a31353a22506f72747567756573652028425229223b693a323b733a31343a22506f727475677565732028425229223b693a333b733a353a2250542d4252223b7d7d41707055497c4f3a363a22434170705549223a32383a7b733a353a227374617465223b613a353a7b733a31323a225341564544504c4143452d31223b733a373a226d3d66696c6573223b733a31303a225341564544504c414345223b733a383a226d3d73797374656d223b733a31343a2246696c6549647850726f6a656374223b693a303b733a31303a2246696c65496478546162223b693a303b733a31323a22436f6e666967496478546162223b693a303b7d733a373a22757365725f6964223b733a313a2231223b733a31353a22757365725f66697273745f6e616d65223b733a353a2241646d696e223b733a31343a22757365725f6c6173745f6e616d65223b733a363a22506572736f6e223b733a31323a22757365725f636f6d70616e79223b733a313a2230223b733a31353a22757365725f6465706172746d656e74223b733a313a2230223b733a31303a22757365725f656d61696c223b733a31373a2261646d696e406578616d706c652e6f7267223b733a393a22757365725f74797065223b733a313a2231223b733a31303a22757365725f7072656673223b613a31383a7b733a31323a2243555252454e4359464f524d223b733a323a22656e223b733a31313a224556454e5446494c544552223b733a323a226d79223b733a363a224c4f43414c45223b733a323a22656e223b733a373a224d41494c414c4c223b733a313a2230223b733a31323a22534844415445464f524d4154223b733a383a2225642f25622f2559223b733a373a2254414256494557223b733a313a2230223b733a31333a225441534b41535349474e4d4158223b733a333a22313030223b733a31323a225441534b4c4f47454d41494c223b733a313a2230223b733a31313a225441534b4c4f474e4f5445223b733a313a2230223b733a31313a225441534b4c4f475355424a223b733a303a22223b733a31303a2254494d45464f524d4154223b733a383a2225493a254d202570223b733a373a2255495354594c45223b733a31313a227765623270726f6a656374223b733a31303a2255534552464f524d4154223b733a343a2275736572223b733a31343a2246554c4c44415445464f524d4154223b733a31313a22642f4d2f5920683a692061223b733a31323a224c4744415445464f524d4154223b733a393a2225422025642c202559223b733a383a2254494d455a4f4e45223b733a31353a22416d65726963612f4368696361676f223b733a31353a224441594c49474854534156494e4753223b733a313a2231223b733a31333a225441534b53455850414e444544223b733a313a2230223b7d733a31323a226461795f73656c6563746564223b4e3b733a31313a22757365725f6c6f63616c65223b733a323a22656e223b733a393a22757365725f6c616e67223b613a343a7b693a303b733a31333a22656e2e49534f383835392d3135223b693a313b733a333a22656e75223b693a323b733a323a22656e223b693a333b733a323a22656e223b7d733a31313a22626173655f6c6f63616c65223b733a323a22656e223b733a333a226d7367223b733a303a22223b733a353a226d73674e6f223b693a303b733a31353a2264656661756c745265646972656374223b733a303a22223b733a333a22636667223b613a313a7b733a31313a226c6f63616c655f7761726e223b623a303b7d733a31333a2276657273696f6e5f6d616a6f72223b693a323b733a31333a2276657273696f6e5f6d696e6f72223b693a303b733a31333a2276657273696f6e5f7061746368223b693a313b733a31343a2276657273696f6e5f737472696e67223b733a393a22322e302e312d707265223b733a31343a226c6173745f696e736572745f6964223b733a323a223136223b733a31303a22757365725f7374796c65223b4e3b733a31333a22757365725f69735f61646d696e223b693a313b733a31363a226c6f6e675f646174655f666f726d6174223b4e3b733a31363a2200434170705549006f626a53746f7265223b4e3b733a31303a2270726f6a6563745f6964223b693a303b733a31343a22626f78546f7052656e6465726564223b623a313b7d616c6c5f746162737c613a323a7b733a353a2266696c6573223b613a303a7b7d733a363a2273797374656d223b613a303a7b7d7d616c6c5f6372756d62737c613a323a7b733a353a2266696c6573223b613a303a7b7d733a363a2273797374656d223b613a303a7b7d7d, '2010-08-03 21:01:00', '2010-08-04 00:26:45');

-- --------------------------------------------------------

-- 
-- Table structure for table `syskeys`
-- 

DROP TABLE IF EXISTS `syskeys`;
CREATE TABLE `syskeys` (
  `syskey_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `syskey_name` varchar(48) NOT NULL DEFAULT '',
  `syskey_label` varchar(255) NOT NULL DEFAULT '',
  `syskey_type` int(1) unsigned NOT NULL DEFAULT '0',
  `syskey_sep1` char(2) DEFAULT '\n',
  `syskey_sep2` char(2) NOT NULL DEFAULT '|',
  PRIMARY KEY (`syskey_id`),
  KEY `syskey_name` (`syskey_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `syskeys`
-- 

INSERT INTO `syskeys` (`syskey_id`, `syskey_name`, `syskey_label`, `syskey_type`, `syskey_sep1`, `syskey_sep2`) VALUES (1, 'SelectList', 'Enter values for list', 0, '\n', '|');
INSERT INTO `syskeys` (`syskey_id`, `syskey_name`, `syskey_label`, `syskey_type`, `syskey_sep1`, `syskey_sep2`) VALUES (2, 'CustomField', 'Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Field''s name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>', 0, '\n', '|');
INSERT INTO `syskeys` (`syskey_id`, `syskey_name`, `syskey_label`, `syskey_type`, `syskey_sep1`, `syskey_sep2`) VALUES (3, 'ColorSelection', 'Hex color values for type=>color association.', 0, '', '|');
INSERT INTO `syskeys` (`syskey_id`, `syskey_name`, `syskey_label`, `syskey_type`, `syskey_sep1`, `syskey_sep2`) VALUES (4, 'ContactMethods', 'Alternate methods of communication for contacts', 0, '\n', '|');

-- --------------------------------------------------------

-- 
-- Table structure for table `sysvals`
-- 

DROP TABLE IF EXISTS `sysvals`;
CREATE TABLE `sysvals` (
  `sysval_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sysval_key_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sysval_title` varchar(48) NOT NULL DEFAULT '',
  `sysval_value` mediumtext NOT NULL,
  `sysval_value_id` varchar(128) DEFAULT '0',
  PRIMARY KEY (`sysval_id`),
  KEY `sysval_key_id` (`sysval_key_id`),
  KEY `sysval_title` (`sysval_title`),
  KEY `sysval_value_id` (`sysval_value_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=803 ;

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
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (661, 1, 'ProjectRequiredFields', '<1', 'f.project_short_name.value.length');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (662, 1, 'FileIndexIgnoreWords', 'a,about,also, an,and,another,any,are,as,at,back,be,because,been,being,but,\n	by,can,could,did,do,each,end,even,for,from,get,go,had,have,he,her,here,his,how, i,if,in,into,is,it,else,\n	just,may,me,might,much,must, my,no,not,ofv,off,on,only,or,other,our,out,should,so,some,still,such,than,\n	that,the,their,them,then,there,these,they,this,those,to,too,try,twov,under, up,us,was,we,were,what,when,\n	where,which,while,who,why,will,with,within,without,would,you,your,MSWordDoc,bjbjU', 'FileIndexIgnoreWords');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (663, 1, 'Timezones', 'Kwajalein', 'Kwajalein');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (664, 1, 'Timezones', 'Pacific/Midway', 'Pacific/Midway');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (665, 1, 'Timezones', 'Pacific/Samoa', 'Pacific/Samoa');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (666, 1, 'Timezones', 'Pacific/Honolulu', 'Pacific/Honolulu');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (667, 1, 'Timezones', 'America/Anchorage', 'America/Anchorage');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (668, 1, 'Timezones', 'America/Los_Angeles', 'America/Los_Angeles');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (669, 1, 'Timezones', 'America/Tijuana', 'America/Tijuana');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (670, 1, 'Timezones', 'America/Denver', 'America/Denver');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (671, 1, 'Timezones', 'America/Chihuahua', 'America/Chihuahua');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (672, 1, 'Timezones', 'America/Mazatlan', 'America/Mazatlan');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (673, 1, 'Timezones', 'America/Phoenix', 'America/Phoenix');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (674, 1, 'Timezones', 'America/Regina', 'America/Regina');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (675, 1, 'Timezones', 'America/Tegucigalpa', 'America/Tegucigalpa');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (676, 1, 'Timezones', 'America/Chicago', 'America/Chicago');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (677, 1, 'Timezones', 'America/Mexico_City', 'America/Mexico_City');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (678, 1, 'Timezones', 'America/Monterrey', 'America/Monterrey');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (679, 1, 'Timezones', 'America/New_York', 'America/New_York');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (680, 1, 'Timezones', 'America/Bogota', 'America/Bogota');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (681, 1, 'Timezones', 'America/Lima', 'America/Lima');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (682, 1, 'Timezones', 'America/Rio_Branco', 'America/Rio_Branco');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (683, 1, 'Timezones', 'America/Indiana/Indianapolis', 'America/Indiana/Indianapolis');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (684, 1, 'Timezones', 'America/Caracas', 'America/Caracas');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (685, 1, 'Timezones', 'America/Halifax', 'America/Halifax');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (686, 1, 'Timezones', 'America/Manaus', 'America/Manaus');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (687, 1, 'Timezones', 'America/Santiago', 'America/Santiago');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (688, 1, 'Timezones', 'America/La_Paz', 'America/La_Paz');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (689, 1, 'Timezones', 'America/St_Johns', 'America/St_Johns');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (690, 1, 'Timezones', 'America/Argentina/Buenos_Aires', 'America/Argentina/Buenos_Aires');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (691, 1, 'Timezones', 'America/Sao_Paulo', 'America/Sao_Paulo');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (692, 1, 'Timezones', 'America/Godthab', 'America/Godthab');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (693, 1, 'Timezones', 'America/Montevideo', 'America/Montevideo');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (694, 1, 'Timezones', 'Atlantic/South_Georgia', 'Atlantic/South_Georgia');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (695, 1, 'Timezones', 'Atlantic/Azores', 'Atlantic/Azores');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (696, 1, 'Timezones', 'Atlantic/Cape_Verde', 'Atlantic/Cape_Verde');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (697, 1, 'Timezones', 'Europe/Dublin', 'Europe/Dublin');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (698, 1, 'Timezones', 'Europe/Lisbon', 'Europe/Lisbon');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (699, 1, 'Timezones', 'Europe/London', 'Europe/London');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (700, 1, 'Timezones', 'Africa/Monrovia', 'Africa/Monrovia');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (701, 1, 'Timezones', 'Atlantic/Reykjavik', 'Atlantic/Reykjavik');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (702, 1, 'Timezones', 'Africa/Casablanca', 'Africa/Casablanca');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (703, 1, 'Timezones', 'Europe/Belgrade', 'Europe/Belgrade');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (704, 1, 'Timezones', 'Europe/Bratislava', 'Europe/Bratislava');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (705, 1, 'Timezones', 'Europe/Budapest', 'Europe/Budapest');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (706, 1, 'Timezones', 'Europe/Ljubljana', 'Europe/Ljubljana');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (707, 1, 'Timezones', 'Europe/Prague', 'Europe/Prague');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (708, 1, 'Timezones', 'Europe/Sarajevo', 'Europe/Sarajevo');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (709, 1, 'Timezones', 'Europe/Skopje', 'Europe/Skopje');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (710, 1, 'Timezones', 'Europe/Warsaw', 'Europe/Warsaw');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (711, 1, 'Timezones', 'Europe/Zagreb', 'Europe/Zagreb');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (712, 1, 'Timezones', 'Europe/Brussels', 'Europe/Brussels');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (713, 1, 'Timezones', 'Europe/Copenhagen', 'Europe/Copenhagen');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (714, 1, 'Timezones', 'Europe/Madrid', 'Europe/Madrid');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (715, 1, 'Timezones', 'Europe/Paris', 'Europe/Paris');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (716, 1, 'Timezones', 'Africa/Algiers', 'Africa/Algiers');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (717, 1, 'Timezones', 'Europe/Amsterdam', 'Europe/Amsterdam');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (718, 1, 'Timezones', 'Europe/Berlin', 'Europe/Berlin');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (719, 1, 'Timezones', 'Europe/Rome', 'Europe/Rome');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (720, 1, 'Timezones', 'Europe/Stockholm', 'Europe/Stockholm');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (721, 1, 'Timezones', 'Europe/Vienna', 'Europe/Vienna');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (722, 1, 'Timezones', 'Europe/Minsk', 'Europe/Minsk');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (723, 1, 'Timezones', 'Africa/Cairo', 'Africa/Cairo');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (724, 1, 'Timezones', 'Europe/Helsinki', 'Europe/Helsinki');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (725, 1, 'Timezones', 'Europe/Riga', 'Europe/Riga');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (726, 1, 'Timezones', 'Europe/Sofia', 'Europe/Sofia');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (727, 1, 'Timezones', 'Europe/Tallinn', 'Europe/Tallinn');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (728, 1, 'Timezones', 'Europe/Vilnius', 'Europe/Vilnius');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (729, 1, 'Timezones', 'Europe/Athens', 'Europe/Athens');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (730, 1, 'Timezones', 'Europe/Bucharest', 'Europe/Bucharest');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (731, 1, 'Timezones', 'Europe/Istanbul', 'Europe/Istanbul');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (732, 1, 'Timezones', 'Asia/Jerusalem', 'Asia/Jerusalem');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (733, 1, 'Timezones', 'Asia/Amman', 'Asia/Amman');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (734, 1, 'Timezones', 'Asia/Beirut', 'Asia/Beirut');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (735, 1, 'Timezones', 'Africa/Windhoek', 'Africa/Windhoek');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (736, 1, 'Timezones', 'Africa/Harare', 'Africa/Harare');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (737, 1, 'Timezones', 'Asia/Kuwait', 'Asia/Kuwait');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (738, 1, 'Timezones', 'Asia/Riyadh', 'Asia/Riyadh');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (739, 1, 'Timezones', 'Asia/Baghdad', 'Asia/Baghdad');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (740, 1, 'Timezones', 'Africa/Nairobi', 'Africa/Nairobi');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (741, 1, 'Timezones', 'Asia/Tbilisi', 'Asia/Tbilisi');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (742, 1, 'Timezones', 'Europe/Moscow', 'Europe/Moscow');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (743, 1, 'Timezones', 'Europe/Volgograd', 'Europe/Volgograd');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (744, 1, 'Timezones', 'Asia/Tehran', 'Asia/Tehran');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (745, 1, 'Timezones', 'Asia/Muscat', 'Asia/Muscat');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (746, 1, 'Timezones', 'Asia/Baku', 'Asia/Baku');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (747, 1, 'Timezones', 'Asia/Yerevan', 'Asia/Yerevan');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (748, 1, 'Timezones', 'Asia/Yekaterinburg', 'Asia/Yekaterinburg');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (749, 1, 'Timezones', 'Asia/Karachi', 'Asia/Karachi');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (750, 1, 'Timezones', 'Asia/Tashkent', 'Asia/Tashkent');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (751, 1, 'Timezones', 'Asia/Kolkata', 'Asia/Kolkata');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (752, 1, 'Timezones', 'Asia/Colombo', 'Asia/Colombo');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (753, 1, 'Timezones', 'Asia/Katmandu', 'Asia/Katmandu');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (754, 1, 'Timezones', 'Asia/Dhaka', 'Asia/Dhaka');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (755, 1, 'Timezones', 'Asia/Almaty', 'Asia/Almaty');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (756, 1, 'Timezones', 'Asia/Novosibirsk', 'Asia/Novosibirsk');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (757, 1, 'Timezones', 'Asia/Rangoon', 'Asia/Rangoon');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (758, 1, 'Timezones', 'Asia/Krasnoyarsk', 'Asia/Krasnoyarsk');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (759, 1, 'Timezones', 'Asia/Bangkok', 'Asia/Bangkok');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (760, 1, 'Timezones', 'Asia/Jakarta', 'Asia/Jakarta');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (761, 1, 'Timezones', 'Asia/Brunei', 'Asia/Brunei');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (762, 1, 'Timezones', 'Asia/Chongqing', 'Asia/Chongqing');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (763, 1, 'Timezones', 'Asia/Hong_Kong', 'Asia/Hong_Kong');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (764, 1, 'Timezones', 'Asia/Urumqi', 'Asia/Urumqi');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (765, 1, 'Timezones', 'Asia/Irkutsk', 'Asia/Irkutsk');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (766, 1, 'Timezones', 'Asia/Ulaanbaatar', 'Asia/Ulaanbaatar');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (767, 1, 'Timezones', 'Asia/Kuala_Lumpur', 'Asia/Kuala_Lumpur');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (768, 1, 'Timezones', 'Asia/Singapore', 'Asia/Singapore');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (769, 1, 'Timezones', 'Asia/Taipei', 'Asia/Taipei');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (770, 1, 'Timezones', 'Australia/Perth', 'Australia/Perth');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (771, 1, 'Timezones', 'Asia/Seoul', 'Asia/Seoul');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (772, 1, 'Timezones', 'Asia/Tokyo', 'Asia/Tokyo');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (773, 1, 'Timezones', 'Asia/Yakutsk', 'Asia/Yakutsk');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (774, 1, 'Timezones', 'Australia/Darwin', 'Australia/Darwin');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (775, 1, 'Timezones', 'Australia/Adelaide', 'Australia/Adelaide');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (776, 1, 'Timezones', 'Australia/Canberra', 'Australia/Canberra');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (777, 1, 'Timezones', 'Australia/Melbourne', 'Australia/Melbourne');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (778, 1, 'Timezones', 'Australia/Sydney', 'Australia/Sydney');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (779, 1, 'Timezones', 'Australia/Brisbane', 'Australia/Brisbane');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (780, 1, 'Timezones', 'Australia/Hobart', 'Australia/Hobart');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (781, 1, 'Timezones', 'Asia/Vladivostok', 'Asia/Vladivostok');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (782, 1, 'Timezones', 'Pacific/Guam', 'Pacific/Guam');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (783, 1, 'Timezones', 'Pacific/Port_Moresby', 'Pacific/Port_Moresby');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (784, 1, 'Timezones', 'Asia/Magadan', 'Asia/Magadan');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (785, 1, 'Timezones', 'Pacific/Fiji', 'Pacific/Fiji');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (786, 1, 'Timezones', 'Asia/Kamchatka', 'Asia/Kamchatka');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (787, 1, 'Timezones', 'Pacific/Auckland', 'Pacific/Auckland');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (788, 1, 'Timezones', 'Pacific/Tongatapu', 'Pacific/Tongatapu');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (789, 4, 'ContactMethods', 'Email: Primary', 'email_primary');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (790, 4, 'ContactMethods', 'Email: Alternate', 'email_alt');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (791, 4, 'ContactMethods', 'Web Site', 'url');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (792, 4, 'ContactMethods', 'Phone: Primary', 'phone_primary');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (793, 4, 'ContactMethods', 'Phone: Alternate', 'phone_alt');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (794, 4, 'ContactMethods', 'Phone: Fax', 'phone_fax');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (795, 4, 'ContactMethods', 'Phone: Mobile', 'phone_mobile');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (796, 4, 'ContactMethods', 'IM: Jabber', 'im_jabber');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (797, 4, 'ContactMethods', 'IM: ICQ', 'im_icq');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (798, 4, 'ContactMethods', 'IM: MSN', 'im_msn');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (799, 4, 'ContactMethods', 'IM: Yahoo', 'im_yahoo');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (800, 4, 'ContactMethods', 'IM: AOL', 'im_aol');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (801, 4, 'ContactMethods', 'IM: Skype', 'im_skype');
INSERT INTO `sysvals` (`sysval_id`, `sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (802, 4, 'ContactMethods', 'IM: Google', 'im_google');

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks`
-- 

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `task_id` int(10) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) DEFAULT NULL,
  `task_parent` int(10) DEFAULT '0',
  `task_milestone` tinyint(1) DEFAULT '0',
  `task_project` int(10) NOT NULL DEFAULT '0',
  `task_owner` int(10) NOT NULL DEFAULT '0',
  `task_start_date` datetime DEFAULT NULL,
  `task_duration` float unsigned DEFAULT '0',
  `task_duration_type` int(10) NOT NULL DEFAULT '1',
  `task_hours_worked` float unsigned NOT NULL DEFAULT '0',
  `task_end_date` datetime DEFAULT NULL,
  `task_status` int(10) DEFAULT '0',
  `task_priority` tinyint(4) DEFAULT '0',
  `task_percent_complete` tinyint(4) DEFAULT '0',
  `task_description` mediumtext,
  `task_target_budget` decimal(10,2) DEFAULT '0.00',
  `task_related_url` varchar(255) DEFAULT NULL,
  `task_creator` int(10) NOT NULL DEFAULT '0',
  `task_order` int(10) NOT NULL DEFAULT '0',
  `task_client_publish` tinyint(1) NOT NULL DEFAULT '0',
  `task_dynamic` tinyint(1) NOT NULL DEFAULT '0',
  `task_access` int(10) NOT NULL DEFAULT '0',
  `task_notify` int(10) NOT NULL DEFAULT '0',
  `task_departments` varchar(100) DEFAULT NULL,
  `task_contacts` varchar(100) DEFAULT NULL,
  `task_custom` longtext,
  `task_type` smallint(6) NOT NULL DEFAULT '0',
  `task_updator` int(10) NOT NULL DEFAULT '0',
  `task_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `task_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `task_dep_reset_dates` tinyint(1) DEFAULT '0',
  `task_represents_project` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`),
  KEY `idx_task1` (`task_start_date`),
  KEY `idx_task2` (`task_end_date`),
  KEY `task_name` (`task_name`),
  KEY `task_parent` (`task_parent`),
  KEY `task_project` (`task_project`),
  KEY `task_owner` (`task_owner`),
  KEY `task_start_date` (`task_start_date`),
  KEY `task_end_date` (`task_end_date`),
  KEY `task_status` (`task_status`),
  KEY `task_priority` (`task_priority`),
  KEY `task_creator` (`task_creator`),
  KEY `task_order` (`task_order`),
  KEY `task_type` (`task_type`),
  KEY `task_updator` (`task_updator`),
  KEY `task_represents_project` (`task_represents_project`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `tasks`
-- 

INSERT INTO `tasks` (`task_id`, `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_owner`, `task_start_date`, `task_duration`, `task_duration_type`, `task_hours_worked`, `task_end_date`, `task_status`, `task_priority`, `task_percent_complete`, `task_description`, `task_target_budget`, `task_related_url`, `task_creator`, `task_order`, `task_client_publish`, `task_dynamic`, `task_access`, `task_notify`, `task_departments`, `task_contacts`, `task_custom`, `task_type`, `task_updator`, `task_created`, `task_updated`, `task_dep_reset_dates`, `task_represents_project`) VALUES (1, 'Task #1', 1, 0, 1, 1, date_add(NOW(), INTERVAL 6 DAY), 40, 1, 3, date_add(NOW(), INTERVAL 10 DAY), 0, 0, 20, NULL, 0.00, '', 1, 0, 0, 0, 0, 1, NULL, NULL, NULL, 0, 0, NOW(), NOW(), 0, 0);
INSERT INTO `tasks` (`task_id`, `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_owner`, `task_start_date`, `task_duration`, `task_duration_type`, `task_hours_worked`, `task_end_date`, `task_status`, `task_priority`, `task_percent_complete`, `task_description`, `task_target_budget`, `task_related_url`, `task_creator`, `task_order`, `task_client_publish`, `task_dynamic`, `task_access`, `task_notify`, `task_departments`, `task_contacts`, `task_custom`, `task_type`, `task_updator`, `task_created`, `task_updated`, `task_dep_reset_dates`, `task_represents_project`) VALUES (2, 'Dynamic Task', 2, 0, 1, 1, NOW(), 142, 1, 0, date_add(NOW(), INTERVAL 22 DAY), 0, 0, 0, NULL, 0.00, '', 1, 0, 0, 1, 0, 1, NULL, NULL, NULL, 0, 0, NOW(), NOW(), 0, 0);
INSERT INTO `tasks` (`task_id`, `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_owner`, `task_start_date`, `task_duration`, `task_duration_type`, `task_hours_worked`, `task_end_date`, `task_status`, `task_priority`, `task_percent_complete`, `task_description`, `task_target_budget`, `task_related_url`, `task_creator`, `task_order`, `task_client_publish`, `task_dynamic`, `task_access`, `task_notify`, `task_departments`, `task_contacts`, `task_custom`, `task_type`, `task_updator`, `task_created`, `task_updated`, `task_dep_reset_dates`, `task_represents_project`) VALUES (3, 'First Child Task', 2, 0, 1, 1, NOW(), 112, 1, 0, date_add(NOW(), INTERVAL 18 DAY), 0, 0, 0, NULL, 0.00, NULL, 1, 0, 0, 0, 0, 1, NULL, NULL, NULL, 0, 0, NOW(), NOW(), 0, 0);
INSERT INTO `tasks` (`task_id`, `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_owner`, `task_start_date`, `task_duration`, `task_duration_type`, `task_hours_worked`, `task_end_date`, `task_status`, `task_priority`, `task_percent_complete`, `task_description`, `task_target_budget`, `task_related_url`, `task_creator`, `task_order`, `task_client_publish`, `task_dynamic`, `task_access`, `task_notify`, `task_departments`, `task_contacts`, `task_custom`, `task_type`, `task_updator`, `task_created`, `task_updated`, `task_dep_reset_dates`, `task_represents_project`) VALUES (4, 'Second Child', 2, 0, 1, 1, date_add(NOW(), INTERVAL 20 DAY), 30, 1, 0, date_add(NOW(), INTERVAL 22 DAY), 0, 0, 0, NULL, 0.00, NULL, 1, 0, 0, 31, 0, 1, NULL, NULL, NULL, 0, 0, NOW(), NOW(), 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_critical`
-- 

DROP TABLE IF EXISTS `tasks_critical`;
CREATE TABLE `tasks_critical` (
  `task_project` int(10) NOT NULL DEFAULT '0',
  `critical_task` int(10) DEFAULT NULL,
  `project_actual_end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`task_project`),
  KEY `task_project` (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `tasks_critical`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_problems`
-- 

DROP TABLE IF EXISTS `tasks_problems`;
CREATE TABLE `tasks_problems` (
  `task_project` int(10) NOT NULL DEFAULT '0',
  `task_log_problem` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`task_project`),
  KEY `task_project` (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `tasks_problems`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_sum`
-- 

DROP TABLE IF EXISTS `tasks_sum`;
CREATE TABLE `tasks_sum` (
  `task_project` int(10) NOT NULL DEFAULT '0',
  `total_tasks` int(6) DEFAULT NULL,
  `project_percent_complete` float DEFAULT NULL,
  `project_duration` float DEFAULT NULL,
  PRIMARY KEY (`task_project`),
  KEY `task_project` (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `tasks_sum`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_summy`
-- 

DROP TABLE IF EXISTS `tasks_summy`;
CREATE TABLE `tasks_summy` (
  `task_project` int(10) NOT NULL DEFAULT '0',
  `my_tasks` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`task_project`),
  KEY `task_project` (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `tasks_summy`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_total`
-- 

DROP TABLE IF EXISTS `tasks_total`;
CREATE TABLE `tasks_total` (
  `task_project` int(10) NOT NULL DEFAULT '0',
  `total_tasks` int(10) DEFAULT NULL,
  PRIMARY KEY (`task_project`),
  KEY `task_project` (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `tasks_total`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tasks_users`
-- 

DROP TABLE IF EXISTS `tasks_users`;
CREATE TABLE `tasks_users` (
  `task_project` int(10) NOT NULL DEFAULT '0',
  `user_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`task_project`),
  KEY `task_project` (`task_project`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `tasks_users`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `task_contacts`
-- 

DROP TABLE IF EXISTS `task_contacts`;
CREATE TABLE `task_contacts` (
  `task_id` int(10) NOT NULL DEFAULT '0',
  `contact_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`,`contact_id`),
  KEY `contact_id` (`contact_id`),
  KEY `task_id` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `task_contacts`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `task_departments`
-- 

DROP TABLE IF EXISTS `task_departments`;
CREATE TABLE `task_departments` (
  `task_id` int(10) NOT NULL DEFAULT '0',
  `department_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`,`department_id`),
  KEY `department_id` (`department_id`),
  KEY `task_id` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `task_departments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `task_dependencies`
-- 

DROP TABLE IF EXISTS `task_dependencies`;
CREATE TABLE `task_dependencies` (
  `dependencies_task_id` int(10) NOT NULL DEFAULT '0',
  `dependencies_req_task_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dependencies_task_id`,`dependencies_req_task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `task_dependencies`
-- 

INSERT INTO `task_dependencies` (`dependencies_task_id`, `dependencies_req_task_id`) VALUES (4, 3);

-- --------------------------------------------------------

-- 
-- Table structure for table `task_log`
-- 

DROP TABLE IF EXISTS `task_log`;
CREATE TABLE `task_log` (
  `task_log_id` int(10) NOT NULL AUTO_INCREMENT,
  `task_log_task` int(10) NOT NULL DEFAULT '0',
  `task_log_help_desk_id` int(10) NOT NULL DEFAULT '0',
  `task_log_name` varchar(255) DEFAULT NULL,
  `task_log_description` mediumtext,
  `task_log_creator` int(10) NOT NULL DEFAULT '0',
  `task_log_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `task_log_updator` int(10) NOT NULL DEFAULT '0',
  `task_log_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `task_log_hours` float NOT NULL DEFAULT '0',
  `task_log_date` date DEFAULT NULL,
  `task_log_costcode` varchar(8) NOT NULL DEFAULT '',
  `task_log_problem` tinyint(1) DEFAULT '0',
  `task_log_reference` tinyint(4) DEFAULT '0',
  `task_log_related_url` varchar(255) DEFAULT NULL,
  `task_log_project` int(10) unsigned NOT NULL DEFAULT '0',
  `task_log_company` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_log_id`),
  KEY `task_log_task` (`task_log_task`),
  KEY `task_log_creator` (`task_log_creator`),
  KEY `task_log_date` (`task_log_date`),
  KEY `task_log_costcode` (`task_log_costcode`),
  KEY `task_log_problem` (`task_log_problem`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `task_log`
-- 

INSERT INTO `task_log` (`task_log_id`, `task_log_task`, `task_log_help_desk_id`, `task_log_name`, `task_log_description`, `task_log_creator`, `task_log_created`, `task_log_updator`, `task_log_updated`, `task_log_hours`, `task_log_date`, `task_log_costcode`, `task_log_problem`, `task_log_reference`, `task_log_related_url`, `task_log_project`, `task_log_company`) VALUES (1, 1, 0, 'Task #1', 'I did some work on my task.', 1, NOW(), 0, NOW(), 3, NOW(), '', 0, 0, NULL, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_contact` int(10) NOT NULL DEFAULT '0',
  `user_username` varchar(255) NOT NULL DEFAULT '',
  `user_password` varchar(32) NOT NULL DEFAULT '',
  `user_parent` int(10) NOT NULL DEFAULT '0',
  `user_type` tinyint(3) NOT NULL DEFAULT '0',
  `user_signature` mediumtext,
  `user_empireint_special` int(1) NOT NULL DEFAULT '0',
  `user_department` int(10) unsigned NOT NULL DEFAULT '0',
  `user_company` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `user_username` (`user_username`),
  KEY `user_password` (`user_password`),
  KEY `user_parent` (`user_parent`),
  KEY `user_type` (`user_type`),
  KEY `user_company` (`user_company`),
  KEY `user_department` (`user_department`),
  KEY `user_contact` (`user_contact`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `users`
-- 

INSERT INTO `users` (`user_id`, `user_contact`, `user_username`, `user_password`, `user_parent`, `user_type`, `user_signature`, `user_empireint_special`, `user_department`, `user_company`) VALUES (1, 1, 'admin', '098f6bcd4621d373cade4e832627b4f6', 0, 1, '', 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `user_access_log`
-- 

DROP TABLE IF EXISTS `user_access_log`;
CREATE TABLE `user_access_log` (
  `user_access_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_ip` varchar(15) NOT NULL DEFAULT '',
  `date_time_in` datetime DEFAULT '0000-00-00 00:00:00',
  `date_time_out` datetime DEFAULT '0000-00-00 00:00:00',
  `date_time_last_action` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`user_access_log_id`),
  KEY `user_id` (`user_id`),
  KEY `date_time_in` (`date_time_in`),
  KEY `date_time_out` (`date_time_out`),
  KEY `date_time_last_action` (`date_time_last_action`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- 
-- Dumping data for table `user_access_log`
-- 

INSERT INTO `user_access_log` (`user_access_log_id`, `user_id`, `user_ip`, `date_time_in`, `date_time_out`, `date_time_last_action`) VALUES (16, 1, '::1', '2010-08-04 00:26:45', '0000-00-00 00:00:00', '2010-08-04 02:01:00');

-- --------------------------------------------------------

-- 
-- Table structure for table `user_events`
-- 

DROP TABLE IF EXISTS `user_events`;
CREATE TABLE `user_events` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `event_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`event_id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user_events`
-- 

INSERT INTO `user_events` (`user_id`, `event_id`) VALUES (1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `user_feeds`
-- 

DROP TABLE IF EXISTS `user_feeds`;
CREATE TABLE `user_feeds` (
  `feed_id` int(10) NOT NULL AUTO_INCREMENT,
  `feed_user` int(10) NOT NULL,
  `feed_token` varchar(255) NOT NULL,
  PRIMARY KEY (`feed_id`),
  KEY `feed_token` (`feed_token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `user_feeds`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user_preferences`
-- 

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `pref_user` varchar(12) NOT NULL DEFAULT '',
  `pref_name` varchar(72) NOT NULL DEFAULT '',
  `pref_value` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`pref_user`,`pref_name`),
  KEY `pref_user_2` (`pref_user`),
  KEY `pref_user` (`pref_user`),
  KEY `pref_name` (`pref_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'LGDATEFORMAT', '%B %d, %Y');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'LGDATEFORMAT', '%B %d, %Y');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'TIMEZONE', 'America/New_York');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TIMEZONE', 'America/Chicago');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('0', 'DAYLIGHTSAVINGS', '1');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'DAYLIGHTSAVINGS', '1');
INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`) VALUES ('1', 'TASKSEXPANDED', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `user_tasks`
-- 

DROP TABLE IF EXISTS `user_tasks`;
CREATE TABLE `user_tasks` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `user_type` tinyint(4) NOT NULL DEFAULT '0',
  `task_id` int(10) NOT NULL DEFAULT '0',
  `perc_assignment` int(10) NOT NULL DEFAULT '100',
  `user_task_priority` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`user_id`,`task_id`),
  KEY `index_ut_to_tasks` (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `task_id` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user_tasks`
-- 

INSERT INTO `user_tasks` (`user_id`, `user_type`, `task_id`, `perc_assignment`, `user_task_priority`) VALUES (1, 0, 1, 100, 0);
INSERT INTO `user_tasks` (`user_id`, `user_type`, `task_id`, `perc_assignment`, `user_task_priority`) VALUES (1, 0, 0, 100, 0);
INSERT INTO `user_tasks` (`user_id`, `user_type`, `task_id`, `perc_assignment`, `user_task_priority`) VALUES (1, 0, 2, 100, 0);
INSERT INTO `user_tasks` (`user_id`, `user_type`, `task_id`, `perc_assignment`, `user_task_priority`) VALUES (1, 0, 3, 100, 0);
INSERT INTO `user_tasks` (`user_id`, `user_type`, `task_id`, `perc_assignment`, `user_task_priority`) VALUES (1, 0, 4, 100, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `user_task_pin`
-- 

DROP TABLE IF EXISTS `user_task_pin`;
CREATE TABLE `user_task_pin` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `task_id` int(10) NOT NULL DEFAULT '0',
  `task_pinned` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`,`task_id`),
  KEY `user_id` (`user_id`),
  KEY `task_id` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `user_task_pin`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `w2pversion`
-- 

DROP TABLE IF EXISTS `w2pversion`;
CREATE TABLE `w2pversion` (
  `code_revision` int(10) unsigned NOT NULL DEFAULT '0',
  `code_version` varchar(10) NOT NULL DEFAULT '',
  `db_version` int(10) NOT NULL DEFAULT '0',
  `last_db_update` date NOT NULL DEFAULT '0000-00-00',
  `last_code_update` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `w2pversion`
-- 

INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (427, '1.0.0', 1, '2010-08-03', '2010-08-03');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 1, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (489, '1.0.0', 0, '0000-00-00', '2010-08-03');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 6, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 7, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 8, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 9, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 10, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 11, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '1.1.0', 12, '0000-00-00', '2010-08-03');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 12, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 13, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 14, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 15, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 16, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 17, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (989, '1.3.0', 18, '2010-08-03', '2010-08-03');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 18, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 19, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 20, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 21, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 22, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 23, '2010-08-03', '0000-00-00');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (1220, '2.0.0', 24, '2010-08-03', '2010-08-03');
INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`) VALUES (0, '', 24, '2010-08-03', '0000-00-00');
