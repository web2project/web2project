<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$config = array();
$config['mod_name'] = 'Calendar';
$config['mod_version'] = '3.0.1';
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
//TODO: wire the permissions change
//TODO: wire the module update
                break;
            default:
                break;
        }
        return $result;
    }
}