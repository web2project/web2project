<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//fixed system SysVals to prevent their deletion
$fixedSysVals = array('CompanyType', 'EventType', 'FileType', 'GlobalCountries', 'GlobalYesNo', 'ProjectPriority', 'ProjectStatus', 'ProjectType', 'TaskDurationType', 'TaskLogReference', 'TaskPriority', 'TaskStatus', 'TaskType', 'UserType');

/**
 * Preferences class
 */
class CPreferences {
	var $pref_user = null;
	var $pref_name = null;
	var $pref_value = null;

	function CPreferences() {
		// empty constructor
	}

	function bind($hash) {
		if (!is_array($hash)) {
			return 'CPreferences::bind failed';
		} else {
			$q = new DBQuery;
			$q->bindHashToObject($hash, $this);
			$q->clear();
			return null;
		}
	}

	function check() {
		// TODO MORE
		return null; // object is ok
	}

	function store() {
		$msg = $this->check();
		if ($msg) {
			return 'CPreference::store-check failed ' . $msg;
		}
		if (($msg = $this->delete())) {
			return 'CPreference::store-delete failed ' . $msg;
		}
		$q = new DBQuery;
		if (!($ret = $q->insertObject('user_preferences', $this))) {
			$q->clear();
			return 'CPreference::store failed ' . db_error();
		} else {
			$q->clear();
			return null;
		}
	}

	function delete() {
		$q = new DBQuery;
		$q->setDelete('user_preferences');
		$q->addWhere('pref_user = ' . (int)$this->pref_user);
		$q->addWhere('pref_name = \'' . $this->pref_name . '\'');
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		} else {
			$q->clear();
			return null;
		}
	}
}

/**
 * Module class
 */
class CModule extends CW2pObject {
	var $mod_id = null;
	var $mod_name = null;
	var $mod_directory = null;
	var $mod_version = null;
	var $mod_setup_class = null;
	var $mod_type = null;
	var $mod_active = null;
	var $mod_ui_name = null;
	var $mod_ui_icon = null;
	var $mod_ui_order = null;
	var $mod_ui_active = null;
	var $mod_description = null;
	var $permissions_item_label = null;
	var $permissions_item_field = null;
	var $permissions_item_table = null;
	var $mod_main_class = null;

	function CModule() {
		$this->CW2pObject('modules', 'mod_id');
	}

	function install() {
		$q = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('mod_directory');
		$q->addWhere('mod_directory = \'' . $this->mod_directory . '\'');
		if ($temp = $q->loadHash()) {
			// the module is already installed
			// TODO: check for older version - upgrade
			return false;
		}

		$q = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('MAX(mod_ui_order)');
		$q->addWhere('mod_name NOT LIKE "Public"');
		// We need to account for "pre-installed" modules that are "UI Inaccessible"
		// in order to make sure we get the "correct" initial value for .
		// mod_ui_order values of "UI Inaccessible" modules are irrelevant
		// and should probably be set to 0 so as not to interfere.

		$this->mod_ui_order = $q->loadResult() + 1;

		$perms = &$GLOBALS['AppUI']->acl();
		$perms->addModule($this->mod_directory, $this->mod_name);
		// Determine if it is an admin module or not, then add it to the correct set
		if (!isset($this->mod_admin)) {
			$this->mod_admin = 0;
		}
		if ($this->mod_admin) {
			$perms->addGroupItem($this->mod_directory, "admin");
		} else {
			$perms->addGroupItem($this->mod_directory, "non_admin");
		}
		if (isset($this->permissions_item_table) && $this->permissions_item_table) {
			$perms->addModuleSection($this->permissions_item_table);
		}
		$this->store();
		return true;
	}

