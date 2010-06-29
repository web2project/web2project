-- Somewhere along the way fields for task_log_changelog* got added to the
-- default database but are no longer used. This will remove them

ALTER TABLE `task_log` DROP COLUMN `task_log_changelog`,
  DROP COLUMN `task_log_changelog_servers`,
  DROP COLUMN `task_log_changelog_whom`,
  DROP COLUMN `task_log_changelog_datetime`,
  DROP COLUMN `task_log_changelog_duration`,
  DROP COLUMN `task_log_changelog_expected_downtime`,
  DROP COLUMN `task_log_changelog_description`,
  DROP COLUMN `task_log_changelog_backout_plan`;

INSERT INTO `w2pversion` (`code_revision`, `code_version`, `db_version`, `last_db_update`, `last_code_update`)
	VALUES (1220, '2.0.0', 24, now(), now());