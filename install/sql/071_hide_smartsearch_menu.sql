-- The Global Search box in the header posts straight to SmartSearch, so the
-- module no longer needs its own entry in the navigation. This only hides the
-- menu entry; access is still governed by permissions, and an admin can show
-- it again from System Admin > View Modules.

UPDATE `modules` SET `mod_ui_active` = 0 WHERE `mod_directory` = 'smartsearch';
