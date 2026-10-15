-- This prepares us to eventually kill off the md5-based hashing of passwords.

# 2026 Update - eliminating '0000-00-00 00:00:00' as values
# users.user_birthday only exists on installs converted from dotProject, so the
# cleanup runs only when the column is there (otherwise: "Unknown column").
SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'user_birthday') > 0,
    'UPDATE `users` SET `user_birthday` = NULL WHERE CAST(`user_birthday` AS CHAR(20)) = ''0000-00-00 00:00:00''',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

ALTER TABLE  `users` CHANGE  `user_password`  `user_password` VARCHAR( 255 ) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';