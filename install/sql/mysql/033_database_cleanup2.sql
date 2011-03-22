-- Minor database cleanups

ALTER TABLE `contacts` CHANGE `contact_email` `contact_email` VARCHAR( 255 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `contacts` CHANGE `contact_phone` `contact_phone` VARCHAR( 30 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';