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
require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';

// Need this to test actions that require permissions.
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/classes/permissions.class.php';
require_once W2P_BASE_DIR . '/includes/session.php';
require_once W2P_BASE_DIR . '/classes/CustomFields.class.php';
require_once W2P_BASE_DIR . '/modules/companies/companies.class.php';
require_once W2P_BASE_DIR . '/modules/projects/projects.class.php';
require_once W2P_BASE_DIR . '/modules/departments/departments.class.php';
require_once 'PHPUnit/Framework.php';
/**
 * DateTest Class.
 * 
 * Class to test the date include
 * @author D. Keith Casey, Jr.
 * @package web2project
 * @subpackage unit_tests
 */
class w2Pacl_Test extends PHPUnit_Framework_TestCase 
{
	public function testNothing()
	{
		$this->markTestIncomplete("w2Pacl_Test doesn't do anything yet.");
	}
}