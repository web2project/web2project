


UPDATE `modules` SET `permissions_item_table` = 'files', `permissions_item_field` = 'file_id',
  `permissions_item_label` = 'file_name' WHERE `mod_main_class` = 'CFile';
UPDATE `modules` SET `permissions_item_table` = 'contacts', `permissions_item_field` = 'contact_id',
  `permissions_item_label` = 'contact_display_name' WHERE `mod_main_class` = 'CContact';
UPDATE `modules` SET `permissions_item_table` = 'forums', `permissions_item_field` = 'forum_id',
  `permissions_item_label` = 'forum_name' WHERE `mod_main_class` = 'CForum';
UPDATE `modules` SET `permissions_item_table` = 'links', `permissions_item_field` = 'link_id',
  `permissions_item_label` = 'link_name' WHERE `mod_main_class` = 'CLink';