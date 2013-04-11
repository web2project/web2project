
-- Add a new permission AXO called Forum Messages

INSERT INTO `gacl_axo` (`section_value`, `value`, `order_value`, `name`, `hidden`) VALUES ('app', 'forum_messages', 7, 'Forum Messages', 0);

-- Add it into the groups 'All Modules' and 'Non-Admin Modules'

INSERT INTO `gacl_groups_axo_map`(`group_id`, `axo_id`) VALUES (11,(SELECT id FROM `gacl_axo` WHERE value = "forum_messages"));
INSERT INTO `gacl_groups_axo_map`(`group_id`, `axo_id`) VALUES (13,(SELECT id FROM `gacl_axo` WHERE value = "forum_messages"));