<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

$config = array();
$config['mod_name'] = 'Reports';
$config['mod_version'] = '3.0.0';
$config['mod_directory'] = 'reports';
$config['mod_setup_class'] = 'CSetupReports';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Reports';
$config['mod_ui_icon'] = 'printer.png';
$config['mod_description'] = 'A module for reports';

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

/**
 * Class CSetupReports
 *
 * @package     web2project\modules\misc
 */
class CSetupReports extends w2p_System_Setup { }