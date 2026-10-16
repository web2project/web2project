-- Users without a contact record (for example LDAP users whose contact could
-- not be saved on their first login) are missing from User Management and
-- cannot be viewed or given a role. Give each of them a minimal contact named
-- after the login name. The marker in contact_notes links the new contact to
-- its user and is removed afterwards.
-- https://github.com/web2project/web2project/issues/320

INSERT INTO `contacts` (`contact_first_name`, `contact_last_name`, `contact_display_name`, `contact_order_by`, `contact_owner`, `contact_notes`)
  SELECT u.`user_username`, '', u.`user_username`, u.`user_username`, 0, CONCAT('w2p-065-user:', u.`user_id`)
  FROM `users` u
  LEFT JOIN `contacts` c ON c.`contact_id` = u.`user_contact`
  WHERE c.`contact_id` IS NULL;

UPDATE `users` u
  JOIN `contacts` c ON c.`contact_notes` = CONCAT('w2p-065-user:', u.`user_id`)
  SET u.`user_contact` = c.`contact_id`;

UPDATE `contacts` SET `contact_notes` = NULL WHERE `contact_notes` LIKE 'w2p-065-user:%';
