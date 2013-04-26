-- Change the files table to add a 'file_company' column

ALTER TABLE `files` ADD COLUMN `file_company` INT(10) NOT NULL DEFAULT 0 AFTER `file_project`;

-- Fill the new column for already existing files

UPDATE `files` SET `file_company`=(SELECT `project_company` FROM `projects` WHERE `project_id`=`file_project`);