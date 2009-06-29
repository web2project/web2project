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
class Main_Functions_Test extends PHPUnit_Framework_TestCase 
{
	public function testW2PgetParam()
	{
		global $AppUI;
		$params = array('m' => 'projects', 'a' => 'view', 'v' => '<script>alert</script>', 
				'html' => '<div onclick="doSomething()">asdf</div>', '<script>' => 'Something Nasty');

		$this->assertEquals('projects', w2PgetParam($params, 'm'));

		$this->assertEquals('', w2PgetParam($params, 'NotGonnaBeThere'));

		$this->assertEquals('Some Default', w2PgetParam($params, 'NotGonnaBeThere', 'Some Default'));

		$this->markTestIncomplete("Currently w2PgetParam redirects for tainted names.. what do we do there?");
		
		$this->markTestIncomplete("Currently w2PgetParam redirects for tainted values.. what do we do there?");
	}
	
	public function testW2PgetCleanParam()
	{
		global $AppUI;
		$params = array('m' => 'projects', 'a' => 'view', 'v' => '<script>alert</script>', 
				'html' => '<div onclick="doSomething()">asdf</div>', '<script>' => 'Something Nasty');

		$this->assertEquals('projects', w2PgetCleanParam($params, 'm'));

		$this->assertEquals('', w2PgetCleanParam($params, 'NotGonnaBeThere'));

		$this->assertEquals('Some Default', w2PgetCleanParam($params, 'NotGonnaBeThere', 'Some Default'));

		$this->assertEquals($params['v'], w2PgetCleanParam($params, 'v', ''));

		$this->assertEquals($params['html'], w2PgetCleanParam($params, 'html', ''));

		$this->assertEquals($params['<script>'], w2PgetCleanParam($params, '<script>', ''));

		$this->markTestIncomplete("This function does *nothing* for tainted values and I suspect it should...");
	}	
}