
-- This applies an update to fill in the percent complete on a project based on
--   the individual tasks' percent complete.

UPDATE projects SET project_parent = project_id,
    project_original_parent = project_id
  WHERE project_parent = 0 AND project_original_parent = 0;