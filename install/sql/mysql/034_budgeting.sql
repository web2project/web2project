
ALTER TABLE `billingcode` CHANGE `company_id` `billingcode_company` INT( 10 ) NOT NULL DEFAULT '0';
ALTER TABLE `billingcode` ADD `billingcode_category` VARCHAR( 50 ) NOT NULL DEFAULT '';

INSERT INTO sysvals (sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES
    (1, 'BudgetCategory', 'Consulting', 'consulting'),
    (1, 'BudgetCategory', 'Hardware', 'hardware'),
    (1, 'BudgetCategory', 'Licenses', 'licenses'),
    (1, 'BudgetCategory', 'Permits', 'permits'),
    (1, 'BudgetCategory', 'Travel', 'travel');

CREATE TABLE `budgets` (
	`budget_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`budget_start_date` DATE NULL DEFAULT NULL ,
	`budget_end_date` DATE NULL DEFAULT NULL ,
	`budget_amount` DECIMAL( 10, 2 ) NOT NULL ,
	`budget_category` VARCHAR( 50 ) NOT NULL DEFAULT ''
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `budgets` ADD `budget_company` INT( 10 ) NOT NULL DEFAULT '0' AFTER `budget_id`;
ALTER TABLE `budgets` ADD `budget_dept`    INT( 10 ) NOT NULL DEFAULT '0' AFTER `budget_company`;

ALTER TABLE `budgets` ADD INDEX ( `budget_start_date` );
ALTER TABLE `budgets` ADD INDEX ( `budget_end_date` );

CREATE TABLE `budgets_assigned` (
    `budget_id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
    `budget_project` INT( 10 ) NOT NULL ,
    `budget_task` INT( 10 ) NOT NULL ,
    `budget_category` VARCHAR( 50 ) NOT NULL DEFAULT '',
    `budget_amount` DECIMAL( 10, 2 ) NOT NULL ,
    PRIMARY KEY ( `budget_id` ) ,
    INDEX ( `budget_project` ),
    INDEX ( `budget_task` )
) ENGINE = InnoDB;


