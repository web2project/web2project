<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//fixed system SysVals to prevent their deletion
$fixedSysVals = array('CompanyType', 'EventType', 'FileType', 'GlobalCountries', 'GlobalYesNo', 'ProjectPriority', 'ProjectStatus', 'ProjectType', 'TaskDurationType', 'TaskLogReference', 'TaskPriority', 'TaskStatus', 'TaskType', 'UserType');

class CSystem {
    private $upgrader = null;

	public function __construct() {
        $this->upgrader = new w2p_Core_UpgradeManager();
	}

    public function upgradeRequired() {
        $this->upgrader->getActionRequired();
        return $this->upgrader->upgradeRequired();
    }

    public function upgradeSystem() {
        $this->upgrader->getActionRequired();
        return $this->upgrader->upgradeSystem();
    }

    public function getUpdatesApplied() {
        return $this->upgrader->getUpdatesApplied();
    }

    public function hook_cron()
    {
        global $w2Pconfig;

        if (date('w') == w2PgetConfig('system_update_day', 0) &&
            date('G') == w2PgetConfig('system_update_hour', 3))
        {
            $AppUI = new CAppUI;
            $configList = array();

            $moduleList = $AppUI->getLoadableModuleList();
            foreach($moduleList as $module) {
                $configList[$module['mod_directory']] = $module['mod_version'];
            }

            $configList['w2p_ver'] = $AppUI->getVersion();
            $configList['php_ver'] = PHP_VERSION;
            $configList['database'] = $w2Pconfig['dbtype'];
            $configList['server'] = $_SERVER['SERVER_SOFTWARE'];
            $configList['connector'] = php_sapi_name();
            $configList['database_ver'] = mysql_get_client_info();
            $libraries = array('tidy', 'json', 'libxml', 'mysql');
            foreach($libraries as $library) {
                $configList[$library.'_extver'] = phpversion($library);
            }
            if (function_exists('gd_info')) {
                $lib_version = gd_info();
                $configList['gd_extver'] = $lib_version['GD Version'];
            }
            if (function_exists('curl_version')) {
                $lib_version = curl_version();
                $configList['curl_extver'] = $lib_version['version'];
            }
            $request = new w2p_Utilities_HTTPRequest('http://stats.web2project.net');
            $request->addParameters($configList);
            $result = $request->processRequest();
            $data = json_decode($result);

            $q = new DBQuery();
            $q->addTable('config');
            if ('' == w2PgetConfig('available_version', '')) {
                $q->addInsert('config_name', 'available_version');
                $q->addInsert('config_value', $data->w2p_ver);
                $q->addInsert('config_group', 'admin_system');
                $q->addInsert('config_type', 'text');
            } else {
                $q->addUpdate('config_value', $data->w2p_ver);
                $q->addWhere("config_name  = 'available_version'");
            }
            $q->exec();
        }
    }
}

/**
 * Preferences class
 */
class CPreferences {
	public $pref_user = null;
	public $pref_name = null;
	public $pref_value = null;

	public function __construct() {
		// empty constructor
	}

	public function bind($hash) {
		if (!is_array($hash)) {
			return 'CPreferences::bind failed';
		} else {
			$q = new DBQuery;
			$q->bindHashToObject($hash, $this);
			$q->clear();
			return null;
		}
	}

	public function check() {
		// TODO MORE
		return null; // object is ok
	}

