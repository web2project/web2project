<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$config = array('mod_name' => 'Resources', 'mod_version' => '1.0.1', 'mod_directory' => 'resources', 'mod_setup_class' => 'SResource', 'mod_type' => 'user', 'mod_ui_name' => 'Resources', 'mod_ui_icon' => 'resources.png', 'mod_description' => '', 'permissions_item_table' => 'resources', 'permissions_item_field' => 'resource_id', 'permissions_item_label' => 'resource_name', 'mod_main_class' => 'CResource');

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

class SResource {
	public function install() {
		$ok = true;
		$q = new DBQuery;
		$sql = '(
			resource_id integer not null auto_increment,
			resource_name varchar(255) not null default "",
			resource_key varchar(64) not null default "",
			resource_type integer not null default 0,
			resource_note text not null default "",
			resource_max_allocation integer not null default 100,
			primary key (resource_id),
			key (resource_name),
			key (resource_type)
		)';
		$q->createTable('resources', $sql);
		$ok = $ok && $q->exec();
		$q->clear();

		$sql = '(
			resource_type_id integer not null auto_increment,
			resource_type_name varchar(255) not null default "",
			resource_type_note text,
			primary key (resource_type_id)
		)';
		$q->createTable('resource_types', $sql);
		$ok = $ok && $q->exec();
		$q->clear();

		$sql = '(
			resource_id integer not null default 0,
			task_id integer not null default 0,
			percent_allocated integer not null default 100,
			key (resource_id),
			key (task_id, resource_id)
		)';
		$q->createTable('resource_tasks', $sql);
		$ok = $ok && $q->exec();
		$q->clear();
		$q->addTable('resource_types');
		$q->addInsert('resource_type_name', 'Equipment');
		$q->exec();
		$q->addInsert('resource_type_name', 'Tool');
		$q->exec();
		$q->addInsert('resource_type_name', 'Venue');
		$ok = $ok && $q->exec();

		if (!$ok) {
			return false;
		}
		return null;
	}

	public function remove() {
		$q = new DBQuery;
		$q->dropTable('resources');
		$q->exec();
		$q->clear();
		$q->dropTable('resource_tasks');
		$q->exec();
		$q->clear();
		$q->dropTable('resource_types');
		$q->exec();

		return null;
	}

	public function upgrade($old_version) {
		switch ($old_version) {
			case '1.0':
				$q = new DBQuery;
				$q->addTable('resources');
				$q->addField('resource_key', 'varchar(64) not null default ""');
				$q->exec();
				if (db_error()) {
					return false;
				}
				// FALLTHROUGH
			case '1.0.1':
				break;
		}
		return true;
	}
}