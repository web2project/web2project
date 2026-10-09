-- The user interface style is fixed to the Bootstrap theme, so the
-- default style setting and the per-user style preference are no longer used.

DELETE FROM `config` WHERE `config_name` = 'host_style';

DELETE FROM `user_preferences` WHERE `pref_name` = 'UISTYLE';
