
--   added by opto

--   Since Revision ??? code is being added to control events creation dates 
--   and creators as well as last edition and editor. 
--   This update adds the missing fields on a w2P setup.

--   The event_updator is added here for consistency with projects and tasks, 
--   on the other hand, currently it is not updated in  those tables (12-04-05, opto) upon store

--   Set created, updated default to 1999-12-31 in order not to break any ical feeds with invalid dates
ALTER TABLE `events` ADD `event_creator` int(10) NOT NULL DEFAULT '0';

ALTER TABLE `events` ADD `event_updator` int(10) NOT NULL DEFAULT '0';

ALTER TABLE `events` ADD `event_created` DATETIME NOT NULL default '1999-12-31 00:00:00';

ALTER TABLE `events` ADD `event_updated` DATETIME NOT NULL default '1999-12-31 00:00:00';

--  added this to bring the module in line with our naming conventions
ALTER TABLE `events` ADD `event_name` VARCHAR( 255 ) NOT NULL AFTER  `event_id`;

UPDATE events SET event_name = event_title;

ALTER TABLE `events` CHANGE `event_title` `event_title` 
    VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '' COMMENT  'deprecated';
UPDATE `modules` SET `permissions_item_label` =  'event_name' WHERE  `mod_directory` =  'calendar';