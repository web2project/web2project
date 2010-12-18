<?php /* $Id: setup.php 1522 2010-12-08 05:08:07Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/reports/setup.php $ */
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
	echo w2PshowModuleConfig($config);
}

class CSetupReports {

	public function install() {
		return true;
	}

	public function remove() {
		return true;
	}

	public function upgrade() {
		return true;
	}
}