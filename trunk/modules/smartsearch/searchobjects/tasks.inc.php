<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * tasks Class
 */
class tasks extends smartsearch {
	public $table = 'tasks';
	public $table_module = 'tasks';
	public $table_key = 'task_id';
	public $table_link = 'index.php?m=tasks&a=view&task_id=';
	public $table_title = 'Tasks';
	public $table_orderby = 'task_name';
	public $search_fields = array('task_name', 'task_description', 'task_related_url', 'task_departments', 'task_contacts', 'task_custom');
	public $display_fields = array('task_name', 'task_description', 'task_related_url', 'task_departments', 'task_contacts', 'task_custom');

	function ctasks() {
		return new tasks();
	}
}