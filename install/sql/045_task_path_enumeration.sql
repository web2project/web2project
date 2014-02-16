-- This monstrousity calculates the entire tree path for parent/child
--  relationships. It is ridiculously ugly and the latter portion is pure brute
--  force. I'm sorry, I couldn't come up with a better approach off hand.
--                  ~ caseydk/caseysoftware 05 July 2013

ALTER TABLE `tasks` ADD `task_path_enumeration` VARCHAR( 255 ) NOT NULL ,
    ADD INDEX ( `task_path_enumeration` );

CREATE  TABLE  `tasks2` (
 `task_id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
 `task_name` varchar( 255  )  DEFAULT NULL ,
 `task_parent` int( 11  ) DEFAULT  '0',
 `task_project` int( 11  )  NOT  NULL DEFAULT  '0',
 `task_order` int( 11  )  NOT  NULL DEFAULT  '0',
 `task_path_enumeration` varchar( 255  )  NOT  NULL ,
 PRIMARY  KEY (  `task_id`  ) ,
 KEY  `task_parent` (  `task_parent`  ) ,
 KEY  `task_project` (  `task_project`  ) ,
 KEY  `task_path_enumeration` (  `task_path_enumeration`  )  
) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8;

UPDATE `tasks` SET `task_path_enumeration` = `task_id` WHERE `task_parent` = `task_id`;
INSERT INTO `tasks2` SELECT `task_id`, `task_name`,`task_parent`,`task_project`,`task_order`,`task_path_enumeration`  FROM `tasks`;


UPDATE `tasks` SET `task_path_enumeration` = CONCAT(`task_parent`, '/', `task_id`) 
    WHERE task_parent IN (SELECT `task_id` FROM `tasks2` WHERE `task_path_enumeration` = `task_id`) AND `task_path_enumeration` = '';
TRUNCATE `tasks2`;
INSERT INTO `tasks2` SELECT `task_id`, `task_name`,`task_parent`,`task_project`,`task_order`,`task_path_enumeration`  FROM `tasks`;

-- Below this are the ugly bits. Yes, that is the same query combination
--  applied five times in a row. That means this is capped at going down seven
--  levels (2 above, 5 here). If your project trees are deeper... well doh.

UPDATE `tasks` 
  INNER JOIN `tasks2` ON `tasks2`.`task_id` = `tasks`.`task_parent`
  SET `tasks`.`task_path_enumeration` = CONCAT(`tasks2`.`task_path_enumeration`, '/', `tasks`.`task_id`)
  WHERE `tasks2`.`task_path_enumeration` <> '' AND `tasks`.`task_path_enumeration` = '';
TRUNCATE `tasks2`;
INSERT INTO `tasks2` SELECT `task_id`, `task_name`,`task_parent`,`task_project`,`task_order`,`task_path_enumeration`  FROM `tasks`;

UPDATE `tasks` 
  INNER JOIN `tasks2` ON `tasks2`.`task_id` = `tasks`.`task_parent`
  SET `tasks`.`task_path_enumeration` = CONCAT(`tasks2`.`task_path_enumeration`, '/', `tasks`.`task_id`)
  WHERE `tasks2`.`task_path_enumeration` <> '' AND `tasks`.`task_path_enumeration` = '';
TRUNCATE `tasks2`;
INSERT INTO `tasks2` SELECT `task_id`, `task_name`,`task_parent`,`task_project`,`task_order`,`task_path_enumeration`  FROM `tasks`;

UPDATE `tasks` 
  INNER JOIN `tasks2` ON `tasks2`.`task_id` = `tasks`.`task_parent`
  SET `tasks`.`task_path_enumeration` = CONCAT(`tasks2`.`task_path_enumeration`, '/', `tasks`.`task_id`)
  WHERE `tasks2`.`task_path_enumeration` <> '' AND `tasks`.`task_path_enumeration` = '';
TRUNCATE `tasks2`;
INSERT INTO `tasks2` SELECT `task_id`, `task_name`,`task_parent`,`task_project`,`task_order`,`task_path_enumeration`  FROM `tasks`;

UPDATE `tasks` 
  INNER JOIN `tasks2` ON `tasks2`.`task_id` = `tasks`.`task_parent`
  SET `tasks`.`task_path_enumeration` = CONCAT(`tasks2`.`task_path_enumeration`, '/', `tasks`.`task_id`)
  WHERE `tasks2`.`task_path_enumeration` <> '' AND `tasks`.`task_path_enumeration` = '';
TRUNCATE `tasks2`;
INSERT INTO `tasks2` SELECT `task_id`, `task_name`,`task_parent`,`task_project`,`task_order`,`task_path_enumeration`  FROM `tasks`;

UPDATE `tasks` 
  INNER JOIN `tasks2` ON `tasks2`.`task_id` = `tasks`.`task_parent`
  SET `tasks`.`task_path_enumeration` = CONCAT(`tasks2`.`task_path_enumeration`, '/', `tasks`.`task_id`)
  WHERE `tasks2`.`task_path_enumeration` <> '' AND `tasks`.`task_path_enumeration` = '';
TRUNCATE `tasks2`;
INSERT INTO `tasks2` SELECT `task_id`, `task_name`,`task_parent`,`task_project`,`task_order`,`task_path_enumeration`  FROM `tasks`;

-- At least I clean up after myself..
DROP TABLE `tasks2`;