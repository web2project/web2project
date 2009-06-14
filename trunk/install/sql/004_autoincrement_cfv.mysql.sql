
-- This applies an auto increment to the primary key in order to close issue #145
ALTER TABLE `custom_fields_values` CHANGE `value_id` `value_id` INT( 11 ) NULL AUTO_INCREMENT  