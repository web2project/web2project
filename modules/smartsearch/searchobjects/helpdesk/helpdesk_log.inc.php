<?php /* $Id$ $URL$ */
/**
 * helpdesk_log Class
 */
class helpdesk_log extends smartsearch {
	public $table = 'task_log';
	public $table_module = 'helpdesk';
	public $table_key = 'task_log_help_desk_id';
	public $table_key2 = 'task_log_id';
	public $table_extra = 'task_log_help_desk_id != 0';
	public $table_link = 'index.php?m=helpdesk&a=view&item_id=';
	public $table_link2 = '&tab=1&task_log_id=';
	public $table_title = 'Helpdesk task logs';
	public $table_orderby = 'task_log_name';
	public $search_fields = array('task_log_name', 'task_log_description', 'task_log_help_desk_id');
	public $display_fields = array('task_log_name', 'task_log_description', 'task_log_help_desk_id');

	public function chelpdesk_log() {
		return new helpdesk_log();
	}
}