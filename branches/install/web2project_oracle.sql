-- $Id$ $URL$
--DDL
SET SCAN OFF;
CREATE USER web2project IDENTIFIED BY web2project DEFAULT TABLESPACE USERS TEMPORARY TABLESPACE TEMP;
GRANT CREATE SESSION, RESOURCE, CREATE VIEW TO web2project;

ALTER USER WEB2PROJECT DEFAULT TABLESPACE USERS TEMPORARY TABLESPACE TEMP ACCOUNT UNLOCK;
ALTER USER WEB2PROJECT DEFAULT ROLE "RESOURCE";
GRANT ALTER DATABASE TO WEB2PROJECT;
GRANT CREATE ANY INDEX TO WEB2PROJECT;
GRANT DROP ANY INDEX TO WEB2PROJECT;
GRANT ALTER ANY INDEX TO WEB2PROJECT;
GRANT ALTER ANY TABLE TO WEB2PROJECT;
GRANT DROP ANY TABLE TO WEB2PROJECT;
GRANT CREATE ANY TABLE TO WEB2PROJECT;

connect web2project/web2project;

CREATE SEQUENCE  billingcode_billingcode_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  common_notes_note_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  companies_company_id_SEQ  
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  config_config_id_SEQ  
  MINVALUE 115 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  config_list_config_list_id_SEQ  
  MINVALUE 10 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  contacts_contact_id_SEQ  
  MINVALUE 2 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  departments_dept_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  event_queue_queue_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  events_event_id_SEQ  
  MINVALUE 2 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  file_folders_file_folder_id_SE  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  files_file_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  forum_messages_message_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  forums_forum_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  modules_mod_id_SEQ  
  MINVALUE 14 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  project_designer_options_pd_op  
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  projects_project_id_SEQ  
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  syskeys_syskey_id_SEQ  
  MINVALUE 3 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  sysvals_sysval_id_SEQ  
  MINVALUE 317 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  task_log_task_log_id_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  tasks_task_id_SEQ  
  MINVALUE 1 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  tickets_ticket_SEQ  
  MINVALUE 0 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  user_access_log_user_access_lo  
  MINVALUE 6 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE SEQUENCE  users_user_id_SEQ  
  MINVALUE 2 MAXVALUE 999999999999999999999999 INCREMENT BY 1  NOCYCLE ;

CREATE TABLE billingcode (
  billingcode_id NUMBER(19,0) NOT NULL,
  billingcode_name VARCHAR2(25) NOT NULL,
  billingcode_value FLOAT DEFAULT '0' NOT NULL,
  billingcode_desc VARCHAR2(255) NOT NULL,
  billingcode_status NUMBER(10,0) DEFAULT '0' NOT NULL,
  company_id NUMBER(19,0) DEFAULT '0' NOT NULL
);


ALTER TABLE billingcode
ADD CONSTRAINT PRIMARY PRIMARY KEY
(
  billingcode_id
)
ENABLE
;
CREATE INDEX billingcode_name ON billingcode
(
  billingcode_name
) 
;
CREATE INDEX billingcode_status ON billingcode
(
  billingcode_status
) 
;

