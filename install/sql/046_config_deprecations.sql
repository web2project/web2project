
-- We have never used this parameter in any way, shape, or form.
DELETE FROM `user_preferences` WHERE `pref_name` = 'DAYLIGHTSAVINGS';

-- The `pref_user` field was previously defined as a varchar(12) which makes *no* sense..
ALTER TABLE  `user_preferences` CHANGE  `pref_user`  `pref_user` INT( 10 ) NOT NULL DEFAULT  '0'

-- We're adding this for later, it's not used yet.
ALTER TABLE  `projects` ADD  `project_shortname` VARCHAR( 10 ) NOT NULL DEFAULT  '' AFTER  `project_name`