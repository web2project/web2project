
-- Updated the database fields because we really only care about the start and end *dates* not also the times.

ALTER TABLE `projects` CHANGE `project_start_date` `project_start_date` DATE NULL DEFAULT NULL;
ALTER TABLE `projects` CHANGE `project_end_date` `project_end_date` DATE NULL DEFAULT NULL;