CREATE TABLE common_notes (
  note_id NUMBER(10,0) NOT NULL,
  note_author NUMBER(10,0) DEFAULT '0' NOT NULL,
  note_module NUMBER(10,0) DEFAULT '0' NOT NULL,
  note_record_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  note_category NUMBER(10,0) DEFAULT '0' NOT NULL,
  note_title VARCHAR2(100) NOT NULL,
  note_body CLOB NOT NULL,
  note_date DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss') NOT NULL,
  note_hours FLOAT DEFAULT '0' NOT NULL,
  note_code VARCHAR2(8) NOT NULL,
  note_created DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss') NOT NULL,
  note_modified DATE DEFAULT SYSDATE,
  note_modified_by NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE common_notes
ADD CONSTRAINT PRIMARY_6 PRIMARY KEY
(
  note_id
)
ENABLE
;

CREATE TABLE companies (
  company_id NUMBER(10,0) NOT NULL,
  company_module NUMBER(10,0) DEFAULT '0' NOT NULL,
  company_name VARCHAR2(100),
  company_phone1 VARCHAR2(30),
  company_phone2 VARCHAR2(30),
  company_fax VARCHAR2(30),
  company_address1 VARCHAR2(50),
  company_address2 VARCHAR2(50),
  company_city VARCHAR2(30),
  company_state VARCHAR2(30),
  company_zip VARCHAR2(11),
  company_primary_url VARCHAR2(255),
  company_owner NUMBER(10,0) DEFAULT '0' NOT NULL,
  company_description CLOB,
  company_type NUMBER(10,0) DEFAULT '0' NOT NULL,
  company_email VARCHAR2(255),
  company_custom CLOB,
  company_country VARCHAR2(100) NOT NULL
);


ALTER TABLE companies
ADD CONSTRAINT PRIMARY_14 PRIMARY KEY
(
  company_id
)
ENABLE
;
CREATE INDEX idx_cpy1 ON companies
(
  company_owner
) 
;
CREATE INDEX company_name ON companies
(
  company_name
) 
;
CREATE INDEX company_type ON companies
(
  company_type
) 
;

CREATE TABLE config (
  config_id NUMBER(10,0) NOT NULL,
  config_name VARCHAR2(255) NOT NULL,
  config_value VARCHAR2(255),
  config_group VARCHAR2(255),
  config_type VARCHAR2(255) NOT NULL
);


ALTER TABLE config
ADD CONSTRAINT PRIMARY_4 PRIMARY KEY
(
  config_id
)
ENABLE
;
CREATE UNIQUE INDEX config_name ON config
(
  config_name
) 
;

CREATE TABLE config_list (
  config_list_id NUMBER(10,0) NOT NULL,
  config_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  config_list_name VARCHAR2(30)
);


ALTER TABLE config_list
ADD CONSTRAINT PRIMARY_39 PRIMARY KEY
(
  config_list_id
)
ENABLE
;
CREATE INDEX config_id ON config_list
(
  config_id
) 
;

CREATE TABLE contacts (
  contact_id NUMBER(10,0) NOT NULL,
  contact_first_name VARCHAR2(30),
  contact_last_name VARCHAR2(30),
  contact_order_by VARCHAR2(30),
  contact_title VARCHAR2(50),
  contact_birthday DATE,
  contact_job VARCHAR2(255),
  contact_company NUMBER(10,0) DEFAULT '0' NOT NULL,
  contact_department NUMBER(10,0) DEFAULT '0' NOT NULL,
  contact_type VARCHAR2(20),
  contact_email VARCHAR2(255),
  contact_email2 VARCHAR2(255),
  contact_url VARCHAR2(255),
  contact_phone VARCHAR2(30),
  contact_phone2 VARCHAR2(30),
  contact_fax VARCHAR2(30),
  contact_mobile VARCHAR2(30),
  contact_address1 VARCHAR2(60),
  contact_address2 VARCHAR2(60),
  contact_city VARCHAR2(30),
  contact_state VARCHAR2(30),
  contact_zip VARCHAR2(11),
  contact_country VARCHAR2(30),
  contact_jabber VARCHAR2(255),
  contact_icq VARCHAR2(20),
  contact_msn VARCHAR2(255),
  contact_yahoo VARCHAR2(255),
  contact_aol VARCHAR2(30),
  contact_notes CLOB,
  contact_project NUMBER(10,0) DEFAULT '0',
  contact_icon VARCHAR2(20) DEFAULT 'obj/contact',
  contact_owner NUMBER(10,0) DEFAULT '0',
  contact_private NUMBER(3,0) DEFAULT '0',
  contact_updatekey VARCHAR2(32),
  contact_lastupdate DATE,
  contact_updateasked DATE,
  contact_skype VARCHAR2(100),
  contact_google VARCHAR2(255)
);


ALTER TABLE contacts
ADD CONSTRAINT PRIMARY_44 PRIMARY KEY
(
  contact_id
)
ENABLE
;
CREATE INDEX idx_oby ON contacts
(
  contact_order_by
) 
;
CREATE INDEX idx_co ON contacts
(
  contact_company
) 
;
CREATE INDEX idx_prp ON contacts
(
  contact_project
) 
;
CREATE INDEX contact_first_name ON contacts
(
  contact_first_name
) 
;
CREATE INDEX contact_last_name ON contacts
(
  contact_last_name
) 
;
CREATE INDEX contact_updatekey ON contacts
(
  contact_updatekey
) 
;
CREATE INDEX contact_email ON contacts
(
  contact_email
) 
;
CREATE INDEX contact_private ON contacts
(
  contact_private
) 
;
CREATE INDEX contact_department ON contacts
(
  contact_department
) 
;

CREATE TABLE custom_fields_lists (
  field_id NUMBER(10,0),
  list_option_id NUMBER(10,0),
  list_value VARCHAR2(250)
);


CREATE INDEX field_id ON custom_fields_lists
(
  field_id
) 
;
CREATE INDEX list_value ON custom_fields_lists
(
  list_value
) 
;

CREATE TABLE custom_fields_struct (
  field_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  field_module VARCHAR2(30),
  field_page VARCHAR2(30),
  field_htmltype VARCHAR2(20),
  field_datatype VARCHAR2(20),
  field_order NUMBER(10,0),
  field_name VARCHAR2(100),
  field_extratags VARCHAR2(250),
  field_description VARCHAR2(250)
);


ALTER TABLE custom_fields_struct
ADD CONSTRAINT PRIMARY_23 PRIMARY KEY
(
  field_id
)
ENABLE
;
CREATE INDEX field_order ON custom_fields_struct
(
  field_order
) 
;
CREATE INDEX field_module ON custom_fields_struct
(
  field_module
) 
;
CREATE INDEX field_page ON custom_fields_struct
(
  field_page
) 
;

CREATE TABLE custom_fields_values (
  value_id NUMBER(10,0),
  value_module VARCHAR2(30),
  value_object_id NUMBER(10,0),
  value_field_id NUMBER(10,0),
  value_charvalue VARCHAR2(250),
  value_intvalue NUMBER(10,0)
);


CREATE INDEX idx_cfv_id ON custom_fields_values
(
  value_id
) 
;
CREATE INDEX value_field_id ON custom_fields_values
(
  value_field_id
) 
;
CREATE INDEX value_object_id ON custom_fields_values
(
  value_object_id
) 
;
CREATE INDEX value_charvalue ON custom_fields_values
(
  value_charvalue
) 
;

CREATE TABLE departments (
  dept_id NUMBER(10,0) NOT NULL,
  dept_parent NUMBER(10,0) DEFAULT '0' NOT NULL,
  dept_company NUMBER(10,0) DEFAULT '0' NOT NULL,
  dept_name VARCHAR2(255) NOT NULL,
  dept_phone VARCHAR2(30),
  dept_fax VARCHAR2(30),
  dept_address1 VARCHAR2(30),
  dept_address2 VARCHAR2(30),
  dept_city VARCHAR2(30),
  dept_state VARCHAR2(30),
  dept_zip VARCHAR2(11),
  dept_url VARCHAR2(25),
  dept_desc CLOB,
  dept_owner NUMBER(10,0) DEFAULT '0' NOT NULL,
  dept_country VARCHAR2(100) NOT NULL,
  dept_email VARCHAR2(255) NOT NULL,
  dept_type NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE departments
ADD CONSTRAINT PRIMARY_45 PRIMARY KEY
(
  dept_id
)
ENABLE
;
CREATE INDEX dept_name ON departments
(
  dept_name
) 
;

CREATE TABLE event_queue (
  queue_id NUMBER(10,0) NOT NULL,
  queue_start NUMBER(10,0) DEFAULT '0' NOT NULL,
  queue_type VARCHAR2(40) NOT NULL,
  queue_repeat_interval NUMBER(10,0) DEFAULT '0' NOT NULL,
  queue_repeat_count NUMBER(10,0) DEFAULT '0' NOT NULL,
  queue_data BLOB NOT NULL,
  queue_callback VARCHAR2(127) NOT NULL,
  queue_owner NUMBER(10,0) DEFAULT '0' NOT NULL,
  queue_origin_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  queue_module VARCHAR2(40) NOT NULL,
  queue_module_type VARCHAR2(20) NOT NULL
);


ALTER TABLE event_queue
ADD CONSTRAINT PRIMARY_30 PRIMARY KEY
(
  queue_id
)
ENABLE
;
CREATE INDEX queue_start ON event_queue
(
  queue_start
) 
;
CREATE INDEX queue_module ON event_queue
(
  queue_module
) 
;
CREATE INDEX queue_type ON event_queue
(
  queue_type
) 
;
CREATE INDEX queue_origin_id ON event_queue
(
  queue_origin_id
) 
;

CREATE TABLE events (
  event_id NUMBER(10,0) NOT NULL,
  event_title VARCHAR2(255) NOT NULL,
  event_start_date DATE,
  event_end_date DATE,
  event_parent NUMBER(10,0) DEFAULT '0' NOT NULL,
  event_description CLOB,
  event_times_recuring NUMBER(10,0) DEFAULT '0' NOT NULL,
  event_recurs NUMBER(10,0) DEFAULT '0' NOT NULL,
  event_remind NUMBER(10,0) DEFAULT '0' NOT NULL,
  event_icon VARCHAR2(20) DEFAULT 'obj/event',
  event_owner NUMBER(10,0) DEFAULT '0',
  event_project NUMBER(10,0) DEFAULT '0',
  event_private NUMBER(3,0) DEFAULT '0',
  event_type NUMBER(3,0) DEFAULT '0',
  event_cwd NUMBER(3,0) DEFAULT '0',
  event_notify NUMBER(3,0) DEFAULT '0' NOT NULL
);


ALTER TABLE events
ADD CONSTRAINT PRIMARY_26 PRIMARY KEY
(
  event_id
)
ENABLE
;
CREATE INDEX id_esd ON events
(
  event_start_date
) 
;
CREATE INDEX id_eed ON events
(
  event_end_date
) 
;
CREATE INDEX id_evp ON events
(
  event_parent
) 
;
CREATE INDEX idx_ev1 ON events
(
  event_owner
) 
;
CREATE INDEX idx_ev2 ON events
(
  event_project
) 
;
CREATE INDEX event_recurs ON events
(
  event_recurs
) 
;

CREATE TABLE file_folders (
  file_folder_id NUMBER(10,0) NOT NULL,
  file_folder_parent NUMBER(10,0) DEFAULT '0' NOT NULL,
  file_folder_name VARCHAR2(255) NOT NULL,
  file_folder_description CLOB
);


ALTER TABLE file_folders
ADD CONSTRAINT PRIMARY_22 PRIMARY KEY
(
  file_folder_id
)
ENABLE
;
CREATE INDEX file_folder_parent ON file_folders
(
  file_folder_parent
) 
;
CREATE INDEX file_folder_name ON file_folders
(
  file_folder_name
) 
;

CREATE TABLE files (
  file_id NUMBER(10,0) NOT NULL,
  file_real_filename VARCHAR2(255) NOT NULL,
  file_folder NUMBER(10,0) DEFAULT '0' NOT NULL,
  file_project NUMBER(10,0) DEFAULT '0' NOT NULL,
  file_task NUMBER(10,0) DEFAULT '0' NOT NULL,
  file_name VARCHAR2(255) NOT NULL,
  file_parent NUMBER(10,0) DEFAULT '0',
  file_description CLOB,
  file_type VARCHAR2(100),
  file_owner NUMBER(10,0) DEFAULT '0',
  file_date DATE,
  file_size NUMBER(10,0) DEFAULT '0',
  file_version FLOAT DEFAULT '0' NOT NULL,
  file_icon VARCHAR2(20) DEFAULT 'obj/',
  file_category NUMBER(10,0) DEFAULT '0',
  file_checkout VARCHAR2(16) NOT NULL,
  file_co_reason CLOB,
  file_version_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE files
ADD CONSTRAINT PRIMARY_46 PRIMARY KEY
(
  file_id
)
ENABLE
;
CREATE INDEX idx_file_task ON files
(
  file_task
) 
;
CREATE INDEX idx_file_project ON files
(
  file_project
) 
;
CREATE INDEX idx_file_parent ON files
(
  file_parent
) 
;
CREATE INDEX idx_file_vid ON files
(
  file_version_id
) 
;
CREATE INDEX file_name ON files
(
  file_name
) 
;
CREATE INDEX file_folder ON files
(
  file_folder
) 
;
CREATE INDEX file_category ON files
(
  file_category
) 
;
CREATE INDEX file_checkout ON files
(
  file_checkout
) 
;

CREATE TABLE files_index (
  file_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  word VARCHAR2(50) NOT NULL,
  word_placement NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE files_index
ADD CONSTRAINT PRIMARY_16 PRIMARY KEY
(
  file_id,
  word,
  word_placement
)
ENABLE
;
CREATE INDEX idx_fwrd ON files_index
(
  word
) 
;

CREATE TABLE forum_messages (
  message_id NUMBER(10,0) NOT NULL,
  message_forum NUMBER(10,0) DEFAULT '0' NOT NULL,
  message_parent NUMBER(10,0) DEFAULT '0' NOT NULL,
  message_author NUMBER(10,0) DEFAULT '0' NOT NULL,
  message_editor NUMBER(10,0) DEFAULT '0' NOT NULL,
  message_title VARCHAR2(255) NOT NULL,
  message_date DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss'),
  message_body CLOB,
  message_published NUMBER(3,0) DEFAULT '1' NOT NULL
);


ALTER TABLE forum_messages
ADD CONSTRAINT PRIMARY_2 PRIMARY KEY
(
  message_id
)
ENABLE
;
CREATE INDEX idx_mparent ON forum_messages
(
  message_parent
) 
;
CREATE INDEX idx_mdate ON forum_messages
(
  message_date
) 
;
CREATE INDEX idx_mforum ON forum_messages
(
  message_forum
) 
;
CREATE INDEX message_author ON forum_messages
(
  message_author
) 
;

CREATE TABLE forum_visits (
  visit_user NUMBER(10,0) DEFAULT '0' NOT NULL,
  visit_forum NUMBER(10,0) DEFAULT '0' NOT NULL,
  visit_message NUMBER(10,0) DEFAULT '0' NOT NULL,
  visit_date DATE DEFAULT SYSDATE
);


CREATE INDEX idx_fv ON forum_visits
(
  visit_user,
  visit_forum,
  visit_message
) 
;

CREATE TABLE forum_watch (
  watch_user NUMBER(10,0) DEFAULT '0' NOT NULL,
  watch_forum NUMBER(10,0),
  watch_topic NUMBER(10,0)
);


CREATE INDEX idx_fw1 ON forum_watch
(
  watch_user,
  watch_forum
) 
;
CREATE INDEX idx_fw2 ON forum_watch
(
  watch_user,
  watch_topic
) 
;

CREATE TABLE forums (
  forum_id NUMBER(10,0) NOT NULL,
  forum_project NUMBER(10,0) DEFAULT '0' NOT NULL,
  forum_status NUMBER(3,0) DEFAULT '-1' NOT NULL,
  forum_owner NUMBER(10,0) DEFAULT '0' NOT NULL,
  forum_name VARCHAR2(50) NOT NULL,
  forum_create_date DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss'),
  forum_last_date DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss'),
  forum_last_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  forum_message_count NUMBER(10,0) DEFAULT '0' NOT NULL,
  forum_description VARCHAR2(255),
  forum_moderated NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE forums
ADD CONSTRAINT PRIMARY_43 PRIMARY KEY
(
  forum_id
)
ENABLE
;
CREATE INDEX idx_fproject ON forums
(
  forum_project
) 
;
CREATE INDEX idx_fowner ON forums
(
  forum_owner
) 
;
CREATE INDEX forum_status ON forums
(
  forum_status
) 
;
CREATE INDEX forum_name ON forums
(
  forum_name
) 
;

CREATE TABLE gacl_acl (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  section_value VARCHAR2(80) DEFAULT 'system' NOT NULL,
  allow NUMBER(10,0) DEFAULT '0' NOT NULL,
  enabled NUMBER(10,0) DEFAULT '0' NOT NULL,
  return_value CLOB,
  note CLOB,
  updated_date NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_acl
ADD CONSTRAINT PRIMARY_7 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE INDEX gacl_enabled_acl ON gacl_acl
(
  enabled
) 
;
CREATE INDEX gacl_section_value_acl ON gacl_acl
(
  section_value
) 
;
CREATE INDEX gacl_updated_date_acl ON gacl_acl
(
  updated_date
) 
;

CREATE TABLE gacl_acl_sections (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL,
  order_value NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(230) NOT NULL,
  hidden NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_acl_sections
ADD CONSTRAINT PRIMARY_28 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE UNIQUE INDEX gacl_value_acl_sections ON gacl_acl_sections
(
  value
) 
;
CREATE INDEX gacl_hidden_acl_sections ON gacl_acl_sections
(
  hidden
) 
;

CREATE TABLE gacl_acl_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_aco (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  section_value VARCHAR2(80) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL,
  order_value NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(255) NOT NULL,
  hidden NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_aco
ADD CONSTRAINT PRIMARY_29 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE INDEX gacl_section_value_value_aco ON gacl_aco
(
  section_value,
  value
) 
;
CREATE INDEX gacl_hidden_aco ON gacl_aco
(
  hidden
) 
;

CREATE TABLE gacl_aco_map (
  acl_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  section_value VARCHAR2(80) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL
);


ALTER TABLE gacl_aco_map
ADD CONSTRAINT PRIMARY_21 PRIMARY KEY
(
  acl_id,
  section_value,
  value
)
ENABLE
;

CREATE TABLE gacl_aco_sections (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL,
  order_value NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(230) NOT NULL,
  hidden NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_aco_sections
ADD CONSTRAINT PRIMARY_3 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE UNIQUE INDEX gacl_value_aco_sections ON gacl_aco_sections
(
  value
) 
;
CREATE INDEX gacl_hidden_aco_sections ON gacl_aco_sections
(
  hidden
) 
;

CREATE TABLE gacl_aco_sections_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_aco_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_aro (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  section_value VARCHAR2(80) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL,
  order_value NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(255) NOT NULL,
  hidden NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_aro
ADD CONSTRAINT PRIMARY_24 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE INDEX gacl_section_value_value_aro ON gacl_aro
(
  section_value,
  value
) 
;
CREATE INDEX gacl_hidden_aro ON gacl_aro
(
  hidden
) 
;

CREATE TABLE gacl_aro_groups (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  parent_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  lft NUMBER(10,0) DEFAULT '0' NOT NULL,
  rgt NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(255) NOT NULL,
  value VARCHAR2(80) NOT NULL
);


ALTER TABLE gacl_aro_groups
ADD CONSTRAINT PRIMARY_17 PRIMARY KEY
(
  id,
  value
)
ENABLE
;
CREATE INDEX gacl_parent_id_aro_groups ON gacl_aro_groups
(
  parent_id
) 
;
CREATE INDEX gacl_value_aro_groups ON gacl_aro_groups
(
  value
) 
;
CREATE INDEX gacl_lft_rgt_aro_groups ON gacl_aro_groups
(
  lft,
  rgt
) 
;

CREATE TABLE gacl_aro_groups_id_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_aro_groups_map (
  acl_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  group_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_aro_groups_map
ADD CONSTRAINT PRIMARY_35 PRIMARY KEY
(
  acl_id,
  group_id
)
ENABLE
;

CREATE TABLE gacl_aro_map (
  acl_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  section_value VARCHAR2(80) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL
);


ALTER TABLE gacl_aro_map
ADD CONSTRAINT PRIMARY_9 PRIMARY KEY
(
  acl_id,
  section_value,
  value
)
ENABLE
;

CREATE TABLE gacl_aro_sections (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL,
  order_value NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(230) NOT NULL,
  hidden NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_aro_sections
ADD CONSTRAINT PRIMARY_31 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE UNIQUE INDEX gacl_value_aro_sections ON gacl_aro_sections
(
  value
) 
;
CREATE INDEX gacl_hidden_aro_sections ON gacl_aro_sections
(
  hidden
) 
;

CREATE TABLE gacl_aro_sections_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_aro_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_axo (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  section_value VARCHAR2(80) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL,
  order_value NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(255) NOT NULL,
  hidden NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_axo
ADD CONSTRAINT PRIMARY_32 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE INDEX gacl_section_value_value_axo ON gacl_axo
(
  section_value,
  value
) 
;
CREATE INDEX gacl_hidden_axo ON gacl_axo
(
  hidden
) 
;

CREATE TABLE gacl_axo_groups (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  parent_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  lft NUMBER(10,0) DEFAULT '0' NOT NULL,
  rgt NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(255) NOT NULL,
  value VARCHAR2(80) NOT NULL
);


ALTER TABLE gacl_axo_groups
ADD CONSTRAINT PRIMARY_18 PRIMARY KEY
(
  id,
  value
)
ENABLE
;
CREATE INDEX gacl_parent_id_axo_groups ON gacl_axo_groups
(
  parent_id
) 
;
CREATE INDEX gacl_value_axo_groups ON gacl_axo_groups
(
  value
) 
;
CREATE INDEX gacl_lft_rgt_axo_groups ON gacl_axo_groups
(
  lft,
  rgt
) 
;

CREATE TABLE gacl_axo_groups_id_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_axo_groups_map (
  acl_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  group_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_axo_groups_map
ADD CONSTRAINT PRIMARY_1 PRIMARY KEY
(
  acl_id,
  group_id
)
ENABLE
;

CREATE TABLE gacl_axo_map (
  acl_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  section_value VARCHAR2(80) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL
);


ALTER TABLE gacl_axo_map
ADD CONSTRAINT PRIMARY_20 PRIMARY KEY
(
  acl_id,
  section_value,
  value
)
ENABLE
;

CREATE TABLE gacl_axo_sections (
  id NUMBER(10,0) DEFAULT '0' NOT NULL,
  value VARCHAR2(80) NOT NULL,
  order_value NUMBER(10,0) DEFAULT '0' NOT NULL,
  name VARCHAR2(230) NOT NULL,
  hidden NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_axo_sections
ADD CONSTRAINT PRIMARY_36 PRIMARY KEY
(
  id
)
ENABLE
;
CREATE UNIQUE INDEX gacl_value_axo_sections ON gacl_axo_sections
(
  value
) 
;
CREATE INDEX gacl_hidden_axo_sections ON gacl_axo_sections
(
  hidden
) 
;

CREATE TABLE gacl_axo_sections_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_axo_seq (
  id NUMBER(10,0) DEFAULT '0' NOT NULL
);



CREATE TABLE gacl_groups_aro_map (
  group_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  aro_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_groups_aro_map
ADD CONSTRAINT PRIMARY_8 PRIMARY KEY
(
  group_id,
  aro_id
)
ENABLE
;

CREATE TABLE gacl_groups_axo_map (
  group_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  axo_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE gacl_groups_axo_map
ADD CONSTRAINT PRIMARY_37 PRIMARY KEY
(
  group_id,
  axo_id
)
ENABLE
;

CREATE TABLE gacl_permissions (
  user_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  user_name VARCHAR2(255) NOT NULL,
  module VARCHAR2(64) NOT NULL,
  item_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  action VARCHAR2(32) NOT NULL,
  "access" NUMBER(10,0) DEFAULT '0' NOT NULL,
  acl_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


CREATE INDEX user_id ON gacl_permissions
(
  user_id
) 
;
CREATE INDEX module ON gacl_permissions
(
  module
) 
;
CREATE INDEX item_id ON gacl_permissions
(
  item_id
) 
;
CREATE INDEX acl_id ON gacl_permissions
(
  acl_id
) 
;
CREATE INDEX user_name ON gacl_permissions
(
  user_name
) 
;
CREATE INDEX action ON gacl_permissions
(
  action
) 
;

CREATE TABLE gacl_phpgacl (
  name VARCHAR2(230) NOT NULL,
  value VARCHAR2(230) NOT NULL
);


ALTER TABLE gacl_phpgacl
ADD CONSTRAINT PRIMARY_25 PRIMARY KEY
(
  name
)
ENABLE
;

CREATE TABLE modules (
  mod_id NUMBER(10,0) NOT NULL,
  mod_name VARCHAR2(64) NOT NULL,
  mod_directory VARCHAR2(64) NOT NULL,
  mod_version VARCHAR2(10) NOT NULL,
  mod_setup_class VARCHAR2(64),
  mod_type VARCHAR2(64) NOT NULL,
  mod_active NUMBER(10,0) DEFAULT '0' NOT NULL,
  mod_ui_name VARCHAR2(20) NOT NULL,
  mod_ui_icon VARCHAR2(64) NOT NULL,
  mod_ui_order NUMBER(3,0) DEFAULT '0' NOT NULL,
  mod_ui_active NUMBER(10,0) DEFAULT '0' NOT NULL,
  mod_description VARCHAR2(255),
  permissions_item_table VARCHAR2(100),
  permissions_item_field VARCHAR2(100),
  permissions_item_label VARCHAR2(100),
  mod_main_class VARCHAR2(30)
);


ALTER TABLE modules
ADD CONSTRAINT PRIMARY_15 PRIMARY KEY
(
  mod_id,
  mod_directory
)
ENABLE
;
CREATE INDEX mod_ui_order ON modules
(
  mod_ui_order
) 
;
CREATE INDEX mod_active ON modules
(
  mod_active
) 
;
CREATE INDEX mod_directory ON modules
(
  mod_directory
) 
;
CREATE INDEX permissions_item_table ON modules
(
  permissions_item_table
) 
;

CREATE TABLE project_contacts (
  project_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  contact_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


CREATE INDEX project_id ON project_contacts
(
  project_id
) 
;
CREATE INDEX contact_id ON project_contacts
(
  contact_id
) 
;

CREATE TABLE project_departments (
  project_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  department_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


CREATE INDEX project_id_46 ON project_departments
(
  project_id
) 
;
CREATE INDEX department_id ON project_departments
(
  department_id
) 
;

CREATE TABLE project_designer_options (
  pd_option_id NUMBER(10,0) NOT NULL,
  pd_option_user NUMBER(10,0) DEFAULT '0' NOT NULL,
  pd_option_view_project NUMBER(10,0) DEFAULT '1' NOT NULL,
  pd_option_view_gantt NUMBER(10,0) DEFAULT '1' NOT NULL,
  pd_option_view_tasks NUMBER(10,0) DEFAULT '1' NOT NULL,
  pd_option_view_actions NUMBER(10,0) DEFAULT '1' NOT NULL,
  pd_option_view_addtasks NUMBER(10,0) DEFAULT '1' NOT NULL,
  pd_option_view_files NUMBER(10,0) DEFAULT '1' NOT NULL
);


ALTER TABLE project_designer_options
ADD CONSTRAINT PRIMARY_40 PRIMARY KEY
(
  pd_option_id
)
ENABLE
;
CREATE UNIQUE INDEX pd_option_user ON project_designer_options
(
  pd_option_user
) 
;

CREATE TABLE projects (
  project_id NUMBER(10,0) NOT NULL,
  project_company NUMBER(10,0) DEFAULT '0' NOT NULL,
  project_department NUMBER(10,0) DEFAULT '0' NOT NULL,
  project_name VARCHAR2(255),
  project_short_name VARCHAR2(10),
  project_owner NUMBER(10,0) DEFAULT '0',
  project_url VARCHAR2(255),
  project_demo_url VARCHAR2(255),
  project_start_date DATE,
  project_end_date DATE,
  project_actual_end_date DATE,
  project_status NUMBER(10,0) DEFAULT '0',
  project_percent_complete NUMBER(3,0) DEFAULT '0',
  project_color_identifier VARCHAR2(6) DEFAULT 'eeeeee',
  project_description CLOB,
  project_target_budget FLOAT DEFAULT '0',
  project_actual_budget FLOAT DEFAULT '0',
  project_creator NUMBER(10,0) DEFAULT '0',
  project_private NUMBER(3,0) DEFAULT '0',
  project_departments VARCHAR2(100),
  project_contacts VARCHAR2(100),
  project_priority NUMBER(3,0) DEFAULT '0',
  project_type NUMBER(5,0) DEFAULT '0' NOT NULL,
  project_active NUMBER(10,0) DEFAULT '1' NOT NULL,
  project_parent NUMBER(10,0) DEFAULT '0' NOT NULL,
  project_original_parent NUMBER(10,0) DEFAULT '0' NOT NULL,
  project_location VARCHAR2(255) NOT NULL,
  project_updator NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE projects
ADD CONSTRAINT PRIMARY_11 PRIMARY KEY
(
  project_id
)
ENABLE
;
CREATE INDEX idx_project_owner ON projects
(
  project_owner
) 
;
CREATE INDEX idx_sdate ON projects
(
  project_start_date
) 
;
CREATE INDEX idx_edate ON projects
(
  project_end_date
) 
;
CREATE INDEX project_short_name ON projects
(
  project_short_name
) 
;
CREATE INDEX idx_proj1 ON projects
(
  project_company
) 
;
CREATE INDEX project_name ON projects
(
  project_name
) 
;
CREATE INDEX project_parent ON projects
(
  project_parent
) 
;
CREATE INDEX project_status ON projects
(
  project_status
) 
;
CREATE INDEX project_type ON projects
(
  project_type
) 
;
CREATE INDEX project_original_parent ON projects
(
  project_original_parent
) 
;

CREATE TABLE sessions (
  session_id VARCHAR2(40) NOT NULL,
  session_user NUMBER(10,0) DEFAULT '0' NOT NULL,
  session_data BLOB,
  session_updated DATE DEFAULT SYSDATE,
  session_created DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss') NOT NULL
);


ALTER TABLE sessions
ADD CONSTRAINT PRIMARY_13 PRIMARY KEY
(
  session_id
)
ENABLE
;
CREATE INDEX session_updated ON sessions
(
  session_updated
) 
;
CREATE INDEX session_created ON sessions
(
  session_created
) 
;
CREATE INDEX session_user ON sessions
(
  session_user
) 
;

CREATE TABLE syskeys (
  syskey_id NUMBER(10,0) NOT NULL,
  syskey_name VARCHAR2(48) NOT NULL,
  syskey_label VARCHAR2(312) NOT NULL,
  syskey_type NUMBER(10,0) DEFAULT '0' NOT NULL,
  syskey_sep1 CHAR(2) DEFAULT ''
,
  syskey_sep2 CHAR(2) DEFAULT '|' NOT NULL
);


ALTER TABLE syskeys
ADD CONSTRAINT PRIMARY_12 PRIMARY KEY
(
  syskey_id
)
ENABLE
;
CREATE UNIQUE INDEX syskey_name ON syskeys
(
  syskey_name
) 
;

CREATE TABLE sysvals (
  sysval_id NUMBER(10,0) NOT NULL,
  sysval_key_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  sysval_title VARCHAR2(48) NOT NULL,
  sysval_value CLOB,
  sysval_value_id VARCHAR2(128) DEFAULT '0'
);


ALTER TABLE sysvals
ADD CONSTRAINT PRIMARY_34 PRIMARY KEY
(
  sysval_id
)
ENABLE
;
CREATE INDEX sysval_value_id ON sysvals
(
  sysval_value_id
) 
;
CREATE INDEX sysval_title ON sysvals
(
  sysval_title
) 
;
CREATE INDEX sysval_key_id ON sysvals
(
  sysval_key_id
) 
;

CREATE TABLE task_contacts (
  task_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  contact_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


CREATE INDEX task_id ON task_contacts
(
  task_id
) 
;
CREATE INDEX contact_id_1 ON task_contacts
(
  contact_id
) 
;

CREATE TABLE task_departments (
  task_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  department_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


CREATE INDEX task_id_46 ON task_departments
(
  task_id
) 
;
CREATE INDEX department_id_1 ON task_departments
(
  department_id
) 
;

CREATE TABLE task_dependencies (
  dependencies_task_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  dependencies_req_task_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE task_dependencies
ADD CONSTRAINT PRIMARY_33 PRIMARY KEY
(
  dependencies_task_id,
  dependencies_req_task_id
)
ENABLE
;

CREATE TABLE task_log (
  task_log_id NUMBER(10,0) NOT NULL,
  task_log_task NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_log_name VARCHAR2(255),
  task_log_description CLOB,
  task_log_creator NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_log_hours FLOAT DEFAULT '0' NOT NULL,
  task_log_date DATE,
  task_log_costcode VARCHAR2(8) NOT NULL,
  task_log_problem NUMBER(3,0) DEFAULT '0',
  task_log_reference NUMBER(3,0) DEFAULT '0',
  task_log_related_url VARCHAR2(255)
);


ALTER TABLE task_log
ADD CONSTRAINT PRIMARY_10 PRIMARY KEY
(
  task_log_id
)
ENABLE
;
CREATE INDEX idx_log_task ON task_log
(
  task_log_task
) 
;
CREATE INDEX task_log_date ON task_log
(
  task_log_date
) 
;
CREATE INDEX task_log_creator ON task_log
(
  task_log_creator
) 
;
CREATE INDEX task_log_problem ON task_log
(
  task_log_problem
) 
;
CREATE INDEX task_log_costcode ON task_log
(
  task_log_costcode
) 
;

CREATE TABLE tasks (
  task_id NUMBER(10,0) NOT NULL,
  task_name VARCHAR2(255),
  task_parent NUMBER(10,0) DEFAULT '0',
  task_milestone NUMBER(3,0) DEFAULT '0',
  task_project NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_owner NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_start_date DATE,
  task_duration FLOAT DEFAULT '0',
  task_duration_type NUMBER(10,0) DEFAULT '1' NOT NULL,
  task_hours_worked FLOAT DEFAULT '0',
  task_end_date DATE,
  task_status NUMBER(10,0) DEFAULT '0',
  task_priority NUMBER(3,0) DEFAULT '0',
  task_percent_complete NUMBER(3,0) DEFAULT '0',
  task_description CLOB,
  task_target_budget FLOAT DEFAULT '0',
  task_related_url VARCHAR2(255),
  task_creator NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_order NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_client_publish NUMBER(3,0) DEFAULT '0' NOT NULL,
  task_dynamic NUMBER(3,0) DEFAULT '0' NOT NULL,
  task_access NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_notify NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_departments VARCHAR2(100),
  task_contacts VARCHAR2(100),
  task_custom CLOB,
  task_type NUMBER(5,0) DEFAULT '0' NOT NULL,
  task_updator NUMBER(10,0) DEFAULT '0' NOT NULL
);


ALTER TABLE tasks
ADD CONSTRAINT PRIMARY_19 PRIMARY KEY
(
  task_id
)
ENABLE
;
CREATE INDEX idx_task_parent ON tasks
(
  task_parent
) 
;
CREATE INDEX idx_task_project ON tasks
(
  task_project
) 
;
CREATE INDEX idx_task_owner ON tasks
(
  task_owner
) 
;
CREATE INDEX idx_task_order ON tasks
(
  task_order
) 
;
CREATE INDEX idx_task1 ON tasks
(
  task_start_date
) 
;
CREATE INDEX idx_task2 ON tasks
(
  task_end_date
) 
;
CREATE INDEX task_priority ON tasks
(
  task_priority
) 
;
CREATE INDEX task_name ON tasks
(
  task_name
) 
;
CREATE INDEX task_status ON tasks
(
  task_status
) 
;
CREATE INDEX task_percent_complete ON tasks
(
  task_percent_complete
) 
;
CREATE INDEX task_creator ON tasks
(
  task_creator
) 
;

CREATE TABLE tasks_critical (
  task_project NUMBER(10,0),
  critical_task NUMBER(10,0),
  project_actual_end_date DATE
);


CREATE INDEX task_project ON tasks_critical
(
  task_project
) 
;

CREATE TABLE tasks_problems (
  task_project NUMBER(10,0),
  task_log_problem NUMBER(3,0)
);


CREATE INDEX task_project_1 ON tasks_problems
(
  task_project
) 
;

CREATE TABLE tasks_sum (
  task_project NUMBER(10,0),
  total_tasks NUMBER(10,0),
  project_percent_complete FLOAT,
  project_duration FLOAT
);


CREATE INDEX task_project_2 ON tasks_sum
(
  task_project
) 
;

CREATE TABLE tasks_summy (
  task_project NUMBER(10,0),
  my_tasks VARCHAR2(10)
);


CREATE INDEX task_project_3 ON tasks_summy
(
  task_project
) 
;

CREATE TABLE tasks_total (
  task_project NUMBER(10,0),
  total_tasks NUMBER(10,0)
);


CREATE INDEX task_project_4 ON tasks_total
(
  task_project
) 
;

CREATE TABLE tasks_users (
  task_project NUMBER(10,0),
  user_id NUMBER(10,0)
);


CREATE INDEX task_project_5 ON tasks_users
(
  task_project
) 
;

CREATE TABLE user_access_log (
  user_access_log_id NUMBER(10,0) NOT NULL,
  user_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  user_ip VARCHAR2(15) NOT NULL,
  date_time_in DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss'),
  date_time_out DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss'),
  date_time_last_action DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss')
);


ALTER TABLE user_access_log
ADD CONSTRAINT PRIMARY_41 PRIMARY KEY
(
  user_access_log_id
)
ENABLE
;
CREATE INDEX date_time_last_action ON user_access_log
(
  date_time_last_action
) 
;
CREATE INDEX date_time_in ON user_access_log
(
  date_time_in
) 
;
CREATE INDEX date_time_out ON user_access_log
(
  date_time_out
) 
;

CREATE TABLE user_events (
  user_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  event_id NUMBER(10,0) DEFAULT '0' NOT NULL
);


CREATE INDEX uek1 ON user_events
(
  user_id,
  event_id
) 
;
CREATE INDEX uek2 ON user_events
(
  event_id,
  user_id
) 
;

CREATE TABLE user_preferences (
  pref_user VARCHAR2(12) NOT NULL,
  pref_name VARCHAR2(72) NOT NULL,
  pref_value VARCHAR2(32)
);


CREATE INDEX pref_user ON user_preferences
(
  pref_user,
  pref_name
) 
;
CREATE INDEX pref_user_2 ON user_preferences
(
  pref_user
) 
;

CREATE TABLE user_task_pin (
  user_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  task_pinned NUMBER(3,0) DEFAULT '1' NOT NULL
);


ALTER TABLE user_task_pin
ADD CONSTRAINT PRIMARY_38 PRIMARY KEY
(
  user_id,
  task_id
)
ENABLE
;
CREATE INDEX task_id_47 ON user_task_pin
(
  task_id
) 
;

CREATE TABLE user_tasks (
  user_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  user_type NUMBER(3,0) DEFAULT '0' NOT NULL,
  task_id NUMBER(10,0) DEFAULT '0' NOT NULL,
  perc_assignment NUMBER(10,0) DEFAULT '100' NOT NULL,
  user_task_priority NUMBER(3,0) DEFAULT '0'
);


ALTER TABLE user_tasks
ADD CONSTRAINT PRIMARY_42 PRIMARY KEY
(
  user_id,
  task_id
)
ENABLE
;
CREATE INDEX perc_assignment ON user_tasks
(
  perc_assignment
) 
;
CREATE INDEX user_id_47 ON user_tasks
(
  user_id
) 
;

CREATE TABLE users (
  user_id NUMBER(10,0) NOT NULL,
  user_contact NUMBER(10,0) DEFAULT '0' NOT NULL,
  user_username VARCHAR2(255) NOT NULL,
  user_password VARCHAR2(32) NOT NULL,
  user_parent NUMBER(10,0) DEFAULT '0' NOT NULL,
  user_type NUMBER(3,0) DEFAULT '0' NOT NULL,
  user_company NUMBER(10,0) DEFAULT '0',
  user_department NUMBER(10,0) DEFAULT '0',
  user_owner NUMBER(10,0) DEFAULT '0' NOT NULL,
  user_signature CLOB
);


ALTER TABLE users
ADD CONSTRAINT PRIMARY_5 PRIMARY KEY
(
  user_id
)
ENABLE
;
CREATE INDEX idx_uid ON users
(
  user_username
) 
;
CREATE INDEX idx_pwd ON users
(
  user_password
) 
;
CREATE INDEX user_contact ON users
(
  user_contact
) 
;

CREATE TABLE w2pversion (
  code_version VARCHAR2(10) NOT NULL,
  db_version NUMBER(10,0) DEFAULT '0' NOT NULL,
  last_db_update DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss') NOT NULL,
  last_code_update DATE DEFAULT to_date('1970-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss') NOT NULL
);



connect web2project/web2project;

CREATE OR REPLACE TRIGGER billingcode_billingcode_id_TRG BEFORE INSERT OR UPDATE ON billingcode
FOR EACH ROW
BEGIN
  if inserting and :new.billingcode_id is NULL then
  SELECT billingcode_billingcode_id_SEQ.nextval into :new.billingcode_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER common_notes_note_id_TRG BEFORE INSERT OR UPDATE ON common_notes
FOR EACH ROW
BEGIN
  if inserting and :new.note_id is NULL then
  SELECT common_notes_note_id_SEQ.nextval into :new.note_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER companies_company_id_TRG BEFORE INSERT OR UPDATE ON companies
FOR EACH ROW
BEGIN
  if inserting and :new.company_id is NULL then
  SELECT companies_company_id_SEQ.nextval into :new.company_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER config_config_id_TRG BEFORE INSERT OR UPDATE ON config
FOR EACH ROW
BEGIN
  if inserting and :new.config_id is NULL then
  SELECT config_config_id_SEQ.nextval into :new.config_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER config_list_config_list_id_TRG BEFORE INSERT OR UPDATE ON config_list
FOR EACH ROW
BEGIN
  if inserting and :new.config_list_id is NULL then
  SELECT config_list_config_list_id_SEQ.nextval into :new.config_list_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER contacts_contact_id_TRG BEFORE INSERT OR UPDATE ON contacts
FOR EACH ROW
BEGIN
  if inserting and :new.contact_id is NULL then
  SELECT contacts_contact_id_SEQ.nextval into :new.contact_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER departments_dept_id_TRG BEFORE INSERT OR UPDATE ON departments
FOR EACH ROW
BEGIN
  if inserting and :new.dept_id is NULL then
  SELECT departments_dept_id_SEQ.nextval into :new.dept_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER event_queue_queue_id_TRG BEFORE INSERT OR UPDATE ON event_queue
FOR EACH ROW
BEGIN
  if inserting and :new.queue_id is NULL then
  SELECT event_queue_queue_id_SEQ.nextval into :new.queue_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER events_event_id_TRG BEFORE INSERT OR UPDATE ON events
FOR EACH ROW
BEGIN
  if inserting and :new.event_id is NULL then
  SELECT events_event_id_SEQ.nextval into :new.event_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER file_folders_file_folder_id_TR BEFORE INSERT OR UPDATE ON file_folders
FOR EACH ROW
BEGIN
  if inserting and :new.file_folder_id is NULL then
  SELECT file_folders_file_folder_id_SE.nextval into :new.file_folder_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER files_file_id_TRG BEFORE INSERT OR UPDATE ON files
FOR EACH ROW
BEGIN
  if inserting and :new.file_id is NULL then
  SELECT files_file_id_SEQ.nextval into :new.file_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER forum_messages_message_id_TRG BEFORE INSERT OR UPDATE ON forum_messages
FOR EACH ROW
BEGIN
  if inserting and :new.message_id is NULL then
  SELECT forum_messages_message_id_SEQ.nextval into :new.message_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER forums_forum_id_TRG BEFORE INSERT OR UPDATE ON forums
FOR EACH ROW
BEGIN
  if inserting and :new.forum_id is NULL then
  SELECT forums_forum_id_SEQ.nextval into :new.forum_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER modules_mod_id_TRG BEFORE INSERT OR UPDATE ON modules
FOR EACH ROW
BEGIN
  if inserting and :new.mod_id is NULL then
  SELECT modules_mod_id_SEQ.nextval into :new.mod_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER project_designer_options_pd_op BEFORE INSERT OR UPDATE ON project_designer_options
FOR EACH ROW
BEGIN
  if inserting and :new.pd_option_id is NULL then
  SELECT project_designer_options_pd_op.nextval into :new.pd_option_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER projects_project_id_TRG BEFORE INSERT OR UPDATE ON projects
FOR EACH ROW
BEGIN
  if inserting and :new.project_id is NULL then
  SELECT projects_project_id_SEQ.nextval into :new.project_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER syskeys_syskey_id_TRG BEFORE INSERT OR UPDATE ON syskeys
FOR EACH ROW
BEGIN
  if inserting and :new.syskey_id is NULL then
  SELECT syskeys_syskey_id_SEQ.nextval into :new.syskey_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER sysvals_sysval_id_TRG BEFORE INSERT OR UPDATE ON sysvals
FOR EACH ROW
BEGIN
  if inserting and :new.sysval_id is NULL then
  SELECT sysvals_sysval_id_SEQ.nextval into :new.sysval_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER task_log_task_log_id_TRG BEFORE INSERT OR UPDATE ON task_log
FOR EACH ROW
BEGIN
  if inserting and :new.task_log_id is NULL then
  SELECT task_log_task_log_id_SEQ.nextval into :new.task_log_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER tasks_task_id_TRG BEFORE INSERT OR UPDATE ON tasks
FOR EACH ROW
BEGIN
  if inserting and :new.task_id is NULL then
  SELECT tasks_task_id_SEQ.nextval into :new.task_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER tickets_ticket_TRG BEFORE INSERT OR UPDATE ON tickets
FOR EACH ROW
BEGIN
  if inserting and :new.ticket is NULL then
  SELECT tickets_ticket_SEQ.nextval into :new.ticket FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER user_access_log_user_access_lo BEFORE INSERT OR UPDATE ON user_access_log
FOR EACH ROW
BEGIN
  if inserting and :new.user_access_log_id is NULL then
  SELECT user_access_log_user_access_lo.nextval into :new.user_access_log_id FROM DUAL;
  end if;
END;

/

CREATE OR REPLACE TRIGGER users_user_id_TRG BEFORE INSERT OR UPDATE ON users
FOR EACH ROW
BEGIN
  if inserting and :new.user_id is NULL then
  SELECT users_user_id_SEQ.nextval into :new.user_id FROM DUAL;
  end if;
END;

/

--DML
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (47, 'host_locale', 'en', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (48, 'check_overallocation', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (49, 'currency_symbol', '$', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (50, 'host_style', 'web2project', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (51, 'company_name', 'My Company', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (52, 'page_title', 'web2Project', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (53, 'site_domain', 'web2project.net', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (54, 'email_prefix', '[web2Project]', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (55, 'admin_username', 'admin', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (56, 'username_min_len', '4', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (57, 'password_min_len', '4', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (58, 'enable_gantt_charts', 'true', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (59, 'log_changes', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (60, 'check_task_dates', 'true', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (61, 'locale_warn', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (62, 'locale_alert', '^', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (63, 'daily_working_hours', '8.0', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (64, 'display_debug', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (65, 'link_tickets_kludge', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (66, 'show_all_task_assignees', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (67, 'direct_edit_assignment', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (68, 'restrict_color_selection', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (69, 'cal_day_view_show_minical', 'true', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (70, 'cal_day_start', '8', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (71, 'cal_day_end', '17', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (72, 'cal_day_increment', '15', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (73, 'cal_working_days', '1,2,3,4,5', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (74, 'restrict_task_time_editing', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (75, 'default_view_m', 'calendar', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (76, 'default_view_a', 'day_view', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (77, 'default_view_tab', '1', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (78, 'index_max_file_size', '-1', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (79, 'session_handling', 'app', 'session', 'select');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (80, 'session_idle_time', '2d', 'session', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (81, 'session_max_lifetime', '1m', 'session', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (82, 'debug', '1', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (83, 'parser_default', '/usr/bin/strings', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (84, 'parser_application/msword', '/usr/bin/strings', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (85, 'parser_text/html', '/usr/bin/strings', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (86, 'parser_application/pdf', '/usr/bin/pdftotext', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (87, 'files_ci_preserve_attr', 'true', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (88, 'files_show_versions_edit', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (89, 'reset_memory_limit', '32M', '', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (90, 'auth_method', 'sql', 'auth', 'select');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (91, 'ldap_host', 'localhost', 'ldap', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (92, 'ldap_port', '389', 'ldap', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (93, 'ldap_version', '3', 'ldap', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (94, 'ldap_base_dn', 'dc=saki,dc=com,dc=au', 'ldap', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (95, 'ldap_user_filter', '(uid=%USERNAME%)', 'ldap', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (96, 'postnuke_allow_login', 'true', 'auth', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (97, 'mail_transport', 'php', 'mail', 'select');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (98, 'mail_host', 'localhost', 'mail', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (99, 'mail_port', '25', 'mail', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (100, 'mail_auth', 'false', 'mail', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (101, 'mail_user', '', 'mail', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (102, 'mail_pass', '', 'mail', 'password');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (103, 'mail_defer', 'false', 'mail', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (104, 'mail_timeout', '30', 'mail', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (105, 'task_reminder_control', 'false', 'task_reminder', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (106, 'task_reminder_days_before', '1', 'task_reminder', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (107, 'task_reminder_repeat', '100', 'task_reminder', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (108, 'session_gc_scan_queue', 'false', 'session', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (109, 'ldap_search_user', 'Manager', 'ldap', 'text');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (110, 'ldap_search_pass', 'secret', 'ldap', 'password');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (111, 'ldap_allow_login', 'true', 'ldap', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (112, 'activate_external_user_creation', 'true', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (113, 'projectdesigner_view_project', 'false', '', 'checkbox');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (114, 'mail_secure', '', 'mail', 'select');
INSERT INTO config (config_id, config_name, config_value, config_group, config_type) VALUES (115, 'mail_debug', 'false', 'mail', 'checkbox');

INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (1, 90, 'sql');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (2, 90, 'ldap');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (3, 90, 'pn');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (4, 79, 'app');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (5, 79, 'php');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (6, 97, 'php');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (7, 97, 'smtp');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (8, 68, '');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (9, 68, 'tls');
INSERT INTO config_list (config_list_id, config_id, config_list_name) VALUES (10, 68, 'ssl');

INSERT INTO contacts (contact_id, contact_first_name, contact_last_name, contact_order_by, contact_title, contact_birthday, contact_job, contact_company, contact_department, contact_type, contact_email, contact_email2, contact_url, contact_phone, contact_phone2, contact_fax, contact_mobile, contact_address1, contact_address2, contact_city, contact_state, contact_zip, contact_country, contact_jabber, contact_icq, contact_msn, contact_yahoo, contact_aol, contact_notes, contact_project, contact_icon, contact_owner, contact_private, contact_updatekey, contact_lastupdate, contact_updateasked, contact_skype, contact_google) VALUES (1, 'Admin', 'Personalização', '', NULL, NULL, NULL, 0, 0, NULL, 'admin@localhost', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'obj/contact', 0, 0, NULL, NULL, NULL, '', '');
INSERT INTO contacts (contact_id, contact_first_name, contact_last_name, contact_order_by, contact_title, contact_birthday, contact_job, contact_company, contact_department, contact_type, contact_email, contact_email2, contact_url, contact_phone, contact_phone2, contact_fax, contact_mobile, contact_address1, contact_address2, contact_city, contact_state, contact_zip, contact_country, contact_jabber, contact_icq, contact_msn, contact_yahoo, contact_aol, contact_notes, contact_project, contact_icon, contact_owner, contact_private, contact_updatekey, contact_lastupdate, contact_updateasked, contact_skype, contact_google) VALUES (2, 'test', 'user', '', NULL, NULL, NULL, 0, 0, NULL, 'pedroa@web2project.net', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'obj/contact', 1, 0, NULL, NULL, NULL, '', '');

INSERT INTO gacl_acl (id, section_value, allow, enabled, return_value, note, updated_date) VALUES (10, 'user', 1, 1, '', '', 1195510857);
INSERT INTO gacl_acl (id, section_value, allow, enabled, return_value, note, updated_date) VALUES (11, 'user', 1, 1, '', '', 1195510857);
INSERT INTO gacl_acl (id, section_value, allow, enabled, return_value, note, updated_date) VALUES (12, 'user', 1, 1, '', '', 1195510857);
INSERT INTO gacl_acl (id, section_value, allow, enabled, return_value, note, updated_date) VALUES (13, 'user', 1, 1, '', '', 1195510857);
INSERT INTO gacl_acl (id, section_value, allow, enabled, return_value, note, updated_date) VALUES (14, 'user', 1, 1, '', '', 1195510857);
INSERT INTO gacl_acl (id, section_value, allow, enabled, return_value, note, updated_date) VALUES (15, 'user', 1, 1, '', '', 1195510857);

INSERT INTO gacl_acl_sections (id, value, order_value, name, hidden) VALUES (1, 'system', 1, 'System', 0);
INSERT INTO gacl_acl_sections (id, value, order_value, name, hidden) VALUES (2, 'user', 2, 'User', 0);

INSERT INTO gacl_acl_seq (id) VALUES (15);

INSERT INTO gacl_aco (id, section_value, value, order_value, name, hidden) VALUES (10, 'system', 'login', 1, 'Login', 0);
INSERT INTO gacl_aco (id, section_value, value, order_value, name, hidden) VALUES (11, 'application', 'access', 1, 'access', 0);
INSERT INTO gacl_aco (id, section_value, value, order_value, name, hidden) VALUES (12, 'application', 'view', 2, 'View', 0);
INSERT INTO gacl_aco (id, section_value, value, order_value, name, hidden) VALUES (13, 'application', 'add', 3, 'Add', 0);
INSERT INTO gacl_aco (id, section_value, value, order_value, name, hidden) VALUES (14, 'application', 'edit', 4, 'Edit', 0);
INSERT INTO gacl_aco (id, section_value, value, order_value, name, hidden) VALUES (15, 'application', 'delete', 5, 'Delete', 0);

INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (10, 'system', 'login');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (11, 'application', 'access');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (11, 'application', 'add');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (11, 'application', 'delete');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (11, 'application', 'edit');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (11, 'application', 'view');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (12, 'application', 'access');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (13, 'application', 'access');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (13, 'application', 'view');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (14, 'application', 'access');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (15, 'application', 'access');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (15, 'application', 'add');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (15, 'application', 'delete');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (15, 'application', 'edit');
INSERT INTO gacl_aco_map (acl_id, section_value, value) VALUES (15, 'application', 'view');

INSERT INTO gacl_aco_sections (id, value, order_value, name, hidden) VALUES (10, 'system', 1, 'System', 0);
INSERT INTO gacl_aco_sections (id, value, order_value, name, hidden) VALUES (11, 'application', 2, 'Application', 0);

INSERT INTO gacl_aco_sections_seq (id) VALUES (11);

INSERT INTO gacl_aco_seq (id) VALUES (15);

INSERT INTO gacl_aro (id, section_value, value, order_value, name, hidden) VALUES (10, 'user', '1', 1, 'admin', 0);
INSERT INTO gacl_aro (id, section_value, value, order_value, name, hidden) VALUES (11, 'user', '2', 1, 'test', 0);

INSERT INTO gacl_aro_groups (id, parent_id, lft, rgt, name, value) VALUES (10, 0, 1, 10, 'Roles', 'role');
INSERT INTO gacl_aro_groups (id, parent_id, lft, rgt, name, value) VALUES (11, 10, 2, 3, 'Administrator', 'admin');
INSERT INTO gacl_aro_groups (id, parent_id, lft, rgt, name, value) VALUES (12, 10, 4, 5, 'Anonymous', 'anon');
INSERT INTO gacl_aro_groups (id, parent_id, lft, rgt, name, value) VALUES (13, 10, 6, 7, 'Guest', 'guest');
INSERT INTO gacl_aro_groups (id, parent_id, lft, rgt, name, value) VALUES (14, 10, 8, 9, 'Project worker', 'normal');

INSERT INTO gacl_aro_groups_id_seq (id) VALUES (14);

INSERT INTO gacl_aro_groups_map (acl_id, group_id) VALUES (10, 10);
INSERT INTO gacl_aro_groups_map (acl_id, group_id) VALUES (11, 11);
INSERT INTO gacl_aro_groups_map (acl_id, group_id) VALUES (12, 11);
INSERT INTO gacl_aro_groups_map (acl_id, group_id) VALUES (13, 13);
INSERT INTO gacl_aro_groups_map (acl_id, group_id) VALUES (14, 12);
INSERT INTO gacl_aro_groups_map (acl_id, group_id) VALUES (15, 14);

INSERT INTO gacl_aro_sections (id, value, order_value, name, hidden) VALUES (10, 'user', 1, 'Users', 0);

INSERT INTO gacl_aro_sections_seq (id) VALUES (10);

INSERT INTO gacl_aro_seq (id) VALUES (11);

INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (10, 'sys', 'acl', 1, 'ACL Administration', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (11, 'app', 'admin', 1, 'User Administration', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (12, 'app', 'calendar', 2, 'Calendar', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (13, 'app', 'events', 2, 'Events', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (14, 'app', 'companies', 3, 'Companies', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (15, 'app', 'contacts', 4, 'Contacts', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (16, 'app', 'departments', 5, 'Departments', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (17, 'app', 'files', 6, 'Files', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (18, 'app', 'forums', 7, 'Forums', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (19, 'app', 'help', 8, 'Help', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (20, 'app', 'projects', 9, 'Projects', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (21, 'app', 'system', 10, 'System Administration', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (22, 'app', 'tasks', 11, 'Tasks', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (23, 'app', 'task_log', 11, 'Task Logs', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (24, 'app', 'ticketsmith', 12, 'Tickets', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (25, 'app', 'public', 13, 'Public', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (26, 'app', 'roles', 14, 'Roles Administration', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (27, 'app', 'users', 15, 'User Table', 0);
INSERT INTO gacl_axo (id, section_value, value, order_value, name, hidden) VALUES (28, 'app', 'projectdesigner', 1, 'ProjectDesigner', 0);

INSERT INTO gacl_axo_groups (id, parent_id, lft, rgt, name, value) VALUES (10, 0, 1, 8, 'Modules', 'mod');
INSERT INTO gacl_axo_groups (id, parent_id, lft, rgt, name, value) VALUES (11, 10, 2, 3, 'All Modules', 'all');
INSERT INTO gacl_axo_groups (id, parent_id, lft, rgt, name, value) VALUES (12, 10, 4, 5, 'Admin Modules', 'admin');
INSERT INTO gacl_axo_groups (id, parent_id, lft, rgt, name, value) VALUES (13, 10, 6, 7, 'Non-Admin Modules', 'non_admin');

INSERT INTO gacl_axo_groups_id_seq (id) VALUES (13);

INSERT INTO gacl_axo_groups_map (acl_id, group_id) VALUES (11, 11);
INSERT INTO gacl_axo_groups_map (acl_id, group_id) VALUES (13, 13);
INSERT INTO gacl_axo_groups_map (acl_id, group_id) VALUES (14, 13);
INSERT INTO gacl_axo_groups_map (acl_id, group_id) VALUES (15, 13);

INSERT INTO gacl_axo_map (acl_id, section_value, value) VALUES (12, 'sys', 'acl');

INSERT INTO gacl_axo_sections (id, value, order_value, name, hidden) VALUES (10, 'sys', 1, 'System', 0);
INSERT INTO gacl_axo_sections (id, value, order_value, name, hidden) VALUES (11, 'app', 2, 'Application', 0);

INSERT INTO gacl_axo_sections_seq (id) VALUES (11);

INSERT INTO gacl_axo_seq (id) VALUES (28);

INSERT INTO gacl_groups_aro_map (group_id, aro_id) VALUES (11, 10);
INSERT INTO gacl_groups_aro_map (group_id, aro_id) VALUES (13, 11);

INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 11);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 12);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 13);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 14);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 15);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 16);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 17);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 18);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 19);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 20);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 21);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 22);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 23);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 24);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 25);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 26);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 27);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (11, 28);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (12, 11);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (12, 21);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (12, 26);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (12, 27);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 12);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 13);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 14);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 15);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 16);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 17);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 18);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 19);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 20);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 22);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 23);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 24);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 25);
INSERT INTO gacl_groups_axo_map (group_id, axo_id) VALUES (13, 28);

INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'contacts', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'companies', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'calendar', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'admin', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'users', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'ticketsmith', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'task_log', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'tasks', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'system', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'roles', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'public', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projects', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projectdesigner', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'help', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'files', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'forums', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'events', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'departments', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'contacts', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'companies', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'calendar', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'admin', 0, 'edit', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'users', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'ticketsmith', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'task_log', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'tasks', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'system', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'roles', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'public', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projects', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projectdesigner', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'help', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'forums', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'files', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'events', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'departments', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'contacts', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'companies', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'calendar', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'admin', 0, 'delete', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'users', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'ticketsmith', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'task_log', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'tasks', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'system', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'roles', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'public', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projects', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projectdesigner', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'help', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'forums', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'files', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'events', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'departments', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'contacts', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'companies', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'calendar', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'admin', 0, 'add', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'users', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'ticketsmith', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'task_log', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'tasks', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'system', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'roles', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'public', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projects', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'acl', 0, 'access', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'acl', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'acl', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'acl', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'acl', 0, 'view', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'admin', 0, 'access', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'calendar', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'companies', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'contacts', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'departments', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'events', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'files', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'forums', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'help', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projectdesigner', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projects', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'public', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'roles', 0, 'access', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'system', 0, 'access', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'tasks', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'task_log', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'ticketsmith', 0, 'access', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'users', 0, 'access', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'admin', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'calendar', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'companies', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'contacts', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'departments', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'events', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'files', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'forums', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'help', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projectdesigner', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projects', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'public', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'roles', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'system', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'tasks', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'task_log', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'ticketsmith', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'users', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'admin', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'calendar', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'companies', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'contacts', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'departments', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'events', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'files', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'forums', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'help', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projectdesigner', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projects', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'public', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'roles', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'system', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'tasks', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'task_log', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'ticketsmith', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'users', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'admin', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'calendar', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'companies', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'contacts', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'departments', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'events', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'files', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'forums', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'help', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projectdesigner', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projects', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'public', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'roles', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'system', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'tasks', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'task_log', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'ticketsmith', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'users', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'admin', 0, 'view', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'calendar', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'companies', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'contacts', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'departments', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'events', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'files', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'forums', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'help', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projectdesigner', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'projects', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'public', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'roles', 0, 'view', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'system', 0, 'view', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'tasks', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'task_log', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'ticketsmith', 0, 'view', 1, 13);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (2, 'test', 'users', 0, 'view', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'departments', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projectdesigner', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'help', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'forums', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'files', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'events', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'departments', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'contacts', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'companies', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'calendar', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'admin', 0, 'access', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'acl', 0, 'view', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'acl', 0, 'edit', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'acl', 0, 'delete', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'acl', 0, 'add', 0, 0);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'acl', 0, 'access', 1, 12);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'events', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'files', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'forums', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'help', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projectdesigner', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'projects', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'public', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'roles', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'system', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'tasks', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'task_log', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'ticketsmith', 0, 'view', 1, 11);
INSERT INTO gacl_permissions (user_id, user_name, module, item_id, action, "access", acl_id) VALUES (1, 'admin', 'users', 0, 'view', 1, 11);

INSERT INTO gacl_phpgacl (name, value) VALUES ('version', '3.3.2');
INSERT INTO gacl_phpgacl (name, value) VALUES ('schema_version', '2.1');

INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (1, 'Companies', 'companies', '1.0.0', '', 'core', 1, 'Companies', 'handshake.png', 1, 1, '', 'companies', 'company_id', 'company_name', 'CCompany');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (2, 'Projects', 'projects', '1.0.0', '', 'core', 1, 'Projects', 'applet3-48.png', 2, 1, '', 'projects', 'project_id', 'project_name', 'CProject');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (3, 'Tasks', 'tasks', '1.0.0', '', 'core', 1, 'Tasks', 'applet-48.png', 3, 1, '', 'tasks', 'task_id', 'task_name', 'CTask');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (4, 'Calendar', 'calendar', '1.0.0', '', 'core', 1, 'Calendar', 'myevo-appointments.png', 4, 1, '', 'events', 'event_id', 'event_title', 'CEvent');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (5, 'Files', 'files', '1.0.0', '', 'core', 1, 'Files', 'folder5.png', 5, 1, '', 'files', 'file_id', 'file_name', 'CFile');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (6, 'Contacts', 'contacts', '1.0.0', '', 'core', 1, 'Contacts', 'monkeychat-48.png', 6, 1, '', 'contacts', 'contact_id', 'contact_first_name', 'CContact');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (7, 'Forums', 'forums', '1.0.0', '', 'core', 1, 'Forums', 'support.png', 7, 1, '', 'forums', 'forum_id', 'forum_name', 'CForum');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (8, 'Tickets', 'ticketsmith', '1.0.0', '', 'core', 1, 'Tickets', 'ticketsmith.gif', 8, 1, '', '', '', '', '');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (9, 'User Administration', 'admin', '1.0.0', '', 'core', 1, 'User Admin', 'helix-setup-users.png', 9, 1, '', 'users', 'user_id', 'user_username', '');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (10, 'System Administration', 'system', '1.0.0', '', 'core', 1, 'System Admin', '48_my_computer.png', 10, 1, '', '', '', '', '');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (11, 'Departments', 'departments', '1.0.0', '', 'core', 1, 'Departments', 'users.gif', 11, 0, '', 'departments', 'dept_id', 'dept_name', 'CDepartment');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (12, 'Help', 'help', '1.0.0', '', 'core', 1, 'Help', 'w2p.gif', 12, 0, '', '', '', '', '');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (13, 'Public', 'public', '1.0.0', '', 'core', 1, 'Public', 'users.gif', 13, 0, '', '', '', '', '');
INSERT INTO modules (mod_id, mod_name, mod_directory, mod_version, mod_setup_class, mod_type, mod_active, mod_ui_name, mod_ui_icon, mod_ui_order, mod_ui_active, mod_description, permissions_item_table, permissions_item_field, permissions_item_label, mod_main_class) VALUES (14, 'ProjectDesigner', 'projectdesigner', '1.0', 'projectDesigner', 'user', 1, 'ProjectDesigner', 'projectdesigner.jpg', 13, 0, 'A module to design projects', NULL, NULL, NULL, '');

INSERT INTO syskeys (syskey_id, syskey_name, syskey_label, syskey_type, syskey_sep1, syskey_sep2) VALUES (1, 'SelectList', 'Enter values for list', 0, '\n', '|');
INSERT INTO syskeys (syskey_id, syskey_name, syskey_label, syskey_type, syskey_sep1, syskey_sep2) VALUES (2, 'CustomField', 'Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Fields name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>', 0, '\n', '|');
INSERT INTO syskeys (syskey_id, syskey_name, syskey_label, syskey_type, syskey_sep1, syskey_sep2) VALUES (3, 'ColorSelection', 'Hex color values for type=>color association.', 0, '\n', '|');

INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (307, 1, 'ProjectStatus', 'Not Defined', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (308, 1, 'ProjectStatus', 'Proposed', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (281, 1, 'CompanyType', 'Not Applicable', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (282, 1, 'CompanyType', 'Client', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (3, 1, 'TaskDurationType', '1|hours\n24|days', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (288, 1, 'EventType', 'General', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (289, 1, 'EventType', 'Appointment', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (5, 1, 'TaskStatus', '0|Active\n-1|Inactive', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (6, 1, 'TaskType', '0|Unknown\n1|Administrative\n2|Operative', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (7, 1, 'ProjectType', '0|Unknown\n1|Administrative\n2|Operative', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (297, 3, 'ProjectColors', 'FFE0AE', 'Web');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (298, 3, 'ProjectColors', 'AEFFB2', 'Engineering');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (294, 1, 'FileType', 'Unknown', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (10, 1, 'TaskPriority', '-1|low\n0|normal\n1|high', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (301, 1, 'ProjectPriority', 'low', '-1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (304, 1, 'ProjectPriorityColor', '#E5F7FF', '-1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (13, 1, 'TaskLogReference', '0|Not Defined\n1|Email\n2|Helpdesk\n3|Phone Call\n4|Fax', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (14, 1, 'TaskLogReferenceImage', '0| 1|./images/obj/email.gif 2|./modules/helpdesk/images/helpdesk.png 3|./images/obj/phone.gif 4|./images/icons/stock_print-16.png', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (15, 1, 'UserType', '0|Default User\r\n1|Administrator\r\n2|CEO\r\n3|Director\r\n4|Branch Manager\r\n5|Manager\r\n6|Supervisor\r\n7|Employee', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (17, 2, 'TicketNotify', '0|admin@localhost\n1|admin@localhost\n2|admin@localhost\r\n3|admin@localhost\r\n4|admin@localhost', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (18, 1, 'TicketPriority', '0|Low\n1|Normal\n2|High\n3|Highest\n4|911', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (19, 1, 'TicketStatus', '0|Open\n1|Closed\n2|Deleted', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (316, 1, 'ProjectRequiredFields', '<3', 'f.project_color_identifier.value.length');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (315, 1, 'ProjectRequiredFields', '<3', 'f.project_name.value.length');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (21, 1, 'GlobalYesNo', 'No', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (22, 1, 'GlobalYesNo', 'Yes', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (23, 1, 'UserType', 'Default User', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (24, 1, 'UserType', 'Administrator', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (25, 1, 'UserType', 'CEO', '2');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (26, 1, 'UserType', 'Director', '3');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (27, 1, 'UserType', 'Branch Manager', '4');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (28, 1, 'UserType', 'Manager', '5');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (29, 1, 'UserType', 'Supervisor', '6');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (30, 1, 'UserType', 'Employee', '7');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (31, 1, 'GlobalCountries', 'Andorra, Principality of', 'AD');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (32, 1, 'GlobalCountries', 'United Arab Emirates', 'AE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (33, 1, 'GlobalCountries', 'Afghanistan, Islamic State of', 'AF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (34, 1, 'GlobalCountries', 'Antigua and Barbuda', 'AG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (35, 1, 'GlobalCountries', 'Anguilla', 'AI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (36, 1, 'GlobalCountries', 'Albania', 'AL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (37, 1, 'GlobalCountries', 'Armenia', 'AM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (38, 1, 'GlobalCountries', 'Netherlands Antilles', 'AN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (39, 1, 'GlobalCountries', 'Angola', 'AO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (40, 1, 'GlobalCountries', 'Antarctica', 'AQ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (41, 1, 'GlobalCountries', 'Argentina', 'AR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (42, 1, 'GlobalCountries', 'American Samoa', 'AS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (43, 1, 'GlobalCountries', 'Austria', 'AT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (44, 1, 'GlobalCountries', 'Australia', 'AU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (45, 1, 'GlobalCountries', 'Aruba', 'AW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (46, 1, 'GlobalCountries', 'Azerbaidjan', 'AZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (47, 1, 'GlobalCountries', 'Bosnia-Herzegovina', 'BA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (48, 1, 'GlobalCountries', 'Barbados', 'BB');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (49, 1, 'GlobalCountries', 'Bangladesh', 'BD');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (50, 1, 'GlobalCountries', 'Belgium', 'BE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (51, 1, 'GlobalCountries', 'Burkina Faso', 'BF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (52, 1, 'GlobalCountries', 'Bulgaria', 'BG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (53, 1, 'GlobalCountries', 'Bahrain', 'BH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (54, 1, 'GlobalCountries', 'Burundi', 'BI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (55, 1, 'GlobalCountries', 'Benin', 'BJ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (56, 1, 'GlobalCountries', 'Bermuda', 'BM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (57, 1, 'GlobalCountries', 'Brunei Darussalam', 'BN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (58, 1, 'GlobalCountries', 'Bolivia', 'BO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (59, 1, 'GlobalCountries', 'Brazil', 'BR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (60, 1, 'GlobalCountries', 'Bahamas', 'BS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (61, 1, 'GlobalCountries', 'Bhutan', 'BT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (62, 1, 'GlobalCountries', 'Bouvet Island', 'BV');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (63, 1, 'GlobalCountries', 'Botswana', 'BW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (64, 1, 'GlobalCountries', 'Belarus', 'BY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (65, 1, 'GlobalCountries', 'Belize', 'BZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (66, 1, 'GlobalCountries', 'Canada', 'CA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (67, 1, 'GlobalCountries', 'Cocos (Keeling) Islands', 'CC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (68, 1, 'GlobalCountries', 'Central African Republic', 'CF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (69, 1, 'GlobalCountries', 'Congo, The Democratic Republic of the', 'CD');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (70, 1, 'GlobalCountries', 'Congo', 'CG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (71, 1, 'GlobalCountries', 'Switzerland', 'CH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (72, 1, 'GlobalCountries', 'Ivory Coast (Cote D''Ivoire)', 'CI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (73, 1, 'GlobalCountries', 'Cook Islands', 'CK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (74, 1, 'GlobalCountries', 'Chile', 'CL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (75, 1, 'GlobalCountries', 'Cameroon', 'CM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (76, 1, 'GlobalCountries', 'China', 'CN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (77, 1, 'GlobalCountries', 'Colombia', 'CO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (78, 1, 'GlobalCountries', 'Costa Rica', 'CR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (79, 1, 'GlobalCountries', 'Former Czechoslovakia', 'CS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (80, 1, 'GlobalCountries', 'Cuba', 'CU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (81, 1, 'GlobalCountries', 'Cape Verde', 'CV');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (82, 1, 'GlobalCountries', 'Christmas Island', 'CX');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (83, 1, 'GlobalCountries', 'Cyprus', 'CY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (84, 1, 'GlobalCountries', 'Czech Republic', 'CZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (85, 1, 'GlobalCountries', 'Germany', 'DE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (86, 1, 'GlobalCountries', 'Djibouti', 'DJ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (87, 1, 'GlobalCountries', 'Denmark', 'DK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (88, 1, 'GlobalCountries', 'Dominica', 'DM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (89, 1, 'GlobalCountries', 'Dominican Republic', 'DO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (90, 1, 'GlobalCountries', 'Algeria', 'DZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (91, 1, 'GlobalCountries', 'Ecuador', 'EC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (92, 1, 'GlobalCountries', 'Estonia', 'EE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (93, 1, 'GlobalCountries', 'Egypt', 'EG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (94, 1, 'GlobalCountries', 'Western Sahara', 'EH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (95, 1, 'GlobalCountries', 'Eritrea', 'ER');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (96, 1, 'GlobalCountries', 'Spain', 'ES');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (97, 1, 'GlobalCountries', 'Ethiopia', 'ET');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (98, 1, 'GlobalCountries', 'Finland', 'FI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (99, 1, 'GlobalCountries', 'Fiji', 'FJ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (100, 1, 'GlobalCountries', 'Falkland Islands', 'FK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (101, 1, 'GlobalCountries', 'Micronesia', 'FM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (102, 1, 'GlobalCountries', 'Faroe Islands', 'FO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (103, 1, 'GlobalCountries', 'France', 'FR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (104, 1, 'GlobalCountries', 'Gabon', 'GA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (105, 1, 'GlobalCountries', 'Great Britain', 'GB');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (106, 1, 'GlobalCountries', 'Grenada', 'GD');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (107, 1, 'GlobalCountries', 'Georgia', 'GE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (108, 1, 'GlobalCountries', 'French Guyana', 'GF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (109, 1, 'GlobalCountries', 'Ghana', 'GH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (110, 1, 'GlobalCountries', 'Gibraltar', 'GI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (111, 1, 'GlobalCountries', 'Greenland', 'GL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (112, 1, 'GlobalCountries', 'Gambia', 'GM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (113, 1, 'GlobalCountries', 'Guinea', 'GN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (114, 1, 'GlobalCountries', 'Guadeloupe (French)', 'GP');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (115, 1, 'GlobalCountries', 'Equatorial Guinea', 'GQ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (116, 1, 'GlobalCountries', 'Greece', 'GR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (117, 1, 'GlobalCountries', 'S. Georgia & S. Sandwich Isls.', 'GS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (118, 1, 'GlobalCountries', 'Guatemala', 'GT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (119, 1, 'GlobalCountries', 'Guam (USA)', 'GU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (120, 1, 'GlobalCountries', 'Guinea Bissau', 'GW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (121, 1, 'GlobalCountries', 'Guyana', 'GY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (122, 1, 'GlobalCountries', 'Hong Kong', 'HK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (123, 1, 'GlobalCountries', 'Heard and McDonald Islands', 'HM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (124, 1, 'GlobalCountries', 'Honduras', 'HN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (125, 1, 'GlobalCountries', 'Croatia', 'HR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (126, 1, 'GlobalCountries', 'Haiti', 'HT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (127, 1, 'GlobalCountries', 'Hungary', 'HU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (128, 1, 'GlobalCountries', 'Indonesia', 'ID');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (129, 1, 'GlobalCountries', 'Ireland', 'IE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (130, 1, 'GlobalCountries', 'Israel', 'IL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (131, 1, 'GlobalCountries', 'India', 'IN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (132, 1, 'GlobalCountries', 'British Indian Ocean Territory', 'IO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (133, 1, 'GlobalCountries', 'Iraq', 'IQ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (134, 1, 'GlobalCountries', 'Iran', 'IR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (135, 1, 'GlobalCountries', 'Iceland', 'IS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (136, 1, 'GlobalCountries', 'Italy', 'IT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (137, 1, 'GlobalCountries', 'Jamaica', 'JM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (138, 1, 'GlobalCountries', 'Jordan', 'JO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (139, 1, 'GlobalCountries', 'Japan', 'JP');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (140, 1, 'GlobalCountries', 'Kenya', 'KE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (141, 1, 'GlobalCountries', 'Kyrgyz Republic (Kyrgyzstan)', 'KG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (142, 1, 'GlobalCountries', 'Cambodia, Kingdom of', 'KH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (143, 1, 'GlobalCountries', 'Kiribati', 'KI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (144, 1, 'GlobalCountries', 'Comoros', 'KM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (145, 1, 'GlobalCountries', 'Saint Kitts & Nevis Anguilla', 'KN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (146, 1, 'GlobalCountries', 'North Korea', 'KP');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (147, 1, 'GlobalCountries', 'South Korea', 'KR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (148, 1, 'GlobalCountries', 'Kuwait', 'KW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (149, 1, 'GlobalCountries', 'Cayman Islands', 'KY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (150, 1, 'GlobalCountries', 'Kazakhstan', 'KZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (151, 1, 'GlobalCountries', 'Laos', 'LA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (152, 1, 'GlobalCountries', 'Lebanon', 'LB');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (153, 1, 'GlobalCountries', 'Saint Lucia', 'LC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (154, 1, 'GlobalCountries', 'Liechtenstein', 'LI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (155, 1, 'GlobalCountries', 'Sri Lanka', 'LK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (156, 1, 'GlobalCountries', 'Liberia', 'LR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (157, 1, 'GlobalCountries', 'Lesotho', 'LS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (158, 1, 'GlobalCountries', 'Lithuania', 'LT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (159, 1, 'GlobalCountries', 'Luxembourg', 'LU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (160, 1, 'GlobalCountries', 'Latvia', 'LV');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (161, 1, 'GlobalCountries', 'Libya', 'LY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (162, 1, 'GlobalCountries', 'Morocco', 'MA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (163, 1, 'GlobalCountries', 'Monaco', 'MC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (164, 1, 'GlobalCountries', 'Moldavia', 'MD');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (165, 1, 'GlobalCountries', 'Madagascar', 'MG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (166, 1, 'GlobalCountries', 'Marshall Islands', 'MH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (167, 1, 'GlobalCountries', 'Macedonia', 'MK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (168, 1, 'GlobalCountries', 'Mali', 'ML');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (169, 1, 'GlobalCountries', 'Myanmar', 'MM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (170, 1, 'GlobalCountries', 'Mongolia', 'MN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (171, 1, 'GlobalCountries', 'Macau', 'MO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (172, 1, 'GlobalCountries', 'Northern Mariana Islands', 'MP');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (173, 1, 'GlobalCountries', 'Martinique (French)', 'MQ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (174, 1, 'GlobalCountries', 'Mauritania', 'MR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (175, 1, 'GlobalCountries', 'Montserrat', 'MS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (176, 1, 'GlobalCountries', 'Malta', 'MT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (177, 1, 'GlobalCountries', 'Mauritius', 'MU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (178, 1, 'GlobalCountries', 'Maldives', 'MV');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (179, 1, 'GlobalCountries', 'Malawi', 'MW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (180, 1, 'GlobalCountries', 'Mexico', 'MX');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (181, 1, 'GlobalCountries', 'Malaysia', 'MY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (182, 1, 'GlobalCountries', 'Mozambique', 'MZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (183, 1, 'GlobalCountries', 'Namibia', 'NA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (184, 1, 'GlobalCountries', 'New Caledonia (French)', 'NC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (185, 1, 'GlobalCountries', 'Niger', 'NE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (186, 1, 'GlobalCountries', 'Norfolk Island', 'NF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (187, 1, 'GlobalCountries', 'Nigeria', 'NG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (188, 1, 'GlobalCountries', 'Nicaragua', 'NI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (189, 1, 'GlobalCountries', 'Netherlands', 'NL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (190, 1, 'GlobalCountries', 'Norway', 'NO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (191, 1, 'GlobalCountries', 'Nepal', 'NP');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (192, 1, 'GlobalCountries', 'Nauru', 'NR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (193, 1, 'GlobalCountries', 'Neutral Zone', 'NT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (194, 1, 'GlobalCountries', 'Niue', 'NU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (195, 1, 'GlobalCountries', 'New Zealand', 'NZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (196, 1, 'GlobalCountries', 'Oman', 'OM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (197, 1, 'GlobalCountries', 'Panama', 'PA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (198, 1, 'GlobalCountries', 'Peru', 'PE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (199, 1, 'GlobalCountries', 'Polynesia (French)', 'PF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (200, 1, 'GlobalCountries', 'Papua New Guinea', 'PG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (201, 1, 'GlobalCountries', 'Philippines', 'PH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (202, 1, 'GlobalCountries', 'Pakistan', 'PK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (203, 1, 'GlobalCountries', 'Poland', 'PL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (204, 1, 'GlobalCountries', 'Saint Pierre and Miquelon', 'PM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (205, 1, 'GlobalCountries', 'Pitcairn Island', 'PN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (206, 1, 'GlobalCountries', 'Puerto Rico', 'PR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (207, 1, 'GlobalCountries', 'Portugal', 'PT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (208, 1, 'GlobalCountries', 'Palau', 'PW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (209, 1, 'GlobalCountries', 'Paraguay', 'PY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (210, 1, 'GlobalCountries', 'Qatar', 'QA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (211, 1, 'GlobalCountries', 'Reunion (French)', 'RE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (212, 1, 'GlobalCountries', 'Romania', 'RO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (213, 1, 'GlobalCountries', 'Russian Federation', 'RU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (214, 1, 'GlobalCountries', 'Rwanda', 'RW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (215, 1, 'GlobalCountries', 'Saudi Arabia', 'SA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (216, 1, 'GlobalCountries', 'Solomon Islands', 'SB');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (217, 1, 'GlobalCountries', 'Seychelles', 'SC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (218, 1, 'GlobalCountries', 'Sudan', 'SD');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (219, 1, 'GlobalCountries', 'Sweden', 'SE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (220, 1, 'GlobalCountries', 'Singapore', 'SG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (221, 1, 'GlobalCountries', 'Saint Helena', 'SH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (222, 1, 'GlobalCountries', 'Slovenia', 'SI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (223, 1, 'GlobalCountries', 'Svalbard and Jan Mayen Islands', 'SJ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (224, 1, 'GlobalCountries', 'Slovak Republic', 'SK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (225, 1, 'GlobalCountries', 'Sierra Leone', 'SL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (226, 1, 'GlobalCountries', 'San Marino', 'SM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (227, 1, 'GlobalCountries', 'Senegal', 'SN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (228, 1, 'GlobalCountries', 'Somalia', 'SO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (229, 1, 'GlobalCountries', 'Suriname', 'SR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (230, 1, 'GlobalCountries', 'Saint Tome (Sao Tome) and Principe', 'ST');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (231, 1, 'GlobalCountries', 'Former USSR', 'SU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (232, 1, 'GlobalCountries', 'El Salvador', 'SV');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (233, 1, 'GlobalCountries', 'Syria', 'SY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (234, 1, 'GlobalCountries', 'Swaziland', 'SZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (235, 1, 'GlobalCountries', 'Turks and Caicos Islands', 'TC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (236, 1, 'GlobalCountries', 'Chad', 'TD');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (237, 1, 'GlobalCountries', 'French Southern Territories', 'TF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (238, 1, 'GlobalCountries', 'Togo', 'TG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (239, 1, 'GlobalCountries', 'Thailand', 'TH');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (240, 1, 'GlobalCountries', 'Tadjikistan', 'TJ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (241, 1, 'GlobalCountries', 'Tokelau', 'TK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (242, 1, 'GlobalCountries', 'Turkmenistan', 'TM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (243, 1, 'GlobalCountries', 'Tunisia', 'TN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (244, 1, 'GlobalCountries', 'Tonga', 'TO');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (245, 1, 'GlobalCountries', 'East Timor', 'TL');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (246, 1, 'GlobalCountries', 'Turkey', 'TR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (247, 1, 'GlobalCountries', 'Trinidad and Tobago', 'TT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (248, 1, 'GlobalCountries', 'Tuvalu', 'TV');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (249, 1, 'GlobalCountries', 'Taiwan', 'TW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (250, 1, 'GlobalCountries', 'Tanzania', 'TZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (251, 1, 'GlobalCountries', 'Ukraine', 'UA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (252, 1, 'GlobalCountries', 'Uganda', 'UG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (253, 1, 'GlobalCountries', 'United Kingdom', 'UK');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (254, 1, 'GlobalCountries', 'USA Minor Outlying Islands', 'UM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (255, 1, 'GlobalCountries', 'United States', 'US');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (256, 1, 'GlobalCountries', 'Uruguay', 'UY');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (257, 1, 'GlobalCountries', 'Uzbekistan', 'UZ');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (258, 1, 'GlobalCountries', 'Holy See (Vatican City State)', 'VA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (259, 1, 'GlobalCountries', 'Saint Vincent & Grenadines', 'VC');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (260, 1, 'GlobalCountries', 'Venezuela', 'VE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (261, 1, 'GlobalCountries', 'Virgin Islands (British)', 'VG');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (262, 1, 'GlobalCountries', 'Virgin Islands (USA)', 'VI');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (263, 1, 'GlobalCountries', 'Vietnam', 'VN');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (264, 1, 'GlobalCountries', 'Vanuatu', 'VU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (265, 1, 'GlobalCountries', 'Wallis and Futuna Islands', 'WF');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (266, 1, 'GlobalCountries', 'Samoa', 'WS');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (267, 1, 'GlobalCountries', 'Yemen', 'YE');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (268, 1, 'GlobalCountries', 'Mayotte', 'YT');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (269, 1, 'GlobalCountries', 'Yugoslavia', 'YU');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (270, 1, 'GlobalCountries', 'South Africa', 'ZA');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (271, 1, 'GlobalCountries', 'Zambia', 'ZM');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (272, 1, 'GlobalCountries', 'Zaire', 'ZR');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (273, 1, 'GlobalCountries', 'Zimbabwe', 'ZW');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (274, 1, 'DepartmentType', 'Not Defined', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (275, 1, 'DepartmentType', 'Profit', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (276, 1, 'DepartmentType', 'Cost', '2');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (317, 1, 'ProjectRequiredFields', '<1', 'f.project_company.options[f.project_company.selectedIndex].value');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (283, 1, 'CompanyType', 'Vendor', '2');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (284, 1, 'CompanyType', 'Supplier', '3');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (285, 1, 'CompanyType', 'Consultant', '4');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (286, 1, 'CompanyType', 'Government', '5');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (287, 1, 'CompanyType', 'Internal', '6');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (290, 1, 'EventType', 'Meeting', '2');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (291, 1, 'EventType', 'All Day Event', '3');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (292, 1, 'EventType', 'Anniversary', '4');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (293, 1, 'EventType', 'Reminder', '5');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (295, 1, 'FileType', 'Document', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (296, 1, 'FileType', 'Application', '2');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (299, 3, 'ProjectColors', 'FFFCAE', 'HelpDesk');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (300, 3, 'ProjectColors', 'FFAEAE', 'System Administration');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (302, 1, 'ProjectPriority', 'normal', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (303, 1, 'ProjectPriority', 'high', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (305, 1, 'ProjectPriorityColor', '', '0');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (306, 1, 'ProjectPriorityColor', '#FFDCB3', '1');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (309, 1, 'ProjectStatus', 'In Planning', '2');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (310, 1, 'ProjectStatus', 'In Progress', '3');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (311, 1, 'ProjectStatus', 'On Hold', '4');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (312, 1, 'ProjectStatus', 'Complete', '5');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (313, 1, 'ProjectStatus', 'Template', '6');
INSERT INTO sysvals (sysval_id, sysval_key_id, sysval_title, sysval_value, sysval_value_id) VALUES (314, 1, 'ProjectStatus', 'Archived', '7');

INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('0', 'LOCALE', 'en');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('0', 'TABVIEW', '0');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('0', 'SHDATEFORMAT', '%d/%m/%Y');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('0', 'TIMEFORMAT', '%I:%M %p');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('0', 'UISTYLE', 'web2project');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('0', 'TASKASSIGNMAX', '100');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('0', 'USERFORMAT', 'user');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('2', 'LOCALE', 'en');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('2', 'TABVIEW', '0');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('2', 'SHDATEFORMAT', '%d/%m/%Y');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('2', 'TIMEFORMAT', '%I:%M %p');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('2', 'UISTYLE', 'web2project');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('2', 'TASKASSIGNMAX', '100');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('2', 'USERFORMAT', 'user');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'LOCALE', 'pt');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'TABVIEW', '0');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'SHDATEFORMAT', '%d/%m/%Y');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'TIMEFORMAT', '%I:%M %p');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'CURRENCYFORM', 'pt');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'UISTYLE', 'web2project');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'TASKASSIGNMAX', '100');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'EVENTFILTER', 'my');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'MAILALL', '0');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'TASKLOGEMAIL', '0');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'TASKLOGSUBJ', '');
INSERT INTO user_preferences (pref_user, pref_name, pref_value) VALUES ('1', 'TASKLOGNOTE', '0');

INSERT INTO user_tasks (user_id, user_type, task_id, perc_assignment, user_task_priority) VALUES (1, 0, 1, 100, 0);

INSERT INTO users (user_id, user_contact, user_username, user_password, user_parent, user_type, user_company, user_department, user_owner, user_signature) VALUES (1, 1, 'admin', '76a2173be6393254e72ffa4d6df1030a', 0, 1, 0, 0, 0, '');
INSERT INTO users (user_id, user_contact, user_username, user_password, user_parent, user_type, user_company, user_department, user_owner, user_signature) VALUES (2, 2, 'test', '76a2173be6393254e72ffa4d6df1030a', 0, 7, 0, 0, 0, NULL);

INSERT INTO w2pversion (code_version, db_version, last_db_update, last_code_update) VALUES ('2.1.1', 2, '2007-11-14', '2007-11-14');