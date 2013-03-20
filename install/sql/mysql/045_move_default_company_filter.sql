
-- Move the Default Company Filter from the Tasks group to the Startup group

UPDATE `config` SET `config_group`="startup" WHERE `config_name` = "company_filter_default";