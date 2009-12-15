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

// Need this to test actions that require permissions.
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
/**
 * Main_Functions_Test Class.
 * 
 * Class to test the main_functions include
 * @author D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
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

		//$this->markTestIncomplete("Currently w2PgetParam redirects for tainted names.. what do we do there?");
		
		//$this->markTestIncomplete("Currently w2PgetParam redirects for tainted values.. what do we do there?");
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

		//$this->markTestIncomplete("This function does *nothing* for tainted values and I suspect it should...");
	}

	public function testArrayMerge()
	{
		$array1 = array('a', 'b', 'c', 4 => 'd', 5 => 'e');
		$array2 = array('z', 6 => 'y', 7 => 'x', 4 => 'w', 5 => 'v');
		$newArray = arrayMerge($array1, $array2);

		$this->assertEquals('b', $newArray[1]);		//	Tests no overwrite
		$this->assertEquals('w', $newArray[4]);		//	Tests explicit overwrite
		$this->assertEquals('z', $newArray[0]);		//	Tests conincidental overwrite
	}
	public function testW2PgetConfig()
	{
		global $w2Pconfig;

		$this->assertEquals('web2project.net', w2PgetConfig('site_domain'));
		$this->assertEquals(null, w2PgetConfig('NotGonnaBeThere'));
		$this->assertEquals('Some Default', w2PgetConfig('NotGonnaBeThere', 'Some Default'));
	}
	public function testConvert2days()
	{		
		$hours = 1;		
		$this->assertEquals(0.125, convert2days($hours, 0));

		$hoursIndicator = 1;
		$hours = 8;
		$this->assertEquals(1, convert2days($hours, $hoursIndicator));

		$dayIndicator = 24;
		$days = 1;
		$this->assertEquals(1, convert2days($days, $dayIndicator));
	}
  
  public function test__autoload()
  {
  	global $AppUI;

    $this->assertTrue(class_exists('CProject'));
    $search = new smartsearch();
    $this->assertTrue(class_exists('smartsearch'));
  }


  /**
   * Tests the proper creation of a link
   */
  public function testURL()
  {
    global $AppUI;

    $target = '<a href="http://web2project.net" target="_new">http://web2project.net</a>';
    $linkText = w2p_url('http://web2project.net');
    $this->assertEquals($target, $linkText);

    $target = '';
    $linkText = w2p_url('');
    $this->assertEquals($target, $linkText);

    $target = '<a href="http://web2project.net" target="_new">web2project</a>';
    $linkText = w2p_url('http://web2project.net', 'web2project');
    $this->assertEquals($target, $linkText);
  }

  /**
   * Tests the proper creation of an email link
   */
  public function testEmail()
  {
    global $AppUI;

    $target = '<a href="mailto:test@test.com">test@test.com</a>';
    $linkText = w2p_email('test@test.com');
    $this->assertEquals($target, $linkText);

    $target = '';
    $linkText = w2p_email('');
    $this->assertEquals($target, $linkText);

    $target = '<a href="mailto:test@test.com">web2project</a>';
    $linkText = w2p_email('test@test.com', 'web2project');
    $this->assertEquals($target, $linkText);
  }
}