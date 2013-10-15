<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$config = array();
$config['mod_name'] = 'Calendar';
$config['mod_version'] = '3.1.0';
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

        $q = $this->_getQuery();

        // NOTE: All cases should fall through so all updates are executed.
        switch ($old_version) {
            case '3.0.0':
                $q = new w2p_Database_Query();
                $q->addTable('modules');
                $q->addUpdate('mod_directory', 'events');
                $q->addWhere("mod_directory = 'calendar'");
                $q->exec();

                $q = new w2p_Database_Query();
                $q->addTable('gacl_axo');
                $q->addUpdate('value', 'events');
                $q->addWhere("value = 'calendar'");
                $q->exec();

                $q = new w2p_Database_Query();
                $q->addTable('gacl_axo');
                $q->addUpdate('section_value', 'events');
                $q->addWhere("section_value = 'calendar'");
                $q->exec();

                $q = new w2p_Database_Query();
                $q->addTable('gacl_axo_map');
                $q->addUpdate('value', 'events');
                $q->addWhere("value = 'calendar'");
                $q->exec();

                $q = new w2p_Database_Query();
                $q->addTable('gacl_axo_map');
                $q->addUpdate('section_value', 'events');
                $q->addWhere("section_value = 'calendar'");
                $q->exec();

                $q = new w2p_Database_Query();
                $q->addTable('gacl_permissions');
                $q->addUpdate('module', 'events');
                $q->addWhere("module = 'calendar'");
                $q->exec();

                $q = new w2p_Database_Query();
                $q->addTable('module_config');
                $q->addUpdate('module_name', 'events');
                $q->addWhere("module_name = 'calendar'");
                $q->exec();
            default:
                break;
        }
        return $result;
    }
}