<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * files Class
 */
class files extends smartsearch {
	public $table = 'files';
	public $table_module = 'files';
	public $table_key = 'file_id';
	public $table_link = 'index.php?m=files&a=addedit&file_id=';
	public $table_title = 'Files';
	public $table_orderby = 'file_name';
	public $search_fields = array('file_name', 'file_description', 'file_type', 'file_version', 'file_co_reason');
	public $display_fields = array('file_name', 'file_description', 'file_type', 'file_version', 'file_co_reason');
	public $follow_up_link = 'fileviewer.php?file_id=';

	function cfiles() {
		return new files();
	}
}