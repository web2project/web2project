
-- We have never used this parameter in any way, shape, or form.
DELETE FROM `user_preferences` WHERE `pref_name` = 'DAYLIGHTSAVINGS';

-- Older versions could store a default preference with pref_user '' as well
-- as '0'. Both read as user 0, so making the column an INT below turns such a
-- pair into a duplicate key ("Duplicate entry '0-TASKSEXPANDED'"). Keep the
-- '0' row, which is what the preferences page saves and shows; a '' row on its
-- own just becomes 0. Once the column is an INT the two values are equal, so
-- this deletes nothing if the migration runs again.
DELETE p1 FROM `user_preferences` p1
  JOIN `user_preferences` p0 ON p0.`pref_name` = p1.`pref_name` AND p0.`pref_user` = '0'
  WHERE p1.`pref_user` = '' AND p1.`pref_user` <> p0.`pref_user`;

-- The `pref_user` field was previously defined as a varchar(12) which makes *no* sense..
ALTER TABLE  `user_preferences` CHANGE  `pref_user`  `pref_user` INT( 10 ) NOT NULL DEFAULT  '0';

-- We're adding this for later, it's not used yet.
ALTER TABLE  `projects` ADD  `project_shortname` VARCHAR( 10 ) NOT NULL DEFAULT  '' AFTER  `project_name`;