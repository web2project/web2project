<?php
/**
 * Necessary global variables 
 */
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';

// Need this to test actions that require permissions.
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/classes/date.class.php';
require_once 'PHPUnit/Framework.php';
/**
 * DateTest Class.
 * 
 * Class to test the date include
 * @author D. Keith Casey, Jr.
 * @package web2project
 * @subpackage unit_tests
 */
class Date_Test extends PHPUnit_Framework_TestCase 
{
	public function testConvertTZ()
	{

		$myDate1 = new CDate('US/Eastern');
		$this->assertEquals($myDate1, new CDate('US/Eastern'));

		$myDate2 = new CDate('CST');
		$myDate2->convertTZ('EST');
		$this->assertEquals($myDate2->hour, $myDate1->hour+1);
		$this->assertEquals($myDate2->minute, $myDate1->minute);
		
		$myDate2->convertTZ('PST');
		$this->assertEquals($myDate2->hour, $myDate1->hour-2);
	}
}