
-- This applies a database change to allow project_location to allow a blank value.
ALTER TABLE `projects` CHANGE `project_location` `project_location` VARCHAR(255) DEFAULT '' NOT NULL;