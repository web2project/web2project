<?php /* $Id: system.class.php 1527 2010-12-13 07:56:13Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/system/system.class.php $ */
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

        if (w2PgetConfig('system_update_check', true)) {
            $lastCheck = w2PgetConfig('system_update_last_check', '');
            $nowDate = new DateTime("now");

            if ('' == $lastCheck) {
                $checkForUpdates = true;
            } else {
                $systemDate = new DateTime($lastCheck);
                $difference = $nowDate->diff($systemDate)->format('%d');
                $checkForUpdates = ($difference >= 7) ? true : false;
            }

            if ($checkForUpdates) {
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

                $q = new w2p_Database_Query();
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

                $q->clear();
                $q->addTable('config');
                $q->addUpdate('config_value', date('Y-m-d H:i:s'));
                $q->addWhere("config_name  = 'system_update_last_check'");
                $q->exec();
            }
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
			$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
		if (!($ret = $q->insertObject('user_preferences', $this))) {
			$q->clear();
			return 'CPreference::store failed ' . db_error();
		} else {
			$q->clear();
			return null;
		}
	}

	public function delete() {
		$q = new w2p_Database_Query;
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
class CModule extends w2p_Core_BaseObject {
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
    protected $modules;

	public function __construct() {
        parent::__construct('modules', 'mod_id');
        $this->modules  = W2P_BASE_DIR.'/modules/';
	}

	public function install() {
		$q = new w2p_Database_Query;
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

    protected function _compactModuleUIOrder() {
		$q = new w2p_Database_Query;
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
		$q = new w2p_Database_Query;
		$q->setDelete('modules');
		$q->addWhere('mod_id = ' . (int)$this->mod_id);
		if (!$q->exec()) {
			$q->clear();
			return db_error();
		} else {
			$perms = &$GLOBALS['AppUI']->acl();
			if (!isset($this->mod_admin)) {
				$this->mod_admin = 0;
            }
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

		$q = new w2p_Database_Query;
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
			$q = new w2p_Database_Query;
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

    public function check() {
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::deploy-check failed - ';

        return $errorArray;
    }

    public function validate() {
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::deploy-validate failed - ';

        if (!is_writable($this->modules)) {
            $errorArray['mod_write'] = $baseErrorMsg . 'the modules directory is not writeable';
        }
        if ($this->filetype != 'application/zip') {
            $errorArray['bad_type'] = $baseErrorMsg . 'this module format is not currently supported';
        }
        if ($this->filesize > 250000) {
            $errorArray['big_file'] = $baseErrorMsg . 'this module is bigger than allowed for this installer';
        }

        return $errorArray;
    }

    public function deploy(array $fileinfo) {
        $this->filename = $fileinfo['tmp_name'];
        $this->filetype = $fileinfo['type'];
        $this->filesize = $fileinfo['size'];

        $errorMsgArray = $this->validate();
        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        if (function_exists('zip_open')) {
            $zip = new ZipArchive;
            $zip->open($this->filename);
            $numFiles = $zip->numFiles;

            if ($numFiles > 0) {
                $moduleDir = $zip->getNameIndex($numFiles-1);
                $moduleName = substr($moduleDir, 0, -1);

//TODO: move module validation to check()
                if ($zip->locateName($moduleDir.'setup.php') === false) {
                    $errorMsgArray['missing_setup'] = 'This module is not well-formed, missing: '.$moduleDir.'setup.php';
                }
                if ($zip->locateName($moduleDir.'index.php') === false) {
                    $errorMsgArray['missing_index'] = 'This module is not well-formed, missing: '.$moduleDir.'index.php';
                }
                if ($zip->locateName($moduleDir.$moduleName.'.class.php') === false) {
                    $errorMsgArray['missing_class'] = 'This module is not well-formed, missing: '.$moduleDir.$moduleName.'.class.php';
                }
                if (count($errorMsgArray) > 0) {
                    return $errorMsgArray;
                }
                $zip->extractTo($this->modules);
                for ($i = 1; $i < $numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    $compFileSize = strlen($zip->getFromIndex($i));
                    $fullFileSize = filesize($this->modules.$filename);
                    if ($compFileSize > 0 && $compFileSize != $fullFileSize) {
                        $this->cleanUp($moduleDir);
                        $errorMsgArray['bad_size'] = 'This module failed filesize validation';
                        break;
                    }
                }
                if (count($errorMsgArray) > 0) {
                    return $errorMsgArray;
                }
            }
            $zip->close($this->filename);
            unlink($this->filename);
        }
        return true;
    }

    protected function cleanUp($moduleName) {
        $modulePath = $this->modules.$moduleName;

        $dir = new DirectoryIterator($modulePath);
        foreach ($dir as $file) {
            if (!$file->isDot()) {
                unlink($modulePath.'/'.$file);
            }
        }
        rmdir($modulePath);
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
 * @deprecated
 */
class CConfig extends w2p_Core_Config { }

class bcode extends w2p_Core_BaseObject {
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

		$q = new w2p_Database_Query;
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

        $q = new w2p_Database_Query;
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