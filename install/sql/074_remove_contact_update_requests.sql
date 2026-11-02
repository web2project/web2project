-- The contact self-update feature is removed in 5.0: updatecontact.php,
-- do_updatecontact.php, the "Awaiting Update" checkbox and the e-mail that
-- carried the key. It had stopped working at both ends (nothing has sent a
-- request since 2014, and the page redirected anonymous visitors to the
-- login screen), so this drops the columns only it used.
--
-- contact_lastupdate stays: every contact save stamps it, and the contact
-- list filters on it.
--
-- Where the index or a column is already gone, the installer ignores the
-- "check that it exists" error, so running this again changes nothing.

ALTER TABLE `contacts` DROP INDEX `contact_updatekey`;

ALTER TABLE `contacts` DROP COLUMN `contact_updatekey`;

ALTER TABLE `contacts` DROP COLUMN `contact_updateasked`;
