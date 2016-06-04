
-- Killing off old fields that break MySQL's strict mode because they default in invalid values

ALTER TABLE `projects` DROP `project_end_date_adjusted`;