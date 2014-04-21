-- This deprecates an old table that is not used anymore. It's a dotproject relic

ALTER TABLE  `event_contacts` COMMENT = 'deprecated';

-- This handles converting a couple tables from their original character set to the proper utf8

ALTER TABLE `user_prefs_list` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `contacts_methods` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;