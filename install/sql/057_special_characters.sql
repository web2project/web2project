
-- Updated the system to support utf8 character sets

ALTER TABLE `contacts_methods` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `module_config` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `budgets` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `budgets_assigned` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `email_templates` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
