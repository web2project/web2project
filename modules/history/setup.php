<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
* Name:      History
* Directory: history
* Version:   0.32
* Class:     user
* UI Name:   History
* UI Icon:
*/

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'History';
$config['mod_version'] = '0.32';
$config['mod_directory'] = 'history';
$config['mod_setup_class'] = 'CSetupHistory';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'History';
$config['mod_ui_icon'] = '';
$config['mod_description'] = 'A module for tracking changes';

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

class CSetupHistory extends w2p_Core_Setup
{
	public function install() {
        global $AppUI;

		$q = $this->_getQuery();
		$q->createTable('history');
        $sql = ' (
			history_id int(10) unsigned NOT NULL auto_increment,
			history_date datetime NOT NULL default \'0000-00-00 00:00:00\',		  
			history_user int(10) NOT NULL default \'0\',
			history_action varchar(20) NOT NULL default \'modify\',
			history_item int(10) NOT NULL,
			history_table varchar(20) NOT NULL default \'\',
			history_project int(10) NOT NULL default \'0\',
			history_name varchar(255),
			history_changes text,
			history_description text,
			PRIMARY KEY  (history_id),
			INDEX index_history_module (history_table, history_item),
		  	INDEX index_history_item (history_item) 
            ) ENGINE = MYISAM DEFAULT CHARSET=utf8';
		$q->createDefinition($sql);
		$q->exec();

        return parent::install();
	}

	public function remove() {
        $q = $this->_getQuery();
		$q->dropTable('history');
		$q->exec();

        return parent::remove();
	}
}