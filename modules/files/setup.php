<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    update version information

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name']         = 'Files';
$config['mod_version']      = '4.0.0';
$config['mod_directory']    = 'files';
$config['mod_setup_class']  = 'CSetupFiles';
$config['mod_type']         = 'core';
$config['mod_ui_name']      = 'Files';
$config['mod_ui_icon']      = 'icon.png';
$config['mod_description']  = '';
$config['mod_config']       = true; // show 'configure' link in viewmods

if ($a == 'setup') {
    echo w2PshowModuleConfig($config);
}

/**
 * Class CSetupFiles
 *
 * @package     web2project\modules\core
 */
class CSetupFiles extends w2p_System_Setup
{
    public function configure() { // configure this module
        global $AppUI;
        $AppUI->redirect('m=files&a=configure'); // load module specific configuration page

        return true;
    }
}
