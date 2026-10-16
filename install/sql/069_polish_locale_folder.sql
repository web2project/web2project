-- The Polish translation folder was named "po" instead of the ISO 639-1 code
-- "pl", and is now locales/pl. The per-user Locale preference stores the
-- language key (pl_PL), which doesn't change, but a Host Locale or a Locale
-- preference set to the folder name would no longer match a language.

UPDATE `config` SET `config_value` = 'pl' WHERE `config_name` = 'host_locale' AND `config_value` = 'po';

UPDATE `user_preferences` SET `pref_value` = 'pl' WHERE `pref_name` = 'LOCALE' AND `pref_value` = 'po';
