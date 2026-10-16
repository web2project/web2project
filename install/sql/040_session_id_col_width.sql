
-- Increase the column width on the session_id
-- to be compatible with PHP 5.3 hash functions

# 2026 Update - eliminating '0000-00-00 00:00:00' as defaults
ALTER TABLE `sessions`
    MODIFY `session_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `session_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `sessions` CHANGE `session_id` `session_id` VARCHAR( 128 ) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';