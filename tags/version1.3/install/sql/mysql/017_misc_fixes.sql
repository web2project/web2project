
-- This updates the contact_fax and contact_aol to allow null and remove
--   the bad (zero) values.

ALTER TABLE `contacts` CHANGE `contact_aol` `contact_aol` VARCHAR( 30 )
  CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `contacts` CHANGE `contact_fax` `contact_fax` VARCHAR( 30 )
  CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

UPDATE `contacts` SET `contact_aol` = NULL WHERE `contact_aol` = 0;
UPDATE `contacts` SET `contact_fax` = NULL WHERE `contact_fax` = 0;

UPDATE `modules` SET `mod_main_class` = 'CReport' WHERE `mod_directory` = 'reports';

-- This applies an insert to create the long date format to all users

INSERT INTO user_preferences(pref_user, pref_name, pref_value)
select distinct pref_user, 'LGDATEFORMAT','%B %d, %Y' from user_preferences;