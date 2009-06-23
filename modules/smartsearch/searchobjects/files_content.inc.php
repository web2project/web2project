<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
Files_content class
*/
class files_content extends smartsearch {
	public $table = 'files_index';
	public $table_module = 'files';
	public $table_key = 'file_id';
	public $table_link = 'fileviewer.php?file_id=';
	public $table_title = 'Files Content';
	public $table_orderby = 'word_placement';
	public $follow_up_link = 'fileviewer.php?file_id=';
	public $search_fields = array('word');
	public $display_fields = array('word');

	function cfiles_content() {
		return new files_content();
	}
}