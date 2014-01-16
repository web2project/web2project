<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$config = array();
$config['mod_name'] = 'SmartSearch';
$config['mod_version'] = '3.0.0';
$config['mod_directory'] = 'smartsearch';
$config['mod_setup_class'] = 'CSetupSmartsearch';
$config['mod_type'] = 'user';
$config['mod_ui_name']     = $config['mod_name'];
$config['mod_ui_icon'] = 'kfind.png';
$config['mod_description'] = 'A module to search keywords and find the needle in the haystack';

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

/**
 * Class CSetupSmartsearch
 *
 * @package     web2project\modules\core
 */
class CSetupSmartsearch extends w2p_System_Setup { }