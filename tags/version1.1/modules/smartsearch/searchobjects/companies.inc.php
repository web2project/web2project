<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

class companies extends smartsearch {
	public $table = 'companies';
	public $table_module = 'companies';
	public $table_key = 'company_id';
	public $table_link = 'index.php?m=companies&a=view&company_id=';
	public $table_title = 'Companies';
	public $table_orderby = 'company_name';
	public $search_fields = array('company_name', 'company_address1', 'company_address2', 'company_city', 'company_state', 'company_zip', 'company_primary_url', 'company_description', 'company_email');
	public $display_fields = array('company_name', 'company_address1', 'company_address2', 'company_city', 'company_state', 'company_zip', 'company_primary_url', 'company_description', 'company_email');

	public function ccompanies() {
		return new companies();
	}
}