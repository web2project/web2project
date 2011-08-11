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
                $difference = 0;//$nowDate->diff($systemDate)->format('%d');
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

        $this->_error = array();
		$q = $this->_query;
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
        $this->_error = array();

        $q = $this->_query;
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
