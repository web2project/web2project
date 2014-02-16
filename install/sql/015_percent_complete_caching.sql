
-- This applies an update to fill in the percent complete on a project based on
--   the individual tasks' percent complete.

UPDATE projects SET project_percent_complete = (
  SELECT
    SUM(t1.task_duration * t1.task_percent_complete *
      IF(t1.task_duration_type = 24,
        (SELECT config_value FROM config where config_name = 'daily_working_hours'),
      t1.task_duration_type))
    /
    SUM(t1.task_duration *
      IF(t1.task_duration_type = 24,
        (SELECT config_value FROM config where config_name = 'daily_working_hours'),
      t1.task_duration_type)) AS project_percent_complete
    FROM `tasks`
    INNER JOIN `tasks` AS t1 ON tasks.task_project = t1.task_project
    WHERE t1.task_id = t1.task_parent AND tasks.task_project = project_id
    GROUP BY tasks.task_project
);