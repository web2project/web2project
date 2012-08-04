
-- Adds sequence numbers to the tables to make the iCalendar syncing work properly.

ALTER TABLE `events` ADD `event_sequence` INT( 10 ) NOT NULL DEFAULT '0';

ALTER TABLE `tasks` ADD `task_sequence` INT( 10 ) NOT NULL DEFAULT '0';