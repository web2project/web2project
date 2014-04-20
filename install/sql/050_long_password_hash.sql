-- This prepares us to eventually kill off the md5-based hashing of passwords.

ALTER TABLE  `users` CHANGE  `user_password`  `user_password` VARCHAR( 255 ) CHARACTER
    SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';