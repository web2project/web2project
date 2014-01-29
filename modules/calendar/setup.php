<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$config = array();
$config['mod_name'] = 'Calendar';
$config['mod_version'] = '3.1.1';
$config['mod_directory'] = 'events';                     // tell web2Project where to find this module
$config['mod_setup_class'] = 'CSetupEvents';             // the name of the PHP setup class (used below)
$config['mod_type'] = 'user';                           // 'core' for modules distributed with w2P by standard, 'user' for additional modules from dotmods
$config['mod_ui_name'] = 'Calendar';
$config['mod_ui_icon'] = 'icon.png';
$config['mod_description'] = 'Calendar';
$config['mod_config'] = false;                          // show 'configure' link in viewmods
$config['mod_main_class'] = 'CEvent';
$config['permissions_item_table'] = 'events';
$config['permissions_item_field'] = 'event_id';
$config['permissions_item_label'] = 'event_name';

/**
 * Class CSetupEvents
 *
 * @package     web2project\modules\deprecated
 */
class CSetupEvents extends w2p_System_Setup
{
    public function remove()
    {
        return false;
    }

    public function install()
    {
        return false;
    }

    public function upgrade($old_version) {
        $result = false;

        // NOTE: All cases should fall through so all updates are executed.
        switch ($old_version) {
            case '3.0.0':
                $this->_renameModule('modules', 'mod_directory');

                $this->_renameModule('gacl_axo', 'value');
                $this->_renameModule('gacl_axo', 'section_value');
                $this->_renameModule('gacl_axo_map', 'value');
                $this->_renameModule('gacl_axo_map', 'section_value');
                $this->_renameModule('gacl_permissions', 'module');

                $this->_renameModule('module_config', 'module_name');
            case '3.1.0':
                $q = $this->_getQuery();
                $q->addTable('config');
                $q->addUpdate('config_value', 'events');
                $q->addWhere("config_name = 'default_view_m'");
                $q->addWhere("config_value = 'calendar'");
                $q->exec();
            default:
                break;
        }
        return $result;
    }

    /**
     * @return boolean
     */
    public function _renameModule($table, $field)
    {
        $q = $this->_getQuery();
        $q->addTable($table);
        $q->addUpdate($field, 'events');
        $q->addWhere("$field = 'calendar'");
        return $q->exec();
    }
}