<?php
/**
 * @package     web2project\system
 */

class w2p_System_Module extends w2p_Core_BaseObject {
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
        parent::__construct('modules', 'mod_id', 'system');
        $this->modules  = W2P_BASE_DIR.'/modules/';
    }

    public function install() {
        $q = $this->_getQuery();
        $q->addTable('modules');
        $q->addQuery('mod_id');
        $q->addWhere('mod_directory = \'' . $this->mod_directory . '\'');
        $result = (int) $q->loadResult();
        if ($result) {
            // the module is already installed
            // TODO: check for older version - upgrade
            return false;
        }
        // This arbitrarily places it at the end of the list.
        $this->mod_ui_order = 100;
        $this->store();

        $this->_compactModuleUIOrder();

        $this->_perms->addModule($this->mod_directory, $this->mod_name);
        // Determine if it is an admin module or not, then add it to the correct set
        if (!isset($this->mod_admin)) {
            $this->mod_admin = 0;
        }
        if ($this->mod_admin) {
            $this->_perms->addGroupItem($this->mod_directory, "admin");
        } else {
            $this->_perms->addGroupItem($this->mod_directory, "non_admin");
        }
        if (isset($this->permissions_item_table) && $this->permissions_item_table) {
            $this->_perms->addModuleSection($this->permissions_item_table);
        }
        return true;
    }

    protected function _compactModuleUIOrder() {
        $q = $this->_getQuery();
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
        $q = $this->_getQuery();
        $q->setDelete('modules');
        $q->addWhere('mod_id = ' . (int)$this->mod_id);
        if (!$q->exec()) {
            $q->clear();
            return db_error();
        } else {
            if (!isset($this->mod_admin)) {
                $this->mod_admin = 0;
            }
            if ($this->mod_admin) {
                $this->_perms->deleteGroupItem($this->mod_directory, 'admin');
            } else {
                $this->_perms->deleteGroupItem($this->mod_directory, 'non_admin');
            }
            $this->_perms->deleteModuleItems($this->mod_directory);
            $this->_perms->deleteModule($this->mod_directory);
            if (isset($this->permissions_item_table) && $this->permissions_item_table) {
                $this->_perms->deleteModuleSection($this->permissions_item_table);
            }
            $this->_compactModuleUIOrder();
            return null;
        }
    }

    public function move($dirn) {
        $new_ui_order = $this->mod_ui_order;

        $q = $this->_getQuery();
        $q->addTable('modules');
        $q->addWhere('mod_id <> ' . (int)$this->mod_id);
        $q->addOrder('mod_ui_order');
        $modules = $q->loadList();
        $q->clear();

        if ($dirn == 'moveup') {
            $new_ui_order--;
        } elseif ($dirn == 'movedn') {
            $new_ui_order++;
        } elseif ($dirn == 'movefirst') {
            $new_ui_order = 1;
        } elseif ($dirn == 'movelast') {
            $new_ui_order = count($modules) + 1;
        }

        if ($new_ui_order && ($new_ui_order <= count($modules) + 1)) { //make sure we aren't going "up" to 0
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

        return $errorArray;
    }

    public function canEdit() {
        return $this->_perms->checkModule($this->_tbl_module, 'edit');
    }
    public function canCreate() {
        return $this->_perms->checkModule($this->_tbl_module, 'add');
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
                $moduleDir = $zip->getNameIndex(0);
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
            $zip->close();
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

//TODO: break these out to a separate w2p_Core_ModuleConfig class
    public function getCustomizableViews($module) {
        $q = $this->_getQuery();
        $q->addTable('module_config');
        $q->addQuery('distinct(module_config_name)');
        $q->addWhere("module_name = '$module'");
        $q->addOrder('module_config_name ASC');
        return $q->loadColumn();
    }

    public function loadSettings($module, $configName = 'index_list') {
        $q = $this->_getQuery();
        $q->addTable('module_config');
        $q->addQuery('module_config_value, module_config_text');
        $q->addWhere("module_name = '$module'");
        $q->addWhere("module_config_name = '$configName'");
        $q->addOrder('module_config_order, module_config_value ASC');

        return $q->loadHashList();
    }
    
    public function storeSettings($moduleName, $configName, $displayColumns, $displayText) {
        if ('' == $moduleName || '' == $configName) {
            return false;
        }

        $i = 0;
        $q = $this->_getQuery();
        foreach ($displayColumns as $index => $column) {
            $q->addTable('module_config');
            $q->addInsert('module_name',            $moduleName);
            $q->addInsert('module_config_name',        $configName);
            $q->addInsert('module_config_value',    $column);
            $q->addInsert('module_config_text',        $displayText[$index]);
            $q->addInsert('module_config_order',    $i);
            $q->exec();
            $q->clear();
            $i++;
        }
    }

    /*
     * @deprecated
     */
    public static function getSettings($module, $configName = '') {
        $q = new w2p_Database_Query();
        $q->addTable('module_config');
        $q->addQuery('module_config_value, module_config_text');
        $q->addWhere("module_name = '$module'");
        if ('' != $configName) {
            $q->addWhere("module_config_name = '$configName'");
        }
        $q->addOrder('module_config_order, module_config_value ASC');
        return $q->loadHashList();
    }

    /*
     * @deprecated
     */
    public static function saveSettings($moduleName, $configName,
            $displayColumns, $displayOrder, $configValue, $configText) {

        if ('' == $moduleName || '' == $configName) {
            return false;
        }

        $q = new w2p_Database_Query;
        $q->setDelete('module_config');
        $q->addWhere("module_name = '$moduleName'");
        $q->addWhere("module_config_name = '$configName'");
        $q->exec();
        $q->clear();

        $i = 0;
        foreach ($configValue as $index => $field) {
            if (isset($displayColumns[$field])) {
                $q->addTable('module_config');
                $q->addInsert('module_name',            $moduleName);
                $q->addInsert('module_config_name',        $configName);
                $q->addInsert('module_config_value',    $field);
                $q->addInsert('module_config_text',        $configText[$index]);
                $q->addInsert('module_config_order',    (int) $displayOrder[$field]);
                $q->exec();
                $q->clear();
                $i++;
            }
        }
        return true;
    }
}