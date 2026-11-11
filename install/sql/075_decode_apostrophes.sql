-- On PHP 8.1+ bind() encoded ' as &#039; and w2PHTMLDecode() (ENT_COMPAT)
-- left it encoded, so every apostrophe saved through a form since the
-- switch to PHP 8.1 was stored as &#039; (#556). The code now decodes
-- with ENT_QUOTES; this turns the stored entities back into apostrophes
-- in the text columns that forms write.
--
-- Only rows containing &#039; are touched, so running this again changes
-- nothing.

UPDATE `billingcode` SET `billingcode_name` = REPLACE(`billingcode_name`, '&#039;', CHAR(39)) WHERE `billingcode_name` LIKE '%&#039;%';
UPDATE `billingcode` SET `billingcode_desc` = REPLACE(`billingcode_desc`, '&#039;', CHAR(39)) WHERE `billingcode_desc` LIKE '%&#039;%';

UPDATE `companies` SET `company_name` = REPLACE(`company_name`, '&#039;', CHAR(39)) WHERE `company_name` LIKE '%&#039;%';
UPDATE `companies` SET `company_description` = REPLACE(`company_description`, '&#039;', CHAR(39)) WHERE `company_description` LIKE '%&#039;%';
UPDATE `companies` SET `company_address1` = REPLACE(`company_address1`, '&#039;', CHAR(39)) WHERE `company_address1` LIKE '%&#039;%';
UPDATE `companies` SET `company_address2` = REPLACE(`company_address2`, '&#039;', CHAR(39)) WHERE `company_address2` LIKE '%&#039;%';
UPDATE `companies` SET `company_city` = REPLACE(`company_city`, '&#039;', CHAR(39)) WHERE `company_city` LIKE '%&#039;%';
UPDATE `companies` SET `company_state` = REPLACE(`company_state`, '&#039;', CHAR(39)) WHERE `company_state` LIKE '%&#039;%';

UPDATE `contacts` SET `contact_first_name` = REPLACE(`contact_first_name`, '&#039;', CHAR(39)) WHERE `contact_first_name` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_last_name` = REPLACE(`contact_last_name`, '&#039;', CHAR(39)) WHERE `contact_last_name` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_display_name` = REPLACE(`contact_display_name`, '&#039;', CHAR(39)) WHERE `contact_display_name` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_title` = REPLACE(`contact_title`, '&#039;', CHAR(39)) WHERE `contact_title` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_job` = REPLACE(`contact_job`, '&#039;', CHAR(39)) WHERE `contact_job` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_notes` = REPLACE(`contact_notes`, '&#039;', CHAR(39)) WHERE `contact_notes` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_address1` = REPLACE(`contact_address1`, '&#039;', CHAR(39)) WHERE `contact_address1` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_address2` = REPLACE(`contact_address2`, '&#039;', CHAR(39)) WHERE `contact_address2` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_city` = REPLACE(`contact_city`, '&#039;', CHAR(39)) WHERE `contact_city` LIKE '%&#039;%';
UPDATE `contacts` SET `contact_state` = REPLACE(`contact_state`, '&#039;', CHAR(39)) WHERE `contact_state` LIKE '%&#039;%';

UPDATE `contacts_methods` SET `method_value` = REPLACE(`method_value`, '&#039;', CHAR(39)) WHERE `method_value` LIKE '%&#039;%';

UPDATE `custom_fields_values` SET `value_charvalue` = REPLACE(`value_charvalue`, '&#039;', CHAR(39)) WHERE `value_charvalue` LIKE '%&#039;%';

UPDATE `departments` SET `dept_name` = REPLACE(`dept_name`, '&#039;', CHAR(39)) WHERE `dept_name` LIKE '%&#039;%';
UPDATE `departments` SET `dept_desc` = REPLACE(`dept_desc`, '&#039;', CHAR(39)) WHERE `dept_desc` LIKE '%&#039;%';
UPDATE `departments` SET `dept_address1` = REPLACE(`dept_address1`, '&#039;', CHAR(39)) WHERE `dept_address1` LIKE '%&#039;%';
UPDATE `departments` SET `dept_address2` = REPLACE(`dept_address2`, '&#039;', CHAR(39)) WHERE `dept_address2` LIKE '%&#039;%';
UPDATE `departments` SET `dept_city` = REPLACE(`dept_city`, '&#039;', CHAR(39)) WHERE `dept_city` LIKE '%&#039;%';
UPDATE `departments` SET `dept_state` = REPLACE(`dept_state`, '&#039;', CHAR(39)) WHERE `dept_state` LIKE '%&#039;%';

