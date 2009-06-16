
-- This applies an update to fill in then total hours worked on a task from the
--   relevant task_log entries.  This resolves #187 which resolves #169.
update tasks set task_hours_worked = (select sum(task_log_hours) from task_log where task_log_task = task_id);