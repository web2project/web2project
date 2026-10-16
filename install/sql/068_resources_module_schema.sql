-- The base install creates the Resources tables at module version 1.0.1
-- (later recorded as 3.0.0), but nothing ran the module's own upgrade to its
-- current version, 3.1.2 (modules/resources/setup.php). So a fresh install:
--   * had no resources.resource_description column, which the resource form,
--     view and list use: adding or editing a resource failed with
--     "Unknown column 'resource_description'";
--   * kept the resource types in the old resource_types table instead of the
--     ResourceTypes system values the code reads, so resources had no types.
-- This applies the same steps as CSetupResources::upgrade('3.0.0'). Every
-- step checks first, so installs where that upgrade already ran, or where the
-- module was removed, are left as they are.

# resource_description, filled from resource_note (as the module upgrade does)
SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'resources') > 0
    AND (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'resources' AND column_name = 'resource_description') = 0,
    'ALTER TABLE `resources` ADD `resource_description` TEXT NULL DEFAULT NULL',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'resources' AND column_name = 'resource_description') > 0,
    'UPDATE `resources` SET `resource_description` = `resource_note` WHERE `resource_description` IS NULL',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'resources' AND column_name = 'resource_note') > 0,
    'ALTER TABLE `resources` MODIFY `resource_note` TEXT NULL DEFAULT NULL',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

# resource_types -> ResourceTypes system values, unless they already exist
SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'resource_types') > 0
    AND (SELECT COUNT(*) FROM `sysvals` WHERE `sysval_title` = 'ResourceTypes') = 0,
    'INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) SELECT 1, ''ResourceTypes'', `resource_type_name`, `resource_type_id` FROM `resource_types` ORDER BY `resource_type_id`',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'resource_types') > 0
    AND (SELECT COUNT(*) FROM `sysvals` WHERE `sysval_title` = 'ResourceTypes') > 0,
    'DROP TABLE `resource_types`',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

# no old table and no types at all: the defaults CSetupResources::install() adds
SET @w2p_sql = IF((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'resources') > 0
    AND (SELECT COUNT(*) FROM `sysvals` WHERE `sysval_title` = 'ResourceTypes') = 0,
    'INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES (1, ''ResourceTypes'', ''All resources'', 1), (1, ''ResourceTypes'', ''Equipment'', 2), (1, ''ResourceTypes'', ''Tool'', 3), (1, ''ResourceTypes'', ''Venue'', 4)',
    'DO 0');
PREPARE w2p_stmt FROM @w2p_sql;
EXECUTE w2p_stmt;
DEALLOCATE PREPARE w2p_stmt;

UPDATE `modules` SET `mod_version` = '3.1.2' WHERE `mod_directory` = 'resources' AND `mod_version` IN ('1.0', '1.0.1', '1.1.0', '3.0.0', '3.1.1');
