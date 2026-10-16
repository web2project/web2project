-- Migrations 048 (admin to users) and 049 (calendar to events) renamed gacl_axo
-- rows onto values that already existed, and 012 had dropped the unique index
-- that would have stopped it. Every install therefore has two app/users rows
-- (User Administration and User Table) and two app/events rows (Calendar and
-- Events). phpGACL treats a section/value pair that matches more than one row
-- as missing, so permissions for those modules cannot be granted to a role.
-- Keep the lowest id of each duplicate and restore the uniqueness.
-- https://github.com/web2project/web2project/issues/385

DELETE m FROM `gacl_groups_axo_map` m
  JOIN `gacl_axo` dup ON dup.`id` = m.`axo_id`
  JOIN `gacl_axo` keep ON keep.`section_value` = dup.`section_value`
                      AND keep.`value` = dup.`value`
                      AND keep.`id` < dup.`id`;

DELETE dup FROM `gacl_axo` dup
  JOIN `gacl_axo` keep ON keep.`section_value` = dup.`section_value`
                      AND keep.`value` = dup.`value`
                      AND keep.`id` < dup.`id`;

ALTER TABLE `gacl_axo` ADD UNIQUE KEY `gacl_section_value_value_axo` (`section_value`, `value`);