	public function store() {
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

	public function delete() {
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
	public $mod_id = null;
	public $mod_name = null;
	public $mod_directory = null;
	public $mod_version = null;
	public $mod_setup_class = null;
	public $mod_type = null;
	public $mod_active = null;
	public $mod_ui_name = null;
	public $mod_ui_icon = null;
	public $mod_ui_order = null;
	public $mod_ui_active = null;
	public $mod_description = null;
	public $permissions_item_label = null;
	public $permissions_item_field = null;
	public $permissions_item_table = null;
	public $mod_main_class = null;

	public function __construct() {
        parent::__construct('modules', 'mod_id');
	}

	public function install() {
		$q = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('mod_directory');
		$q->addWhere('mod_directory = \'' . $this->mod_directory . '\'');
		if ($temp = $q->loadHash()) {
			// the module is already installed
			// TODO: check for older version - upgrade
			return false;
		}
		// This arbitrarily places it at the end of the list.
		$this->mod_ui_order = 100;
		$this->store();

		$this->_compactModuleUIOrder();

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
		return true;
	}
	private function _compactModuleUIOrder() {
		$q = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('mod_id');
		$q->addOrder('mod_ui_order ASC');
		$q->addOrder('mod_directory ASC');
		$moduleList = $q->loadList();

		$i = 1;
		foreach ($moduleList as $module) {
			$q->clear();
			$q->addTable('modules');
			$q->addUpdate('mod_ui_order', $i);
			$q->addWhere('mod_id = ' . $module['mod_id']);
			$q->exec();
			$i++;
		}
	}

	public function remove() {
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
			$this->_compactModuleUIOrder();
			return null;
		}
	}

	public function move($dirn) {
		$new_ui_order = $this->mod_ui_order;

		$q = new DBQuery;
		$q->addTable('modules');
		$q->addWhere('mod_id <> ' . (int)$this->mod_id);
		$q->addOrder('mod_ui_order');
		$modules = $q->loadList();
		$q->clear();

		if ($dirn == 'moveup') {
			$other_new = $new_ui_order;
			$new_ui_order--;
		} elseif ($dirn == 'movedn') {
			$other_new = $new_ui_order;
			$new_ui_order++;
		} elseif ($dirn == 'movefirst') {
			$other_new = $new_ui_order;
			$new_ui_order = 1;
		} elseif ($dirn == 'movelast') {
			$other_new = $new_ui_order;
			$new_ui_order = count($modules) + 1;
		}

		if ($new_ui_order && ($new_ui_order <= count($modules) + 1)) { //make sure we aren't going "up" to 0
			$q = new DBQuery;
			$q->addTable('modules');
			$q->addUpdate('mod_ui_order', $new_ui_order);
			$q->addWhere('mod_id = ' . (int)$this->mod_id);
			$q->exec();
			$q->clear();
			$idx = 1;
			foreach ($modules as $module) {
				if ((int)$idx != (int)$new_ui_order) {
					$q->addTable('modules');
					$q->addUpdate('mod_ui_order', $idx);
					$q->addWhere('mod_id = ' . (int)$module['mod_id']);
					$q->exec();
					$q->clear();
					$idx++;
				} else {
					$q->addTable('modules');
					$q->addUpdate('mod_ui_order', $idx + 1);
					$q->addWhere('mod_id = ' . (int)$module['mod_id']);
					$q->exec();
					$q->clear();
					$idx = $idx + 2;
				}
			}
		}
	}
	// overridable functions
	public function moduleInstall() {
		return null;
	}
	public function moduleRemove() {
		return null;
	}
	public function moduleUpgrade() {
		return null;
	}
}

/**
 * Configuration class
 */
class CConfig extends CW2pObject {

	public function __construct() {
        parent::__construct('config', 'config_id');
	}

	public function getChildren($id) {
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
	public $_billingcode_id = null;
	public $company_id;
	public $billingcode_id = null;
	public $billingcode_desc;
	public $billingcode_name;
	public $billingcode_value;
	public $billingcode_status;

	public function __construct() {
		parent::__construct('billingcode', 'billingcode_id');
	}

	public function delete(CAppUI $AppUI = null) {

		$q = new DBQuery;
		$q->addTable('billingcode');
		$q->addUpdate('billingcode_status', '1');
		$q->addWhere('billingcode_id = ' . (int)$this->_billingcode_id);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		} else {
			$q->clear();
			return true;
		}
	}

	public function store(CAppUI $AppUI = null) {
        global $AppUI;
        $perms = $AppUI->acl();
        $stored = false;

        $q = new DBQuery;
		$q->addQuery('billingcode_id');
		$q->addTable('billingcode');
		$q->addWhere('billingcode_name = \'' . $this->billingcode_name . '\'');
		$q->addWhere('company_id = ' . (int)$this->company_id);
		$found_id = $q->loadResult();
		$q->clear();

		if ($found_id && $found_id != $this->_billingcode_id) {
			return 'Billing Code::code already exists';
		} else {
            if ($perms->checkModuleItem('system', 'edit')) {
                if (($msg = parent::store())) {
                    return $msg;
                }
                $stored = true;
            }
        }
        return $stored;
	}
}