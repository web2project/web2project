-- projects.project_color_identifier is printed into style attributes and
-- CSS throughout web2project, and CProject::isValid() now accepts only six
-- hex digits. Bring stored values in line so those projects still save:
--   "#abc123" -> "abc123", three digits "abc" -> "aabbcc", anything else
--   (empty, names, broken values) -> "FFFFFF".
-- Valid values are not touched, so running this again changes nothing.

UPDATE `projects` SET `project_color_identifier` = SUBSTRING(`project_color_identifier`, 2)
  WHERE `project_color_identifier` LIKE '#%';

UPDATE `projects` SET `project_color_identifier` = CONCAT(
    REPEAT(SUBSTRING(`project_color_identifier`, 1, 1), 2),
    REPEAT(SUBSTRING(`project_color_identifier`, 2, 1), 2),
    REPEAT(SUBSTRING(`project_color_identifier`, 3, 1), 2))
  WHERE `project_color_identifier` REGEXP '^[0-9A-Fa-f]{3}$';

UPDATE `projects` SET `project_color_identifier` = 'FFFFFF'
  WHERE `project_color_identifier` IS NULL OR `project_color_identifier` NOT REGEXP '^[0-9A-Fa-f]{6}$';
