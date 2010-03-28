
-- This is a list of timezones supported by the system. Eventually, we should
--   be able to deprecate this in favor of core php functionality availble in
--   5.3. PHP 5.3 should be evaluated as a potential minimum version for
--   web2project v3.0 in June 2011.

INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'Timezones', 'America/New_York', -18000);
INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES
	(1, 'Timezones', 'America/Chicago', -24000);


-- This applies an insert to fill in a default timezone and daylight savings setting to all users
INSERT INTO user_preferences(pref_user, pref_name, pref_value)
    SELECT DISTINCT pref_user, 'TIMEZONE','America/New_York' FROM user_preferences;
INSERT INTO user_preferences(pref_user, pref_name, pref_value)
    SELECT DISTINCT pref_user, 'DAYLIGHTSAVINGS', 1 FROM user_preferences;