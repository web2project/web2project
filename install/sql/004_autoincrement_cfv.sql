
-- This applies an auto increment to the primary key in order to close issue #145
-- NOT NULL: a primary key column can't be NULL. MySQL 5 and MariaDB quietly
-- made it NOT NULL; MySQL 8 refuses the statement.
ALTER TABLE `custom_fields_values` CHANGE `value_id` `value_id` 
	INT( 11 ) NOT NULL AUTO_INCREMENT;  
