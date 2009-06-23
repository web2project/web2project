<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * Contacts Class
 */
class contacts extends smartsearch {
	public $table = 'contacts';
	public $table_module = 'contacts';
	public $table_key = 'contact_id';
	public $table_link = 'index.php?m=contacts&a=view&contact_id=';
	public $table_title = 'Contacts';
	public $table_orderby = 'contact_last_name,contact_first_name';
	public $search_fields = array('contact_first_name', 'contact_last_name', 'contact_title', 'contact_company', 'contact_type', 'contact_email', 'contact_email2', 'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_zip', 'contact_country', 'contact_notes');
	public $display_fields = array('contact_first_name', 'contact_last_name', 'contact_title', 'contact_company', 'contact_type', 'contact_email', 'contact_email2', 'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_zip', 'contact_country', 'contact_notes');

	function ccontacts() {
		return new contacts();
	}
}