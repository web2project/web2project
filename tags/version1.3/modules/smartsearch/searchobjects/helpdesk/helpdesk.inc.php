<?php /* $Id$ $URL$ */
/**
 * helpdesk Class
 */
class helpdesk extends smartsearch {
	public $table = 'helpdesk_items';
	public $table_module = 'helpdesk';
	public $table_key = 'item_id';
	public $table_link = 'index.php?m=helpdesk&a=view&item_id=';
	public $table_title = 'Helpdesk';
	public $table_orderby = 'item_title';
	public $search_fields = array('item_title', 'item_summary', 'item_os', 'item_application', 'item_requestor', 'item_requestor_email');
	public $display_fields = array('item_title', 'item_summary', 'item_os', 'item_application', 'item_requestor', 'item_requestor_email');

	public function chelpdesk() {
		return new helpdesk();
	}
}