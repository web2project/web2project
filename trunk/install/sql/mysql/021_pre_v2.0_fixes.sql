
-- This addition adds support for token tasks to represent subprojects. The
--   best part about this method is that you get dependencies and other
--   planning aspects for "free".

ALTER TABLE `tasks` ADD `task_represents_project` INT( 10 ) NOT NULL ;
ALTER TABLE `tasks` ADD INDEX ( `task_represents_project` ) ;

