<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * forum_messages Class
 */
class forum_messages extends smartsearch {
	public $table = 'forum_messages';
	public $table_module = 'forums';
	public $table_key = 'message_id'; // primary key
	public $table_link = 'index.php?m=forums&a=viewer&message_id=';
	public $table_title = 'Forum messages';
	public $table_orderby = 'message_title';
	public $search_fields = array('message_title', 'message_body');
	public $display_fields = array('message_title', 'message_body');

	public function cforum_messages() {
		return new forum_messages();
	}
}