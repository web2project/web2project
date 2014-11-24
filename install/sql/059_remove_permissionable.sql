
UPDATE `modules` SET `permissions_item_table` = '', `permissions_item_field` = '', `permissions_item_label` = '' WHERE
    `permissions_item_table` IN ('files', 'contacts', 'forums', 'links');