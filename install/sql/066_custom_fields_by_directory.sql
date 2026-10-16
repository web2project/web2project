-- Custom fields are looked up by module directory (companies, events and so
-- on) everywhere except the custom field editor, which stored the module's
-- display name, or the lowercase display name before 2011. The two only
-- differ for Calendar, whose directory became 'events' in migration 049, so
-- Calendar custom fields stopped appearing on events. Store the directory for
-- every definition.

UPDATE `custom_fields_struct` s
  JOIN `modules` m ON LOWER(s.`field_module`) = LOWER(m.`mod_name`)
  SET s.`field_module` = m.`mod_directory`;