UPDATE `events` SET `event_name` = REPLACE(`event_name`, '&#039;', CHAR(39)) WHERE `event_name` LIKE '%&#039;%';
UPDATE `events` SET `event_title` = REPLACE(`event_title`, '&#039;', CHAR(39)) WHERE `event_title` LIKE '%&#039;%';
UPDATE `events` SET `event_description` = REPLACE(`event_description`, '&#039;', CHAR(39)) WHERE `event_description` LIKE '%&#039;%';
UPDATE `events` SET `event_location` = REPLACE(`event_location`, '&#039;', CHAR(39)) WHERE `event_location` LIKE '%&#039;%';

UPDATE `files` SET `file_name` = REPLACE(`file_name`, '&#039;', CHAR(39)) WHERE `file_name` LIKE '%&#039;%';
UPDATE `files` SET `file_description` = REPLACE(`file_description`, '&#039;', CHAR(39)) WHERE `file_description` LIKE '%&#039;%';
UPDATE `files` SET `file_co_reason` = REPLACE(`file_co_reason`, '&#039;', CHAR(39)) WHERE `file_co_reason` LIKE '%&#039;%';

UPDATE `file_folders` SET `file_folder_name` = REPLACE(`file_folder_name`, '&#039;', CHAR(39)) WHERE `file_folder_name` LIKE '%&#039;%';
UPDATE `file_folders` SET `file_folder_description` = REPLACE(`file_folder_description`, '&#039;', CHAR(39)) WHERE `file_folder_description` LIKE '%&#039;%';

UPDATE `forums` SET `forum_name` = REPLACE(`forum_name`, '&#039;', CHAR(39)) WHERE `forum_name` LIKE '%&#039;%';
UPDATE `forums` SET `forum_description` = REPLACE(`forum_description`, '&#039;', CHAR(39)) WHERE `forum_description` LIKE '%&#039;%';

UPDATE `forum_messages` SET `message_title` = REPLACE(`message_title`, '&#039;', CHAR(39)) WHERE `message_title` LIKE '%&#039;%';
UPDATE `forum_messages` SET `message_body` = REPLACE(`message_body`, '&#039;', CHAR(39)) WHERE `message_body` LIKE '%&#039;%';

UPDATE `history` SET `history_name` = REPLACE(`history_name`, '&#039;', CHAR(39)) WHERE `history_name` LIKE '%&#039;%';
UPDATE `history` SET `history_description` = REPLACE(`history_description`, '&#039;', CHAR(39)) WHERE `history_description` LIKE '%&#039;%';

UPDATE `links` SET `link_name` = REPLACE(`link_name`, '&#039;', CHAR(39)) WHERE `link_name` LIKE '%&#039;%';
UPDATE `links` SET `link_description` = REPLACE(`link_description`, '&#039;', CHAR(39)) WHERE `link_description` LIKE '%&#039;%';

UPDATE `projects` SET `project_name` = REPLACE(`project_name`, '&#039;', CHAR(39)) WHERE `project_name` LIKE '%&#039;%';
UPDATE `projects` SET `project_short_name` = REPLACE(`project_short_name`, '&#039;', CHAR(39)) WHERE `project_short_name` LIKE '%&#039;%';
UPDATE `projects` SET `project_description` = REPLACE(`project_description`, '&#039;', CHAR(39)) WHERE `project_description` LIKE '%&#039;%';
UPDATE `projects` SET `project_status_comment` = REPLACE(`project_status_comment`, '&#039;', CHAR(39)) WHERE `project_status_comment` LIKE '%&#039;%';
UPDATE `projects` SET `project_location` = REPLACE(`project_location`, '&#039;', CHAR(39)) WHERE `project_location` LIKE '%&#039;%';

UPDATE `resources` SET `resource_name` = REPLACE(`resource_name`, '&#039;', CHAR(39)) WHERE `resource_name` LIKE '%&#039;%';
UPDATE `resources` SET `resource_description` = REPLACE(`resource_description`, '&#039;', CHAR(39)) WHERE `resource_description` LIKE '%&#039;%';
UPDATE `resources` SET `resource_note` = REPLACE(`resource_note`, '&#039;', CHAR(39)) WHERE `resource_note` LIKE '%&#039;%';

UPDATE `tasks` SET `task_name` = REPLACE(`task_name`, '&#039;', CHAR(39)) WHERE `task_name` LIKE '%&#039;%';
UPDATE `tasks` SET `task_description` = REPLACE(`task_description`, '&#039;', CHAR(39)) WHERE `task_description` LIKE '%&#039;%';

UPDATE `task_log` SET `task_log_name` = REPLACE(`task_log_name`, '&#039;', CHAR(39)) WHERE `task_log_name` LIKE '%&#039;%';
UPDATE `task_log` SET `task_log_description` = REPLACE(`task_log_description`, '&#039;', CHAR(39)) WHERE `task_log_description` LIKE '%&#039;%';

UPDATE `users` SET `user_signature` = REPLACE(`user_signature`, '&#039;', CHAR(39)) WHERE `user_signature` LIKE '%&#039;%';
