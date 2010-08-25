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
global $_DATE_TIMEZONE_DATA;

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
        $date       = new CDate();
        $datetime   = new DateTime();

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
     * Tests constructor with a datetime, but no timezone
     */
    public function testConstructorDateTimeNoTz()
    {
        $date       = new CDate('2010-08-07 11:00:00');
        $datetime   = new DateTime('2010-08-07 11:00:00');

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
     * Tests constructor with a datetime and timezone
     */
    public function testConstructorDateTimeTz()
    {
        $date       = new CDate('2010-08-07 11:00:00', 'America/Halifax');
        $datetime   = new DateTime('2010-08-07 11:00:00', new DateTimeZone('America/Halifax'));

        $this->assertType('CDate',                      $date);
        $this->assertEquals($datetime->format('Y'),     $date->year);
        $this->assertEquals($datetime->format('m'),     $date->month);
        $this->assertEquals($datetime->format('d'),     $date->day);
        $this->assertEquals($datetime->format('H'),     $date->hour);
        $this->assertEquals($datetime->format('i'),     $date->minute);
        $this->assertEquals($datetime->format('s'),     $date->second);
        $this->assertEquals(-14400000,                  $date->tz['offset']);
        $this->assertEquals('Atlantic Standard Time',   $date->tz['longname']);
        $this->assertEquals('AST',                      $date->tz['shortname']);
        $this->assertEquals('Atlantic Daylight Time',   $date->tz['dstlongname']);
        $this->assertEquals('ADT',                      $date->tz['dstshortname']);
        $this->assertEquals('America/Halifax',          $date->tz['id']);
        $this->assertTrue($date->tz['hasdst']);
    }

    /**
     * Tests constructor with an invalid datetime
     */
    public function testConstructorInvalidDateTime()
    {
        $date = new CDate('2010-35-35 28:65:85');

        $this->assertType('CDate',                  $date);
        $this->assertEquals(2010,                   $date->year);
        $this->assertEquals(35,                     $date->month);
        $this->assertEquals(35,                     $date->day);
        $this->assertEquals(28,                     $date->hour);
        $this->assertEquals(65,                     $date->minute);
        $this->assertEquals(85,                     $date->second);
        $this->assertEquals(0,                      $date->tz['offset']);
        $this->assertEquals('Greenwich Mean Time',  $date->tz['longname']);
        $this->assertEquals('GMT',                  $date->tz['shortname']);
        $this->assertEquals('British Summer Time',  $date->tz['dstlongname']);
        $this->assertEquals('BST',                  $date->tz['dstshortname']);
        $this->assertEquals('Europe/London',        $date->tz['id']);
        $this->assertEquals('2010-35-35 28:65:85',  $date->getDate()); // WTF?
        $this->assertTrue($date->tz['hasdst']);
    }

    /**
     * Tests constructor with an invalid timezone
     *
     * expectedException PHPUnit_Framework_Error
     */
    public function testConstructorInvalidTimezone()
    {
        $date = new CDate('2010-08-07 22:10:27', 'Halifax');
        $datetime = new DateTime('2010-08-07 22:10:27');

        $this->assertType('CDate',                  $date);
        $this->assertEquals($datetime->format('Y'), $date->year);
        $this->assertEquals($datetime->format('m'), $date->month);
        $this->assertEquals($datetime->format('d'), $date->day);
        $this->assertEquals($datetime->format('H'), $date->hour);
        $this->assertEquals($datetime->format('i'), $date->minute);
        $this->assertEquals($datetime->format('s'), $date->second);
        $this->assertEquals('Halifax',              $date->tz['id']);
        $this->assertFalse(isset($data->tz['offset']));
        $this->assertFalse(isset($data->tz['longname']));
        $this->assertFalse(isset($data->tz['shortname']));
        $this->assertFalse(isset($data->tz['dstlongname']));
        $this->assertFalse(isset($data->tz['dstshortname']));
        $this->assertFalse(isset($data->tz['hasdst']));
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
