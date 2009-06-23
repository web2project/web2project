<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * users Class
 */
class users extends smartsearch {
	public $table = 'users';
	public $table_module = 'admin';
	public $table_key = 'user_id';
	public $table_link = 'index.php?m=admin&a=viewuser&user_id=';
	public $table_title = 'Users';
	public $table_orderby = 'user_username';
	public $search_fields = array('user_username', 'user_signature');
	public $display_fields = array('user_username', 'user_signature');

	function cusers() {
		return new users();
	}
}