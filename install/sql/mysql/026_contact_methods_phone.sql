-- This restores two fields removed in database update 021 to improve
--   performance, simplify queries, and generally make existing modules work a
--   bit more smoothly.

ALTER TABLE `contacts`
    ADD `contact_email` VARCHAR( 255 ) NOT NULL AFTER `contact_type` ,
    ADD `contact_phone` VARCHAR( 30 ) NOT NULL AFTER `contact_email` ;

UPDATE contacts c
  SET contact_phone = (
      SELECT method_value
          FROM contacts_methods cm where method_name = 'phone_primary'
          AND cm.contact_id = c.contact_id
);

DELETE FROM `contacts_methods` WHERE `method_name` IN ('phone_primary');
DELETE FROM `sysvals` WHERE `sysval_title` = 'ContactMethods' AND
    `sysval_value_id` = 'phone_primary';

UPDATE contacts c
  SET contact_email = (
      SELECT method_value
          FROM contacts_methods cm where method_name = 'email_primary'
          AND cm.contact_id = c.contact_id
);

DELETE FROM `contacts_methods` WHERE `method_name` = 'email_primary';
DELETE FROM `sysvals` WHERE `sysval_title` = 'ContactMethods' AND
    `sysval_value_id` = 'email_primary';