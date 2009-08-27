
-- This adds the necessary data to the database for making the
-- shortname required for projects.

INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`,`sysval_value_id`) VALUES ('1', 'ProjectRequiredFields', '<1', 'f.project_short_name.value.length');
