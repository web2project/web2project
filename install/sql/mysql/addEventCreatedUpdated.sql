--   added by opto

--   Since Revision ??? code is being added to control events creation dates and creators 
--   as well as last edition and editor. 
--   This update adds the missing fields on a w2P setup.

--   The event_updator is added here for consistency with projects and tasks, 
--   on the other hand, currently it is not updated in  those tables (12-04-05, opto) upon store
ALTER TABLE `events` ADD  `event_creator` int(10) NOT NULL DEFAULT '0';

ALTER TABLE `events` ADD  `event_updator` int(10) NOT NULL DEFAULT '0';

ALTER TABLE `events` ADD `event_created` DATETIME NOT NULL default '0000-00-00 00:00:00';

ALTER TABLE `events` ADD `event_updated` DATETIME NOT NULL default '0000-00-00 00:00:00';
