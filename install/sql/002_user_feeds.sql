
-- This adds the functionality required for user-based calendar feeds.
-- The table is utf8 (006 converts it to utf8 anyway): in a database whose
-- default is utf8mb4, as on MySQL 8, the unique key on the 255-character
-- feed_token is over MyISAM's 1000-byte limit.

CREATE TABLE IF NOT EXISTS `user_feeds` (
	`feed_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`feed_user` INT( 10 ) NOT NULL,
	`feed_token` VARCHAR( 255 ) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `user_feeds` ADD UNIQUE (`feed_token`);