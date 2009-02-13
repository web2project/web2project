
CREATE TABLE `user_feeds` (
	`feed_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`feed_user` INT( 10 ) NOT NULL,
	`feed_token` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM ;

ALTER TABLE `user_feeds` ADD UNIQUE (`feed_token`);