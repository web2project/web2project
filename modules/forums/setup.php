<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    update version information

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Forums';
$config['mod_version'] = '1.0.0';
$config['mod_directory'] = 'forums';
$config['mod_setup_class'] = 'CSetupForums';
$config['mod_type'] = 'core';
$config['mod_ui_name'] = 'Forums';
$config['mod_ui_icon'] = 'communicate.gif';
$config['mod_description'] = '';
$config['mod_config'] = true; // show 'configure' link in viewmods

if ($a == 'setup') {
    echo w2PshowModuleConfig($config);
}

/**
 * Class CSetupForums
 *
 * @package     web2project\modules\misc
 */
class CSetupForums extends w2p_System_Setup
{
    public function configure() { // configure this module
        global $AppUI;
        $AppUI->redirect('m=forums&a=configure'); // load module specific configuration page

        return true;
    }
}
