<?php /* $Id$ $URL$ */
/**
 * helpdesk_log Class
 */
class helpdesk_log extends smartsearch {
	var $table = 'task_log';
	var $table_module = 'helpdesk';
	var $table_key = 'task_log_help_desk_id';
	var $table_key2 = 'task_log_id';
	var $table_extra = 'task_log_help_desk_id != 0';
	var $table_link = 'index.php?m=helpdesk&a=view&item_id=';
	var $table_link2 = '&tab=1&task_log_id=';
	var $table_title = 'Helpdesk task logs';
	var $table_orderby = 'task_log_name';
	var $search_fields = array('task_log_name', 'task_log_description', 'task_log_help_desk_id');
	var $display_fields = array('task_log_name', 'task_log_description', 'task_log_help_desk_id');

	function chelpdesk_log() {
		return new helpdesk_log();
	}
}
?>
