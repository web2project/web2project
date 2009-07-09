<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * projects Class
 */
class projects extends smartsearch {
	public $table = 'projects';
	public $table_alias = 'p';
	public $table_module = 'projects';
	public $table_key = 'p.project_id';
	public $table_link = 'index.php?m=projects&a=view&project_id=';
	public $table_title = 'Projects';
	public $table_orderby = 'p.project_name';
	public $search_fields = array('p.project_id', 'p.project_name', 'p.project_short_name', 'p.project_location', 'p.project_description', 'p.project_url', 'p.project_demo_url', 'con.contact_last_name', 'con.contact_first_name', 'con.contact_email', 'con.contact_title', 'con.contact_email2', 'con.contact_phone', 'con.contact_phone2', 'con.contact_address1', 'con.contact_notes');
	public $display_fields = array('p.project_id', 'p.project_name', 'p.project_short_name', 'p.project_location', 'p.project_description', 'p.project_url', 'p.project_demo_url', 'con.contact_last_name', 'con.contact_first_name', 'con.contact_email', 'con.contact_title', 'con.contact_email2', 'con.contact_phone', 'con.contact_phone2', 'con.contact_address1', 'con.contact_notes');
	public $table_joins = array(array('table' => 'project_contacts', 'alias' => 'pc', 'join' => 'p.project_id = pc.project_id'), array('table' => 'contacts', 'alias' => 'con', 'join' => 'pc.contact_id = con.contact_id'));

	public function cprojects() {
		return new projects();
	}
}