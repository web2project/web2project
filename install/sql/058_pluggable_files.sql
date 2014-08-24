
ALTER TABLE `files` ADD `file_system` VARCHAR(20) NOT NULL ;

INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES('file_system', '', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES('dropbox_key', '', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES('dropbox_secret', '', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES('dropbox_access_token', '', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES('aws_bucket_name', '', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES('aws_secret_key', '', 'files', 'text');
INSERT INTO `config` (`config_name`, `config_value`, `config_group`, `config_type`) VALUES('aws_access_key', '', 'files', '');
