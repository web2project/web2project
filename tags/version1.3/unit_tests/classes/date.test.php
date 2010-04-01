<?php
/**
 * Necessary global variables 
 */
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';

// Need this to test actions that require permissions.
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

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

		$myDate1 = new CDate('', 'US/Eastern');
		$this->assertEquals($myDate1, new CDate('', 'US/Eastern'));

		$myDate2 = new CDate('', 'CST');
		$myDate2->convertTZ('EST');

		//This tweaks the test data in case the +1 is across the day change.
		$tmpHour = ($myDate1->hour+1 >=24) ? $myDate1->hour+1-24 : $myDate1->hour+1;
		$this->assertEquals($tmpHour, $myDate2->hour);
		$this->assertEquals($myDate1->minute, $myDate2->minute);
		
		$myDate2->convertTZ('PST');
		$tmpHour = ($myDate1->hour-2 < 0) ? $myDate1->hour-2+24 : $myDate1->hour-2;
		$this->assertEquals($tmpHour, $myDate2->hour);
	}
}