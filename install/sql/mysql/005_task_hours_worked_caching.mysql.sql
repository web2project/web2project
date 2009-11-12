
-- This applies an update to fill in the total hours worked on a task from the
--   relevant task_log entries.  This resolves #187 which resolves #169.

UPDATE tasks SET task_hours_worked = (SELECT SUM(task_log_hours) 
	FROM task_log WHERE task_log_task = task_id);
