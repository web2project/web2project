-- Minor database cleanups

-- Each column becomes NOT NULL DEFAULT ''. On non-strict servers the ALTER
-- turns existing NULLs into '', but strict servers (MySQL 5.7+, MariaDB 10.2+)
-- refuse instead, so set them to '' first.
UPDATE `contacts` SET `contact_email` = '' WHERE `contact_email` IS NULL;
ALTER TABLE `contacts` CHANGE `contact_email` `contact_email` VARCHAR( 255 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

UPDATE `contacts` SET `contact_phone` = '' WHERE `contact_phone` IS NULL;
ALTER TABLE `contacts` CHANGE `contact_phone` `contact_phone` VARCHAR( 30 )
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';