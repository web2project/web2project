<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
* Name:      Forums
* Directory: forums
* Version:   1.0.0
* Class:     core
* UI Name:   Forums
* UI Icon:   communicate.gif
*/

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Projects';
$config['mod_version'] = '1.0.0';
$config['mod_directory'] = 'projects';
$config['mod_setup_class'] = 'CSetupProjects';
$config['mod_type'] = 'core';
$config['mod_ui_name'] = 'Projects';
$config['mod_ui_icon'] = 'applet3-48.png';
$config['mod_description'] = '';
$config['mod_config'] = true; // show 'configure' link in viewmods

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

class CSetupProjects {

	public function configure() { // configure this module
		global $AppUI;
		$AppUI->redirect('m=projects&a=configure'); // load module specific configuration page
		return true;
	}
}