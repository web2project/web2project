
-- Some missing user preferences

INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`)
VALUES
(0, 'TASKSEXPANDED', '0');

-- This query should add the TASKSEXPANDED preference to all the users
-- who don't have it yet!
-- Uses MySQLs INSERT INTO ... SELECT syntax
-- http://dev.mysql.com/doc/refman/5.0/en/ansi-diff-select-into-table.html

INSERT INTO `user_preferences` (`pref_user`, `pref_name`, `pref_value`)
SELECT `user_id`, 'TASKSEXPANDED', '0' FROM `users`
WHERE `user_id` NOT IN (
    SELECT `pref_user` FROM `user_preferences` WHERE `pref_name` = 'TASKSEXPANDED'
);