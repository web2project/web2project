<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * departments Class
 */
class departments extends smartsearch {
	public $table = 'departments';
	public $table_module = 'departments';
	public $table_key = 'dept_id';
	public $table_link = 'index.php?m=departments&a=view&dept_id=';
	public $table_title = 'Departments';
	public $order_by = 'dept_name';
	public $search_fields = array('dept_name', 'dept_address1', 'dept_address2', 'dept_city', 'dept_state', 'dept_zip', 'dept_url', 'dept_desc');
	public $display_fields = array('dept_name', 'dept_address1', 'dept_address2', 'dept_city', 'dept_state', 'dept_zip', 'dept_url', 'dept_desc');

	function cdepartments() {
		return new departments();
	}
}