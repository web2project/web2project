-- This deprecates an old table that is not used anymore. It's a dotproject relic

ALTER TABLE  `event_contacts` COMMENT = 'deprecated';

-- This handles converting a couple tables from their original character set to the proper utf8

# user_prefs_list only exists on installs converted from dotProject, so it is
# converted only when the table is there (otherwise: "Table ... doesn't exist").
SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'user_prefs_list') > 0,
    'ALTER TABLE `user_prefs_list` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;
ALTER TABLE `contacts_methods` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;