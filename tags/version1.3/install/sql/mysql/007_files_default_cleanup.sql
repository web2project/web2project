
-- This resolves issue #201 where the file_checkout field did not have a 
--   default value set so file uploads were not posible.

ALTER TABLE `files` CHANGE `file_checkout` `file_checkout` 
	VARCHAR(16) NOT NULL DEFAULT '';