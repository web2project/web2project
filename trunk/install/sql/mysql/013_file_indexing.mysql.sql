
-- This handles a bit of cleanup to the database that should be in place.

ALTER TABLE `gacl_axo` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT  

-- This adds a simple flag to each of the files to flag if a file has been indexed or not.

ALTER TABLE `files` ADD `file_indexed` TINYINT( 10 ) NOT NULL DEFAULT '0';