	function remove() {
		$q = new DBQuery;
		$q->setDelete('modules');
		$q->addWhere('mod_id = ' . (int)$this->mod_id);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		} else {
			$perms = &$GLOBALS['AppUI']->acl();
			if (!isset($this->mod_admin))
				$this->mod_admin = 0;
			if ($this->mod_admin) {
				$perms->deleteGroupItem($this->mod_directory, 'admin');
			} else {
				$perms->deleteGroupItem($this->mod_directory, 'non_admin');
			}
			$perms->deleteModuleItems($this->mod_directory);
			$perms->deleteModule($this->mod_directory);
			if (isset($this->permissions_item_table) && $this->permissions_item_table) {
				$perms->deleteModuleSection($this->permissions_item_table);
			}
			return null;
		}
	}

	function move($dirn) {
		$new_ui_order = $this->mod_ui_order;

		if ($dirn == 'moveup') {
			$other_new = $new_ui_order;
			$new_ui_order--;
		} else
			if ($dirn == 'movedn') {
				$other_new = $new_ui_order;
				$new_ui_order++;
			}

		if ($new_ui_order) { //make sure we aren't going "up" to 0
			$q = new DBQuery;
			if ($other_new) { //make sure value was set.
				$q->addTable('modules');
				$q->addUpdate('mod_ui_order', '' . $other_new);
				$q->addWhere('mod_ui_order = ' . (int)$new_ui_order);
				$q->exec();
				$q->clear();
			}
			$q->addTable('modules');
			$q->addUpdate('mod_ui_order', $new_ui_order);
			$q->addWhere('mod_id = ' . (int)$this->mod_id);
			$q->exec();
			$q->clear();

			$this->mod_ui_order = $new_ui_order;
		}
	}

	// overridable functions
	function moduleInstall() {
		return null;
	}
	function moduleRemove() {
		return null;
	}
	function moduleUpgrade() {
		return null;
	}
}

/**
 * Configuration class
 */
class CConfig extends CW2pObject {

	function CConfig() {
		$this->CW2pObject('config', 'config_id');
	}

	function getChildren($id) {
		$this->_query->clear();
		$this->_query->addTable('config_list');
		$this->_query->addOrder('config_list_id');
		$this->_query->addWhere('config_id = ' . (int)$id);
		$result = $this->_query->loadHashList('config_list_id');
		$this->_query->clear();
		return $result;
	}

}

class bcode extends CW2pObject {
	var $_billingcode_id = null;
	var $company_id;
	var $billingcode_id = null;
	var $billingcode_desc;
	var $billingcode_name;
	var $billingcode_value;
	var $billingcode_status;

	function bcode() {
		$this->CW2pObject('billingcode', 'billingcode_id');
	}

	function bind($hash) {
		if (!is_array($hash)) {
			return 'Billing Code::bind failed';
		} else {
			$q = new DBQuery;
			$q->bindHashToObject($hash, $this);
			$q->clear();
			return null;
		}
	}

	function delete() {
		$q = new DBQuery;
		$q->addTable('billingcode');
		$q->addUpdate('billingcode_status', '1');
		$q->addWhere('billingcode_id = ' . (int)$this->_billingcode_id);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		} else {
			$q->clear();
			return null;
		}
	}

	function store() {
		$q = new DBQuery;
		$q->addQuery('billingcode_id');
		$q->addTable('billingcode');
		$q->addWhere('billingcode_name = \'' . $this->billingcode_name . '\'');
		$q->addWhere('company_id = ' . (int)$this->company_id);
		$found_id = $q->loadResult();
		$q->clear();

		if ($found_id && $found_id != $this->_billingcode_id) {
			return 'Billing Code::code already exists';
		} elseif ($this->_billingcode_id) {
			$q->addTable('billingcode');
			$q->addUpdate('billingcode_desc', $this->billingcode_desc);
			$q->addUpdate('billingcode_name', $this->billingcode_name);
			$q->addUpdate('billingcode_value', $this->billingcode_value);
			$q->addUpdate('billingcode_status', $this->billingcode_status);
			$q->addUpdate('company_id', $this->company_id);
			$q->addWhere('billingcode_id = ' . (int)$this->_billingcode_id);
			$q->exec();
			$q->clear();
		} elseif (!($ret = $q->insertObject('billingcode', $this, 'billingcode_id'))) {
			return 'Billing Code::store failed <br />' . db_error();
		} else {
			return null;
		}
	}
}