<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

$config = array();
$config['mod_name'] = 'Reports';
$config['mod_version'] = '0.1';
$config['mod_directory'] = 'reports';
$config['mod_setup_class'] = 'CSetupReports';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Reports';
$config['mod_ui_icon'] = 'printer.png';
$config['mod_description'] = 'A module for reports';

if ($a == 'setup') {
	echo dPshowModuleConfig($config);
}

class CSetupReports {

	public function install() {
		return null;
	}

	public function remove() {
		return null;
	}

	public function upgrade() {
		return null;
	}
}
?>