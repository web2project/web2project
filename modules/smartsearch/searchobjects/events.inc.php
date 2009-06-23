<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * events Class
 */
class events extends smartsearch {
	public $table = 'events';
	public $table_module = 'calendar';
	public $table_key = 'event_id';
	public $table_extra = '';
	public $table_link = 'index.php?m=calendar&a=view&event_id=';
	public $table_title = 'Events';
	public $table_orderby = 'event_start_date';
	public $search_fields = array('event_title', 'event_description', 'event_start_date', 'event_end_date');
	public $display_fields = array('event_title', 'event_description', 'event_start_date', 'event_end_date');

	function events() {
		global $AppUI;
		$this->table_extra = '(event_private = 0 or event_owner = ' . (int)$AppUI->user_id . ')';
	}

	function cevents() {
		return new events();
	}
}