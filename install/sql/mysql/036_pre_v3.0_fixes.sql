
-- Applying some database schema changes to allow for longer data types in the
--   text-based Custom Fields
--   Resolves http://bugs.web2project.net/view.php?id=940

ALTER TABLE  `custom_fields_values` CHANGE  `value_charvalue`  `value_charvalue`
    LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE  `custom_fields_values` DROP INDEX  `value_charvalue`;

ALTER TABLE  `custom_fields_values` ADD INDEX (  `value_charvalue` );

-- This is just a tweak to get rid of the deprecation notice in the Hooks subststem

UPDATE modules SET mod_main_class = 'CSmartSearch' WHERE mod_directory = 'smartsearch';