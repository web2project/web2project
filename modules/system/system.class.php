<?php

/**
 * @package     web2project\modules\core
 */

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
                $AppUI = new w2p_Core_CAppUI();
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
 * @deprecated
 */
class CPreferences extends w2p_Core_Preferences {
	public function __construct() {
		parent::__construct();
        trigger_error("CPreferences has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Core_Preferences instead.", E_USER_NOTICE );
	}
}

/**
 * @deprecated
 */
class CConfig extends w2p_Core_Config { }