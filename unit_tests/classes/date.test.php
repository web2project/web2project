<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing Date functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Date
 * @package     web2project
 * @subpackage  unit_tests
 * @author      D. Keith Casey, Jr.
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */

/**
 * Necessary global variables
 */
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';

/*
 * Need this to test actions that require permissions.
 */
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

    /**
     * Tests constructor without arguments
     */
    public function testConstructorNoDateTimeNoTz()
    {
        $date = new CDate();
        $datetime = new DateTime();

        $this->assertType('CDate',                  $date);
        $this->assertEquals($datetime->format('Y'), $date->year);
        $this->assertEquals($datetime->format('m'), $date->month);
        $this->assertEquals($datetime->format('d'), $date->day);
        $this->assertEquals($datetime->format('H'), $date->hour);
        $this->assertEquals($datetime->format('i'), $date->minute);
        $this->assertEquals($datetime->format('s'), $date->second);
        $this->assertEquals(0,                      $date->tz['offset']);
        $this->assertEquals('Greenwich Mean Time',  $date->tz['longname']);
        $this->assertEquals('GMT',                  $date->tz['shortname']);
        $this->assertEquals('British Summer Time',  $date->tz['dstlongname']);
        $this->assertEquals('BST',                  $date->tz['dstshortname']);
        $this->assertEquals('Europe/London',        $date->tz['id']);
        $this->assertTrue($date->tz['hasdst']);
    }

    /**
     * Tests converting between timezones
     */
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
