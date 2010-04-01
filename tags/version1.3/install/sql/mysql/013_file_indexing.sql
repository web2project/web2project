
-- This handles a bit of cleanup to the database that should be in place.

ALTER TABLE `gacl_axo` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT;

-- This adds a simple flag to each of the files to flag if a file has been indexed or not.

ALTER TABLE `files` ADD `file_indexed` TINYINT( 10 ) NOT NULL DEFAULT '0';

-- Adds the keywords for the filtering required for the file indexing

INSERT INTO `sysvals` (`sysval_key_id`, `sysval_title`, `sysval_value`, `sysval_value_id`) VALUES 
	(1, 'FileIndexIgnoreWords', 'a,about,also, an,and,another,any,are,as,at,back,be,because,been,being,but,
	by,can,could,did,do,each,end,even,for,from,get,go,had,have,he,her,here,his,how, i,if,in,into,is,it,else,
	just,may,me,might,much,must, my,no,not,ofv,off,on,only,or,other,our,out,should,so,some,still,such,than,
	that,the,their,them,then,there,these,they,this,those,to,too,try,twov,under, up,us,was,we,were,what,when,
	where,which,while,who,why,will,with,within,without,would,you,your,MSWordDoc,bjbjU','FileIndexIgnoreWords');
