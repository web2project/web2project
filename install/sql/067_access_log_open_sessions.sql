-- user_access_log.date_time_out marks when a login ended. Migrations 056/060
-- gave it a CURRENT_TIMESTAMP default, so every login was stored as already
-- ended: CAppUI::registerLogout() (which closes rows WHERE date_time_out IS
-- NULL) never matched, and the user list's online check could not tell open
-- logins from closed ones. An open login is NULL again.

ALTER TABLE `user_access_log` MODIFY `date_time_out` DATETIME NULL DEFAULT NULL;

-- Logins that still have a session are open.
UPDATE `user_access_log` SET `date_time_out` = NULL
  WHERE `user_access_log_id` IN (SELECT `session_user` FROM `sessions` WHERE `session_user` > 0);
