-- task_log.task_log_project has been in the schema since the first install
-- script but was never written, so it is 0 on every row. From 5.0 a task log
-- takes its task's project when it is stored, and moving a task to another
-- project moves its logs too. This fills the column for the existing logs.
--
-- The ADD COLUMN only matters on databases that never got the column. Where
-- it exists, the installer ignores the "Duplicate column name" error.
-- Running this again changes nothing.

ALTER TABLE `task_log` ADD COLUMN `task_log_project` int(10) unsigned NOT NULL default '0';

UPDATE `task_log` tl JOIN `tasks` t ON t.`task_id` = tl.`task_log_task`
  SET tl.`task_log_project` = t.`task_project`
  WHERE tl.`task_log_project` <> t.`task_project`;
