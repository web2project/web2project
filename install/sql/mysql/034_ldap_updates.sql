
INSERT INTO `config` (`config_name` , `config_value` , `config_group` , `config_type`)
    VALUES ('ldap_complete_string', '', 'ldap', 'text');

INSERT INTO w2pversion (code_version, db_version, last_code_update) VALUES ('2.4.0', 34, now());