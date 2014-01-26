
-- Applying some database schema changes to allow for longer data types in the
--   text-based Custom Fields
--   Resolves http://bugs.web2project.net/view.php?id=940

ALTER TABLE  `custom_fields_values` CHANGE  `value_charvalue`  `value_charvalue`
TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE  `custom_fields_values` DROP INDEX  `value_charvalue`;

ALTER TABLE  `custom_fields_values` ADD INDEX (  `value_charvalue` );

-- This is just a tweak to get rid of the deprecation notice in the Hooks subsystem

UPDATE modules SET mod_main_class = 'CSmartSearch' WHERE mod_directory = 'smartsearch';


--  This begins the database changes for the new budgeting system.

ALTER TABLE `billingcode` ADD `billingcode_company` INT( 10 ) NOT NULL DEFAULT '0';
ALTER TABLE `billingcode` CHANGE  `company_id`  `company_id` BIGINT( 20 ) NOT NULL DEFAULT  '0' COMMENT  'deprecated';
UPDATE `billingcode` SET `billingcode_company` = `company_id`;

ALTER TABLE `billingcode` ADD `billingcode_category` VARCHAR( 50 ) NOT NULL DEFAULT '';

INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) 
    VALUES(1, 'BudgetCategory', 'Not Specified', '0');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) 
    VALUES(1, 'BudgetCategory', 'Labor', '1');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) 
    VALUES(1, 'BudgetCategory', 'Travel', '2');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) 
    VALUES(1, 'BudgetCategory', 'Licensing', '3');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) 
    VALUES(1, 'BudgetCategory', 'Software', '4');
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) 
    VALUES(1, 'BudgetCategory', 'Administrative', '5');

CREATE TABLE IF NOT EXISTS `budgets` (
  `budget_id` int(10) NOT NULL AUTO_INCREMENT,
  `budget_company` int(10) NOT NULL DEFAULT '0',
  `budget_dept` int(10) NOT NULL DEFAULT '0',
  `budget_start_date` date DEFAULT NULL,
  `budget_end_date` date DEFAULT NULL,
  `budget_amount` decimal(10,2) NOT NULL,
  `budget_category` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`budget_id`),
  KEY `budget_start_date` (`budget_start_date`),
  KEY `budget_end_date` (`budget_end_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `budgets_assigned` (
  `budget_id` int(10) NOT NULL AUTO_INCREMENT,
  `budget_project` int(10) NOT NULL,
  `budget_task` int(10) NOT NULL,
  `budget_category` varchar(50) NOT NULL DEFAULT '',
  `budget_amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`budget_id`),
  KEY `budget_project` (`budget_project`),
  KEY `budget_task` (`budget_task`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;