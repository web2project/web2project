<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * forums Class
 */
class forums extends smartsearch {
	public $table = 'forums';
	public $table_module = 'forums';
	public $table_key = 'forum_id';
	public $table_link = 'index.php?m=forums&a=viewer&forum_id=';
	public $table_title = 'Forums';
	public $table_orderby = 'forum_name';
	public $search_fields = array('forum_name', 'forum_description');
	public $display_fields = array('forum_name', 'forum_description');

	public function cforums() {
		return new forums();
	}
}