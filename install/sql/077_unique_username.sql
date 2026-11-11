-- users.user_username gets a unique index (#438). Until now only
-- CUser::isValid() checked for duplicates, so two creates at the same time
-- (self-registration, an LDAP user's first login) could add two users with
-- the same login, and login then used whichever row came back first.
--
-- Existing duplicates are kept apart first: the oldest user (lowest user_id)
-- keeps the name and each later one becomes "<name>-<user_id>", which is
-- unique because user_id is. Names are compared the way the index compares
-- them (the column's collation, so "Bob" and "bob" are the same name). Each
-- rename is written to the history table, so an administrator can find the
-- users who now log in with a new name:
--   SELECT * FROM history WHERE history_table = 'users'
--     AND history_description LIKE 'Migration 077%';
--
-- The old non-unique indexes are dropped; where one is missing the installer
-- ignores the "check that it exists" error. Running this again finds no
-- duplicates and changes nothing.

INSERT INTO `history` (`history_datetime`, `history_user`, `history_action`, `history_item`, `history_table`, `history_project`, `history_name`, `history_changes`, `history_description`)
  SELECT NOW(), 0, 'update', `u`.`user_id`, 'users', 0, `u`.`user_username`, '',
         CONCAT('Migration 077: username "', `u`.`user_username`, '" was also used by user ', `d`.`keep_id`, ', renamed to "', `u`.`user_username`, '-', `u`.`user_id`, '"')
  FROM `users` `u`
  INNER JOIN (SELECT `user_username` AS `name`, MIN(`user_id`) AS `keep_id`
              FROM `users` GROUP BY `user_username` HAVING COUNT(*) > 1) `d`
    ON `u`.`user_username` = `d`.`name` AND `u`.`user_id` <> `d`.`keep_id`;

UPDATE `users` `u`
  INNER JOIN (SELECT `user_username` AS `name`, MIN(`user_id`) AS `keep_id`
              FROM `users` GROUP BY `user_username` HAVING COUNT(*) > 1) `d`
    ON `u`.`user_username` = `d`.`name` AND `u`.`user_id` <> `d`.`keep_id`
  SET `u`.`user_username` = CONCAT(`u`.`user_username`, '-', `u`.`user_id`);

ALTER TABLE `users` DROP INDEX `idx_uid`;

ALTER TABLE `users` DROP INDEX `user_username`;

ALTER TABLE `users` ADD UNIQUE KEY `user_username` (`user_username`);
