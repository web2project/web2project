
-- This applies an update to fill in the project_parent and 
--   project_original_parent on a project based on their own project_id.

UPDATE projects SET project_parent = project_id,
    project_original_parent = project_id
  WHERE project_parent = 0 AND project_original_parent = 0;