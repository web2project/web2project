-- This prepares us to eventually kill off the md5-based hashing of passwords.

# 2026 Update - eliminating '0000-00-00 00:00:00' as values
UPDATE `users` SET `user_birthday` = NULL WHERE CAST(`user_birthday` AS CHAR(20)) = '0000-00-00 00:00:00';

ALTER TABLE  `users` CHANGE  `user_password`  `user_password` VARCHAR( 255 ) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';