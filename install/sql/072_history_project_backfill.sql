-- history.history_project was never filled (issue #525): addHistory() takes a
-- project id, but no caller passed one. From 5.0 the save and delete hooks
-- pass the record's project, and a project's History tab lists every entry
-- with its history_project. This fills the column for the entries written
-- before, from the records they describe.
--
-- Only rows still at 0 are touched, so running this again changes nothing.
-- Entries for records that have since been deleted can't be matched and stay
-- at 0, except deleted projects themselves (their id is the history_item).

ALTER TABLE `history` ADD INDEX `history_project` (`history_project`);

-- The project's own entries.
UPDATE `history` SET `history_project` = `history_item`
  WHERE `history_table` = 'projects' AND `history_project` = 0 AND `history_item` > 0;

UPDATE `history` h JOIN `tasks` t ON t.`task_id` = h.`history_item`
  SET h.`history_project` = t.`task_project`
  WHERE h.`history_table` = 'tasks' AND h.`history_project` = 0 AND t.`task_project` > 0;

-- Task logs belong to their task's project (task_log_project is never filled).
UPDATE `history` h JOIN `task_log` tl ON tl.`task_log_id` = h.`history_item`
  JOIN `tasks` t ON t.`task_id` = tl.`task_log_task`
  SET h.`history_project` = t.`task_project`
  WHERE h.`history_table` = 'task_log' AND h.`history_project` = 0 AND t.`task_project` > 0;

UPDATE `history` h JOIN `files` f ON f.`file_id` = h.`history_item`
  SET h.`history_project` = f.`file_project`
  WHERE h.`history_table` = 'files' AND h.`history_project` = 0 AND f.`file_project` > 0;

UPDATE `history` h JOIN `links` l ON l.`link_id` = h.`history_item`
  SET h.`history_project` = l.`link_project`
  WHERE h.`history_table` = 'links' AND h.`history_project` = 0 AND l.`link_project` > 0;

UPDATE `history` h JOIN `events` e ON e.`event_id` = h.`history_item`
  SET h.`history_project` = e.`event_project`
  WHERE h.`history_table` = 'events' AND h.`history_project` = 0 AND e.`event_project` > 0;

UPDATE `history` h JOIN `forums` fo ON fo.`forum_id` = h.`history_item`
  SET h.`history_project` = fo.`forum_project`
  WHERE h.`history_table` = 'forums' AND h.`history_project` = 0 AND fo.`forum_project` > 0;

-- Forum messages belong to their forum's project.
UPDATE `history` h JOIN `forum_messages` fm ON fm.`message_id` = h.`history_item`
  JOIN `forums` fo ON fo.`forum_id` = fm.`message_forum`
  SET h.`history_project` = fo.`forum_project`
  WHERE h.`history_table` = 'forum_messages' AND h.`history_project` = 0 AND fo.`forum_project` > 0;

UPDATE `history` h JOIN `contacts` c ON c.`contact_id` = h.`history_item`
  SET h.`history_project` = c.`contact_project`
  WHERE h.`history_table` = 'contacts' AND h.`history_project` = 0 AND c.`contact_project` > 0;
