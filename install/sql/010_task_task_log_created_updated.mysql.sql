
-- Since Revision 580 code is being added to control projects/tasks/tasklogs creation dates and creators 
--   as well as last edition and editor. 
--   This update adds the missing fields on a w2P setup.

ALTER TABLE `tasks` ADD `task_created` datetime NOT NULL default '0000-00-00 00:00:00';

ALTER TABLE `tasks` ADD `task_updated` datetime NOT NULL default '0000-00-00 00:00:00';

ALTER TABLE `task_log` ADD `task_log_created` datetime NOT NULL default '0000-00-00 00:00:00';

ALTER TABLE `task_log` ADD `task_log_updator` int(10) NOT NULL default '0';

ALTER TABLE `task_log` ADD `task_log_updated` datetime NOT NULL default '0000-00-00 00:00:00';