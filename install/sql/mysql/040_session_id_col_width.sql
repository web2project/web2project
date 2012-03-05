
-- Increase the column width on the session_id
-- to be compatible with PHP 5.3 hash functions

ALTER TABLE `sessions` CHANGE `session_id` `session_id` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';