-- Zero dates in user_access_log (#537). 056 cleaned this table, but a 4.x
-- site is already past 056 (db_version 62) and 070 left the table out, so a
-- '0000-00-00 00:00:00' written to a 4.x database survived the upgrade. A
-- zero date_time_out is neither an open login (NULL, since 067) nor a real
-- logout, so CAppUI::registerLogout() never closes it, and strict servers
-- (NO_ZERO_DATE) refuse any later ALTER on the table.
--
-- Each zero date gets the nearest real time from the same row:
--   date_time_in           <- date_time_out, else date_time_last_action,
--                             else the upgrade time
--   date_time_last_action  <- date_time_in
--   date_time_out          <- date_time_last_action (the login ended at its
--                             last action; NULL would reopen it)
--
-- Rows without zero dates are not touched, so running this again changes
-- nothing.

UPDATE `user_access_log` SET `date_time_in` = `date_time_out`
  WHERE CAST(`date_time_in` AS CHAR(20)) LIKE '0000-00-00%'
    AND `date_time_out` IS NOT NULL AND CAST(`date_time_out` AS CHAR(20)) NOT LIKE '0000-00-00%';

UPDATE `user_access_log` SET `date_time_in` = `date_time_last_action`
  WHERE CAST(`date_time_in` AS CHAR(20)) LIKE '0000-00-00%'
    AND `date_time_last_action` IS NOT NULL AND CAST(`date_time_last_action` AS CHAR(20)) NOT LIKE '0000-00-00%';

UPDATE `user_access_log` SET `date_time_in` = NOW()
  WHERE CAST(`date_time_in` AS CHAR(20)) LIKE '0000-00-00%';

UPDATE `user_access_log` SET `date_time_last_action` = `date_time_in`
  WHERE CAST(`date_time_last_action` AS CHAR(20)) LIKE '0000-00-00%';

UPDATE `user_access_log` SET `date_time_out` = `date_time_last_action`
  WHERE CAST(`date_time_out` AS CHAR(20)) LIKE '0000-00-00%';
