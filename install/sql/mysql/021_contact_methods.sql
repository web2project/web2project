
-- This cleanup shifts all the contact methods (email, phone, IM, etc) from the
--   contacts table into the more flexible contact_methods table. We'll be able
--   to add more and even make them customizable by Admins at some point.

CREATE TABLE `contacts_methods` (
  `method_id` int(10) NOT NULL auto_increment,
  `contact_id` int(10) NOT NULL,
  `method_name` varchar(20) NOT NULL,
  `method_value` varchar(255) NOT NULL,
  PRIMARY KEY (`method_id`),
  KEY (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `contacts_methods` (`contact_id`, `method_name`, `method_value`)
    SELECT `contact_id`, 'email_primary', `contact_email` FROM `contacts` WHERE TRIM(`contact_email`) IS NOT NULL UNION
    SELECT `contact_id`, 'email_alt', `contact_email2` FROM `contacts` WHERE TRIM(`contact_email2`) IS NOT NULL UNION
    SELECT `contact_id`, 'url', `contact_url` FROM `contacts` WHERE TRIM(`contact_url`) IS NOT NULL UNION
    SELECT `contact_id`, 'phone_primary', `contact_phone` FROM `contacts` WHERE TRIM(`contact_phone`) IS NOT NULL UNION
    SELECT `contact_id`, 'phone_alt', `contact_phone2` FROM `contacts` WHERE TRIM(`contact_phone2`) IS NOT NULL UNION
    SELECT `contact_id`, 'phone_fax', `contact_fax` FROM `contacts` WHERE TRIM(`contact_fax`) IS NOT NULL UNION
    SELECT `contact_id`, 'phone_mobile', `contact_mobile` FROM `contacts` WHERE TRIM(`contact_mobile`) IS NOT NULL UNION
    SELECT `contact_id`, 'im_jabber', `contact_jabber` FROM `contacts` WHERE TRIM(`contact_jabber`) IS NOT NULL UNION
    SELECT `contact_id`, 'im_icq', `contact_icq` FROM `contacts` WHERE TRIM(`contact_icq`) IS NOT NULL UNION
    SELECT `contact_id`, 'im_msn', `contact_msn` FROM `contacts` WHERE TRIM(`contact_msn`) IS NOT NULL UNION
    SELECT `contact_id`, 'im_yahoo', `contact_yahoo` FROM `contacts` WHERE TRIM(`contact_yahoo`) IS NOT NULL UNION
    SELECT `contact_id`, 'im_aol', `contact_aol` FROM `contacts` WHERE TRIM(`contact_aol`) IS NOT NULL UNION
    SELECT `contact_id`, 'im_skype', `contact_skype` FROM `contacts` WHERE TRIM(`contact_skype`) IS NOT NULL UNION
    SELECT `contact_id`, 'im_google', `contact_google` FROM `contacts` WHERE TRIM(`contact_google`) IS NOT NULL;

ALTER TABLE `contacts`
    DROP `contact_email`,
    DROP `contact_email2`,
    DROP `contact_url`,
    DROP `contact_phone`,
    DROP `contact_phone2`,
    DROP `contact_fax`,
    DROP `contact_mobile`,
    DROP `contact_jabber`,
    DROP `contact_icq`,
    DROP `contact_msn`,
    DROP `contact_yahoo`,
    DROP `contact_aol`,
    DROP `contact_skype`,
    DROP `contact_google`;

INSERT INTO syskeys (syskey_name, syskey_label, syskey_type, syskey_sep1, syskey_sep2) VALUES
    ('ContactMethods', 'Alternate methods of communication for contacts', 0, '\n', '|');
SET @syskey_id = LAST_INSERT_ID();

INSERT INTO sysvals (sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES
    (@syskey_id, 'ContactMethods', 'Email: Primary', 'email_primary'),
    (@syskey_id, 'ContactMethods', 'Email: Alternate', 'email_alt'),
    (@syskey_id, 'ContactMethods', 'Web Site', 'url'),
    (@syskey_id, 'ContactMethods', 'Phone: Primary', 'phone_primary'),
    (@syskey_id, 'ContactMethods', 'Phone: Alternate', 'phone_alt'),
    (@syskey_id, 'ContactMethods', 'Phone: Fax', 'phone_fax'),
    (@syskey_id, 'ContactMethods', 'Phone: Mobile', 'phone_mobile'),
    (@syskey_id, 'ContactMethods', 'IM: Jabber', 'im_jabber'),
    (@syskey_id, 'ContactMethods', 'IM: ICQ', 'im_icq'),
    (@syskey_id, 'ContactMethods', 'IM: MSN', 'im_msn'),
    (@syskey_id, 'ContactMethods', 'IM: Yahoo', 'im_yahoo'),
    (@syskey_id, 'ContactMethods', 'IM: AOL', 'im_aol'),
    (@syskey_id, 'ContactMethods', 'IM: Skype', 'im_skype'),
    (@syskey_id, 'ContactMethods', 'IM: Google', 'im_google');

DELETE FROM `contacts_methods` WHERE `method_value` = '' 
    AND `method_name` NOT IN ('email_primary', 'phone_primary');