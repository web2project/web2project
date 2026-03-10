-- Minor database cleanups

UPDATE `contacts` SET `contact_email` = NULL WHERE LENGTH(`contact_email`) = 0;
ALTER TABLE `contacts` CHANGE `contact_email` `contact_email` VARCHAR( 255 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

UPDATE `contacts` SET `contact_phone` = NULL WHERE LENGTH(`contact_phone`) = 0;
ALTER TABLE `contacts` CHANGE `contact_phone` `contact_phone` VARCHAR( 30 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';