<?php
/**
 * Necessary global variables
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DataSetFilter.php';
/**
 * PermissionsTest Class.
 *
 * Class to test the permissions class
 * @author D. Keith Casey, Jr.
 * @package web2project
 * @subpackage unit_tests
 */
class w2p_Extensions_Permissions_Test extends PHPUnit_Framework_TestCase
{
	public function testDebugText()
	{
		$perms = new w2p_Extensions_Permissions();

		$perms->debug_text('test message');

		$this->assertEquals('test message', $perms->msg());
	}
}